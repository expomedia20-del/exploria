<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\MissionInstance;
use App\Models\MissionTemplate;
use App\Models\PartnerAccount;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\SponsorAccount;
use App\Models\SponsorProposal;
use App\Models\SponsorProposalActivation;
use App\Models\Treasure;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SponsorActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_viewer_can_open_sponsor_activation_console(): void
    {
        $this->withoutVite();
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.sponsors.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/sponsors/index')
                ->where('stats.sponsors', 0)
                ->where('stats.sponsorships', 0)
                ->has('formOptions.campaigns', 1)
                ->has('formOptions.venues', 3));
    }

    public function test_hub_manager_can_open_sponsor_activation_console_read_only(): void
    {
        $this->withoutVite();
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->get(route('admin.sponsors.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/sponsors/index')
                ->where('stats.sponsors', 0)
                ->has('formOptions.campaigns', 1)
                ->has('formOptions.venues', 1));

        $this->actingAs($manager)
            ->postJson(route('admin.sponsors.api.store'), [
                'code' => 'blocked-hub-manager-sponsor',
                'name' => 'Blocked Hub Manager Sponsor',
                'sponsor_type' => 'brand',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_create_sponsor_and_attach_it_to_campaign(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.sponsors.api.store'), [
                'venue_id' => $venue->id,
                'code' => 'family-market-sponsor',
                'name' => 'Family Market Sponsor',
                'sponsor_type' => 'retail',
                'status' => 'active',
                'contact_name' => 'Sponsor Manager',
                'contact_mobile' => '09120000010',
                'website_url' => 'https://example.test',
                'notes' => 'Pilot family team offer.',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success');

        $sponsor = SponsorAccount::query()->where('code', 'family-market-sponsor')->firstOrFail();

        $this->assertSame($venue->id, $sponsor->venue_id);
        $this->assertSame('Pilot family team offer.', $sponsor->metadata['notes']);

        $this->actingAs($admin)
            ->postJson(route('admin.campaign-sponsorships.api.store'), [
                'campaign_id' => $campaign->id,
                'sponsor_account_id' => $sponsor->id,
                'sponsorship_goal' => 'footfall',
                'package_type' => 'family_team_challenge',
                'status' => 'active',
                'budget_amount' => 50000000,
                'contract_value' => 75000000,
                'notes' => 'Launch package.',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('campaign_sponsorships', [
            'campaign_id' => $campaign->id,
            'sponsor_account_id' => $sponsor->id,
            'sponsorship_goal' => 'footfall',
            'package_type' => 'family_team_challenge',
            'status' => 'active',
            'budget_amount' => 50000000,
            'contract_value' => 75000000,
        ]);

        $reward = RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->where('code', 'manual-sp-ecopark-pilot-1405-family-market-sponsor')
            ->firstOrFail();

        $this->assertSame('admin_sponsor_activation', $reward->metadata['source']);
        $this->assertSame('family_team_challenge', $reward->metadata['package_type']);
        $this->assertSame([], $reward->metadata['target_partner_account_ids']);
        $this->assertSame('sponsor_reward', $reward->reward_type);

        $partner = PartnerAccount::query()->where('code', 'cafe-eco')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.sponsor-partner-assignments.api.store'), [
                'sponsor_account_id' => $sponsor->id,
                'partner_account_id' => $partner->id,
                'campaign_id' => $campaign->id,
                'activation_role' => 'reward_redemption',
                'status' => 'active',
                'notes' => 'Cafe handles manual sponsor campaign rewards.',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success');

        $reward->refresh();
        $this->assertSame([$partner->id], $reward->metadata['target_partner_account_ids']);
        $this->assertSame($partner->id, $reward->partner_account_id);

        $missionTemplate = MissionTemplate::query()->create([
            'code' => 'manual-sponsor-mission',
            'title' => 'Manual Sponsor Mission',
            'description' => 'Mission for manually connected sponsor incentive.',
            'mission_type' => 'challenge',
            'trigger_type' => 'manual',
            'point_value' => 80,
            'status' => 'active',
        ]);
        $mission = MissionInstance::query()->create([
            'mission_template_id' => $missionTemplate->id,
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'code' => 'manual-sponsor-mission-01',
            'title_override' => 'Manual sponsor mission',
            'status' => 'active',
            'metadata' => [
                'source' => 'test',
                'cycle_step_index' => 3,
                'cycle_step_label' => 'گام مشوق دستی اسپانسر',
            ],
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.rewards.api.sponsor-assignment', $reward), [
                'mission_instance_id' => $mission->id,
                'reward_tier' => 'silver',
                'reward_option' => 'manual family challenge',
                'claim_condition' => 'mission_completion',
                'point_cost' => 0,
                'stock_quantity' => 20,
                'partner_allocations' => [
                    ['partner_account_id' => $partner->id, 'quantity' => 20],
                ],
                'status' => 'active',
                'availability_status' => 'active',
                'fulfillment_window' => '3 days after mission completion',
                'notes' => 'Assign manual sponsor incentive to mission.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $reward->refresh();
        $this->assertSame(20, $reward->stock_quantity);
        $this->assertSame('assigned_to_mission', $reward->metadata['assignment_status']);
        $this->assertSame([['partner_account_id' => $partner->id, 'quantity' => 20]], $reward->metadata['partner_allocations']);
        $this->assertDatabaseHas('reward_inventory_allocations', [
            'reward_definition_id' => $reward->id,
            'partner_account_id' => $partner->id,
            'mission_instance_id' => $mission->id,
            'allocated_quantity' => 20,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.sponsors.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.sponsors', 1)
            ->assertJsonPath('data.stats.activeSponsors', 1)
            ->assertJsonPath('data.stats.sponsorships', 1)
            ->assertJsonPath('data.stats.contractValue', 75000000)
            ->assertJsonPath('data.sponsorships.0.packageType', 'family_team_challenge');
    }

    public function test_sponsor_code_is_generated_when_left_empty(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        SponsorAccount::query()->create([
            'venue_id' => $venue->id,
            'code' => 'ecopark-abbasabad-retail-0001',
            'name' => 'Existing Retail Sponsor',
            'sponsor_type' => 'retail',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.sponsors.api.store'), [
                'venue_id' => $venue->id,
                'name' => 'Auto Code Retail Sponsor',
                'sponsor_type' => 'retail',
                'status' => 'active',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('sponsor_accounts', [
            'venue_id' => $venue->id,
            'code' => 'ecopark-abbasabad-retail-0002',
            'name' => 'Auto Code Retail Sponsor',
        ]);
    }

    public function test_admin_can_connect_sponsor_to_partner_unit(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $partner = PartnerAccount::query()->where('code', 'cafe-eco')->firstOrFail();

        $sponsor = SponsorAccount::query()->create([
            'venue_id' => $venue->id,
            'code' => 'ecopark-family-drink-0001',
            'name' => 'Family Drink Sponsor',
            'sponsor_type' => 'brand',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.sponsor-partner-assignments.api.store'), [
                'sponsor_account_id' => $sponsor->id,
                'partner_account_id' => $partner->id,
                'campaign_id' => $campaign->id,
                'activation_role' => 'reward_redemption',
                'status' => 'active',
                'notes' => 'Cafe handles sponsored reward handoff.',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('sponsor_partner_assignments', [
            'sponsor_account_id' => $sponsor->id,
            'partner_account_id' => $partner->id,
            'campaign_id' => $campaign->id,
            'activation_role' => 'reward_redemption',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.sponsors.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.partnerAssignments', 1)
            ->assertJsonPath('data.stats.activePartnerAssignments', 1)
            ->assertJsonPath('data.partnerAssignments.0.activationRole', 'reward_redemption')
            ->assertJsonPath('data.partnerAssignments.0.partner.code', 'cafe-eco');
    }

    public function test_sponsor_partner_assignment_must_match_campaign_venue(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $otherVenue = Venue::query()->where('code', 'eram-park')->firstOrFail();
        $partner = PartnerAccount::query()->where('code', 'cafe-eco')->firstOrFail();

        $sponsor = SponsorAccount::query()->create([
            'venue_id' => $otherVenue->id,
            'code' => 'eram-family-drink-0001',
            'name' => 'Eram Family Drink Sponsor',
            'sponsor_type' => 'brand',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.sponsor-partner-assignments.api.store'), [
                'sponsor_account_id' => $sponsor->id,
                'partner_account_id' => $partner->id,
                'campaign_id' => $campaign->id,
                'activation_role' => 'sales_point',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('partner_account_id');

        $this->assertDatabaseMissing('sponsor_partner_assignments', [
            'sponsor_account_id' => $sponsor->id,
            'partner_account_id' => $partner->id,
        ]);
    }

    public function test_sponsor_account_cannot_be_attached_to_another_venue_campaign(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $otherVenue = Venue::query()->where('code', 'eram-park')->firstOrFail();

        $sponsor = SponsorAccount::query()->create([
            'venue_id' => $otherVenue->id,
            'code' => 'eram-only-sponsor',
            'name' => 'Eram Only Sponsor',
            'sponsor_type' => 'brand',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.campaign-sponsorships.api.store'), [
                'campaign_id' => $campaign->id,
                'sponsor_account_id' => $sponsor->id,
                'sponsorship_goal' => 'awareness',
                'package_type' => 'pilot_activation',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sponsor_account_id');

        $this->assertDatabaseMissing('campaign_sponsorships', [
            'campaign_id' => $campaign->id,
            'sponsor_account_id' => $sponsor->id,
        ]);
    }

    public function test_admin_can_activate_approved_sponsor_proposal_into_campaign_reward_pool(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $partners = PartnerAccount::query()
            ->where('venue_id', $campaign->venue_id)
            ->where('partner_type', '!=', 'sponsor')
            ->orderBy('code')
            ->limit(2)
            ->get();

        if ($partners->count() < 2) {
            $partners->push(PartnerAccount::query()->create([
                'venue_id' => $campaign->venue_id,
                'code' => 'admin-activation-extra-unit',
                'name' => 'Admin Activation Extra Unit',
                'partner_type' => 'retail',
                'status' => 'active',
            ]));
        }

        $sponsor = SponsorAccount::query()->create([
            'venue_id' => $venue->id,
            'code' => 'activation-sponsor',
            'name' => 'Activation Sponsor',
            'sponsor_type' => 'brand',
            'status' => 'active',
        ]);

        $proposal = SponsorProposal::query()->create([
            'sponsor_account_id' => $sponsor->id,
            'campaign_id' => $campaign->id,
            'preferred_partner_account_id' => $partners[0]->id,
            'code' => 'sp-activation-sponsor-0001',
            'title' => 'Activation sponsor reward package',
            'proposal_type' => 'reward_offer',
            'objective' => 'engagement',
            'status' => 'approved',
            'proposed_budget_amount' => 15000000,
            'estimated_value_amount' => 25000000,
        ]);

        foreach ($partners as $index => $partner) {
            $proposal->partnerAccounts()->create([
                'partner_account_id' => $partner->id,
                'sort_order' => $index,
            ]);
        }

        $proposal->items()->create([
            'item_type' => 'reward',
            'title' => 'Sponsor family prize box',
            'quantity' => 100,
            'estimated_unit_value_amount' => 200000,
            'target_partner_account_ids' => $partners->pluck('id')->all(),
            'partner_allocations' => [
                ['partner_account_id' => $partners[0]->id, 'quantity' => 40],
                ['partner_account_id' => $partners[1]->id, 'quantity' => 60],
            ],
            'description' => 'Prize box for mission completion tiers.',
        ]);
        $proposal->items()->create([
            'item_type' => 'discount',
            'title' => 'Sponsor cafe code',
            'quantity' => 50,
            'target_partner_account_ids' => [$partners[0]->id],
            'partner_allocations' => [
                ['partner_account_id' => $partners[0]->id, 'quantity' => 50],
            ],
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.sponsor-proposals.api.activate', $proposal), [
                'activation_notes' => 'Ready for mission reward assignment.',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'ready_for_campaign_design');

        $activation = SponsorProposalActivation::query()->where('sponsor_proposal_id', $proposal->id)->firstOrFail();

        $this->assertCount(2, $activation->reward_definition_ids);
        $this->assertCount(2, $activation->treasure_ids);
        $this->assertCount(2, $activation->partner_assignment_ids);
        $this->assertDatabaseHas('campaign_sponsorships', [
            'campaign_id' => $campaign->id,
            'sponsor_account_id' => $sponsor->id,
            'package_type' => 'treasure_sponsor',
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('sponsor_partner_assignments', [
            'sponsor_account_id' => $sponsor->id,
            'partner_account_id' => $partners[0]->id,
            'campaign_id' => $campaign->id,
            'activation_role' => 'discount_redemption',
            'status' => 'draft',
        ]);

        $reward = RewardDefinition::query()->where('name', 'Sponsor family prize box')->firstOrFail();
        $this->assertSame('sponsor_reward', $reward->reward_type);
        $this->assertSame(100, $reward->stock_quantity);
        $this->assertSame('sponsor_proposal_activation', $reward->metadata['source']);
        $this->assertSame($proposal->id, $reward->metadata['sponsor_proposal_id']);
        $this->assertCount(2, $reward->metadata['partner_allocations']);

        $treasure = Treasure::query()->where('name', 'Sponsor family prize box')->firstOrFail();
        $this->assertSame('sponsor_reward_treasure', $treasure->treasure_type);
        $this->assertSame('sponsor_proposal_activation', $treasure->metadata['source']);
        $this->assertSame($reward->id, $treasure->metadata['reward_definition_id']);
        $this->assertSame($reward->id, $treasure->reveal_rule['reward_definition_id']);

        $missionTemplate = MissionTemplate::query()->create([
            'code' => 'sponsor-prize-mission',
            'title' => 'Sponsor Prize Mission',
            'description' => 'Mission for sponsored prize claim.',
            'mission_type' => 'challenge',
            'trigger_type' => 'manual',
            'point_value' => 100,
            'status' => 'active',
        ]);
        $mission = MissionInstance::query()->create([
            'mission_template_id' => $missionTemplate->id,
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'code' => 'sponsor-prize-mission-01',
            'title_override' => 'Sponsor prize mission',
            'status' => 'active',
            'metadata' => [
                'source' => 'test',
                'cycle_step_index' => 2,
                'cycle_step_label' => 'گام جایزه اسپانسری',
            ],
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.rewards.api.sponsor-assignment', $reward), [
                'mission_instance_id' => $mission->id,
                'treasure_id' => $treasure->id,
                'reward_tier' => 'gold',
                'reward_option' => 'family prize box',
                'claim_condition' => 'family_team_completion',
                'point_cost' => 10,
                'status' => 'active',
                'availability_status' => 'active',
                'fulfillment_window' => '7 days after mission completion',
                'notes' => 'Assign sponsor prize to mission step.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $reward->refresh();
        $treasure->refresh();

        $this->assertSame('gold', $reward->metadata['reward_tier']);
        $this->assertSame('family_team_completion', $reward->metadata['claim_condition']);
        $this->assertSame($mission->id, $reward->metadata['mission_instance_id']);
        $this->assertSame($treasure->id, $reward->metadata['linked_treasure_id']);
        $this->assertSame($mission->id, $treasure->mission_instance_id);
        $this->assertSame('family_team_completion', $treasure->reveal_rule['claim_condition']);
        $this->assertSame('gold', $treasure->metadata['treasure_tier']);

        $this->assertSame(2, RewardInventoryAllocation::query()->where('reward_definition_id', $reward->id)->count());
        $this->assertDatabaseHas('reward_inventory_allocations', [
            'reward_definition_id' => $reward->id,
            'partner_account_id' => $partners[0]->id,
            'mission_instance_id' => $mission->id,
            'allocated_quantity' => 40,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('reward_inventory_allocations', [
            'reward_definition_id' => $reward->id,
            'partner_account_id' => $partners[1]->id,
            'mission_instance_id' => $mission->id,
            'allocated_quantity' => 60,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.missions.index', ['campaign' => $campaign->code]))
            ->assertOk();

        $this->assertTrue(collect($response->json('data.rewards'))->contains(
            fn (array $reward): bool => $reward['source'] === 'sponsor_proposal_activation'
                && $reward['name'] === 'Sponsor family prize box'
                && $reward['claimCondition'] === 'family_team_completion'
                && $reward['inventorySummary']['allocated'] === 100,
        ));
        $this->assertTrue(collect($response->json('data.treasures'))->contains(
            fn (array $treasure): bool => $treasure['source'] === 'sponsor_proposal_activation'
                && $treasure['name'] === 'Sponsor family prize box',
        ));
    }
}
