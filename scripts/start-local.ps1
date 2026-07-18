$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$hostName = '127.0.0.1'
$port = 8004
$url = "http://$hostName`:$port"
$hotFile = Join-Path $projectRoot 'public\hot'
$manifestFile = Join-Path $projectRoot 'public\build\manifest.json'
$externalPhp = 'E:\exploria-toolchain-local\php\php.exe'
$localPhp = Join-Path $projectRoot '.codex-runtime\exploria-toolchain-local\php\php.exe'
$localNpm = Join-Path $projectRoot '.codex-runtime\node\npm.cmd'
$pathPhp = Get-Command 'php.exe' -ErrorAction SilentlyContinue
$pathNpm = Get-Command 'npm.cmd' -ErrorAction SilentlyContinue

if (Test-Path $externalPhp) {
    $php = $externalPhp
} elseif (Test-Path $localPhp) {
    $php = $localPhp
} elseif ($pathPhp) {
    $php = $pathPhp.Source
} else {
    throw 'PHP executable was not found. Install PHP, add php.exe to PATH, or provide the project runtime under .codex-runtime.'
}

if (Test-Path $localNpm) {
    $npm = $localNpm
} elseif ($pathNpm) {
    $npm = $pathNpm.Source
} else {
    throw 'npm.cmd was not found. Install Node.js, add npm.cmd to PATH, or provide the project runtime under .codex-runtime.'
}

$viteIsRunning = Get-NetTCPConnection -LocalAddress $hostName -LocalPort 5173 -State Listen -ErrorAction SilentlyContinue
if ((Test-Path $hotFile) -and -not $viteIsRunning) {
    Remove-Item -LiteralPath $hotFile -Force
}

$frontendRoots = @(
    (Join-Path $projectRoot 'resources\js'),
    (Join-Path $projectRoot 'resources\css')
)
$newestFrontendSource = Get-ChildItem -Path $frontendRoots -Recurse -File -ErrorAction SilentlyContinue |
    Sort-Object LastWriteTime -Descending |
    Select-Object -First 1
$buildNeedsRefresh = -not (Test-Path $manifestFile)

if (-not $buildNeedsRefresh -and $newestFrontendSource) {
    $buildNeedsRefresh = $newestFrontendSource.LastWriteTime -gt (Get-Item $manifestFile).LastWriteTime
}

if ($buildNeedsRefresh -and -not $viteIsRunning) {
    Write-Host 'Building EXPLORIA frontend assets...'
    $phpDir = Split-Path -Parent $php
    $nodeDir = Split-Path -Parent $npm
    $previousPath = $env:PATH
    $env:PATH = "$phpDir;$nodeDir;$previousPath"

    try {
        & $npm run build
    } finally {
        $env:PATH = $previousPath
    }
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
