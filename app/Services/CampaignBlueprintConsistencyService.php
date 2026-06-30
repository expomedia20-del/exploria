<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\MissionInstance;
use App\Models\MissionTemplate;
use App\Models\RewardDefinition;
use App\Models\Treasure;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CampaignBlueprintConsistencyService
{
    public function __construct(private readonly MissionRewardBlueprintService $blueprints) {}

    /** @return array<string, mixed>|null */
    public function blueprintForCampaign(Campaign $campaign): ?array
    {
        $code = $campaign->metadata['blueprint_code'] ?? null;

        if (! is_string($code) && $campaign->campaign_type === 'pilot_visit') {
            $code = 'ecopark-pilot-treasure-route';
        }

        return $this->blueprints->handoff(is_string($code) ? $code : null);
    }

    /** @param array<string, mixed> $data */
    public function assertMissionInput(Campaign $campaign, array $data): void
    {
        $blueprint = $this->blueprintForCampaign($campaign);

        if (! $blueprint) {
            return;
        }

        $step = $this->stepFromData($blueprint, $data);
        $template = MissionTemplate::query()->find($data['mission_template_id']);

        if (! $template || $template->code !== $step['recommendedTemplateCode']) {
            throw ValidationException::withMessages([
                'mission_template_id' => 'قالب مأموریت باید با گام انتخاب‌شده در چرخه همین الگوی کمپین همخوان باشد.',
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    public function assertRewardInput(Campaign $campaign, array $data): void
    {
        $blueprint = $this->blueprintForCampaign($campaign);

        if (! $blueprint) {
            return;
        }

        $step = $this->stepFromData($blueprint, $data);
        $tierKey = (string) ($data['reward_tier'] ?? '');

        if ($tierKey === '' || $tierKey !== $step['rewardTier']) {
            throw ValidationException::withMessages([
                'reward_tier' => 'سطح پاداش باید با سطح پیشنهادی همان گام در چرخه کمپین همخوان باشد.',
            ]);
        }

        $this->assertRewardOption($blueprint, $tierKey, $data['reward_option'] ?? null);
    }

    /** @param array<string, mixed> $data */
    public function assertPartnerOfferInput(Campaign $campaign, array $data): void
    {
        $blueprint = $this->blueprintForCampaign($campaign);

        if (! $blueprint) {
            return;
        }

        $tierKey = (string) ($data['reward_tier'] ?? '');
        if ($tierKey === '') {
            throw ValidationException::withMessages([
                'reward_tier' => 'برای پیشنهاد فروشگاه، سطح پاداش مرتبط با الگوی کمپین را انتخاب کنید.',
            ]);
        }

        if (! collect($blueprint['rewardDesign']['tiers'] ?? [])->firstWhere('tierKey', $tierKey)) {
            throw ValidationException::withMessages([
                'reward_tier' => 'این سطح پاداش در الگوی مرجع کمپین وجود ندارد.',
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    public function assertTreasureInput(Campaign $campaign, array $data): void
    {
        $blueprint = $this->blueprintForCampaign($campaign);

        if (! $blueprint) {
            return;
        }

        $step = $this->stepFromData($blueprint, $data);
        $tierKey = (string) ($data['treasure_tier'] ?? '');

        if ($tierKey === '') {
            throw ValidationException::withMessages([
                'treasure_tier' => 'برای گنج کمپین، سطح گنج مرتبط با الگوی کمپین را انتخاب کنید.',
            ]);
        }

        if (! collect($blueprint['rewardDesign']['tiers'] ?? [])->firstWhere('tierKey', $tierKey)) {
            throw ValidationException::withMessages([
                'treasure_tier' => 'این سطح گنج در الگوی مرجع کمپین وجود ندارد.',
            ]);
        }

        $missionId = $data['mission_instance_id'] ?? null;
        if (! $missionId) {
            throw ValidationException::withMessages([
                'mission_instance_id' => 'برای گنج کمپین، ابتدا مأموریت همان گام چرخه را ثبت و به گنج وصل کنید.',
            ]);
        }

        $mission = MissionInstance::query()->find($missionId);
        if ((int) ($mission?->metadata['cycle_step_index'] ?? 0) !== (int) $step['index']) {
            throw ValidationException::withMessages([
                'mission_instance_id' => 'گنج باید به مأموریت همان گام چرخه وصل شود.',
            ]);
        }
    }

    /** @return array{status: string, issues: array<int, array<string, string>>, expectedSteps: int, completedSteps: int} */
    public function review(Campaign $campaign): array
    {
        $blueprint = $this->blueprintForCampaign($campaign);

        if (! $blueprint) {
            return [
                'status' => 'unchecked',
                'issues' => [[
                    'level' => 'warning',
                    'code' => 'blueprint_missing',
                    'title' => 'برای این کمپین الگوی مرجع ثبت نشده است.',
                    'action' => 'کمپین را از گنجینه مأموریت‌ها یا کارگاه ساخت به یک الگوی مرجع وصل کنید.',
                ]],
                'expectedSteps' => 0,
                'completedSteps' => 0,
            ];
        }

        $steps = collect($blueprint['missionPlan'] ?? []);
        $missions = $this->missionsByStep($campaign);
        $rewards = $this->rewardsByStep($campaign);
        $treasures = $this->treasuresByStep($campaign);
        $issues = [];

        foreach ($steps as $step) {
            $index = (int) $step['index'];
            $mission = $missions->get($index);
            $reward = $rewards->get($index);

            if (! $mission) {
                $issues[] = $this->issue('error', 'mission_missing', "گام {$index} چرخه مأموریت ثبت‌شده ندارد.", 'در مرحله ۳، مأموریت همین گام را از قالب پیشنهادی ثبت کنید.');
            } elseif ($mission->missionTemplate?->code !== $step['recommendedTemplateCode']) {
                $issues[] = $this->issue('error', 'mission_template_mismatch', "قالب مأموریت گام {$index} با الگو همخوان نیست.", 'مأموریت این گام را ویرایش کنید و قالب پیشنهادی همان گام را ثبت کنید.');
            }

            if (! $reward) {
                $issues[] = $this->issue('error', 'reward_missing', "گام {$index} چرخه پاداش ثبت‌شده ندارد.", 'در مرحله ۳، پاداش همان گام را با سطح پیشنهادی ثبت کنید.');
            } elseif (($reward->metadata['reward_tier'] ?? null) !== $step['rewardTier']) {
                $issues[] = $this->issue('error', 'reward_tier_mismatch', "سطح پاداش گام {$index} با الگو همخوان نیست.", 'پاداش این گام را ویرایش کنید و سطح پاداش پیشنهادی چرخه را انتخاب کنید.');
            }
        }

        $lastStep = $steps->last();
        if ($lastStep && ! $treasures->has((int) $lastStep['index'])) {
            $issues[] = $this->issue('error', 'final_treasure_missing', 'گنج نهایی چرخه هنوز ثبت نشده است.', 'در مرحله ۳، گنج نهایی یا گنج پنهان را به آخرین گام چرخه وصل کنید.');
        }

        $completedSteps = $steps
            ->filter(fn (array $step): bool => $missions->has((int) $step['index']) && $rewards->has((int) $step['index']))
            ->count();

        return [
            'status' => collect($issues)->where('level', 'error')->isEmpty() ? 'ready' : 'needs_attention',
            'issues' => $issues,
            'expectedSteps' => $steps->count(),
            'completedSteps' => $completedSteps,
            'treasureSteps' => $treasures->keys()->filter(fn (int $index): bool => $index > 0)->values()->all(),
        ];
    }

    /** @param array<string, mixed> $blueprint @param array<string, mixed> $data @return array<string, mixed> */
    private function stepFromData(array $blueprint, array $data): array
    {
        $index = (int) ($data['cycle_step_index'] ?? 0);
        $step = collect($blueprint['missionPlan'] ?? [])->firstWhere('index', $index);

        if (! $step) {
            throw ValidationException::withMessages([
                'cycle_step_index' => 'برای کمپین دارای الگو، باید یکی از گام‌های چرخه همان الگو انتخاب شود.',
            ]);
        }

        return $step;
    }

    /** @param array<string, mixed> $blueprint */
    private function assertRewardOption(array $blueprint, string $tierKey, mixed $rewardOption): void
    {
        $tier = collect($blueprint['rewardDesign']['tiers'] ?? [])->firstWhere('tierKey', $tierKey);

        if (! $tier) {
            throw ValidationException::withMessages([
                'reward_tier' => 'این سطح پاداش در الگوی مرجع کمپین وجود ندارد.',
            ]);
        }

        if (blank($rewardOption)) {
            return;
        }

        $options = collect($tier['options'] ?? []);
        if ($options->isNotEmpty() && ! $options->contains((string) $rewardOption)) {
            throw ValidationException::withMessages([
                'reward_option' => 'گزینه پاداش باید از گزینه‌های همان سطح در الگوی کمپین انتخاب شود.',
            ]);
        }
    }

    /** @return Collection<int, MissionInstance> */
    private function missionsByStep(Campaign $campaign): Collection
    {
        return MissionInstance::query()
            ->where('campaign_id', $campaign->id)
            ->where('metadata->source', 'admin_campaign_components')
            ->with('missionTemplate:id,code,title')
            ->get()
            ->keyBy(fn (MissionInstance $mission): int => (int) ($mission->metadata['cycle_step_index'] ?? 0));
    }

    /** @return Collection<int, RewardDefinition> */
    private function rewardsByStep(Campaign $campaign): Collection
    {
        return RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->where('metadata->source', 'admin_campaign_components')
            ->get()
            ->keyBy(fn (RewardDefinition $reward): int => (int) ($reward->metadata['cycle_step_index'] ?? 0));
    }

    /** @return Collection<int, Treasure> */
    private function treasuresByStep(Campaign $campaign): Collection
    {
        return Treasure::query()
            ->where('campaign_id', $campaign->id)
            ->where('metadata->source', 'admin_campaign_components')
            ->get()
            ->keyBy(fn (Treasure $treasure): int => (int) ($treasure->metadata['cycle_step_index'] ?? 0));
    }

    /** @return array<string, string> */
    private function issue(string $level, string $code, string $title, string $action): array
    {
        return compact('level', 'code', 'title', 'action');
    }
}
