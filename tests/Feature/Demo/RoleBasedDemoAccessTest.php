<?php

namespace Tests\Feature\Demo;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RoleBasedDemoAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PilotLocationSeeder::class);
        $this->artisan('exploria:prepare-stress-demo', ['--execute-visitor' => true])
            ->assertSuccessful();
    }

    public function test_dashboard_entry_routes_each_operational_role_to_its_home_surface(): void
    {
        $admin = User::query()->where('email', 'admin.stress-demo@example.test')->firstOrFail();
        $visitor = User::query()->where('email', 'visitor.stress-demo@example.test')->firstOrFail();
        $shopPartner = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $sponsor = User::query()->where('email', 'family.sponsor@example.test')->firstOrFail();
        $hubManager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($visitor)
            ->get(route('dashboard'))
            ->assertRedirect(route('participant.dashboard'));

        $this->actingAs($shopPartner)
            ->get(route('dashboard'))
            ->assertRedirect(route('partner.dashboard'));

        $this->actingAs($sponsor)
            ->get(route('dashboard'))
            ->assertRedirect(route('sponsor.dashboard'));

        $this->actingAs($hubManager)
            ->get(route('dashboard'))
            ->assertRedirect(route('ravaq.dashboard'));
    }

    public function test_demo_role_surfaces_are_scoped_to_their_operational_context(): void
    {
        $venueManager = User::query()->where('email', 'venue.manager.ecopark@example.test')->firstOrFail();
        $hubManager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();
        $shopPartner = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $sponsor = User::query()->where('email', 'family.sponsor@example.test')->firstOrFail();
        $visitor = User::query()->where('email', 'visitor.stress-demo@example.test')->firstOrFail();

        $venueResponse = $this->actingAs($venueManager)
            ->getJson(route('venue.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.venues', 1)
            ->assertJsonPath('data.venues.0.code', 'ecopark-abbasabad')
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('status', 'success')
                ->has('data.campaigns')
                ->etc());
        $this->assertNotNull(collect($venueResponse->json('data.campaigns'))->firstWhere('code', 'ecopark-online-treasure-map-game-campaign'));

        $this->actingAs($hubManager)
            ->getJson(route('ravaq.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.hubs', 2)
            ->assertJsonMissing(['code' => 'gonbad-mina-science-hub'])
            ->assertJsonMissing(['code' => 'family-route-sponsor']);

        $partnerResponse = $this->actingAs($shopPartner)
            ->getJson(route('partner.dashboard.index', ['campaign' => 'ecopark-online-treasure-map-game-campaign']))
            ->assertOk()
            ->assertJsonPath('data.partner.code', 'cafe-eco')
            ->assertJsonPath('data.stats.confirmedRedemptions', 1);
        $this->assertSame('confirmed', collect($partnerResponse->json('data.redemptions'))->first()['status'] ?? null);

        $this->actingAs($sponsor)
            ->getJson(route('sponsor.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.sponsor.code', 'family-route-sponsor')
            ->assertJsonPath('data.formOptions.campaigns.0.venueName', 'اکوپارک عباس‌آباد');

        $this->actingAs($visitor)
            ->get(route('participant.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('participant/dashboard')
                ->where('participant.name', 'کاربر دموی فشار')
                ->where('missionFlow.stats.completedMissions', 5)
                ->where('journey.points.redeemedRewards', 1));
    }

    public function test_stress_demo_visitor_can_open_visitor_support_center(): void
    {
        $this->withoutVite();

        $visitor = User::query()->where('email', 'visitor.stress-demo@example.test')->firstOrFail();

        $this->actingAs($visitor)
            ->get(route('admin.support.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/support/index')
                ->where('support.roleContext.key', 'visitor')
                ->where('support.roleContext.title', 'پشتیبانی بازدیدکننده'));
    }

    public function test_roles_are_blocked_from_unrelated_operational_surfaces(): void
    {
        $visitor = User::query()->where('email', 'visitor.stress-demo@example.test')->firstOrFail();
        $shopPartner = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $sponsor = User::query()->where('email', 'family.sponsor@example.test')->firstOrFail();
        $hubManager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();
        $venueManager = User::query()->where('email', 'venue.manager.ecopark@example.test')->firstOrFail();

        $this->actingAs($visitor)
            ->get(route('admin.demo-cycle.page'))
            ->assertForbidden();

        $this->actingAs($shopPartner)
            ->get(route('sponsor.dashboard'))
            ->assertForbidden();

        $this->actingAs($sponsor)
            ->get(route('partner.dashboard'))
            ->assertForbidden();

        $this->actingAs($sponsor)
            ->get(route('partner.ads.page'))
            ->assertForbidden();

        $this->actingAs($hubManager)
            ->get(route('venue.dashboard'))
            ->assertForbidden();

        $this->actingAs($venueManager)
            ->get(route('partner.dashboard'))
            ->assertForbidden();

        $this->assertSame(UserRole::Viewer, $venueManager->role);
    }
}
