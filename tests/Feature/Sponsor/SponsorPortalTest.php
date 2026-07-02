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
