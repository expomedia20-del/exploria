#!/usr/bin/env bash

set -Eeuo pipefail

backup_path="${1:-}"
database="${EXPLORIA_PG_RESTORE_DATABASE:-}"
username="${EXPLORIA_PG_USERNAME:-}"
password="${EXPLORIA_PG_PASSWORD:-}"
host="${EXPLORIA_PG_HOST:-127.0.0.1}"
port="${EXPLORIA_PG_PORT:-5432}"

for tool in psql pg_restore sha256sum awk; do
    command -v "$tool" >/dev/null 2>&1 || {
        printf "Required PostgreSQL tool '%s' was not found.\n" "$tool" >&2
        exit 1
    }
done

if [[ -z "$backup_path" || -z "$database" || -z "$username" ]]; then
    echo 'Backup path, EXPLORIA_PG_RESTORE_DATABASE, and EXPLORIA_PG_USERNAME are required.' >&2
    exit 1
fi

if [[ ! "$database" =~ (^|[_-])restore[_-]test$ ]]; then
    printf "Refusing restore test: database '%s' must end with _restore_test or -restore-test.\n" "$database" >&2
    exit 1
fi

if [[ ! -f "$backup_path" ]]; then
    printf "Backup archive '%s' does not exist.\n" "$backup_path" >&2
    exit 1
fi

backup_path="$(cd "$(dirname "$backup_path")" && pwd -P)/$(basename "$backup_path")"
checksum_path="$backup_path.sha256"
if [[ ! -f "$checksum_path" ]]; then
    printf "Backup checksum manifest '%s' does not exist.\n" "$checksum_path" >&2
    exit 1
fi

checksum_line_count="$(awk 'NF { count++ } END { print count + 0 }' "$checksum_path")"
expected_checksum="$(awk 'NF { print $1; exit }' "$checksum_path")"
manifest_file_name="$(awk 'NF { sub(/^[^[:space:]]+[[:space:]]+/, ""); print; exit }' "$checksum_path")"
backup_file_name="$(basename "$backup_path")"

if [[ "$checksum_line_count" != '1' || ! "$expected_checksum" =~ ^[[:xdigit:]]{64}$ || "$manifest_file_name" != "$backup_file_name" ]]; then
    echo 'Backup checksum manifest is invalid.' >&2
    exit 1
fi

actual_checksum="$(sha256sum "$backup_path" | awk '{ print $1 }')"
if [[ "${actual_checksum,,}" != "${expected_checksum,,}" ]]; then
    echo 'Backup checksum verification failed.' >&2
    exit 1
fi

pg_restore --list "$backup_path" >/dev/null

export PGPASSWORD="$password"
resolved_database="$(
    psql -w -h "$host" -p "$port" -U "$username" -d "$database" -tAc 'select current_database();'
)"
resolved_database="${resolved_database//[[:space:]]/}"

if [[ "$resolved_database" != "$database" ]]; then
    printf "PostgreSQL connection verification failed for restore database '%s'.\n" "$database" >&2
    exit 1
fi

pg_restore \
    -w \
    -h "$host" \
    -p "$port" \
    -U "$username" \
    -d "$database" \
    --clean \
    --if-exists \
    --exit-on-error \
    "$backup_path"

migration_table_exists="$(
    psql \
        -w \
        -h "$host" \
        -p "$port" \
        -U "$username" \
        -d "$database" \
        -tAc "select to_regclass('public.migrations') is not null;"
)"
migration_table_exists="${migration_table_exists//[[:space:]]/}"

if [[ "$migration_table_exists" != 't' ]]; then
    echo 'Restore verification failed: migrations table is missing.' >&2
    exit 1
fi

printf "Restore verified on isolated database '%s'.\n" "$database"
