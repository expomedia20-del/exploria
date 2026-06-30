<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\MissionInstance;
use App\Models\RewardDefinition;
use App\Models\User;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MissionFlowService
{
    /** @return array<string, mixed> */
    public function visitMissionSummary(User $user, Visit $visit): array
    {
        $this->ensureVisitOwner($user, $visit);

        $missions = $this->missionsForVisit($visit);
        $progressByMissionId = UserMissionProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('mission_instance_id', $missions->pluck('id'))
            ->get()
            ->keyBy('mission_instance_id');
        $completedPoints = $this->completedPointsForCampaign($user, $visit->campaign_id);
        $serializedMissions = $missions
            ->map(fn (MissionInstance $mission): array => $this->serializeMission(
                $mission,
                $progressByMissionId->get($mission->id),
                $completedPoints,
            ))
            ->values();
        $wallet = $this->walletForUser($user, $visit->campaign_id);

        return [
            'stats' => [
                'totalPoints' => $completedPoints,
                'completedMissions' => $serializedMissions->where('status', 'completed')->count(),
                'availableMissions' => $serializedMissions->where('isLocked', false)->count(),
                'rewards' => count($wallet),
            ],
            'missions' => $serializedMissions,
            'rewards' => $wallet,
        ];
    }

    public function start(User $user, Visit $visit, MissionInstance $mission): UserMissionProgress
    {
        $this->ensureMissionCanBeUsed($user, $visit, $mission);

        return DB::transaction(function () use ($user, $visit, $mission): UserMissionProgress {
            return UserMissionProgress::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'mission_instance_id' => $mission->id,
                ],
                [
                    'visit_id' => $visit->id,
                    'status' => 'started',
                    'started_at' => now(),
                    'points_awarded' => 0,
                    'metadata' => ['source' => 'visit_mission_flow'],
                ],
            );
        });
    }

    /** @return array{progress: UserMissionProgress, reward: UserReward|null} */
    public function complete(User $user, Visit $visit, MissionInstance $mission): array
    {
        $this->ensureMissionCanBeUsed($user, $visit, $mission);

        return DB::transaction(function () use ($user, $visit, $mission): array {
            $progress = UserMissionProgress::query()->firstOrNew([
                'user_id' => $user->id,
                'mission_instance_id' => $mission->id,
            ]);

            if ($progress->status === 'completed') {
                return ['progress' => $progress, 'reward' => $this->existingRewardForMission($user, $mission)];
            }

            $mission->loadMissing('missionTemplate');
            $progress->fill([
                'visit_id' => $visit->id,
                'status' => 'completed',
                'started_at' => $progress->started_at ?? now(),
                'completed_at' => now(),
                'points_awarded' => $mission->missionTemplate->point_value,
                'metadata' => ['source' => 'visit_mission_flow'],
            ]);
            $progress->save();

            return ['progress' => $progress, 'reward' => $this->awardRewardForMission($user, $mission)];
        });
    }

    /** @return array<int, array<string, mixed>> */
    public function walletForUser(User $user, ?string $campaignId = null): array
    {
        return UserReward::query()
            ->with(['rewardDefinition.partnerAccount:id,code,name,partner_type'])
            ->where('user_id', $user->id)
            ->when($campaignId, fn ($query) => $query->where('campaign_id', $campaignId))
            ->latest('awarded_at')
            ->get()
            ->map(fn (UserReward $reward): array => [
                'id' => $reward->id,
                'status' => $reward->status,
                'awardedAt' => $reward->awarded_at?->toIso8601String(),
                'expiresAt' => $reward->expires_at?->toIso8601String(),
                'reward' => $reward->rewardDefinition ? [
                    'id' => $reward->rewardDefinition->id,
                    'code' => $reward->rewardDefinition->code,
                    'name' => $reward->rewardDefinition->name,
                    'rewardType' => $reward->rewardDefinition->reward_type,
                    'partnerName' => $reward->rewardDefinition->partnerAccount?->name,
                ] : null,
            ])
            ->values()
            ->all();
    }

    private function ensureMissionCanBeUsed(User $user, Visit $visit, MissionInstance $mission): void
    {
        $this->ensureVisitOwner($user, $visit);
        $mission->loadMissing('missionTemplate');

        if ($mission->campaign_id !== $visit->campaign_id || $mission->venue_id !== $visit->venue_id) {
            throw ValidationException::withMessages([
                'mission' => 'این ماموریت برای بازدید فعلی معتبر نیست.',
            ]);
        }

        if (! $this->isMissionActive($mission)) {
            throw ValidationException::withMessages([
                'mission' => 'این ماموریت فعال یا در بازه زمانی معتبر نیست.',
            ]);
        }

        if ($this->isLocked($mission, $this->completedPointsForCampaign($user, $visit->campaign_id))) {
            throw ValidationException::withMessages([
                'mission' => 'شرط باز شدن این ماموریت هنوز کامل نشده است.',
            ]);
        }
    }

    private function ensureVisitOwner(User $user, Visit $visit): void
    {
        if ($visit->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'visit' => 'این بازدید متعلق به کاربر فعلی نیست.',
            ]);
        }
    }

    /** @return EloquentCollection<int, MissionInstance> */
    private function missionsForVisit(Visit $visit): EloquentCollection
    {
        return MissionInstance::query()
            ->with(['missionTemplate', 'hub:id,code,name', 'touchpoint:id,code,label', 'treasure:id,mission_instance_id,code,name'])
            ->where('campaign_id', $visit->campaign_id)
            ->where('venue_id', $visit->venue_id)
            ->orderBy('created_at')
            ->get();
    }

    private function completedPointsForCampaign(User $user, string $campaignId): int
    {
        return (int) UserMissionProgress::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereHas('missionInstance', fn ($query) => $query->where('campaign_id', $campaignId))
            ->sum('points_awarded');
    }

    /** @return array<string, mixed> */
    private function serializeMission(MissionInstance $mission, ?UserMissionProgress $progress, int $completedPoints): array
    {
        $mission->loadMissing('missionTemplate');
        $locked = $this->isLocked($mission, $completedPoints);
        $status = $progress ? $progress->status : ($locked ? 'locked' : 'available');

        return [
            'id' => $mission->id,
            'code' => $mission->code,
            'title' => $mission->title_override ?? $mission->missionTemplate->title,
            'description' => $mission->metadata['visitor_instruction'] ?? $mission->missionTemplate->description,
            'templateDescription' => $mission->missionTemplate->description,
            'completionEvidence' => $mission->metadata['completion_evidence'] ?? $this->evidenceLabel($mission),
            'successMessage' => $mission->metadata['success_message'] ?? null,
            'cycleStep' => [
                'index' => $mission->metadata['cycle_step_index'] ?? null,
                'label' => $mission->metadata['cycle_step_label'] ?? null,
            ],
            'status' => $status,
            'isLocked' => $locked,
            'canStart' => ! $locked && $progress === null,
            'canComplete' => ! $locked && $status !== 'completed',
            'points' => $mission->missionTemplate->point_value,
            'missionType' => $mission->missionTemplate->mission_type,
            'triggerType' => $mission->missionTemplate->trigger_type,
            'hubName' => $mission->hub?->name,
            'touchpointLabel' => $mission->touchpoint?->label,
            'treasureName' => $mission->treasure?->name,
            'unlockRule' => $mission->unlock_rule,
            'rewardCode' => data_get($mission->metadata, 'reward_code'),
            'startedAt' => $progress?->started_at?->toIso8601String(),
            'completedAt' => $progress?->completed_at?->toIso8601String(),
        ];
    }

    private function evidenceLabel(MissionInstance $mission): string
    {
        return match ($mission->missionTemplate->trigger_type) {
            'qr_scan' => 'اسکن QR معتبر',
            'location_hint' => 'حضور در نقطه راهنما یا مشاهده نشانه مسیر',
            'content_view' => 'مشاهده محتوا یا پاسخ کوتاه',
            'admin_approval' => 'تأیید مجری یا ادمین',
            default => 'ثبت انجام مأموریت',
        };
    }

    private function isMissionActive(MissionInstance $mission): bool
    {
        $now = now();

        return $mission->status === RecordStatus::Active
            && $mission->missionTemplate->status === RecordStatus::Active
            && (! $mission->starts_at || $mission->starts_at->lessThanOrEqualTo($now))
            && (! $mission->ends_at || $mission->ends_at->isFuture());
    }

    private function isLocked(MissionInstance $mission, int $completedPoints): bool
    {
        $minimumPoints = data_get($mission->unlock_rule, 'min_points');

        return is_numeric($minimumPoints) && $completedPoints < (int) $minimumPoints;
    }

    private function awardRewardForMission(User $user, MissionInstance $mission): ?UserReward
    {
        $rewardCode = data_get($mission->metadata, 'reward_code');

        if (! is_string($rewardCode) || $rewardCode === '') {
            return null;
        }

        $rewardDefinition = RewardDefinition::query()
            ->where('campaign_id', $mission->campaign_id)
            ->where('code', $rewardCode)
            ->where('status', RecordStatus::Active)
            ->first();

        if (! $rewardDefinition) {
            return null;
        }

        $userReward = UserReward::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'reward_definition_id' => $rewardDefinition->id,
                'campaign_id' => $mission->campaign_id,
            ],
            [
                'status' => 'awarded',
                'awarded_at' => now(),
                'metadata' => ['source' => 'mission_completed', 'mission_code' => $mission->code],
            ],
        );

        if ($rewardDefinition->partner_account_id) {
            app(PartnerDashboardService::class)->ensureRedemptionForReward($userReward);
        }

        return $userReward;
    }

    private function existingRewardForMission(User $user, MissionInstance $mission): ?UserReward
    {
        $rewardCode = data_get($mission->metadata, 'reward_code');

        if (! is_string($rewardCode) || $rewardCode === '') {
            return null;
        }

        $rewardDefinition = RewardDefinition::query()
            ->where('campaign_id', $mission->campaign_id)
            ->where('code', $rewardCode)
            ->first();

        if (! $rewardDefinition) {
            return null;
        }

        return UserReward::query()
            ->where('user_id', $user->id)
            ->where('reward_definition_id', $rewardDefinition->id)
            ->where('campaign_id', $mission->campaign_id)
            ->first();
    }
}
