$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$phpDir = 'E:\exploria-toolchain-local\php'

$env:Path = "$phpDir;C:\Program Files\Git\cmd;$env:Path"

Set-Location $projectRoot

$listener = Get-NetTCPConnection -LocalAddress 127.0.0.1 -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue

if (-not $listener) {
    Start-Process `
        -FilePath (Join-Path $phpDir 'php.exe') `
        -ArgumentList 'artisan', 'serve', '--host=127.0.0.1', '--port=8000' `
        -WorkingDirectory $projectRoot `
        -WindowStyle Hidden

    Start-Sleep -Seconds 2
}

Write-Host 'EXPLORIA demo is ready:'
Write-Host 'Visitor QR landing: http://127.0.0.1:8000/scan/ep1405-a7f3k9m2q8x4'
Write-Host 'Operational dashboard: http://127.0.0.1:8000/dashboard'
Write-Host 'QR registry: http://127.0.0.1:8000/admin/qr-codes'
