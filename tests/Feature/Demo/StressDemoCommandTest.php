<?php

namespace Tests\Feature\Demo;

use App\Models\Campaign;
use App\Models\PartnerAccount;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\RewardRedemption;
use App\Models\SponsorProposal;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Venue;
use App\Services\VenueRegistryService;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StressDemoCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_stress_demo_command_prepares_full_sellable_demo_cycle(): void
    {
        $this->artisan('exploria:prepare-stress-demo', ['--execute-visitor' => true])
            ->assertSuccessful()
            ->expectsOutput('Stress demo prepared.');

        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $campaign = Campaign::query()->where('code', 'ecopark-online-treasure-map-game-campaign')->firstOrFail();

        $this->assertSame('ecopark-online-treasure-map-game', $campaign->metadata['blueprint_code']);
        $this->assertNotNull($campaign->metadata['route_reviewed_at']);
        $this->assertSame(5, $campaign->missionInstances()->count());
        $this->assertSame(2, PartnerAccount::query()->where('venue_id', $venue->id)->where('partner_type', '!=', 'sponsor')->count());
        $this->assertSame(3, $campaign->campaignParticipants()->where('onboarding_status', 'ready')->count());

        $discount = RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->where('reward_type', 'sponsor_discount')
            ->where('metadata->assignment_status', 'assigned_to_mission')
            ->firstOrFail();

        $this->assertSame(4, $discount->metadata['cycle_step_index']);
        $this->assertSame(100, $discount->stock_quantity);
        $this->assertSame(2, RewardInventoryAllocation::query()->where('reward_definition_id', $discount->id)->where('status', 'active')->count());
        $this->assertSame(100, RewardInventoryAllocation::query()->where('reward_definition_id', $discount->id)->sum('allocated_quantity'));

        $this->assertDatabaseHas('sponsor_proposals', [
            'code' => 'stress-family-brand-proposal-0001',
            'status' => 'approved',
        ]);
        $this->assertTrue(SponsorProposal::query()->where('code', 'stress-family-brand-proposal-0001')->firstOrFail()->activation()->exists());
        $this->assertSame(5, UserMissionProgress::query()->where('status', 'completed')->count());
        $this->assertSame(1, UserReward::query()->where('campaign_id', $campaign->id)->count());
        $this->assertSame(1, RewardRedemption::query()->where('status', 'confirmed')->count());

        $plan = app(VenueRegistryService::class)->list()->firstWhere('code', $venue->code)['demoStressPlan'];

        $this->assertSame(100, $plan['summary']['progress']);
        $this->assertSame(11, $plan['summary']['completeCount']);
        $this->assertNull($plan['nextAction']);
    }

    public function test_stress_demo_command_is_idempotent(): void
    {
        $this->artisan('exploria:prepare-stress-demo', ['--execute-visitor' => true])->assertSuccessful();
        $this->artisan('exploria:prepare-stress-demo', ['--execute-visitor' => true])->assertSuccessful();

        $campaign = Campaign::query()->where('code', 'ecopark-online-treasure-map-game-campaign')->firstOrFail();
        $discount = RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->where('reward_type', 'sponsor_discount')
            ->where('metadata->assignment_status', 'assigned_to_mission')
            ->firstOrFail();

        $this->assertSame(5, $campaign->missionInstances()->count());
        $this->assertSame(1, SponsorProposal::query()->where('code', 'stress-family-brand-proposal-0001')->count());
        $this->assertSame(2, RewardInventoryAllocation::query()->where('reward_definition_id', $discount->id)->count());
        $this->assertSame(1, RewardRedemption::query()->where('redemption_code', 'STRESS-DEMO-REDEEM-001')->count());
    }
}
