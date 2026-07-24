<?php

namespace Tests\Feature\Partner;

use App\Enums\UserRole;
use App\Models\HubManagementAssignment;
use App\Models\PartnerAccount;
use App\Models\PartnerLocation;
use App\Models\PartnerUser;
use App\Models\User;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PartnerFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_pilot_seed_creates_partner_foundation_records(): void
    {
        $this->assertDatabaseCount('partner_accounts', 5);
        $this->assertDatabaseCount('partner_locations', 5);
        $this->assertDatabaseCount('partner_users', 5);
        $this->assertDatabaseCount('hub_management_assignments', 2);
        $this->assertDatabaseCount('user_access_scopes', 9);

        $this->assertDatabaseHas('partner_accounts', [
            'code' => 'cafe-eco',
            'partner_type' => 'member_shop',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('partner_accounts', [
            'code' => 'family-route-sponsor',
            'partner_type' => 'sponsor',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('hubs', [
            'code' => 'ecopark-food-island-hub',
            'name' => 'جزیره غذایی اکوپارک',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('partner_accounts', [
            'code' => 'food-island-cafe',
            'partner_type' => 'member_shop',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('partner_accounts', [
            'code' => 'independent-park-restaurant',
            'partner_type' => 'member_shop',
            'status' => 'active',
        ]);
    }

    public function test_pilot_partner_seed_is_idempotent(): void
    {
        $this->seed(PilotLocationSeeder::class);

        $this->assertDatabaseCount('partner_accounts', 5);
        $this->assertDatabaseCount('partner_locations', 5);
        $this->assertDatabaseCount('partner_users', 5);
        $this->assertDatabaseCount('hub_management_assignments', 2);
        $this->assertDatabaseCount('user_access_scopes', 9);
    }

    public function test_partner_accounts_are_bound_to_ecopark_hubs_and_users(): void
    {
        $partner = PartnerAccount::query()
            ->where('code', 'ravaq-store')
            ->with(['venue', 'locations.hub', 'partnerUsers.user'])
            ->firstOrFail();

        $this->assertSame('ecopark-abbasabad', $partner->venue->code);
        $this->assertSame('ravaq-commercial-hub', $partner->locations->first()->hub->code);
        $this->assertSame(UserRole::ShopPartner, $partner->partnerUsers->first()->user->role);
    }

    public function test_food_island_and_independent_food_units_are_outside_ravaq_scope(): void
    {
        $foodIslandCafe = PartnerAccount::query()
            ->where('code', 'food-island-cafe')
            ->with('locations.hub.zone')
            ->firstOrFail();
        $independentRestaurant = PartnerAccount::query()
            ->where('code', 'independent-park-restaurant')
            ->with('locations.zone')
            ->firstOrFail();

        $this->assertSame('ecopark-food-island-hub', $foodIslandCafe->locations->firstOrFail()->hub?->code);
        $this->assertSame('park-food-services-zone', $foodIslandCafe->locations->firstOrFail()->hub?->zone?->code);
        $this->assertNull($independentRestaurant->locations->firstOrFail()->hub_id);
        $this->assertSame('park-food-services-zone', $independentRestaurant->locations->firstOrFail()->zone?->code);
    }

    public function test_hub_manager_assignment_is_seeded_for_ravaq(): void
    {
        $assignment = HubManagementAssignment::query()->with(['hub', 'user'])->firstOrFail();

        $this->assertSame('ravaq-commercial-hub', $assignment->hub->code);
        $this->assertSame(UserRole::HubManager, $assignment->user->role);
    }

    public function test_partner_registry_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/partners')->assertUnauthorized();
    }

    public function test_admin_can_read_partner_registry(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($viewer)
            ->getJson('/api/v1/admin/partners')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('data.0.venue.code', 'ecopark-abbasabad');
    }

    public function test_hub_manager_can_open_partner_registry_page(): void
    {
        $this->withoutVite();
        $manager = User::query()->where('role', UserRole::HubManager)->firstOrFail();

        $this->actingAs($manager)
            ->get(route('admin.partners.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/partners/index')
                ->has('partners', 2)
                ->where('partners.0.code', 'cafe-eco')
                ->where('partners.0.venue.code', 'ecopark-abbasabad'));
    }

    public function test_partner_location_model_relationships_are_available(): void
    {
        $location = PartnerLocation::query()->with(['partnerAccount', 'venue', 'zone', 'hub'])->firstOrFail();
        $partnerUser = PartnerUser::query()->with(['partnerAccount', 'user'])->firstOrFail();

        $this->assertNotNull($location->partnerAccount);
        $this->assertNotNull($location->venue);
        $this->assertNotNull($location->zone);
        $this->assertNotNull($location->hub);
        $this->assertNotNull($partnerUser->partnerAccount);
        $this->assertNotNull($partnerUser->user);
    }
}
