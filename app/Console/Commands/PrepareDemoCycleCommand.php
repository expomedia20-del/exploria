<?php

namespace App\Console\Commands;

use App\Enums\RecordStatus;
use App\Models\Campaign;
use App\Models\MissionInstance;
use App\Models\MissionTemplate;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\Treasure;
use App\Models\User;
use App\Services\CampaignBlueprintConsistencyService;
use App\Services\MissionRewardBlueprintService;
use App\Services\MissionRewardRegistryService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PrepareDemoCycleCommand extends Command
{
    protected $signature = 'exploria:prepare-demo-cycle
        {campaign=ecopark-online-treasure-map-game-campaign : Campaign code}
        {--reward-code= : Sponsor reward code to connect to the demo cycle}
        {--reward-step=4 : Blueprint step index that should unlock the connected sponsor reward}
        {--claim-condition=mission_completion : Claim condition for the connected sponsor reward}';

    protected $description = 'Prepare an end-to-end demo cycle for a campaign by creating blueprint missions and connecting a sponsor reward.';

    public function handle(
        CampaignBlueprintConsistencyService $consistency,
        MissionRewardBlueprintService $blueprints,
        MissionRewardRegistryService $registry,
    ): int {
        $campaign = Campaign::query()
            ->where('code', Str::lower((string) $this->argument('campaign')))
            ->first();

        if (! $campaign) {
            $this->error('Campaign not found.');

            return self::FAILURE;
        }

        $blueprint = $consistency->blueprintForCampaign($campaign);

        if (! $blueprint) {
            $blueprintCode = $campaign->metadata['blueprint_code'] ?? null;
            $blueprint = $blueprints->handoff(is_string($blueprintCode) ? $blueprintCode : null);
        }

        $missionPlan = $blueprint['missionPlan'] ?? [];
        $steps = collect(is_array($missionPlan) ? $missionPlan : [])
            ->filter(fn (mixed $step): bool => is_array($step));

        if ($steps->isEmpty()) {
            $this->error('Campaign blueprint has no mission plan.');

            return self::FAILURE;
        }

        $missions = [];
        foreach ($steps as $step) {
            $template = MissionTemplate::query()
                ->where('code', $step['recommendedTemplateCode'])
                ->first();

            if (! $template) {
                $this->error("Missing mission template: {$step['recommendedTemplateCode']}");

                return self::FAILURE;
            }

            $missions[(int) $step['index']] = $this->upsertMission($campaign, $template, $step);
        }

        $reward = $this->sponsorReward($campaign);
        if (! $reward) {
            $this->warn('No sponsor reward found to connect. Demo missions were prepared.');
            $this->info('Missions prepared: '.count($missions));

            return self::SUCCESS;
        }

        $rewardStepIndex = (int) $this->option('reward-step');
        $rewardStep = $steps->firstWhere('index', $rewardStepIndex);

        if (! $rewardStep || ! isset($missions[$rewardStepIndex])) {
            $this->error("Reward step {$rewardStepIndex} is not available in this campaign blueprint.");

            return self::FAILURE;
        }

        $rewardMission = $missions[$rewardStepIndex];
        $actor = User::query()
            ->whereIn('role', ['admin', 'operator'])
            ->orderBy('id')
            ->first();

        if (! $actor) {
            $this->error('No admin or operator user is available to assign the sponsor reward.');

            return self::FAILURE;
        }

        $this->resetPreviousDemoRewardAssignments($campaign, $reward);
        $this->attachRewardToMission($registry, $actor, $campaign, $rewardMission, $reward, $rewardStep);

        $this->info("Demo cycle prepared for {$campaign->code}.");
        $this->line('Missions prepared: '.count($missions));
        $this->line("Sponsor reward connected: {$reward->code} on step {$rewardStepIndex}");

        return self::SUCCESS;
    }

    /** @param array<string, mixed> $step */
    private function upsertMission(Campaign $campaign, MissionTemplate $template, array $step): MissionInstance
    {
        $index = (int) $step['index'];
        $code = Str::limit("{$campaign->code}-step-{$index}", 96, '');
        $metadata = [
            'source' => 'demo_cycle_preparation',
            'cycle_step_index' => $index,
            'cycle_step_label' => $step['userStep'] ?? "گام {$index}",
            'visitor_instruction' => $this->visitorInstruction($index, (string) ($step['userStep'] ?? '')),
            'completion_evidence' => $this->completionEvidence($index),
            'success_message' => 'گام دمو با موفقیت تکمیل شد.',
        ];

        return MissionInstance::query()->updateOrCreate(
            [
                'campaign_id' => $campaign->id,
                'code' => $code,
            ],
            [
                'mission_template_id' => $template->id,
                'venue_id' => $campaign->venue_id,
                'hub_id' => null,
                'touchpoint_id' => null,
                'title_override' => $step['userStep'] ?? $template->title,
                'status' => RecordStatus::Active,
                'starts_at' => null,
                'ends_at' => null,
                'unlock_rule' => $index > 1 ? ['min_points' => ($index - 1) * 10] : null,
                'metadata' => $metadata,
            ],
        );
    }

    private function sponsorReward(Campaign $campaign): ?RewardDefinition
    {
        $rewardCode = $this->option('reward-code');

        $query = RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->whereIn('metadata->source', ['sponsor_proposal_activation', 'admin_sponsor_activation']);

        if (is_string($rewardCode) && $rewardCode !== '') {
            return (clone $query)->where('code', $rewardCode)->first();
        }

        return (clone $query)
            ->orderBy('created_at')
            ->get()
            ->sortBy(fn (RewardDefinition $reward): int => $this->sponsorRewardPreferenceScore($reward))
            ->first();
    }

    private function sponsorRewardPreferenceScore(RewardDefinition $reward): int
    {
        $name = Str::lower((string) $reward->name);
        $type = Str::lower((string) $reward->reward_type);
        $code = Str::lower((string) $reward->code);

        if (Str::contains($type, 'discount') || Str::contains($name, ['discount', 'تخفیف', '70'])) {
            return 0;
        }

        if (Str::contains($code, ['discount', 'tkhfyf', '-70-'])) {
            return 1;
        }

        return 10;
    }

    private function resetPreviousDemoRewardAssignments(Campaign $campaign, RewardDefinition $selectedReward): void
    {
        RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->whereKeyNot($selectedReward->id)
            ->whereIn('metadata->source', ['sponsor_proposal_activation', 'admin_sponsor_activation'])
            ->where('metadata->assignment_notes', 'Demo cycle preparation.')
            ->get()
            ->each(function (RewardDefinition $reward): void {
                $metadata = $reward->metadata ?? [];

                foreach ([
                    'assignment_status',
                    'mission_instance_id',
                    'linked_treasure_id',
                    'cycle_step_index',
                    'cycle_step_label',
                    'claim_condition',
                    'assigned_by_user_id',
                    'assigned_at',
                    'assignment_notes',
                ] as $key) {
                    unset($metadata[$key]);
                }

                $reward->update(['metadata' => $metadata]);

                RewardInventoryAllocation::query()
                    ->where('reward_definition_id', $reward->id)
                    ->where('reserved_quantity', 0)
                    ->where('redeemed_quantity', 0)
                    ->update([
                        'mission_instance_id' => null,
                        'treasure_id' => null,
                        'status' => 'inactive',
                    ]);
            });
    }

    /** @param array<string, mixed> $step */
    private function attachRewardToMission(
        MissionRewardRegistryService $registry,
        User $actor,
        Campaign $campaign,
        MissionInstance $mission,
        RewardDefinition $reward,
        array $step,
    ): void {
        $treasure = Treasure::query()
            ->where('campaign_id', $campaign->id)
            ->get()
            ->first(fn (Treasure $treasure): bool => ($treasure->metadata['reward_definition_id'] ?? null) === $reward->id
                || ($treasure->reveal_rule['reward_definition_id'] ?? null) === $reward->id);

        $rawPartnerAllocations = $reward->metadata['partner_allocations'] ?? [];
        $partnerAllocations = collect(is_array($rawPartnerAllocations) ? $rawPartnerAllocations : [])
            ->filter(fn (mixed $allocation): bool => is_array($allocation))
            ->map(fn (array $allocation): array => [
                'partner_account_id' => (string) ($allocation['partner_account_id'] ?? ''),
                'quantity' => (int) ($allocation['quantity'] ?? 0),
            ])
            ->filter(fn (array $allocation): bool => $allocation['partner_account_id'] !== '' && $allocation['quantity'] > 0)
            ->values()
            ->all();

        $registry->assignSponsorIncentive($reward, [
            'mission_instance_id' => $mission->id,
            'treasure_id' => $treasure?->id,
            'reward_tier' => (string) ($step['rewardTier'] ?? 'bronze'),
            'reward_option' => $reward->metadata['reward_option'] ?? $reward->reward_type,
            'claim_condition' => (string) $this->option('claim-condition'),
            'point_cost' => $reward->point_cost ?? 0,
            'stock_quantity' => $reward->stock_quantity,
            'partner_allocations' => $partnerAllocations,
            'status' => RecordStatus::Active,
            'availability_status' => 'active',
            'fulfillment_window' => $reward->metadata['fulfillment_window'] ?? 'تا ۷ روز پس از تکمیل ماموریت',
            'notes' => 'Demo cycle preparation.',
        ], $actor);

        MissionInstance::query()
            ->where('campaign_id', $campaign->id)
            ->whereKeyNot($mission->id)
            ->get()
            ->each(function (MissionInstance $otherMission) use ($reward): void {
                $metadata = $otherMission->metadata ?? [];

                if (($metadata['reward_code'] ?? null) !== $reward->code) {
                    return;
                }

                unset($metadata['reward_code']);
                $otherMission->update(['metadata' => $metadata]);
            });

        $metadata = $mission->metadata ?? [];
        $metadata['reward_code'] = $reward->code;
        $mission->update(['metadata' => $metadata]);
    }

    private function visitorInstruction(int $index, string $label): string
    {
        return match ($index) {
            1 => 'ورود دمو را تایید کنید و مسیر بازی را شروع کنید.',
            2 => 'مسیر اکوپارک را انتخاب کنید.',
            3 => 'چند نقطه نقشه را باز کنید.',
            4 => 'سرنخ کوتاه مسیر را حل کنید.',
            5 => 'کد شروع حضوری را دریافت کنید.',
            default => $label,
        };
    }

    private function completionEvidence(int $index): string
    {
        return match ($index) {
            1 => 'ورود آنلاین یا QR دمو',
            2 => 'انتخاب مسیر',
            3 => 'باز کردن نقطه‌های نقشه',
            4 => 'پاسخ سرنخ',
            5 => 'دریافت کد حضوری',
            default => 'تایید ادمین',
        };
    }
}
