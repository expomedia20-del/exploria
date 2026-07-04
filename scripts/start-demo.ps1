$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot

Set-Location $projectRoot

$startLocal = Join-Path $PSScriptRoot 'start-local.ps1'
& $startLocal

Write-Host 'EXPLORIA demo is ready:'
Write-Host 'Visitor QR landing: http://127.0.0.1:8004/scan/ep1405-a7f3k9m2q8x4'
Write-Host 'Operational dashboard: http://127.0.0.1:8004/dashboard'
Write-Host 'QR registry: http://127.0.0.1:8004/admin/qr-codes'