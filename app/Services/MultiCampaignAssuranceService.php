<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\Campaign;
use App\Models\MissionInstance;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\RewardRedemption;
use App\Models\Treasure;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Venue;
use Illuminate\Support\Collection;

/**
 * @phpstan-type AssuranceCheck array{key: string, label: string, status: string, count: int, message: string, nextAction: string|null, minimum?: int}
 * @phpstan-type AssuranceReport array{
 *     summary: array{scope: string, ready: bool, activeCampaigns: int, campaignCodes: list<string>, passCount: int, warningCount: int, failCount: int, requireExecution: bool},
 *     checks: list<AssuranceCheck>,
 *     nextActions: list<string>
 * }
 */
class MultiCampaignAssuranceService
{
    /** @return AssuranceReport */
    public function report(?string $venueCode = null, int $minimumCampaigns = 2, bool $requireExecution = false): array
    {
        $minimumCampaigns = max(1, $minimumCampaigns);
        $venue = $venueCode ? Venue::query()->where('code', $venueCode)->first() : null;
        $campaignQuery = Campaign::query()->where('status', RecordStatus::Active);

        if ($venueCode) {
            $campaignQuery->where('venue_id', $venue instanceof Venue ? $venue->id : '__missing_venue__');
        }

        $campaigns = $campaignQuery->orderBy('created_at')->get(['id', 'code', 'name', 'campaign_type', 'venue_id']);
        $campaignIds = $campaigns->pluck('id');
        $scopeLabel = $venueCode ? "venue:{$venueCode}" : 'all-active-campaigns';
        $executionWarningOnly = ! $requireExecution;

        $checks = collect([
            $this->minimumCountCheck(
                'active_campaign_variety',
                'Active campaign variety',
                $campaigns->count(),
                $minimumCampaigns,
                'At least two active campaigns are available for cross-campaign verification.',
                'Create or activate a second campaign before treating the assurance result as multi-campaign evidence.',
            ),
            $this->minimumCountCheck(
                'active_qr_campaign_coverage',
                'Active QR coverage by campaign',
                $this->distinctActiveQrCampaigns($campaignIds),
                $minimumCampaigns,
                'Active QR entry points cover the required campaign set.',
                'Attach an active QR code to each campaign that must be proven before launch.',
            ),
            $this->minimumCountCheck(
                'mission_type_variety',
                'Mission type variety',
                $this->distinctActiveMissionTypes($campaignIds),
                3,
                'Mission templates cover QR, route/content, and challenge-style flows.',
                'Add active mission templates/instances for at least three mission types.',
            ),
            $this->minimumCountCheck(
                'reward_layer_variety',
                'Reward layer variety',
                $this->rewardLayerCount($campaignIds),
                3,
                'Internal, partner, and sponsor reward layers are represented.',
                'Define active internal, partner, and sponsor reward definitions.',
            ),
            $this->minimumCountCheck(
                'locked_mission_rules',
                'Locked mission rules',
                $this->lockedMissionCount($campaignIds),
                1,
                'At least one point-gated mission exists.',
                'Add a mission with an unlock rule such as min_points to prove progression gates.',
            ),
            $this->minimumCountCheck(
                'treasure_connections',
                'Treasure connections',
                $this->activeTreasureCount($campaignIds),
                1,
                'At least one treasure is connected to the campaign structure.',
                'Connect a treasure to a campaign or mission before final campaign assurance.',
            ),
            $this->check(
                'campaign_scope_integrity',
                'Campaign scope integrity',
                $this->scopeMismatchCount($campaignIds) === 0,
                $this->scopeMismatchCount($campaignIds),
                'QR, mission, reward, treasure, and inventory rows match their campaign venue/scope.',
                'Fix rows where campaign_id, venue_id, or reward inventory campaign linkage does not match.',
            ),
            $this->minimumCountCheck(
                'executed_mission_campaigns',
                'Executed mission campaigns',
                $this->executedMissionCampaignCount($campaignIds),
                $minimumCampaigns,
                'Completed mission evidence exists across the required campaign set.',
                'Run participant journeys in each campaign and complete at least one mission per campaign.',
                warningOnly: $executionWarningOnly,
            ),
            $this->minimumCountCheck(
                'issued_reward_evidence',
                'Issued reward evidence',
                $this->issuedRewardCount($campaignIds),
                1,
                'At least one real reward issuance has been exercised.',
                'Complete a reward-bearing mission and verify a user reward is issued.',
                warningOnly: $executionWarningOnly,
            ),
            $this->minimumCountCheck(
                'partner_redemption_evidence',
                'Partner redemption evidence',
                $this->partnerRedemptionCount($campaignIds),
                1,
                'At least one partner redemption code has been created or confirmed.',
                'Exercise a partner or sponsor reward through redemption-code creation and confirmation.',
                warningOnly: $executionWarningOnly,
            ),
        ]);

        $summary = [
            'scope' => $scopeLabel,
            'ready' => $checks->where('status', 'fail')->isEmpty(),
            'activeCampaigns' => $campaigns->count(),
            'campaignCodes' => array_values($campaigns->pluck('code')->all()),
            'passCount' => $checks->where('status', 'pass')->count(),
            'warningCount' => $checks->where('status', 'warning')->count(),
            'failCount' => $checks->where('status', 'fail')->count(),
            'requireExecution' => $requireExecution,
        ];

        return [
            'summary' => $summary,
            'checks' => array_values($checks->all()),
            'nextActions' => array_values($checks
                ->whereIn('status', ['warning', 'fail'])
                ->pluck('nextAction')
                ->filter(fn (mixed $action): bool => is_string($action))
                ->unique()
                ->all()),
        ];
    }

