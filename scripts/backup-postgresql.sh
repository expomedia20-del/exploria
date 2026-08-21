#!/usr/bin/env bash

set -Eeuo pipefail
umask 077

database="${EXPLORIA_PG_DATABASE:-}"
username="${EXPLORIA_PG_USERNAME:-}"
password="${EXPLORIA_PG_PASSWORD:-}"
host="${EXPLORIA_PG_HOST:-127.0.0.1}"
port="${EXPLORIA_PG_PORT:-5432}"
output_directory="${1:-${EXPLORIA_BACKUP_DIRECTORY:-}}"

for tool in psql pg_dump pg_restore sha256sum; do
    command -v "$tool" >/dev/null 2>&1 || {
        printf "Required PostgreSQL tool '%s' was not found.\n" "$tool" >&2
        exit 1
    }
done

if [[ -z "$database" || -z "$username" || -z "$output_directory" ]]; then
    echo 'EXPLORIA_PG_DATABASE, EXPLORIA_PG_USERNAME, and a backup output directory are required.' >&2
    exit 1
fi

mkdir -p "$output_directory"
output_directory="$(cd "$output_directory" && pwd -P)"

export PGPASSWORD="$password"
resolved_database="$(
    psql -w -h "$host" -p "$port" -U "$username" -d "$database" -tAc 'select current_database();'
)"
resolved_database="${resolved_database//[[:space:]]/}"

if [[ "$resolved_database" != "$database" ]]; then
    printf "PostgreSQL connection verification failed for '%s'.\n" "$database" >&2
    exit 1
fi

timestamp="$(date -u +'%Y%m%d-%H%M%S')"
backup_path="$output_directory/exploria-$database-$timestamp.dump"

pg_dump \
    -w \
    -h "$host" \
    -p "$port" \
    -U "$username" \
    -d "$database" \
    --format=custom \
    --file="$backup_path"

pg_restore --list "$backup_path" >/dev/null

if [[ ! -s "$backup_path" ]]; then
    echo 'Backup archive is empty.' >&2
    exit 1
fi

backup_file_name="$(basename "$backup_path")"
checksum_path="$backup_path.sha256"
(
    cd "$output_directory"
    sha256sum "$backup_file_name" >"$backup_file_name.sha256"
)
chmod 600 "$backup_path" "$checksum_path"

printf '%s\n' "$backup_path"
