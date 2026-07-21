<?php

namespace Tests\Feature\Venue;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VenueManagerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_scoped_venue_manager_can_open_read_only_dashboard(): void
    {
        $this->withoutVite();
        $manager = User::factory()->create(['role' => UserRole::Viewer]);
        $ecoPark = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        Venue::query()->create([
            'code' => 'azadi-cultural-park',
            'name' => 'بوستان فرهنگی آزادی',
            'city' => 'تهران',
            'status' => RecordStatus::Active,
            'profile_status' => RecordStatus::Active,
            'metadata' => ['is_test' => true],
        ]);

        UserAccessScope::query()->create([
            'user_id' => $manager->id,
            'role_key' => 'venue_executive',
            'scope_type' => 'venue',
            'scope_id' => $ecoPark->id,
            'status' => RecordStatus::Active,
            'metadata' => ['source' => 'test'],
        ]);

        $this->actingAs($manager)
            ->get(route('venue.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('venue/dashboard')
                ->where('stats.venues', 1)
                ->where('venues.0.code', 'ecopark-abbasabad')
                ->has('campaigns', 1)
                ->has('hubs', 4)
                ->has('partners', 3));

        $this->actingAs($manager)
            ->getJson(route('venue.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.venues', 1)
            ->assertJsonPath('data.venues.0.code', 'ecopark-abbasabad')
            ->assertJsonMissing(['code' => 'azadi-cultural-park']);
    }

    public function test_viewer_without_venue_scope_sees_empty_dashboard(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->getJson(route('venue.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.venues', 0)
            ->assertJsonCount(0, 'data.venues');
    }

    public function test_regional_admin_can_open_venue_dashboard_for_assigned_region_only(): void
    {
        $this->withoutVite();

        $regionalAdmin = User::factory()->create(['role' => UserRole::RegionalAdmin]);
        Venue::query()->create([
            'code' => 'isfahan-demo-venue',
            'name' => 'مکان نمونه اصفهان',
            'city' => 'اصفهان',
            'status' => RecordStatus::Active,
            'profile_status' => RecordStatus::Active,
            'metadata' => ['is_test' => true],
        ]);

        UserAccessScope::query()->create([
            'user_id' => $regionalAdmin->id,
            'role_key' => 'regional_admin',
            'scope_type' => 'region',
            'scope_id' => 'تهران',
            'status' => RecordStatus::Active,
            'metadata' => ['source' => 'test'],
        ]);

        $this->actingAs($regionalAdmin)
            ->get(route('venue.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('venue/dashboard')
                ->where('stats.venues', 1)
                ->where('venues.0.code', 'ecopark-abbasabad'));

        $this->actingAs($regionalAdmin)
            ->getJson(route('venue.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.venues', 1)
            ->assertJsonPath('data.venues.0.code', 'ecopark-abbasabad')
            ->assertJsonMissing(['code' => 'isfahan-demo-venue']);
    }

    public function test_admin_can_open_venue_dashboard_for_support(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('venue.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('venue/dashboard')
                ->where('stats.venues', 1)
                ->where('venues.0.code', 'ecopark-abbasabad'));
    }
}
