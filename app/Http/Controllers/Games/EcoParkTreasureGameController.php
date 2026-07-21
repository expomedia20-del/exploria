<?php

namespace App\Http\Controllers\Games;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\MissionInstance;
use App\Models\User;
use App\Models\Visit;
use App\Services\MissionFlowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EcoParkTreasureGameController extends Controller
{
    private const CAMPAIGN_CODE = 'ecopark-online-treasure-map-game-campaign';

    private const FALLBACK_CAMPAIGN_CODE = 'ecopark-pilot-1405';

    private const BLUEPRINT_CODE = 'ecopark-online-treasure-map-game';

    public function __invoke(Request $request, MissionFlowService $missionFlow): Response
    {
        $campaign = $this->campaign();
        $user = $request->user();
        $visit = $user instanceof User && $campaign
            ? $this->latestVisit($user, $campaign)
            : null;

        return Inertia::render('games/ecopark-treasure', [
            'game' => [
                'campaign' => $campaign ? $this->serializeCampaign($campaign) : null,
                'entryQr' => $campaign ? $this->entryQr($campaign) : null,
                'missionNodes' => $campaign ? $this->missionNodes($campaign) : [],
                'latestVisit' => $visit ? [
                    'id' => $visit->id,
                    'occurredAt' => $visit->occurred_at->toIso8601String(),
                    'showUrl' => route('visits.show', ['visit' => $visit->id]),
                ] : null,
                'missionFlow' => $user instanceof User && $visit
                    ? $missionFlow->visitMissionSummary($user, $visit)
                    : null,
                'visitorState' => [
                    'isAuthenticated' => $user instanceof User,
                    'hasLinkedVisit' => $visit instanceof Visit,
                    'participantDashboardUrl' => $user instanceof User ? route('participant.dashboard') : null,
                ],
            ],
        ]);
    }

    private function campaign(): ?Campaign
    {
        return Campaign::query()
            ->with([
                'venue:id,name,city',
                'qrCodes' => fn ($query) => $query->with(['venue', 'touchpoint', 'campaign'])->orderBy('created_at'),
                'missionInstances' => fn ($query) => $query
                    ->with(['missionTemplate', 'hub:id,name', 'touchpoint:id,label', 'treasure:id,mission_instance_id,name'])
                    ->orderBy('created_at'),
            ])
            ->where('status', RecordStatus::Active)
            ->where(function ($query): void {
                $query
                    ->where('code', self::CAMPAIGN_CODE)
                    ->orWhere('code', self::FALLBACK_CAMPAIGN_CODE)
                    ->orWhere('metadata->blueprint_code', self::BLUEPRINT_CODE);
            })
            ->orderByRaw('case when code = ? then 0 when code = ? then 2 else 1 end', [self::CAMPAIGN_CODE, self::FALLBACK_CAMPAIGN_CODE])
            ->first();
    }

    /** @return array<string, mixed> */
    private function serializeCampaign(Campaign $campaign): array
    {
        return [
            'id' => $campaign->id,
            'code' => $campaign->code,
            'name' => $campaign->name,
            'venueName' => $campaign->venue?->name,
            'city' => $campaign->venue?->city,
            'scanUrl' => $this->entryQr($campaign)['scanUrl'] ?? null,
        ];
    }

    /** @return array<string, string|null>|null */
    private function entryQr(Campaign $campaign): ?array
    {
        $qr = $campaign->qrCodes->first(fn ($qr): bool => $qr->isAvailableForLanding());

        if (! $qr) {
            return null;
        }

        return [
            'code' => $qr->code,
            'label' => $qr->touchpoint?->label,
            'scanUrl' => route('scan.landing', ['code' => $qr->code]),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function missionNodes(Campaign $campaign): array
    {
        return $campaign->missionInstances
            ->values()
            ->map(fn (MissionInstance $mission, int $index): array => [
                'id' => $mission->id,
                'code' => $mission->code,
                'title' => $mission->title_override ?? $mission->missionTemplate->title,
                'place' => $mission->hub_id ? $mission->hub->name : $mission->touchpoint?->label,
                'clue' => $mission->metadata['visitor_instruction']
                    ?? $mission->missionTemplate->description
                    ?? 'سرنخ این مرحله در مسیر کمپین نمایش داده می‌شود.',
                'mission' => $mission->missionTemplate->description,
                'reward' => $mission->metadata['success_message']
                    ?? ($mission->treasure?->name ? 'باز شدن گنج: '.$mission->treasure->name : null),
                'points' => (int) $mission->missionTemplate->point_value,
                'treasureName' => $mission->treasure?->name,
                'cycleStep' => [
                    'index' => $mission->metadata['cycle_step_index'] ?? $index + 1,
                    'label' => $mission->metadata['cycle_step_label'] ?? null,
                ],
                'unlockMinPoints' => data_get($mission->unlock_rule, 'min_points'),
            ])
            ->all();
    }

    private function latestVisit(User $user, Campaign $campaign): ?Visit
    {
        return Visit::query()
            ->where('user_id', $user->id)
            ->where('campaign_id', $campaign->id)
            ->latest('occurred_at')
            ->first();
    }
}
