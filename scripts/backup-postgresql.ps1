param(
    [string]$Database = $env:EXPLORIA_PG_DATABASE,
    [string]$Username = $env:EXPLORIA_PG_USERNAME,
    [string]$Password = $env:EXPLORIA_PG_PASSWORD,
    [string]$HostName = $(if ($env:EXPLORIA_PG_HOST) { $env:EXPLORIA_PG_HOST } else { '127.0.0.1' }),
    [int]$Port = $(if ($env:EXPLORIA_PG_PORT) { [int]$env:EXPLORIA_PG_PORT } else { 5432 }),
    [Parameter(Mandatory = $true)]
    [string]$OutputDirectory
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($Database) -or [string]::IsNullOrWhiteSpace($Username)) {
    throw 'EXPLORIA_PG_DATABASE and EXPLORIA_PG_USERNAME are required.'
}

$resolvedOutputDirectory = [System.IO.Path]::GetFullPath($OutputDirectory)
[System.IO.Directory]::CreateDirectory($resolvedOutputDirectory) | Out-Null

$env:PGPASSWORD = $Password
$resolvedDatabase = (& psql -w -h $HostName -p $Port -U $Username -d $Database -tAc 'select current_database();').Trim()

if ($LASTEXITCODE -ne 0 -or $resolvedDatabase -ne $Database) {
    throw "PostgreSQL connection verification failed for '$Database'."
}

$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupPath = Join-Path $resolvedOutputDirectory "exploria-$Database-$timestamp.dump"

& pg_dump -w -h $HostName -p $Port -U $Username -d $Database --format=custom --file=$backupPath
if ($LASTEXITCODE -ne 0) { throw 'pg_dump failed.' }

& pg_restore --list $backupPath | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'Backup archive verification failed.' }

$backupFile = Get-Item -LiteralPath $backupPath
if ($backupFile.Length -le 0) { throw 'Backup archive is empty.' }

Write-Output $backupFile.FullName
