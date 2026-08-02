param(
    [string]$Venue = 'ecopark-abbasabad',
    [int]$MinimumCampaigns = 2,
    [switch]$SkipCi,
    [switch]$SkipBuild,
    [switch]$LocalDryRun,
    [string]$HealthUrl,
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
$composer = Resolve-Tool -Name 'composer' -Candidates @(
    (Join-Path $root '.codex-runtime\composer\composer.bat'),
    (Join-Path $root '.codex-runtime\composer\composer.cmd')
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

Invoke-Step -Name 'Demo readiness gate' -Command {
    & $php artisan exploria:demo-readiness --venue=$Venue --json
}

if ($LocalDryRun) {
    Write-Host '==> Local production-block verification'
    $readinessLines = @(& $php artisan exploria:production-readiness --json 2>&1)
    $readinessExitCode = $LASTEXITCODE
    $readinessJson = ($readinessLines | ForEach-Object { $_.ToString() }) -join "`n"
    Write-Host $readinessJson

    try {
        $readinessReport = $readinessJson | ConvertFrom-Json -ErrorAction Stop
    } catch {
        throw 'The local production-readiness report was not valid JSON.'
    }

    $environmentCheck = $readinessReport.checks | Where-Object { $_.key -eq 'environment' } | Select-Object -First 1
    if ($readinessExitCode -eq 0 -or $readinessReport.summary.ready -ne $false) {
        throw 'Local dry run failed closed: the local environment unexpectedly passed the production-readiness gate.'
    }

    if (-not $environmentCheck -or $environmentCheck.actual -ne 'local') {
        throw 'Local dry run requires the production-readiness report to identify the environment as local.'
    }

    Write-Host 'Local dry run remains production-blocked, as required.'
} else {
    Invoke-Step -Name 'Staging/production readiness gate' -Command {
        & $php artisan exploria:production-readiness --json
    }
}

if (-not [string]::IsNullOrWhiteSpace($HealthUrl)) {
    $healthUri = $null
    if (-not [Uri]::TryCreate($HealthUrl, [UriKind]::Absolute, [ref]$healthUri)) {
        throw 'HealthUrl must be an absolute HTTP(S) URL.'
    }

    $allowedSchemes = if ($LocalDryRun) { @('http', 'https') } else { @('https') }
    if ($allowedSchemes -notcontains $healthUri.Scheme) {
        throw 'HealthUrl must use HTTPS outside LocalDryRun mode.'
    }

    Write-Host "==> Runtime health check: $HealthUrl"
    $healthResponse = Invoke-WebRequest -UseBasicParsing -Uri $healthUri -TimeoutSec 15
    if ([int]$healthResponse.StatusCode -lt 200 -or [int]$healthResponse.StatusCode -ge 300) {
        throw "Runtime health check failed with HTTP $($healthResponse.StatusCode)."
    }

    Write-Host "Runtime health check passed with HTTP $($healthResponse.StatusCode)."
}

if (-not $SkipCi) {
    if ($composer) {
        Invoke-Step -Name 'Full application CI' -Command {
            & $composer ci:check
        }
    } elseif (Test-Path -LiteralPath $composerPhar -PathType Leaf) {
        Invoke-Step -Name 'Full application CI' -Command {
            & $php $composerPhar ci:check
        }
    } else {
        throw 'Composer was not found on PATH or in the local toolchain.'
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

$mode = if ($LocalDryRun) { 'local dry run' } else { 'staging/production' }
Write-Host "Launch assurance completed in $mode mode."
