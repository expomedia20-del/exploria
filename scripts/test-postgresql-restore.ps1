param(
    [Parameter(Mandatory = $true)]
    [string]$BackupPath,
    [string]$Database = $env:EXPLORIA_PG_RESTORE_DATABASE,
    [string]$Username = $env:EXPLORIA_PG_USERNAME,
    [string]$Password = $env:EXPLORIA_PG_PASSWORD,
    [string]$HostName = $(if ($env:EXPLORIA_PG_HOST) { $env:EXPLORIA_PG_HOST } else { '127.0.0.1' }),
    [int]$Port = $(if ($env:EXPLORIA_PG_PORT) { [int]$env:EXPLORIA_PG_PORT } else { 5432 })
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($Database) -or [string]::IsNullOrWhiteSpace($Username)) {
    throw 'EXPLORIA_PG_RESTORE_DATABASE and EXPLORIA_PG_USERNAME are required.'
}

if ($Database -notmatch '(^|[_-])restore[_-]test$') {
    throw "Refusing restore test: database '$Database' must end with _restore_test or -restore-test."
}

$resolvedBackupPath = [System.IO.Path]::GetFullPath($BackupPath)
if (-not (Test-Path -LiteralPath $resolvedBackupPath -PathType Leaf)) {
    throw "Backup archive '$resolvedBackupPath' does not exist."
}

& pg_restore --list $resolvedBackupPath | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'Backup archive verification failed.' }

$env:PGPASSWORD = $Password
$resolvedDatabase = (& psql -w -h $HostName -p $Port -U $Username -d $Database -tAc 'select current_database();').Trim()

if ($LASTEXITCODE -ne 0 -or $resolvedDatabase -ne $Database) {
    throw "PostgreSQL connection verification failed for restore database '$Database'."
}

& pg_restore -w -h $HostName -p $Port -U $Username -d $Database --clean --if-exists --exit-on-error $resolvedBackupPath
if ($LASTEXITCODE -ne 0) { throw 'Restore test failed.' }

$migrationTableExists = (& psql -w -h $HostName -p $Port -U $Username -d $Database -tAc "select to_regclass('public.migrations') is not null;").Trim()
if ($LASTEXITCODE -ne 0 -or $migrationTableExists -ne 't') {
    throw 'Restore verification failed: migrations table is missing.'
}

Write-Output "Restore verified on isolated database '$Database'."
