param(
    [string]$Database = $env:EXPLORIA_PG_DATABASE,
    [string]$Username = $env:EXPLORIA_PG_USERNAME,
    [string]$Password = $env:EXPLORIA_PG_PASSWORD,
    [string]$HostName = $(if ($env:EXPLORIA_PG_HOST) { $env:EXPLORIA_PG_HOST } else { '127.0.0.1' }),
    [int]$Port = $(if ($env:EXPLORIA_PG_PORT) { [int]$env:EXPLORIA_PG_PORT } else { 5432 })
)

$ErrorActionPreference = 'Stop'

function Get-PostgresTool {
    param([Parameter(Mandatory = $true)][string]$Name)

    if ($env:EXPLORIA_PG_BIN) {
        $candidate = Join-Path $env:EXPLORIA_PG_BIN $Name
        if (Test-Path -LiteralPath $candidate -PathType Leaf) {
            return $candidate
        }
    }

    $command = Get-Command $Name -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    throw "Required PostgreSQL tool '$Name' was not found. Install PostgreSQL client tools or set EXPLORIA_PG_BIN to the folder that contains psql.exe, pg_dump.exe, and pg_restore.exe."
}

if ([string]::IsNullOrWhiteSpace($Database) -or [string]::IsNullOrWhiteSpace($Username)) {
    throw 'EXPLORIA_PG_DATABASE and EXPLORIA_PG_USERNAME are required.'
}

if ($Database -notmatch '(^|[_-])test(ing)?$') {
    throw "Refusing destructive test setup: database '$Database' must end with _test, -test, _testing, or -testing."
}

$psql = Get-PostgresTool 'psql.exe'

$env:PGPASSWORD = $Password
$resolvedDatabase = (& $psql -w -h $HostName -p $Port -U $Username -d $Database -tAc 'select current_database();').Trim()

if ($LASTEXITCODE -ne 0 -or $resolvedDatabase -ne $Database) {
    throw "PostgreSQL connection verification failed for '$Database'."
}

$env:DB_CONNECTION = 'pgsql'
$env:DB_HOST = $HostName
$env:DB_PORT = [string]$Port
$env:DB_DATABASE = $Database
$env:DB_USERNAME = $Username
$env:DB_PASSWORD = $Password
$env:APP_ENV = 'testing'
$env:APP_DEBUG = 'true'
$env:APP_URL = 'http://localhost'
$env:BROADCAST_CONNECTION = 'null'
$env:CACHE_STORE = 'array'
$env:QUEUE_CONNECTION = 'sync'
$env:SESSION_DRIVER = 'array'
$env:SESSION_SECURE_COOKIE = 'false'
$env:SESSION_HTTP_ONLY = 'true'
$env:OTP_DRIVER = 'local'
$env:OTP_HTTP_ENDPOINT = ''
$env:OTP_HTTP_TOKEN = ''

php artisan migrate:fresh --force --no-interaction
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

php vendor/bin/phpunit -c phpunit.pgsql.xml
exit $LASTEXITCODE
