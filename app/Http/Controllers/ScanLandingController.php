<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Inertia\Inertia;
use Inertia\Response;

class ScanLandingController extends Controller
{
    public function __invoke(string $code): Response
    {
        $qr = QrCode::query()
            ->with(['venue', 'touchpoint.hub.zone', 'campaign'])
            ->where('code', $code)
            ->firstOrFail();

        abort_unless($qr->isAvailableForLanding(), 404);

        $venue = $qr->venue;
        $touchpoint = $qr->touchpoint;
        $hub = $touchpoint?->hub;
        $zone = $hub?->zone;
        $campaign = $qr->campaign;

        abort_unless($venue && $touchpoint && $hub && $zone && $campaign, 404);

        return Inertia::render('scan/landing', [
            'qr' => [
                'code' => $qr->code,
                'label' => $qr->label,
                'venueName' => $venue->name,
                'city' => $venue->city,
                'zoneName' => $zone->name,
                'hubName' => $hub->name,
                'touchpointLabel' => $touchpoint->label,
                'campaignName' => $campaign->name,
                'isDemo' => (bool) data_get($qr->metadata, 'is_demo', false),
            ],
        ]);
    }
}
