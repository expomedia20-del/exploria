<?php

namespace Tests\Feature\Governance;

use App\Enums\RecordStatus;
use App\Models\RewardDefinition;
use App\Models\User;
use App\Services\PartnerDashboardService;
use App\Services\RewardGovernanceService;
use Carbon\CarbonImmutable;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RewardGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_pilot_rewards_have_explicit_cost_inventory_window_and_issuance_constraints(): void
    {
        $this->seed(PilotLocationSeeder::class);

        $badge = RewardDefinition::query()->where('code', 'pilot-entry-badge')->firstOrFail();
        $coupon = RewardDefinition::query()->where('code', 'small-drink-coupon')->firstOrFail();
        $sponsorPrize = RewardDefinition::query()->where('code', 'pilot-prize-draw')->firstOrFail();

        $this->assertSame('non_inventory', $badge->inventory_mode->value);
        $this->assertSame('platform', $badge->costOwnerFinancialAccount->account_type);
        $this->assertNull($badge->stock_quantity);
        $this->assertSame(1, $badge->per_user_award_limit);

        $this->assertSame('finite', $coupon->inventory_mode->value);
        $this->assertSame('partner', $coupon->costOwnerFinancialAccount->account_type);
        $this->assertSame(500, $coupon->stock_quantity);
        $this->assertSame(500, $coupon->inventoryAllocations()->sum('allocated_quantity'));

        $this->assertSame('finite', $sponsorPrize->inventory_mode->value);
        $this->assertSame('sponsor', $sponsorPrize->costOwnerFinancialAccount->account_type);
        $this->assertSame(RecordStatus::Draft, $sponsorPrize->status);
        $this->assertNotNull($sponsorPrize->available_from);
        $this->assertNotNull($sponsorPrize->available_until);
    }

    public function test_activation_fails_closed_without_a_cost_owner(): void
    {
        $this->seed(PilotLocationSeeder::class);

        $actor = User::factory()->create();
        $reward = RewardDefinition::query()->where('code', 'small-drink-coupon')->firstOrFail();
        $reward->update([
            'cost_owner_financial_account_id' => null,
            'status' => RecordStatus::Active,
        ]);

        $this->assertFalse(
            RewardDefinition::query()->availableForIssuance()->whereKey($reward)->exists(),
        );

        $reward->update(['status' => RecordStatus::Draft]);

        $this->expectException(ValidationException::class);

        app(RewardGovernanceService::class)->activate($reward, $actor);
    }

    public function test_issuance_is_idempotent_and_expiry_is_capped_by_the_reward_window(): void
    {
        CarbonImmutable::setTestNow('2026-08-15 10:00:00');
        $this->seed(PilotLocationSeeder::class);

        $user = User::factory()->create();
        $reward = RewardDefinition::query()->where('code', 'small-drink-coupon')->firstOrFail();
        $governance = app(RewardGovernanceService::class);

        $first = $governance->issue($user, $reward, ['source' => 'governance_test']);
        $second = $governance->issue($user, $reward, ['source' => 'duplicate_attempt']);

        $this->assertTrue($first->is($second));
        $this->assertSame(1, $reward->userRewards()->where('user_id', $user->id)->count());
        $this->assertSame('partner-cafe-eco', $first->metadata['cost_owner_account_key']);
        $this->assertSame('finite', $first->metadata['inventory_mode']);
        $this->assertTrue($first->expires_at->equalTo(CarbonImmutable::parse('2026-08-22 10:00:00')));
    }

    public function test_expired_reward_cannot_be_reserved_for_redemption(): void
    {
        CarbonImmutable::setTestNow('2026-08-15 10:00:00');
        $this->seed(PilotLocationSeeder::class);

        $user = User::factory()->create();
        $reward = RewardDefinition::query()->where('code', 'small-drink-coupon')->firstOrFail();
        $userReward = app(RewardGovernanceService::class)->issue($user, $reward, ['source' => 'governance_test']);
        $userReward->update(['expires_at' => now()->subMinute()]);

        $this->expectException(ValidationException::class);

        app(PartnerDashboardService::class)->ensureRedemptionForReward($userReward->refresh());
    }
}
