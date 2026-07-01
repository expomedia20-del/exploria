<?php

namespace Tests\Feature\Campaign;

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\PartnerAccount;
use App\Models\RewardDefinition;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\UserReward;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CampaignOperationsBlueprintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_admin_can_read_campaign_operations_blueprint_api(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->getJson(route('admin.campaign-operations.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.campaigns', 1)
            ->assertJsonPath('data.stats.participants', 3)
            ->assertJsonPath('data.stats.internalSponsors', 1)
            ->assertJsonPath('data.stats.externalSponsors', 0)
            ->assertJsonPath('data.stats.entryPoints', 1)
            ->assertJsonPath('data.campaigns.0.stats.missions', 4)
            ->assertJsonPath('data.campaigns.0.stats.readyParticipants', 2)
            ->assertJsonPath('data.campaigns.0.operationalReview.status', 'needs_attention')
            ->assertJsonPath('data.campaigns.0.operationalReview.checks.0.key', 'qr')
            ->assertJsonPath('data.campaigns.0.operationTimeline.0.index', 1)
            ->assertJsonPath('data.campaigns.0.operationTimeline.0.checks.0.key', 'entry');
    }

    public function test_campaign_operations_reports_reward_redemption_overview(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $reward = RewardDefinition::query()->where('code', 'small-drink-coupon')->firstOrFail();
        $partner = PartnerAccount::query()->where('code', 'cafe-eco')->firstOrFail();
        $userReward = UserReward::query()->create([
            'user_id' => $visitor->id,
            'reward_definition_id' => $reward->id,
            'campaign_id' => $campaign->id,
            'status' => 'awarded',
            'awarded_at' => now(),
        ]);

        RewardRedemption::query()->create([
            'user_reward_id' => $userReward->id,
            'user_id' => $visitor->id,
            'partner_account_id' => $partner->id,
            'redemption_code' => 'TEST-REDEEM-1',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.campaign-operations.index', ['campaign' => $campaign->code]))
            ->assertOk()
            ->assertJsonPath('data.campaigns.0.redemptionOverview.stats.total', 1)
            ->assertJsonPath('data.campaigns.0.redemptionOverview.stats.pending', 1)
            ->assertJsonPath('data.campaigns.0.redemptionOverview.latest.0.redemptionCode', 'TEST-REDEEM-1')
            ->assertJsonPath('data.campaigns.0.redemptionOverview.latest.0.partnerName', $partner->name);
    }

    public function test_hub_manager_reads_only_scoped_campaign_operations(): void
    {
        $this->withoutVite();
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->get(route('admin.campaign-operations.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/campaign-operations/index')
                ->where('stats.campaigns', 1)
                ->where('stats.participants', 1)
                ->where('stats.internalSponsors', 0)
                ->where('campaigns.0.participantsByHub.0.hub.code', 'ravaq-commercial-hub')
                ->where('campaigns.0.journey.commercial.items.0.partner.name', 'فروشگاه X'));
    }

    public function test_guest_cannot_read_campaign_operations(): void
    {
        $this->getJson(route('admin.campaign-operations.index'))->assertUnauthorized();
    }

    public function test_admin_cannot_confirm_operational_route_while_checks_need_attention(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $campaignId = \App\Models\Campaign::query()
            ->where('code', 'ecopark-pilot-1405')
            ->valueOrFail('id');

        $this->actingAs($admin)
            ->post(route('admin.campaign-operations.review'), [
                'campaign_id' => $campaignId,
                'route_notes' => 'route checked',
            ])
            ->assertSessionHasErrors('campaign_id');

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaignId,
        ]);

        $this->assertNull(\App\Models\Campaign::query()->findOrFail($campaignId)->metadata['route_reviewed_at'] ?? null);
    }
}
