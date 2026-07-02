<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\SponsorAccount;
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
}
