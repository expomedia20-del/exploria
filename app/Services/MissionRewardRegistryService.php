<?php

namespace App\Services;

use App\Models\MissionInstance;
use App\Models\RewardDefinition;
use App\Models\Treasure;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class MissionRewardRegistryService
{
    public function __construct(private readonly UserAccessScopeService $accessScopes) {}

    /** @return array<string, mixed> */
    public function overview(?User $user = null): array
    {
        $venueIds = $user ? $this->accessScopes->assignedVenueIds($user) : collect();
        $hubIds = $user ? $this->accessScopes->hubIds($user) : collect();
        $partnerIds = $user ? $this->accessScopes->partnerIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        $missions = MissionInstance::query()
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
        ];
    }

    /** @return array<string, mixed> */
    private function serializeMission(MissionInstance $mission): array
    {
        return [
            'id' => $mission->id,
            'code' => $mission->code,
            'title' => $mission->title_override ?? $mission->missionTemplate?->title,
            'status' => $mission->status->value,
            'missionType' => $mission->missionTemplate?->mission_type,
            'triggerType' => $mission->missionTemplate?->trigger_type,
            'points' => $mission->missionTemplate->point_value,
            'startsAt' => $mission->starts_at?->toIso8601String(),
            'endsAt' => $mission->ends_at?->toIso8601String(),
            'unlockRule' => $mission->unlock_rule,
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
            'availableFrom' => $reward->metadata['available_from'] ?? null,
            'availableUntil' => $reward->metadata['available_until'] ?? null,
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
            'campaign' => $treasure->campaign ? ['id' => $treasure->campaign->id, 'code' => $treasure->campaign->code, 'name' => $treasure->campaign->name] : null,
            'venue' => $treasure->venue ? ['id' => $treasure->venue->id, 'code' => $treasure->venue->code, 'name' => $treasure->venue->name] : null,
            'missionCode' => $treasure->missionInstance?->code,
        ];
    }
}