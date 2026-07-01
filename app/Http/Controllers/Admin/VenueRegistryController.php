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
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function facilitiesTemplate(): StreamedResponse
    {
        $rows = [
            ['name', 'function', 'campaign_uses', 'priority', 'parent', 'notes'],
            ['کافه رواق', 'retail', 'reward,sponsor', 'primary', 'پروژه رواق', 'پیشنهاد نوشیدنی یا تخفیف'],
            ['فست فود رواق', 'retail', 'reward,ad', 'secondary', 'پروژه رواق', 'غذا، تخفیف یا کوپن'],
            ['نقطه عکس رواق', 'discovery', 'qr,mission,treasure', 'secondary', 'پروژه رواق', 'مناسب برای مأموریت کشف و گنج'],
        ];

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

    public function updateProfile(UpdateVenueProfileRequest $request, Venue $venue, VenueRegistryService $service): JsonResponse|RedirectResponse
    {
        $service->updateProfile($venue, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('success', 'شناخت‌نامه مکان ذخیره شد.');
    }
}
