<?php

namespace App\Http\Controllers;

use App\Models\ConsentLog;
use App\Models\OtpRequest;
use App\Models\QrCode;
use App\Models\Venue;
use App\Models\Visit;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $latestVisits = Visit::query()
            ->with(['venue', 'touchpoint', 'campaign', 'user'])
            ->latest('occurred_at')
            ->limit(8)
            ->get()
            ->map(fn (Visit $visit): array => [
                'id' => $visit->id,
                'venueName' => $visit->venue->name,
                'touchpointLabel' => $visit->touchpoint->label,
                'campaignName' => $visit->campaign->name,
                'visitorName' => $visit->user->name,
                'status' => $visit->status,
                'occurredAt' => $visit->occurred_at->toIso8601String(),
            ]);

        return Inertia::render('dashboard', [
            'stats' => [
                'venues' => Venue::query()->count(),
                'activeQrCodes' => QrCode::query()->where('status', 'active')->count(),
                'otpRequests' => OtpRequest::query()->count(),
                'consents' => ConsentLog::query()->count(),
                'visits' => Visit::query()->count(),
            ],
            'latestVisits' => $latestVisits,
        ]);
    }
}
