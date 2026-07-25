#!/usr/bin/env bash

set -Eeuo pipefail
umask 027

repository_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
deploy_root="${EXPLORIA_DEPLOY_ROOT:-}"
deploy_ref="${EXPLORIA_DEPLOY_REF:-}"
health_url="${EXPLORIA_HEALTH_URL:-}"
verified_backup_path="${EXPLORIA_VERIFIED_BACKUP_PATH:-}"
backup_max_age_minutes="${EXPLORIA_BACKUP_MAX_AGE_MINUTES:-1440}"

fail() {
    printf 'Deployment refused: %s\n' "$1" >&2
    exit 1
}

if [[ "$(id -u)" -eq 0 ]]; then
    fail 'run as the dedicated application user, not root.'
fi

[[ -n "$deploy_root" ]] || fail 'EXPLORIA_DEPLOY_ROOT is required.'
[[ "$deploy_root" == */exploria-staging ]] || fail 'EXPLORIA_DEPLOY_ROOT must end with /exploria-staging.'
[[ -n "$deploy_ref" ]] || fail 'EXPLORIA_DEPLOY_REF is required.'
[[ "$health_url" =~ ^https:// ]] || fail 'EXPLORIA_HEALTH_URL must be an HTTPS /up URL.'
[[ "$health_url" == */up ]] || fail 'EXPLORIA_HEALTH_URL must end with /up.'
[[ "$backup_max_age_minutes" =~ ^[1-9][0-9]*$ ]] || fail 'EXPLORIA_BACKUP_MAX_AGE_MINUTES must be a positive integer.'

for tool in git php composer npm curl tar pg_restore awk; do
    command -v "$tool" >/dev/null 2>&1 || fail "required tool '$tool' was not found."
done

deploy_root="$(mkdir -p "$deploy_root" && cd "$deploy_root" && pwd -P)"
shared_path="$deploy_root/shared"
releases_path="$deploy_root/releases"
current_link="$deploy_root/current"
environment_path="$shared_path/.env"
shared_storage_path="$shared_path/storage"

[[ -f "$environment_path" ]] || fail "shared environment file '$environment_path' is missing."
[[ -d "$shared_storage_path" ]] || fail "shared storage directory '$shared_storage_path' is missing."
[[ -f "$verified_backup_path" ]] || fail 'EXPLORIA_VERIFIED_BACKUP_PATH must point to a verified PostgreSQL archive.'
pg_restore --list "$verified_backup_path" >/dev/null || fail 'the PostgreSQL backup archive is invalid.'

read_environment_value() {
    local key="$1"
    local value

    value="$(
        awk -F= -v requested_key="$key" \
            '$1 == requested_key { print substr($0, index($0, "=") + 1) }' \
            "$environment_path" |
            tail -n 1
    )"
    value="${value%$'\r'}"
    value="${value#\"}"
    value="${value%\"}"
    value="${value#\'}"
    value="${value%\'}"
    printf '%s' "$value"
}

app_environment="$(read_environment_value APP_ENV)"
app_debug="$(read_environment_value APP_DEBUG)"
app_url="$(read_environment_value APP_URL)"
app_key="$(read_environment_value APP_KEY)"
database_connection="$(read_environment_value DB_CONNECTION)"

[[ "$app_environment" == 'staging' ]] || fail 'shared .env must contain APP_ENV=staging.'
[[ "$app_debug" == 'false' ]] || fail 'shared .env must contain APP_DEBUG=false.'
[[ "$app_url" =~ ^https:// ]] || fail 'shared APP_URL must use HTTPS.'
[[ "${app_url%/}/up" == "$health_url" ]] || fail 'EXPLORIA_HEALTH_URL must match APP_URL plus /up.'
[[ -n "$app_key" ]] || fail 'shared APP_KEY must be configured.'
[[ "$database_connection" == 'pgsql' ]] || fail 'shared .env must contain DB_CONNECTION=pgsql.'

if ! find "$verified_backup_path" -mmin "-$backup_max_age_minutes" -print -quit | grep -q .; then
    fail "the verified backup is older than $backup_max_age_minutes minutes."
fi

cd "$repository_root"
[[ -z "$(git status --porcelain)" ]] || fail 'the deployment repository must have a clean worktree.'

git fetch --prune origin
deploy_commit="$(git rev-parse --verify "$deploy_ref^{commit}")" || fail "deployment ref '$deploy_ref' does not exist."
release_name="$(date -u +'%Y%m%d-%H%M%S')-${deploy_commit:0:12}"
release_path="$releases_path/$release_name"

mkdir -p "$releases_path"
[[ ! -e "$release_path" ]] || fail "release '$release_path' already exists."
mkdir "$release_path"

git archive "$deploy_commit" | tar -x -C "$release_path"
ln -s "$environment_path" "$release_path/.env"

if [[ -d "$release_path/storage" && ! -L "$release_path/storage" ]]; then
    cp -a "$release_path/storage/." "$shared_storage_path/"
    mv "$release_path/storage" "$release_path/storage.scaffold"
fi

ln -s "$shared_storage_path" "$release_path/storage"

printf '%s\n' "$deploy_commit" >"$release_path/REVISION"
printf '%s\n' "$(date -u +'%Y-%m-%dT%H:%M:%SZ')" >"$release_path/DEPLOYED_AT"

cd "$release_path"
composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader
npm ci
npm run build

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan storage:link

previous_release=''
if [[ -L "$current_link" ]]; then
    previous_release="$(readlink -f "$current_link")"
fi

maintenance_started=false
release_switched=false

recover_previous_release() {
    local status=$?

    if [[ "$status" -eq 0 ]]; then
        return
    fi

    if [[ "$release_switched" == true && -n "$previous_release" && -d "$previous_release" ]]; then
        rollback_link="$deploy_root/current.rollback"
        ln -s "$previous_release" "$rollback_link"
        mv -Tf "$rollback_link" "$current_link"
        php "$previous_release/artisan" up || true
        php "$previous_release/artisan" queue:restart || true
    elif [[ "$maintenance_started" == true && -n "$previous_release" && -d "$previous_release" ]]; then
        php "$previous_release/artisan" up || true
    fi

    printf "Deployment failed. Previous release restored when available. Failed release kept at '%s' for inspection.\n" "$release_path" >&2
    exit "$status"
}

trap recover_previous_release ERR

if [[ -n "$previous_release" && -f "$previous_release/artisan" ]]; then
    php "$previous_release/artisan" down --retry=60
    maintenance_started=true
fi

php artisan migrate --force --no-interaction
php artisan optimize
php artisan exploria:production-readiness --json
php artisan exploria:demo-readiness --json

next_link="$deploy_root/current.next"
ln -s "$release_path" "$next_link"
mv -Tf "$next_link" "$current_link"
release_switched=true

php artisan queue:restart
php artisan up
maintenance_started=false

curl \
    --fail \
    --silent \
    --show-error \
    --location \
    --max-time 20 \
    "$health_url" >/dev/null

trap - ERR

printf "Staging deployment completed.\nRevision: %s\nRelease: %s\nHealth: %s\n" \
    "$deploy_commit" \
    "$release_path" \
    "$health_url"
