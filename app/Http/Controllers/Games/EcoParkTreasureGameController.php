<?php

namespace App\Http\Controllers\Games;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\MissionInstance;
use App\Models\User;
use App\Models\Visit;
use App\Services\EcoParkOnlineGameService;
use App\Services\MissionFlowService;
use App\Services\SmartOffersService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EcoParkTreasureGameController extends Controller
{
    private const CAMPAIGN_CODE = 'ecopark-online-treasure-map-game-campaign';

    private const FALLBACK_CAMPAIGN_CODE = 'ecopark-pilot-1405';

    private const BLUEPRINT_CODE = 'ecopark-online-treasure-map-game';

    public function __invoke(
        Request $request,
        MissionFlowService $missionFlow,
        SmartOffersService $offers,
        EcoParkOnlineGameService $onlineGame,
    ): Response {
        $campaign = $this->campaign();
        $user = $request->user();
        $visit = $user instanceof User && $campaign
            ? $this->selectedVisit($request, $user, $campaign)
            : null;

        return Inertia::render('games/ecopark-treasure', [
            'game' => [
                'campaign' => $campaign ? $this->serializeCampaign($campaign) : null,
                'entryQr' => $campaign ? $this->entryQr($campaign) : null,
                'onsiteGate' => $campaign ? $this->onsiteGate($campaign) : null,
                'physicalCheckpoints' => $campaign ? $this->physicalCheckpoints($campaign) : [],
                'missionNodes' => $campaign ? $this->missionNodes($campaign) : [],
                'gameOffers' => $offers->gameOffersForCampaign($campaign)->all(),
                'definition' => $onlineGame->definition(),
                'party' => $user instanceof User && $campaign
                    ? $onlineGame->serializeParty($onlineGame->partyFor($user, $campaign), $user)
                    : null,
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
        $qr = $campaign->qrCodes->first(fn ($qr): bool => $qr->isAvailableForLanding()
            && data_get($qr->metadata, 'online_game_role', 'start') === 'start');

        if (! $qr) {
            return null;
        }

        return [
            'code' => $qr->code,
            'label' => $qr->touchpoint?->label,
            'scanUrl' => route('scan.landing', ['code' => $qr->code]),
        ];
    }

    /** @return array<string, mixed>|null */
    private function onsiteGate(Campaign $campaign): ?array
    {
        $qr = $campaign->qrCodes->first(fn ($qr): bool => $qr->isAvailableForLanding()
            && data_get($qr->metadata, 'online_game_role') === 'onsite_gate');

        if (! $qr) {
            return null;
        }

        return [
            'label' => $qr->touchpoint->label ?? 'دروازه حضور بازی اکسپلوریا',
            'location' => data_get(
                $qr->metadata,
                'public_location',
                'ورودی اصلی اکوپارک عباس‌آباد، کنار میز راهنمای بازدیدکنندگان',
            ),
            'findingInstruction' => data_get(
                $qr->metadata,
                'finding_instruction',
                'استند سبز اکسپلوریا با عنوان «دروازه حضور بازی» را پیدا کنید.',
            ),
            'isDemo' => (bool) data_get($qr->metadata, 'is_demo', false),
            'demoScanUrl' => data_get($qr->metadata, 'is_demo', false)
                ? route('scan.landing', ['code' => $qr->code])
                : null,
        ];
    }

    /** @return list<array<string, mixed>> */
    private function physicalCheckpoints(Campaign $campaign): array
    {
        return array_values($campaign->qrCodes
            ->filter(fn ($qr): bool => $qr->isAvailableForLanding()
                && data_get($qr->metadata, 'online_game_role') === 'physical_checkpoint')
            ->map(fn ($qr): array => [
                'key' => (string) data_get($qr->metadata, 'checkpoint_key'),
                'label' => $qr->touchpoint->label ?? $qr->label,
                'location' => data_get($qr->metadata, 'public_location'),
                'findingInstruction' => data_get($qr->metadata, 'finding_instruction'),
                'isDemo' => (bool) data_get($qr->metadata, 'is_demo', false),
                'demoScanUrl' => data_get($qr->metadata, 'is_demo', false)
                    ? route('scan.landing', ['code' => $qr->code])
                    : null,
            ])
            ->values()
            ->all());
    }

    /** @return array<int, array<string, mixed>> */
    private function missionNodes(Campaign $campaign): array
    {
        return $campaign->missionInstances
            ->sortBy(fn (MissionInstance $mission): int => (int) data_get(
                $mission->metadata,
                'cycle_step_index',
                999999,
            ))
            ->values()
            ->map(fn (MissionInstance $mission, int $index): array => [
                'id' => $mission->id,
                'code' => $mission->code,
                'title' => $mission->title_override ?? $mission->missionTemplate->title,
                'place' => $mission->hub_id ? $mission->hub->name : $mission->touchpoint?->label,
                'hubName' => $mission->hub?->name,
                'touchpointLabel' => $mission->touchpoint?->label,
                'clue' => $mission->metadata['visitor_instruction']
                    ?? $mission->missionTemplate->description
                    ?? 'سرنخ این مرحله در مسیر کمپین نمایش داده می‌شود.',
                'mission' => $mission->missionTemplate->description,
                'missionType' => $mission->missionTemplate->mission_type,
                'triggerType' => $mission->missionTemplate->trigger_type,
                'completionEvidence' => $mission->metadata['completion_evidence'] ?? null,
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

    private function selectedVisit(Request $request, User $user, Campaign $campaign): ?Visit
    {
        $visitId = $request->string('visit')->toString();

        if ($visitId !== '') {
            $visit = Visit::query()
                ->whereKey($visitId)
                ->where('user_id', $user->id)
                ->where('campaign_id', $campaign->id)
                ->first();

            if ($visit instanceof Visit) {
                return $visit;
            }
        }

        return $this->latestVisit($user, $campaign);
    }
}
