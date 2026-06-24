<?php

namespace Tests\Feature\Partner;

use App\Enums\UserRole;
use App\Models\MissionInstance;
use App\Models\QrCode;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PartnerRewardRedemptionTest extends TestCase
{
    use RefreshDatabase;

    private User $visitor;

    private Visit $visit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);

        $this->visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $qr = QrCode::query()->firstOrFail();
        $this->visit = Visit::query()->create([
            'user_id' => $this->visitor->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $qr->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $qr->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
        ]);
    }

    public function test_partner_can_open_dashboard_for_own_rewards(): void
    {
        $this->withoutVite();
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();

        $this->actingAs($partnerUser)
            ->get(route('partner.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('partner/dashboard')
                ->where('partner.code', 'cafe-eco')
                ->where('stats.rewardDefinitions', 1)
                ->has('rewardDefinitions', 1));
    }

    public function test_partner_reward_completion_creates_pending_redemption(): void
    {
        $this->completeMission('scan-entry-qr');
        $this->completeMission('discover-route-guide');

        $redemption = RewardRedemption::query()
            ->with(['partnerAccount', 'userReward.rewardDefinition'])
            ->firstOrFail();

        $this->assertSame('cafe-eco', $redemption->partnerAccount->code);
        $this->assertSame('small-drink-coupon', $redemption->userReward->rewardDefinition->code);
        $this->assertSame('pending', $redemption->status);
    }

    public function test_partner_can_confirm_own_redemption_code(): void
    {
        $this->completeMission('scan-entry-qr');
        $this->completeMission('discover-route-guide');

        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $redemption = RewardRedemption::query()->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.redemptions.api.confirm'), [
                'redemption_code' => strtolower($redemption->redemption_code),
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $redemption->refresh();

        $this->assertSame('confirmed', $redemption->status);
        $this->assertSame('redeemed', $redemption->userReward->status);
    }

    public function test_other_partner_cannot_confirm_foreign_redemption_code(): void
    {
        $this->completeMission('scan-entry-qr');
        $this->completeMission('discover-route-guide');

        $otherPartnerUser = User::query()->where('email', 'ravaq.store@example.test')->firstOrFail();
        $redemption = RewardRedemption::query()->firstOrFail();

        $this->actingAs($otherPartnerUser)
            ->postJson(route('partner.redemptions.api.confirm'), [
                'redemption_code' => $redemption->redemption_code,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('redemption_code');

        $this->assertSame('pending', $redemption->fresh()->status);
    }

    public function test_partner_dashboard_api_reports_pending_and_confirmed_redemptions(): void
    {
        $this->completeMission('scan-entry-qr');
        $this->completeMission('discover-route-guide');

        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $redemption = RewardRedemption::query()->firstOrFail();

        $this->actingAs($partnerUser)
            ->getJson(route('partner.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.pendingRedemptions', 1)
            ->assertJsonPath('data.redemptions.0.redemptionCode', $redemption->redemption_code);

        $this->actingAs($partnerUser)
            ->postJson(route('partner.redemptions.api.confirm'), [
                'redemption_code' => $redemption->redemption_code,
            ])
            ->assertOk();

        $this->actingAs($partnerUser)
            ->getJson(route('partner.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.pendingRedemptions', 0)
            ->assertJsonPath('data.stats.confirmedRedemptions', 1);
    }

    private function completeMission(string $code): void
    {
        $mission = MissionInstance::query()->where('code', $code)->firstOrFail();

        $this->actingAs($this->visitor)
            ->post(route('visits.missions.complete', [$this->visit, $mission]))
            ->assertRedirect();
    }
}
