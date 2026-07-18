<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateVenueProfileRequest;
use App\Models\Venue;
use App\Services\VenueRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class VenueRegistryController extends Controller
{
    public function page(Request $request, VenueRegistryService $service): Response
    {
        return Inertia::render('admin/venues/index', [
            'venues' => $service->list($request->user()),
        ]);
    }

    public function index(Request $request, VenueRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->list($request->user())]);
    }

    public function facilitiesTemplate(): BinaryFileResponse|StreamedResponse
    {
        $rows = [
            ['نام', 'کارکرد', 'کاربرد کمپینی', 'اولویت', 'زیرمجموعه', 'یادداشت'],
            ['کافه رواق', 'retail', 'reward,sponsor', 'primary', 'پروژه رواق', 'پیشنهاد نوشیدنی یا تخفیف'],
            ['فست فود رواق', 'retail', 'reward,ad', 'secondary', 'پروژه رواق', 'غذا، تخفیف یا کوپن'],
            ['نقطه عکس رواق', 'discovery', 'qr,mission,treasure', 'secondary', 'پروژه رواق', 'مناسب برای مأموریت کشف و گنج'],
            ['', '', '', '', '', ''],
        ];

        if (class_exists(ZipArchive::class)) {
            return $this->xlsxTemplate($rows);
        }

        return response()->streamDownload(function () use ($rows): void {
            echo "\xEF\xBB\xBF";

            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }

            fclose($output);
        }, 'exploria-venue-facilities-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** @param array<int, array<int, string>> $rows */
    private function xlsxTemplate(array $rows): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'exploria-venue-template-');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML);
        $zip->addFromString('_rels/.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML);
        $zip->addFromString('xl/workbook.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="امکانات مکان" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML);
        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML);
        $zip->addFromString('xl/styles.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2"><font><sz val="11"/></font><font><b/><sz val="11"/></font></fonts>
  <fills count="1"><fill><patternFill patternType="none"/></fill></fills>
  <borders count="1"><border/></borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/></cellXfs>
</styleSheet>
XML);
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($rows));
        $zip->close();

        return response()->download($path, 'exploria-venue-facilities-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /** @param array<int, array<int, string>> $rows */
    private function worksheetXml(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" rightToLeft="1">'
            .'<sheetViews><sheetView workbookViewId="0" rightToLeft="1"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<cols><col min="1" max="1" width="26" customWidth="1"/><col min="2" max="2" width="18" customWidth="1"/><col min="3" max="3" width="28" customWidth="1"/><col min="4" max="4" width="16" customWidth="1"/><col min="5" max="5" width="24" customWidth="1"/><col min="6" max="6" width="38" customWidth="1"/></cols>'
            .'<sheetData>';

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $xml .= '<row r="'.$excelRow.'">';

            foreach ($row as $columnIndex => $value) {
                $cell = ['A', 'B', 'C', 'D', 'E', 'F'][$columnIndex].$excelRow;
                $style = $excelRow === 1 ? ' s="1"' : '';
                $xml .= '<c r="'.$cell.'" t="inlineStr"'.$style.'><is><t xml:space="preserve">'.e($value).'</t></is></c>';
            }

            $xml .= '</row>';
        }

        return $xml.'</sheetData></worksheet>';
    }

    public function updateProfile(UpdateVenueProfileRequest $request, Venue $venue, VenueRegistryService $service): JsonResponse|RedirectResponse
    {
        $service->updateProfile($venue, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('success', 'ارزیابی مکان ذخیره شد.');
    }
}