    /** @param Collection<int, string> $campaignIds */
    private function distinctActiveQrCampaigns(Collection $campaignIds): int
    {
        if ($campaignIds->isEmpty()) {
            return 0;
        }

        return (int) QrCode::query()
            ->whereIn('campaign_id', $campaignIds)
            ->where('status', RecordStatus::Active)
            ->distinct()
            ->count('campaign_id');
    }

    /** @param Collection<int, string> $campaignIds */
    private function distinctActiveMissionTypes(Collection $campaignIds): int
    {
        if ($campaignIds->isEmpty()) {
            return 0;
        }

        return (int) MissionInstance::query()
            ->join('mission_templates', 'mission_instances.mission_template_id', '=', 'mission_templates.id')
            ->whereIn('mission_instances.campaign_id', $campaignIds)
            ->where('mission_instances.status', RecordStatus::Active)
            ->where('mission_templates.status', RecordStatus::Active)
            ->distinct()
            ->count('mission_templates.mission_type');
    }

    /** @param Collection<int, string> $campaignIds */
    private function rewardLayerCount(Collection $campaignIds): int
    {
        if ($campaignIds->isEmpty()) {
            return 0;
        }

        $baseQuery = RewardDefinition::query()
            ->whereIn('campaign_id', $campaignIds)
            ->where('status', RecordStatus::Active);

        $hasInternal = (clone $baseQuery)->whereNull('partner_account_id')->exists();
        $hasPartner = (clone $baseQuery)
            ->whereNotNull('partner_account_id')
            ->whereHas('partnerAccount', fn ($query) => $query->where('partner_type', '!=', 'sponsor'))
            ->exists();
        $hasSponsor = (clone $baseQuery)
            ->where(fn ($query) => $query
                ->where('reward_type', 'like', '%sponsor%')
                ->orWhereHas('partnerAccount', fn ($partnerQuery) => $partnerQuery->where('partner_type', 'sponsor')))
            ->exists();

        return collect([$hasInternal, $hasPartner, $hasSponsor])->filter()->count();
    }

    /** @param Collection<int, string> $campaignIds */
    private function lockedMissionCount(Collection $campaignIds): int
    {
        if ($campaignIds->isEmpty()) {
            return 0;
        }

        return MissionInstance::query()
            ->whereIn('campaign_id', $campaignIds)
            ->where('status', RecordStatus::Active)
            ->get(['unlock_rule'])
            ->filter(fn (MissionInstance $mission): bool => is_numeric(data_get($mission->unlock_rule, 'min_points')))
            ->count();
    }

    /** @param Collection<int, string> $campaignIds */
    private function activeTreasureCount(Collection $campaignIds): int
    {
        if ($campaignIds->isEmpty()) {
            return 0;
        }

        return Treasure::query()
            ->whereIn('campaign_id', $campaignIds)
            ->where('status', RecordStatus::Active)
            ->count();
    }

