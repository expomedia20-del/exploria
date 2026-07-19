param(
    [string]$Database = $env:EXPLORIA_PG_DATABASE,
    [string]$Username = $env:EXPLORIA_PG_USERNAME,
    [string]$Password = $env:EXPLORIA_PG_PASSWORD,
    [string]$HostName = $(if ($env:EXPLORIA_PG_HOST) { $env:EXPLORIA_PG_HOST } else { '127.0.0.1' }),
    [int]$Port = $(if ($env:EXPLORIA_PG_PORT) { [int]$env:EXPLORIA_PG_PORT } else { 5432 })
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($Database) -or [string]::IsNullOrWhiteSpace($Username)) {
    throw 'EXPLORIA_PG_DATABASE and EXPLORIA_PG_USERNAME are required.'
}

if ($Database -notmatch '(^|[_-])test(ing)?$') {
    throw "Refusing destructive test setup: database '$Database' must end with _test, -test, _testing, or -testing."
}

$env:PGPASSWORD = $Password
$resolvedDatabase = (& psql -w -h $HostName -p $Port -U $Username -d $Database -tAc 'select current_database();').Trim()

if ($LASTEXITCODE -ne 0 -or $resolvedDatabase -ne $Database) {
    throw "PostgreSQL connection verification failed for '$Database'."
}

$env:DB_CONNECTION = 'pgsql'
$env:DB_HOST = $HostName
$env:DB_PORT = [string]$Port
$env:DB_DATABASE = $Database
$env:DB_USERNAME = $Username
$env:DB_PASSWORD = $Password

php artisan migrate:fresh --force --no-interaction
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

php vendor/bin/phpunit -c phpunit.pgsql.xml
exit $LASTEXITCODE
