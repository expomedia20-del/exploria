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

$psql = Get-PostgresTool 'psql.exe'
$pgDump = Get-PostgresTool 'pg_dump.exe'
$pgRestore = Get-PostgresTool 'pg_restore.exe'

$resolvedOutputDirectory = [System.IO.Path]::GetFullPath($OutputDirectory)
[System.IO.Directory]::CreateDirectory($resolvedOutputDirectory) | Out-Null

$env:PGPASSWORD = $Password
$resolvedDatabase = (& $psql -w -h $HostName -p $Port -U $Username -d $Database -tAc 'select current_database();').Trim()

if ($LASTEXITCODE -ne 0 -or $resolvedDatabase -ne $Database) {
    throw "PostgreSQL connection verification failed for '$Database'."
}

$timestamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupPath = Join-Path $resolvedOutputDirectory "exploria-$Database-$timestamp.dump"

& $pgDump -w -h $HostName -p $Port -U $Username -d $Database --format=custom --file=$backupPath
if ($LASTEXITCODE -ne 0) { throw 'pg_dump failed.' }

& $pgRestore --list $backupPath | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'Backup archive verification failed.' }

$backupFile = Get-Item -LiteralPath $backupPath
if ($backupFile.Length -le 0) { throw 'Backup archive is empty.' }

Write-Output $backupFile.FullName