    /** @param Collection<int, string> $campaignIds */
    private function scopeMismatchCount(Collection $campaignIds): int
    {
        if ($campaignIds->isEmpty()) {
            return 0;
        }

        $qrMismatches = QrCode::query()
            ->join('campaigns', 'qr_codes.campaign_id', '=', 'campaigns.id')
            ->whereIn('qr_codes.campaign_id', $campaignIds)
            ->whereColumn('qr_codes.venue_id', '!=', 'campaigns.venue_id')
            ->count();
        $missionMismatches = MissionInstance::query()
            ->join('campaigns', 'mission_instances.campaign_id', '=', 'campaigns.id')
            ->whereIn('mission_instances.campaign_id', $campaignIds)
            ->whereColumn('mission_instances.venue_id', '!=', 'campaigns.venue_id')
            ->count();
        $rewardMismatches = RewardDefinition::query()
            ->join('campaigns', 'reward_definitions.campaign_id', '=', 'campaigns.id')
            ->whereIn('reward_definitions.campaign_id', $campaignIds)
            ->whereColumn('reward_definitions.venue_id', '!=', 'campaigns.venue_id')
            ->count();
        $treasureMismatches = Treasure::query()
            ->join('campaigns', 'treasures.campaign_id', '=', 'campaigns.id')
            ->whereIn('treasures.campaign_id', $campaignIds)
            ->whereColumn('treasures.venue_id', '!=', 'campaigns.venue_id')
            ->count();
        $inventoryMismatches = RewardInventoryAllocation::query()
            ->join('reward_definitions', 'reward_inventory_allocations.reward_definition_id', '=', 'reward_definitions.id')
            ->whereIn('reward_inventory_allocations.campaign_id', $campaignIds)
            ->whereColumn('reward_inventory_allocations.campaign_id', '!=', 'reward_definitions.campaign_id')
            ->count();

        return $qrMismatches + $missionMismatches + $rewardMismatches + $treasureMismatches + $inventoryMismatches;
    }

    /** @param Collection<int, string> $campaignIds */
    private function executedMissionCampaignCount(Collection $campaignIds): int
    {
        if ($campaignIds->isEmpty()) {
            return 0;
        }

        return (int) UserMissionProgress::query()
            ->join('mission_instances', 'user_mission_progress.mission_instance_id', '=', 'mission_instances.id')
            ->whereIn('mission_instances.campaign_id', $campaignIds)
            ->where('user_mission_progress.status', 'completed')
            ->distinct()
            ->count('mission_instances.campaign_id');
    }

    /** @param Collection<int, string> $campaignIds */
    private function issuedRewardCount(Collection $campaignIds): int
    {
        if ($campaignIds->isEmpty()) {
            return 0;
        }

        return UserReward::query()
            ->whereIn('campaign_id', $campaignIds)
            ->count();
    }

    /** @param Collection<int, string> $campaignIds */
    private function partnerRedemptionCount(Collection $campaignIds): int
    {
        if ($campaignIds->isEmpty()) {
            return 0;
        }

        return RewardRedemption::query()
            ->whereNotNull('partner_account_id')
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereHas('userReward', fn ($query) => $query->whereIn('campaign_id', $campaignIds))
            ->count();
    }

    /** @return AssuranceCheck */
    private function minimumCountCheck(
        string $key,
        string $label,
        int $count,
        int $minimum,
        string $passMessage,
        string $nextAction,
        bool $warningOnly = false,
    ): array {
        return $this->check(
            $key,
            $label,
            $count >= $minimum,
            $count,
            $passMessage,
            $nextAction,
            $warningOnly,
        ) + ['minimum' => $minimum];
    }

    /** @return AssuranceCheck */
    private function check(
        string $key,
        string $label,
        bool $passes,
        int $count,
        string $passMessage,
        string $nextAction,
        bool $warningOnly = false,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $passes ? 'pass' : ($warningOnly ? 'warning' : 'fail'),
            'count' => $count,
            'message' => $passes ? $passMessage : $nextAction,
            'nextAction' => $passes ? null : $nextAction,
        ];
    }
}
