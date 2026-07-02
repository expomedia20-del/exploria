<?php

namespace Tests\Feature\Sponsor;

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\PartnerAccount;
use App\Models\SponsorAccount;
use App\Models\SponsorProposal;
use App\Models\SponsorUser;
use App\Models\User;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SponsorPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_sponsor_can_open_self_service_dashboard_from_existing_partner_sponsor(): void
    {
        $this->withoutVite();
        $sponsorUser = User::query()->where('email', 'family.sponsor@example.test')->firstOrFail();

        $this->actingAs($sponsorUser)
            ->get(route('sponsor.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('sponsor/dashboard')
                ->where('sponsor.code', 'family-route-sponsor')
                ->where('stats.proposals', 0)
                ->has('formOptions.campaigns', 1)
                ->has('formOptions.partners', 3));

        $this->assertDatabaseHas('sponsor_accounts', [
            'code' => 'family-route-sponsor',
            'name' => 'اسپانسر مسیر خانوادگی',
        ]);
        $this->assertDatabaseHas('sponsor_users', [
            'user_id' => $sponsorUser->id,
        ]);
    }

    public function test_sponsor_can_submit_proposal_for_admin_review(): void
    {
        $sponsorUser = User::query()->where('email', 'family.sponsor@example.test')->firstOrFail();
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $partner = PartnerAccount::query()->where('code', 'cafe-eco')->firstOrFail();

        $this->actingAs($sponsorUser)
            ->postJson(route('sponsor.proposals.store'), [
                'campaign_id' => $campaign->id,
                'preferred_partner_account_id' => $partner->id,
                'title' => 'Family weekend drink reward',
                'proposal_type' => 'reward_offer',
                'objective' => 'engagement',
                'proposed_budget_amount' => 12000000,
                'estimated_value_amount' => 18000000,
                'reward_offer' => 'Free family drink package for completed routes.',
                'discount_offer' => '20 percent cafe code for family teams.',
                'asset_url' => 'https://example.test/sponsor-kit',
                'target_audience' => 'Families with children visiting Eco Park.',
                'notes' => 'Sponsor can provide product and creative assets.',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success');

        $sponsor = SponsorAccount::query()->where('code', 'family-route-sponsor')->firstOrFail();

        $this->assertDatabaseHas('sponsor_proposals', [
            'sponsor_account_id' => $sponsor->id,
            'campaign_id' => $campaign->id,
            'preferred_partner_account_id' => $partner->id,
            'title' => 'Family weekend drink reward',
            'proposal_type' => 'reward_offer',
            'status' => 'pending_review',
        ]);

        $this->actingAs($sponsorUser)
            ->getJson(route('sponsor.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.proposals', 1)
            ->assertJsonPath('data.stats.pendingProposals', 1)
            ->assertJsonPath('data.proposals.0.title', 'Family weekend drink reward');
    }

    public function test_sponsor_can_submit_multi_partner_multi_item_package(): void
    {
        $sponsorUser = User::query()->where('email', 'family.sponsor@example.test')->firstOrFail();
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $partners = PartnerAccount::query()
            ->where('venue_id', $campaign->venue_id)
            ->where('partner_type', '!=', 'sponsor')
            ->orderBy('code')
            ->limit(2)
            ->get();

        if ($partners->count() < 2) {
            $partners->push(PartnerAccount::query()->create([
                'venue_id' => $campaign->venue_id,
                'code' => 'test-sponsor-allocation-unit',
                'name' => 'Test Sponsor Allocation Unit',
                'partner_type' => 'retail',
                'status' => 'active',
            ]));
        }

        $this->assertCount(2, $partners);

        $this->actingAs($sponsorUser)
            ->postJson(route('sponsor.proposals.store'), [
                'campaign_id' => $campaign->id,
                'partner_account_ids' => $partners->pluck('id')->all(),
                'title' => 'Family multi unit reward package',
                'proposal_type' => 'product_sampling',
                'objective' => 'sales',
                'proposed_budget_amount' => 25000000,
                'estimated_value_amount' => 42000000,
                'items' => [
                    [
                        'item_type' => 'reward',
                        'title' => 'Family treasure reward box',
                        'quantity' => 100,
                        'estimated_unit_value_amount' => 250000,
                        'target_partner_account_ids' => $partners->pluck('id')->all(),
                        'partner_allocations' => [
                            ['partner_account_id' => $partners[0]->id, 'quantity' => 40],
                            ['partner_account_id' => $partners[1]->id, 'quantity' => 60],
                        ],
                        'description' => 'Reward box for families that complete the route.',
                    ],
                    [
                        'item_type' => 'product',
                        'title' => 'Healthy snack sample',
                        'quantity' => 300,
                        'estimated_unit_value_amount' => 80000,
                        'target_partner_account_ids' => [$partners[0]->id],
                        'partner_allocations' => [
                            ['partner_account_id' => $partners[0]->id, 'quantity' => 300],
                        ],
                        'description' => 'Sampling pack for the first sales point.',
                    ],
                ],
                'target_audience' => 'Family teams and group visitors.',
                'notes' => 'Sponsor wants two execution units in the pilot.',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'success');

        $sponsor = SponsorAccount::query()->where('code', 'family-route-sponsor')->firstOrFail();
        $proposal = SponsorProposal::query()->where('sponsor_account_id', $sponsor->id)
            ->where('title', 'Family multi unit reward package')
            ->firstOrFail();

        $this->assertSame($partners[0]->id, $proposal->preferred_partner_account_id);
        $this->assertDatabaseCount('sponsor_proposal_partner_accounts', 2);
        $this->assertDatabaseCount('sponsor_proposal_items', 2);
        $this->assertDatabaseHas('sponsor_proposal_items', [
            'sponsor_proposal_id' => $proposal->id,
            'item_type' => 'reward',
            'title' => 'Family treasure reward box',
            'quantity' => 100,
        ]);
        $rewardItem = $proposal->items()->where('item_type', 'reward')->firstOrFail();
        $this->assertSame([
            ['partner_account_id' => $partners[0]->id, 'quantity' => 40],
            ['partner_account_id' => $partners[1]->id, 'quantity' => 60],
        ], $rewardItem->partner_allocations);

        $response = $this->actingAs($sponsorUser)
            ->getJson(route('sponsor.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.proposals.0.title', 'Family multi unit reward package')
            ->assertJsonCount(2, 'data.proposals.0.partners')
            ->assertJsonCount(2, 'data.proposals.0.items');

        $rewardItem = collect($response->json('data.proposals.0.items'))
            ->firstWhere('title', 'Family treasure reward box');

        $this->assertCount(2, $rewardItem['partnerAllocations']);
    }

    public function test_sponsor_item_allocations_must_match_item_quantity(): void
    {
        $sponsorUser = User::query()->where('email', 'family.sponsor@example.test')->firstOrFail();
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $partners = PartnerAccount::query()
            ->where('venue_id', $campaign->venue_id)
            ->where('partner_type', '!=', 'sponsor')
            ->orderBy('code')
            ->limit(2)
            ->get();

        if ($partners->count() < 2) {
            $partners->push(PartnerAccount::query()->create([
                'venue_id' => $campaign->venue_id,
                'code' => 'test-invalid-allocation-unit',
                'name' => 'Test Invalid Allocation Unit',
                'partner_type' => 'retail',
                'status' => 'active',
            ]));
        }

        $this->actingAs($sponsorUser)
            ->postJson(route('sponsor.proposals.store'), [
                'campaign_id' => $campaign->id,
                'partner_account_ids' => $partners->pluck('id')->all(),
                'title' => 'Invalid allocation package',
                'proposal_type' => 'reward_offer',
                'objective' => 'engagement',
                'items' => [
                    [
                        'item_type' => 'reward',
                        'title' => 'Unbalanced reward',
                        'quantity' => 100,
                        'partner_allocations' => [
                            ['partner_account_id' => $partners[0]->id, 'quantity' => 40],
                            ['partner_account_id' => $partners[1]->id, 'quantity' => 50],
                        ],
                    ],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items');

        $this->assertDatabaseMissing('sponsor_proposals', [
            'title' => 'Invalid allocation package',
        ]);
    }

    public function test_admin_can_review_sponsor_proposal_from_sponsor_console(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $sponsor = SponsorAccount::query()->create([
            'code' => 'admin-review-sponsor',
            'name' => 'Admin Review Sponsor',
            'sponsor_type' => 'brand',
            'status' => 'active',
        ]);
        SponsorUser::query()->create([
            'sponsor_account_id' => $sponsor->id,
            'user_id' => $admin->id,
            'role' => 'manager',
            'status' => 'active',
        ]);
        $proposal = SponsorProposal::query()->create([
            'sponsor_account_id' => $sponsor->id,
            'code' => 'sp-admin-review-0001',
            'title' => 'Review me',
            'proposal_type' => 'campaign_sponsorship',
            'objective' => 'awareness',
            'status' => 'pending_review',
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.sponsors.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.proposals', 1)
            ->assertJsonPath('data.stats.pendingProposals', 1)
            ->assertJsonPath('data.proposals.0.title', 'Review me');

        $this->actingAs($admin)
            ->postJson(route('admin.sponsor-proposals.api.status', $proposal), [
                'status' => 'approved',
                'review_notes' => 'Ready for activation package.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('sponsor_proposals', [
            'id' => $proposal->id,
            'status' => 'approved',
        ]);
    }
}
