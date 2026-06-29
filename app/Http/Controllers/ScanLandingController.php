<?php

namespace App\Http\Controllers;

use App\Enums\RecordStatus;
use App\Models\QrCode;
use App\Models\RewardDefinition;
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

        $rewardOptions = RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->where('status', RecordStatus::Active)
            ->where('metadata->approval_status', 'approved')
            ->with('partnerAccount:id,name')
            ->orderBy('created_at')
            ->get()
            ->map(fn (RewardDefinition $reward): array => [
                'id' => $reward->id,
                'name' => $reward->name,
                'tier' => $reward->metadata['reward_tier'] ?? null,
                'option' => $reward->metadata['reward_option'] ?? null,
                'partnerName' => $reward->partnerAccount?->name,
                'description' => $reward->metadata['description'] ?? null,
            ])
            ->values();

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
            'rewardOptions' => $rewardOptions,
        ]);
    }
}
