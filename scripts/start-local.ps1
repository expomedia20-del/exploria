$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$hostName = '127.0.0.1'
$port = 8004
$url = "http://$hostName`:$port"
$localPhp = Join-Path $projectRoot '.codex-runtime\exploria-toolchain-local\php\php.exe'
$fallbackPhp = 'E:\exploria-toolchain-local\php\php.exe'

if (Test-Path $localPhp) {
    $php = $localPhp
} elseif (Test-Path $fallbackPhp) {
    $php = $fallbackPhp
} else {
    throw 'PHP executable was not found. Expected .codex-runtime\exploria-toolchain-local\php\php.exe.'
}

$listener = Get-NetTCPConnection -LocalAddress $hostName -LocalPort $port -State Listen -ErrorAction SilentlyContinue

if ($listener) {
    Write-Host "EXPLORIA local server is already running:"
    Write-Host $url
    exit 0
}

$logDir = Join-Path $projectRoot 'storage\logs'
$outLog = Join-Path $logDir 'exploria-local-8004.out.log'
$errLog = Join-Path $logDir 'exploria-local-8004.err.log'

Start-Process `
    -FilePath $php `
    -ArgumentList 'artisan', 'serve', "--host=$hostName", "--port=$port" `
    -WorkingDirectory $projectRoot `
    -RedirectStandardOutput $outLog `
    -RedirectStandardError $errLog `
    -WindowStyle Hidden

Start-Sleep -Seconds 3

$listener = Get-NetTCPConnection -LocalAddress $hostName -LocalPort $port -State Listen -ErrorAction SilentlyContinue

if (-not $listener) {
    Write-Host 'EXPLORIA local server did not start. Latest error log:'
    Get-Content -LiteralPath $errLog -ErrorAction SilentlyContinue | Select-Object -Last 40
    exit 1
}

Write-Host 'EXPLORIA local server is ready:'
Write-Host $url
Write-Host "$url/dashboard"
Write-Host "$url/admin/campaign-builder"
