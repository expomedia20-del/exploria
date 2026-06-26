$ErrorActionPreference = 'Stop'

$handoffRoot = if ($PSScriptRoot) {
    $PSScriptRoot
} else {
    Join-Path (Get-Location) '00-بسته انتقال اسناد به کدنویسی'
}
$projectRoot = Split-Path -Parent $handoffRoot

$documents = @(
    @{ Source = 'AGENTS.md'; Destination = 'AGENTS.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/0000-ROOLS/20-EXPLORIA_AI_Development_Framework_Laravel_React_Monolith_v1.0.md'; Destination = 'governance/20-EXPLORIA_AI_Development_Framework_Laravel_React_Monolith_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/0000-ROOLS/21-EXPLORIA_PreCoding_Master_Execution_Control_v1.0.md'; Destination = 'governance/21-EXPLORIA_PreCoding_Master_Execution_Control_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/0000-ROOLS/22-EXPLORIA_Open_Decisions_Lock_Register_v1.0.md'; Destination = 'governance/22-EXPLORIA_Open_Decisions_Lock_Register_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/0000-ROOLS/23-EXPLORIA_Sprint_1A_Scope_Acceptance_Lock_v1.0.md'; Destination = 'governance/23-EXPLORIA_Sprint_1A_Scope_Acceptance_Lock_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/0000-ROOLS/24-EXPLORIA_Toolchain_Readiness_v1.0.md'; Destination = 'governance/24-EXPLORIA_Toolchain_Readiness_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/BRD v1.1 UP/Exploria_26_Control_Items_Lock_Register_v1.0.md'; Destination = 'governance/Exploria_26_Control_Items_Lock_Register_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/01 chek list/Exploria_BRD_v1.1_Final_Acceptance_Checklist_v1.0.md'; Destination = 'governance/Exploria_BRD_v1.1_Final_Acceptance_Checklist_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/0000-MVP/EXPLORIA_MVP_Documentation_Pack_v1.0.zip'; Destination = 'governance/EXPLORIA_MVP_Documentation_Pack_v1.0.zip' },
    @{ Source = 'نسخ های اصلی تکمیلی/02  Brd-pilot revenue/Exploria_BRD_v1.1_Pilot_Revenue_Update.md'; Destination = 'product/Exploria_BRD_v1.1_Pilot_Revenue_Update.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/03 FRD-Pilot operation/Exploria_FRD_v1.1_Pilot_Operations_Update.md'; Destination = 'product/Exploria_FRD_v1.1_Pilot_Operations_Update.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/04 product  backlog/Exploria_Product_Backlog_v1.0.md'; Destination = 'product/Exploria_Product_Backlog_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/04 product  backlog/Exploria_Product_Backlog_v1.0.xlsx'; Destination = 'product/Exploria_Product_Backlog_v1.0.xlsx' },
    @{ Source = 'نسخ های اصلی تکمیلی/05 sprint_0_1 execation plan/Exploria_Sprint_0_1_Execution_Plan_v1.0 (1).md'; Destination = 'product/Exploria_Sprint_0_1_Execution_Plan_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/05 sprint_0_1 execation plan/Exploria_Sprint_0_1_Execution_Plan_v1.0 (1).xlsx'; Destination = 'product/Exploria_Sprint_0_1_Execution_Plan_v1.0.xlsx' },
    @{ Source = 'نسخ های اصلی تکمیلی/06 technical design/Exploria_Technical_Design_Pack_v1.0.md'; Destination = 'technical/Exploria_Technical_Design_Pack_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/06 technical design/Exploria_Technical_Design_Pack_v1.0.sql'; Destination = 'technical/Exploria_Technical_Design_Pack_v1.0.sql' },
    @{ Source = 'نسخ های اصلی تکمیلی/06 technical design/Exploria_OpenAPI_Sprint1_v1.0.yaml'; Destination = 'technical/Exploria_OpenAPI_Sprint1_v1.0_ORIGINAL.yaml' },
    @{ Source = 'نسخ های اصلی تکمیلی/11-2 Integration patch/Exploria_OpenAPI_Sprint1_v1.0_PATCHED.yaml'; Destination = 'technical/Exploria_OpenAPI_Sprint1_v1.0_CANONICAL.yaml' },
    @{ Source = 'نسخ های اصلی تکمیلی/06 technical design/Exploria_Sprint1_UI_Flows_v1.0.mmd'; Destination = 'technical/Exploria_Sprint1_UI_Flows_v1.0.mmd' },
    @{ Source = 'نسخ های اصلی تکمیلی/07 UI-UX Wireframe/Exploria_UI_UX_Wireframe_Pack_v1.0.md'; Destination = 'technical/Exploria_UI_UX_Wireframe_Pack_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/07 UI-UX Wireframe/Exploria_UI_UX_Wireframe_Pack_v1.0.html'; Destination = 'technical/Exploria_UI_UX_Wireframe_Pack_v1.0.html' },
    @{ Source = 'نسخ های اصلی تکمیلی/11 Integration QA Report v1.0/Exploria_Integration_QA_Report_v1.0.md'; Destination = 'quality/Exploria_Integration_QA_Report_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/11-2 Integration patch/Exploria_Integration_Patch_v1.0.md'; Destination = 'quality/Exploria_Integration_Patch_v1.0.md' },
    @{ Source = 'نسخ های اصلی تکمیلی/15/Exploria_Integration_Patch_v1.0_Execution_Control_Document.docx'; Destination = 'quality/Exploria_Integration_Patch_v1.0_Execution_Control_Document.docx' },
    @{ Source = 'نسخ های اصلی تکمیلی/16/15-Exploria_Local_Runtime_Smoke_Test_Result_v1.0.docx'; Destination = 'quality/Exploria_Local_Runtime_Smoke_Test_Result_v1.0.docx' }
)

$manifest = foreach ($document in $documents) {
    $sourcePath = Join-Path $projectRoot $document.Source
    $destinationPath = Join-Path $handoffRoot $document.Destination

    if (-not (Test-Path -LiteralPath $sourcePath)) {
        throw "Required handoff source is missing: $($document.Source)"
    }

    $destinationDirectory = Split-Path -Parent $destinationPath
    New-Item -ItemType Directory -Path $destinationDirectory -Force | Out-Null
    Copy-Item -LiteralPath $sourcePath -Destination $destinationPath -Force

    $file = Get-Item -LiteralPath $destinationPath
    [pscustomobject]@{
        Destination = $document.Destination
        Source = $document.Source
        Size = $file.Length
        SHA256 = (Get-FileHash -LiteralPath $destinationPath -Algorithm SHA256).Hash
        SyncedAt = (Get-Date).ToString('yyyy-MM-dd HH:mm:ss zzz')
    }
}

$manifestPath = Join-Path $handoffRoot 'MANIFEST-SHA256.csv'
$manifest | Export-Csv -LiteralPath $manifestPath -NoTypeInformation -Encoding UTF8

Write-Host "Handoff synchronized: $($manifest.Count) documents"
Write-Host "Manifest: $manifestPath"
