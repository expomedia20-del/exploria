<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\Campaign;
use App\Models\Hub;
use App\Models\MissionInstance;
use App\Models\MissionTemplate;
use App\Models\PartnerAccount;
use App\Models\RewardDefinition;
use App\Models\Touchpoint;
use App\Models\Treasure;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MissionRewardRegistryService
{
    public function __construct(
        private readonly UserAccessScopeService $accessScopes,
        private readonly CampaignBlueprintConsistencyService $blueprintConsistency,
    ) {}

    /** @return array<string, mixed> */
    public function overview(?User $user = null, ?string $campaignId = null): array
    {
        $campaign = $campaignId ? Campaign::query()->find($campaignId) : null;
        $venueIds = $user ? $this->accessScopes->assignedVenueIds($user) : collect();
        $hubIds = $user ? $this->accessScopes->hubIds($user) : collect();
        $partnerIds = $user ? $this->accessScopes->partnerIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        $missions = MissionInstance::query()
            ->when($campaignId, fn (Builder $query) => $query->where('campaign_id', $campaignId))
            ->when(! $isGlobal, fn (Builder $query) => $query->where(function (Builder $query) use ($venueIds, $hubIds): void {
                $query->whereIn('venue_id', $venueIds)
                    ->orWhereIn('hub_id', $hubIds);
            }))
            ->with([
                'missionTemplate:id,code,title,mission_type,trigger_type,point_value,status',
                'campaign:id,code,name',
                'venue:id,code,name',
                'hub:id,code,name',
                'touchpoint:id,code,label',
                'treasure:id,mission_instance_id,code,name,treasure_type,status',
            ])
            ->withCount('progressRecords')
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (MissionInstance $mission): array => $this->serializeMission($mission));

        $rewards = RewardDefinition::query()
            ->when($campaignId, fn (Builder $query) => $query->where('campaign_id', $campaignId))
            ->when(! $isGlobal, fn (Builder $query) => $query->where(function (Builder $query) use ($venueIds, $partnerIds): void {
                $query->whereIn('venue_id', $venueIds)
                    ->orWhereIn('partner_account_id', $partnerIds);
            }))
            ->with(['campaign:id,code,name', 'venue:id,code,name', 'partnerAccount:id,code,name,partner_type'])
            ->withCount('userRewards')
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (RewardDefinition $reward): array => $this->serializeReward($reward));

        $treasures = Treasure::query()
            ->when($campaignId, fn (Builder $query) => $query->where('campaign_id', $campaignId))
            ->when(! $isGlobal, fn (Builder $query) => $query->whereIn('venue_id', $venueIds))
            ->with(['campaign:id,code,name', 'venue:id,code,name', 'missionInstance:id,code'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (Treasure $treasure): array => $this->serializeTreasure($treasure));

        return [
            'stats' => [
                'missions' => $missions->count(),
                'activeMissions' => $missions->where('status', 'active')->count(),
                'totalPoints' => $missions->sum('points'),
                'rewards' => $rewards->count(),
                'pendingRewards' => $rewards->where('approvalStatus', 'pending_review')->count(),
                'approvedRewards' => $rewards->where('approvalStatus', 'approved')->count(),
                'rejectedRewards' => $rewards->where('approvalStatus', 'rejected')->count(),
                'treasures' => $treasures->count(),
            ],
            'missions' => $missions,
            'rewards' => $rewards,
            'treasures' => $treasures,
            'alignment' => $campaign ? $this->blueprintConsistency->review($campaign) : null,
            'formOptions' => $this->formOptions($campaignId),
        ];
    }

    /** @return array<string, mixed> */
    public function formOptions(?string $campaignId): array
    {
        $campaign = $campaignId ? Campaign::query()->find($campaignId) : null;
        $venueId = $campaign?->venue_id;

        return [
            'missionTemplates' => MissionTemplate::query()
                ->where('status', RecordStatus::Active)
                ->get(['id', 'code', 'title', 'description', 'mission_type', 'trigger_type', 'point_value'])
                ->map(function (MissionTemplate $template) use ($campaign): array {
                    $recommendation = $this->missionTemplateRecommendation($template, $campaign);

                    return [
                        'id' => $template->id,
                        'code' => $template->code,
                        'title' => $template->title,
                        'description' => $template->description,
                        'missionType' => $template->mission_type,
                        'triggerType' => $template->trigger_type,
                        'points' => $template->point_value,
                        'recommended' => $recommendation['score'] > 0,
                        'recommendationReason' => $recommendation['reason'],
                        '_score' => $recommendation['score'],
                    ];
                })
                ->sortBy([
                    ['_score', 'desc'],
                    ['points', 'asc'],
                    ['title', 'asc'],
                ])
                ->values()
                ->map(fn (array $template): array => collect($template)->except('_score')->all()),
            'hubs' => Hub::query()
                ->when($venueId, fn (Builder $query) => $query->whereHas('zone', fn (Builder $zone) => $zone->where('venue_id', $venueId)))
                ->orderBy('name')
                ->get(['id', 'code', 'name'])
                ->map(fn (Hub $hub): array => ['id' => $hub->id, 'code' => $hub->code, 'name' => $hub->name]),
            'touchpoints' => Touchpoint::query()
                ->when($venueId, fn (Builder $query) => $query->whereHas('hub.zone', fn (Builder $zone) => $zone->where('venue_id', $venueId)))
                ->orderBy('label')
                ->get(['id', 'hub_id', 'code', 'label'])
                ->map(fn (Touchpoint $touchpoint): array => ['id' => $touchpoint->id, 'hubId' => $touchpoint->hub_id, 'code' => $touchpoint->code, 'label' => $touchpoint->label]),
            'partners' => PartnerAccount::query()
                ->when($venueId, fn (Builder $query) => $query->where('venue_id', $venueId))
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'partner_type'])
                ->map(fn (PartnerAccount $partner): array => ['id' => $partner->id, 'code' => $partner->code, 'name' => $partner->name, 'partnerType' => $partner->partner_type]),
        ];
    }

    /** @return array{score: int, reason: string|null} */
    private function missionTemplateRecommendation(MissionTemplate $template, ?Campaign $campaign): array
    {
        if (! $campaign) {
            return ['score' => 0, 'reason' => null];
        }

        $blueprint = (string) ($campaign->metadata['blueprint_code'] ?? '');
        $context = strtolower($blueprint.' '.$campaign->campaign_type);
        $code = $template->code;

        $rules = [
            'scan-entry-qr' => [
                'score' => 90,
                'keywords' => ['pilot', 'starter', 'entry', 'online', 'treasure-route', 'hologram'],
                'reason' => 'برای نقطه شروع، QR ورودی و ساخت اولین گام نقشه عملیات مناسب است.',
            ],
            'discover-route-guide' => [
                'score' => 80,
                'keywords' => ['route', 'treasure', 'ravaq', 'foodcourt', 'quiet', 'hidden'],
                'reason' => 'برای اتصال مأموریت به مسیر، هاب و نقطه تماس عملیاتی مناسب است.',
            ],
            'watch-place-story' => [
                'score' => 70,
                'keywords' => ['online', 'story', 'star', 'science', 'photo', 'bridge'],
                'reason' => 'برای روایت مکان، محتوای راهنما و مأموریت‌های آموزشی/رسانه‌ای مناسب است.',
            ],
            'photo-memory-challenge' => [
                'score' => 60,
                'keywords' => ['photo', 'challenge', 'skate', 'family', 'sponsor', 'legendary'],
                'reason' => 'برای مشارکت عمیق‌تر، تأیید مجری و چالش‌های مرحله‌ای مناسب است.',
            ],
        ];

        $rule = $rules[$code] ?? null;

        if (! $rule) {
            return ['score' => 0, 'reason' => null];
        }

        foreach ($rule['keywords'] as $keyword) {
            if (str_contains($context, $keyword)) {
                return ['score' => $rule['score'], 'reason' => $rule['reason']];
            }
        }

        return ['score' => 10, 'reason' => 'قابل استفاده برای این کمپین، اما اولویت پیشنهادی پایین‌تری دارد.'];
    }

    /** @param array<string, mixed> $data */
    public function createMission(array $data): MissionInstance
    {
        $campaign = Campaign::query()->findOrFail($data['campaign_id']);

        $this->assertSameVenueHub($campaign, $data['hub_id'] ?? null);
        $this->assertSameVenueTouchpoint($campaign, $data['touchpoint_id'] ?? null);
        $this->blueprintConsistency->assertMissionInput($campaign, $data);

        $attributes = [
            'mission_template_id' => $data['mission_template_id'],
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'hub_id' => $data['hub_id'] ?? null,
            'touchpoint_id' => $data['touchpoint_id'] ?? null,
            'code' => $data['code'],
            'title_override' => $data['title_override'] ?? null,
            'status' => $data['status'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'unlock_rule' => isset($data['unlock_min_points']) ? ['min_points' => (int) $data['unlock_min_points']] : null,
            'metadata' => [
                'source' => 'admin_campaign_components',
                'cycle_step_index' => $data['cycle_step_index'] ?? null,
                'cycle_step_label' => $data['cycle_step_label'] ?? null,
                'visitor_instruction' => $data['visitor_instruction'] ?? null,
                'completion_evidence' => $data['completion_evidence'] ?? null,
                'success_message' => $data['success_message'] ?? null,
            ],
        ];

        return DB::transaction(fn (): MissionInstance => $this->replaceMissionCycleStep($campaign, $data['cycle_step_index'] ?? null, $attributes));
    }

    /** @param array<string, mixed> $data */
    public function createReward(array $data): RewardDefinition
    {
        $campaign = Campaign::query()->findOrFail($data['campaign_id']);
        $this->assertSameVenuePartner($campaign, $data['partner_account_id'] ?? null);
        $this->blueprintConsistency->assertRewardInput($campaign, $data);

        $attributes = [
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'partner_account_id' => $data['partner_account_id'] ?? null,
            'code' => $data['code'],
            'name' => $data['name'],
            'reward_type' => $data['reward_type'],
            'point_cost' => $data['point_cost'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? null,
            'status' => $data['status'],
            'metadata' => [
                'source' => 'admin_campaign_components',
                'approval_status' => $data['status'],
                'availability_status' => $data['status'] === RecordStatus::Inactive->value ? 'paused' : 'active',
                'reward_tier' => $data['reward_tier'] ?? null,
                'reward_option' => $data['reward_option'] ?? null,
                'cycle_step_index' => $data['cycle_step_index'] ?? null,
                'cycle_step_label' => $data['cycle_step_label'] ?? null,
                'available_from' => $data['available_from'] ?? null,
                'available_until' => $data['available_until'] ?? null,
                'fulfillment_window' => $data['fulfillment_window'] ?? null,
                'description' => $data['description'] ?? null,
                'terms' => $data['terms'] ?? null,
            ],
        ];

        return DB::transaction(fn (): RewardDefinition => $this->replaceRewardCycleStep($campaign, $data['cycle_step_index'] ?? null, $attributes));
    }

    /** @param array<string, mixed> $data */
    public function createTreasure(array $data): Treasure
    {
        $campaign = Campaign::query()->findOrFail($data['campaign_id']);
        $data['mission_instance_id'] ??= $this->missionIdForCycleStep($campaign, $data['cycle_step_index'] ?? null);

        $this->assertSameCampaignMission($campaign, $data['mission_instance_id'] ?? null);
        $this->blueprintConsistency->assertTreasureInput($campaign, $data);

        $attributes = [
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'mission_instance_id' => $data['mission_instance_id'] ?? null,
            'code' => $data['code'],
            'name' => $data['name'],
            'treasure_type' => $data['treasure_type'],
            'status' => $data['status'],
            'reveal_rule' => [
                'required_completed_missions' => isset($data['required_completed_missions']) ? (int) $data['required_completed_missions'] : null,
                'required_min_points' => isset($data['required_min_points']) ? (int) $data['required_min_points'] : null,
                'required_reward_tier' => $data['treasure_tier'] ?? null,
                'reveal_mode' => $data['reveal_mode'] ?? null,
            ],
            'metadata' => [
                'source' => 'admin_campaign_components',
                'cycle_step_index' => $data['cycle_step_index'] ?? null,
                'cycle_step_label' => $data['cycle_step_label'] ?? null,
                'treasure_tier' => $data['treasure_tier'] ?? null,
                'reveal_description' => $data['reveal_description'] ?? null,
                'discovery_hint' => $data['discovery_hint'] ?? null,
            ],
        ];

        return DB::transaction(fn (): Treasure => $this->replaceTreasureCycleStep($campaign, $data['cycle_step_index'] ?? null, $attributes, $data['treasure_id'] ?? null));
    }

    private function missionIdForCycleStep(Campaign $campaign, mixed $cycleStepIndex): ?string
    {
        if (! $cycleStepIndex) {
            return null;
        }

        return MissionInstance::query()
            ->where('campaign_id', $campaign->id)
            ->where('metadata->cycle_step_index', (int) $cycleStepIndex)
            ->latest()
            ->value('id');
    }

    public function deleteMission(MissionInstance $mission): void
    {
        if ($mission->progressRecords()->exists()) {
            throw ValidationException::withMessages(['mission' => 'این مأموریت سابقه پیشرفت کاربر دارد و حذف مستقیم آن مجاز نیست.']);
        }

        $mission->delete();
    }

    public function deleteReward(RewardDefinition $reward): void
    {
        if ($reward->userRewards()->exists()) {
            throw ValidationException::withMessages(['reward' => 'این پاداش سابقه دریافت کاربر دارد و حذف مستقیم آن مجاز نیست.']);
        }

        $reward->delete();
    }

    public function deleteTreasure(Treasure $treasure): void
    {
        $treasure->delete();
    }

    /** @param array<string, mixed> $attributes */
    private function replaceMissionCycleStep(Campaign $campaign, mixed $cycleStepIndex, array $attributes): MissionInstance
    {
        if (! $cycleStepIndex) {
            return MissionInstance::query()->create($attributes);
        }

        $previousMissions = MissionInstance::query()
            ->where('campaign_id', $campaign->id)
            ->where('metadata->cycle_step_index', (int) $cycleStepIndex)
            ->withCount('progressRecords')
            ->latest()
            ->get();

        $missionWithProgress = $previousMissions->first(fn (MissionInstance $mission): bool => (int) $mission->getAttribute('progress_records_count') > 0);

        $previousMissions
            ->reject(fn (MissionInstance $mission): bool => $missionWithProgress?->is($mission) ?? false)
            ->each(fn (MissionInstance $mission): ?bool => $mission->delete());

        if ($missionWithProgress) {
            $missionWithProgress->update($attributes);

            return $missionWithProgress->refresh();
        }

        return MissionInstance::query()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    private function replaceRewardCycleStep(Campaign $campaign, mixed $cycleStepIndex, array $attributes): RewardDefinition
    {
        if (! $cycleStepIndex) {
            return RewardDefinition::query()->create($attributes);
        }

        $previousRewards = RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->where('metadata->cycle_step_index', (int) $cycleStepIndex)
            ->withCount('userRewards')
            ->latest()
            ->get();

        $rewardWithUsers = $previousRewards->first(fn (RewardDefinition $reward): bool => (int) $reward->getAttribute('user_rewards_count') > 0);

        $previousRewards
            ->reject(fn (RewardDefinition $reward): bool => $rewardWithUsers?->is($reward) ?? false)
            ->each(fn (RewardDefinition $reward): ?bool => $reward->delete());

        if ($rewardWithUsers) {
            $rewardWithUsers->update($attributes);

            return $rewardWithUsers->refresh();
        }

        return RewardDefinition::query()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    private function replaceTreasureCycleStep(Campaign $campaign, mixed $cycleStepIndex, array $attributes, mixed $treasureId = null): Treasure
    {
        if ($treasureId) {
            $treasure = Treasure::query()
                ->where('campaign_id', $campaign->id)
                ->whereKey((string) $treasureId)
                ->firstOrFail();

            if ($cycleStepIndex) {
                Treasure::query()
                    ->where('campaign_id', $campaign->id)
                    ->where('metadata->cycle_step_index', (int) $cycleStepIndex)
                    ->whereKeyNot($treasure->id)
                    ->delete();
            }

            $treasure->update($attributes);

            return $treasure->refresh();
        }

        if (! $cycleStepIndex) {
            return Treasure::query()->create($attributes);
        }

        $previousTreasures = Treasure::query()
            ->where('campaign_id', $campaign->id)
            ->where('metadata->cycle_step_index', (int) $cycleStepIndex)
            ->latest()
            ->get();

        $treasureToKeep = $previousTreasures->first();

        $previousTreasures
            ->reject(fn (Treasure $treasure): bool => $treasureToKeep?->is($treasure) ?? false)
            ->each(fn (Treasure $treasure): ?bool => $treasure->delete());

        if ($treasureToKeep) {
            $treasureToKeep->update($attributes);

            return $treasureToKeep->refresh();
        }

        return Treasure::query()->create($attributes);
    }

    /** @return array<string, mixed> */
    private function serializeMission(MissionInstance $mission): array
    {
        return [
            'id' => $mission->id,
            'code' => $mission->code,
            'title' => $mission->title_override ?? $mission->missionTemplate?->title,
            'status' => $mission->status->value,
            'missionTemplate' => $mission->missionTemplate ? ['id' => $mission->missionTemplate->id, 'code' => $mission->missionTemplate->code, 'title' => $mission->missionTemplate->title] : null,
            'missionType' => $mission->missionTemplate?->mission_type,
            'triggerType' => $mission->missionTemplate?->trigger_type,
            'points' => $mission->missionTemplate->point_value,
            'startsAt' => $mission->starts_at?->toIso8601String(),
            'endsAt' => $mission->ends_at?->toIso8601String(),
            'unlockRule' => $mission->unlock_rule,
            'visitorInstruction' => $mission->metadata['visitor_instruction'] ?? null,
            'completionEvidence' => $mission->metadata['completion_evidence'] ?? null,
            'successMessage' => $mission->metadata['success_message'] ?? null,
            'cycleStep' => [
                'index' => $mission->metadata['cycle_step_index'] ?? null,
                'label' => $mission->metadata['cycle_step_label'] ?? null,
            ],
            'progressCount' => (int) $mission->getAttribute('progress_records_count'),
            'campaign' => $mission->campaign ? ['id' => $mission->campaign->id, 'code' => $mission->campaign->code, 'name' => $mission->campaign->name] : null,
            'venue' => $mission->venue ? ['id' => $mission->venue->id, 'code' => $mission->venue->code, 'name' => $mission->venue->name] : null,
            'hub' => $mission->hub ? ['id' => $mission->hub->id, 'code' => $mission->hub->code, 'name' => $mission->hub->name] : null,
            'touchpoint' => $mission->touchpoint ? ['id' => $mission->touchpoint->id, 'code' => $mission->touchpoint->code, 'label' => $mission->touchpoint->label] : null,
            'treasure' => $mission->treasure ? ['id' => $mission->treasure->id, 'code' => $mission->treasure->code, 'name' => $mission->treasure->name, 'treasureType' => $mission->treasure->treasure_type] : null,
        ];
    }

    /** @return array<string, mixed> */
    private function serializeReward(RewardDefinition $reward): array
    {
        return [
            'id' => $reward->id,
            'code' => $reward->code,
            'name' => $reward->name,
            'rewardType' => $reward->reward_type,
            'status' => $reward->status->value,
            'approvalStatus' => $reward->metadata['approval_status'] ?? $reward->status->value,
            'availabilityStatus' => $reward->metadata['availability_status'] ?? ($reward->status->value === 'inactive' ? 'paused' : 'active'),
            'source' => $reward->metadata['source'] ?? null,
            'rewardTier' => $reward->metadata['reward_tier'] ?? null,
            'rewardOption' => $reward->metadata['reward_option'] ?? null,
            'cycleStep' => [
                'index' => $reward->metadata['cycle_step_index'] ?? null,
                'label' => $reward->metadata['cycle_step_label'] ?? null,
            ],
            'availableFrom' => $reward->metadata['available_from'] ?? null,
            'availableUntil' => $reward->metadata['available_until'] ?? null,
            'fulfillmentWindow' => $reward->metadata['fulfillment_window'] ?? null,
            'description' => $reward->metadata['description'] ?? null,
            'terms' => $reward->metadata['terms'] ?? null,
            'reviewNotes' => $reward->metadata['review_notes'] ?? null,
            'submittedAt' => $reward->metadata['submitted_at'] ?? $reward->created_at?->toIso8601String(),
            'reviewedAt' => $reward->metadata['approved_at'] ?? $reward->metadata['rejected_at'] ?? null,
            'pointCost' => $reward->point_cost,
            'stockQuantity' => $reward->stock_quantity,
            'awardedCount' => (int) $reward->getAttribute('user_rewards_count'),
            'campaign' => $reward->campaign ? ['id' => $reward->campaign->id, 'code' => $reward->campaign->code, 'name' => $reward->campaign->name] : null,
            'venue' => $reward->venue ? ['id' => $reward->venue->id, 'code' => $reward->venue->code, 'name' => $reward->venue->name] : null,
            'partner' => $reward->partnerAccount ? ['id' => $reward->partnerAccount->id, 'code' => $reward->partnerAccount->code, 'name' => $reward->partnerAccount->name, 'partnerType' => $reward->partnerAccount->partner_type] : null,
        ];
    }

    /** @return array<string, mixed> */
    private function serializeTreasure(Treasure $treasure): array
    {
        return [
            'id' => $treasure->id,
            'code' => $treasure->code,
            'name' => $treasure->name,
            'treasureType' => $treasure->treasure_type,
            'status' => $treasure->status->value,
            'revealRule' => $treasure->reveal_rule,
            'treasureTier' => $treasure->metadata['treasure_tier'] ?? null,
            'revealMode' => $treasure->reveal_rule['reveal_mode'] ?? null,
            'revealDescription' => $treasure->metadata['reveal_description'] ?? null,
            'discoveryHint' => $treasure->metadata['discovery_hint'] ?? null,
            'source' => $treasure->metadata['source'] ?? null,
            'cycleStep' => [
                'index' => $treasure->metadata['cycle_step_index'] ?? null,
                'label' => $treasure->metadata['cycle_step_label'] ?? null,
            ],
            'campaign' => $treasure->campaign ? ['id' => $treasure->campaign->id, 'code' => $treasure->campaign->code, 'name' => $treasure->campaign->name] : null,
            'venue' => $treasure->venue ? ['id' => $treasure->venue->id, 'code' => $treasure->venue->code, 'name' => $treasure->venue->name] : null,
            'missionCode' => $treasure->missionInstance?->code,
        ];
    }

    private function assertSameVenueHub(Campaign $campaign, ?string $hubId): void
    {
        if (! $hubId) {
            return;
        }

        $matches = Hub::query()
            ->whereKey($hubId)
            ->whereHas('zone', fn (Builder $query) => $query->where('venue_id', $campaign->venue_id))
            ->exists();

        if (! $matches) {
            throw ValidationException::withMessages(['hub_id' => 'هاب انتخاب‌شده به مکان کمپین تعلق ندارد.']);
        }
    }

    private function assertSameVenueTouchpoint(Campaign $campaign, ?string $touchpointId): void
    {
        if (! $touchpointId) {
            return;
        }

        $matches = Touchpoint::query()
            ->whereKey($touchpointId)
            ->whereHas('hub.zone', fn (Builder $query) => $query->where('venue_id', $campaign->venue_id))
            ->exists();

        if (! $matches) {
            throw ValidationException::withMessages(['touchpoint_id' => 'نقطه تماس انتخاب‌شده به مکان کمپین تعلق ندارد.']);
        }
    }

    private function assertSameVenuePartner(Campaign $campaign, ?string $partnerId): void
    {
        if (! $partnerId) {
            return;
        }

        if (! PartnerAccount::query()->whereKey($partnerId)->where('venue_id', $campaign->venue_id)->exists()) {
            throw ValidationException::withMessages(['partner_account_id' => 'مالک پاداش باید به مکان همین کمپین تعلق داشته باشد.']);
        }
    }

    private function assertSameCampaignMission(Campaign $campaign, ?string $missionId): void
    {
        if (! $missionId) {
            return;
        }

        if (! MissionInstance::query()->whereKey($missionId)->where('campaign_id', $campaign->id)->exists()) {
            throw ValidationException::withMessages(['mission_instance_id' => 'گنج فقط می‌تواند به مأموریت‌های همین کمپین وصل شود.']);
        }
    }
}
