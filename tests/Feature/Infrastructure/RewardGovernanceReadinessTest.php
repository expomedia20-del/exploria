<?php

namespace Tests\Feature\Infrastructure;

use App\Enums\RecordStatus;
use App\Models\RewardDefinition;
use App\Services\ProductionReadinessService;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewardGovernanceReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pilot_reward_governance_is_ready_without_activating_an_unfunded_sponsor_reward(): void
    {
        $this->seed(PilotLocationSeeder::class);

        $report = app(ProductionReadinessService::class)->report('staging');
        $check = collect($report['checks'])->firstWhere('key', 'reward_governance');

        $this->assertIsArray($check);
        $this->assertSame('pass', $check['status']);
        $this->assertSame(0, $check['actual']['invalidRewards']);
        $this->assertNotContains('pilot-prize-draw', $check['actual']['invalidRewardCodes']);
    }

    public function test_active_legacy_reward_with_missing_cost_owner_fails_production_readiness(): void
    {
        $this->seed(PilotLocationSeeder::class);

        RewardDefinition::query()
            ->where('code', 'small-drink-coupon')
            ->update([
                'cost_owner_financial_account_id' => null,
                'status' => RecordStatus::Active->value,
            ]);

        $report = app(ProductionReadinessService::class)->report('staging');
        $check = collect($report['checks'])->firstWhere('key', 'reward_governance');

        $this->assertIsArray($check);
        $this->assertSame('fail', $check['status']);
        $this->assertSame(1, $check['actual']['invalidRewards']);
        $this->assertContains('small-drink-coupon', $check['actual']['invalidRewardCodes']);
        $this->assertContains('cost_owner_financial_account_id', $check['actual']['invalidFields']);
    }
}
