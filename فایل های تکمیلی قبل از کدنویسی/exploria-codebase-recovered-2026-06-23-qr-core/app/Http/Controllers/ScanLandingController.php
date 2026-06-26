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

        return Inertia::render('scan/landing', [
            'qr' => [
                'code' => $qr->code,
                'label' => $qr->label,
                'venueName' => $qr->venue->name,
                'city' => $qr->venue->city,
                'zoneName' => $qr->touchpoint->hub->zone->name,
                'hubName' => $qr->touchpoint->hub->name,
                'touchpointLabel' => $qr->touchpoint->label,
                'campaignName' => $qr->campaign->name,
                'isDemo' => (bool) data_get($qr->metadata, 'is_demo', false),
            ],
        ]);
    }
}
