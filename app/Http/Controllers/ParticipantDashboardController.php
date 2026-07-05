<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Visit;
use App\Services\MissionFlowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantDashboardController extends Controller
{
    public function __invoke(Request $request, MissionFlowService $missionFlow): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        $latestVisit = Visit::query()
            ->with(['venue', 'touchpoint.hub.zone', 'campaign', 'qrCode'])
            ->where('user_id', $user->id)
            ->latest('occurred_at')
            ->first();

        $flow = $latestVisit ? $missionFlow->visitMissionSummary($user, $latestVisit) : null;
        $participation = $this->participationProfile($latestVisit);

        return Inertia::render('participant/dashboard', [
            'participant' => [
                'name' => $user->name,
                'email' => $user->email,
                'mode' => $participation['mode'],
                'modeLabel' => $participation['modeLabel'],
                'members' => $participation['members'],
                'teamName' => $participation['teamName'],
            ],
            'latestVisit' => $latestVisit ? [
                'id' => $latestVisit->id,
                'status' => $latestVisit->status,
                'occurredAt' => $latestVisit->occurred_at->toIso8601String(),
                'qrCode' => $latestVisit->qrCode?->code,
                'venueName' => $latestVisit->venue?->name,
                'city' => $latestVisit->venue?->city,
                'zoneName' => $latestVisit->touchpoint?->hub?->zone?->name,
                'hubName' => $latestVisit->touchpoint?->hub?->name,
                'touchpointLabel' => $latestVisit->touchpoint?->label,
                'campaignName' => $latestVisit->campaign?->name,
                'isDemo' => (bool) data_get($latestVisit->metadata, 'is_demo', false),
            ] : null,
            'missionFlow' => $flow,
        ]);
    }

    /** @return array{mode: string, modeLabel: string, teamName: string|null, members: array<int, string>} */
    private function participationProfile(?Visit $visit): array
    {
        $mode = (string) data_get($visit?->metadata, 'participation_mode', 'individual');
        $members = data_get($visit?->metadata, 'participants', []);

        if (! is_array($members) || $members === []) {
            $members = match ($mode) {
                'family' => ['سرپرست خانواده', 'همراه کودک', 'عضو خانواده'],
                'team' => ['عضو اول تیم', 'عضو دوم تیم'],
                default => ['بازدیدکننده اصلی'],
            };
        }

        return [
            'mode' => $mode,
            'modeLabel' => match ($mode) {
                'family' => 'خانوادگی',
                'team' => 'تیمی',
                default => 'انفرادی',
            },
            'teamName' => data_get($visit?->metadata, 'team_name'),
            'members' => array_values(array_map('strval', $members)),
        ];
    }
}
