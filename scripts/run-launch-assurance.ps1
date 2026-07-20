param(
    [string]$Venue = 'ecopark-abbasabad',
    [int]$MinimumCampaigns = 2,
    [switch]$SkipCi,
    [switch]$SkipBuild,
    [switch]$RequirePostgreSQL,
    [string]$BackupOutputDirectory,
    [string]$RestoreBackupPath
)

$ErrorActionPreference = 'Stop'

function Resolve-RepositoryRoot {
    return (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
}

function Resolve-Tool {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Name,
        [string[]]$Candidates = @()
    )

    foreach ($candidate in $Candidates) {
        if ($candidate -and (Test-Path -LiteralPath $candidate -PathType Leaf)) {
            return (Resolve-Path -LiteralPath $candidate).Path
        }
    }

    $command = Get-Command $Name -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    return $null
}

function Add-ToolDirectory {
    param([string]$ToolPath)

    if (-not $ToolPath) {
        return
    }

    $directory = Split-Path -Parent $ToolPath
    $pathEntries = $env:PATH -split ';'
    if ($pathEntries -notcontains $directory) {
        $env:PATH = "$directory;$env:PATH"
    }
}

function Invoke-Step {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Name,
        [Parameter(Mandatory = $true)]
        [scriptblock]$Command
    )

    Write-Host "==> $Name"
    & $Command
    if ($LASTEXITCODE -ne 0) {
        throw "Step failed: $Name"
    }
}

function Test-PostgresToolingReady {
    $toolNames = @('psql.exe', 'pg_dump.exe', 'pg_restore.exe')
    foreach ($toolName in $toolNames) {
        $candidate = $null
        if ($env:EXPLORIA_PG_BIN) {
            $candidate = Join-Path $env:EXPLORIA_PG_BIN $toolName
        }

        if (-not (Resolve-Tool -Name $toolName -Candidates @($candidate))) {
            return $false
        }
    }

    return -not [string]::IsNullOrWhiteSpace($env:EXPLORIA_PG_DATABASE) -and
        -not [string]::IsNullOrWhiteSpace($env:EXPLORIA_PG_USERNAME)
}

$root = Resolve-RepositoryRoot
$php = Resolve-Tool -Name 'php' -Candidates @(
    (Join-Path $root '.codex-runtime\exploria-toolchain-local\php\php.exe'),
    (Join-Path $root '.codex-runtime\exploria-toolchain-local\php83\php.exe')
)
$npm = Resolve-Tool -Name 'npm' -Candidates @(
    (Join-Path $root '.codex-runtime\node\npm.cmd')
)
$composerPhar = Join-Path $root '.codex-runtime\exploria-toolchain-local\composer\composer.phar'

if (-not $php) {
    throw 'PHP was not found. Install PHP or keep the local .codex-runtime PHP toolchain available.'
}

Add-ToolDirectory -ToolPath $php
Add-ToolDirectory -ToolPath $npm

Set-Location $root

Invoke-Step -Name 'Multi-campaign assurance gate' -Command {
    & $php artisan exploria:campaign-assurance --venue=$Venue --minimum-campaigns=$MinimumCampaigns --require-execution --json
}

Invoke-Step -Name 'Staging/production readiness gate' -Command {
    & $php artisan exploria:production-readiness --json
}

if (-not $SkipCi) {
    if (-not (Test-Path -LiteralPath $composerPhar -PathType Leaf)) {
        throw 'composer.phar was not found in the local toolchain.'
    }

    Invoke-Step -Name 'Full application CI' -Command {
        & $php $composerPhar ci:check
    }
}

if (-not $SkipBuild) {
    if (-not $npm) {
        throw 'npm was not found. Install Node.js/npm or keep the local .codex-runtime Node toolchain available.'
    }

    Invoke-Step -Name 'Production frontend build' -Command {
        & $npm run build
    }
}

$postgresReady = Test-PostgresToolingReady
if ($RequirePostgreSQL -or $postgresReady) {
    Invoke-Step -Name 'PostgreSQL PHPUnit gate' -Command {
        & (Join-Path $root 'scripts\test-postgresql.ps1')
    }
} else {
    Write-Warning 'PostgreSQL gate skipped: set EXPLORIA_PG_BIN, EXPLORIA_PG_DATABASE, EXPLORIA_PG_USERNAME, and EXPLORIA_PG_PASSWORD, or pass -RequirePostgreSQL to fail closed.'
}

if (-not [string]::IsNullOrWhiteSpace($BackupOutputDirectory)) {
    Invoke-Step -Name 'PostgreSQL backup verification' -Command {
        & (Join-Path $root 'scripts\backup-postgresql.ps1') -OutputDirectory $BackupOutputDirectory
    }
}

if (-not [string]::IsNullOrWhiteSpace($RestoreBackupPath)) {
    Invoke-Step -Name 'PostgreSQL restore verification' -Command {
        & (Join-Path $root 'scripts\test-postgresql-restore.ps1') -BackupPath $RestoreBackupPath
    }
}

Write-Host 'Launch assurance completed.'
