<?php

namespace Tests\Feature\AccessScope;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Hub;
use App\Models\PartnerAccount;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Services\UserAccessScopeService;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_pilot_seed_creates_operational_access_scopes(): void
    {
        $this->assertDatabaseCount('user_access_scopes', 5);

        $this->assertDatabaseHas('user_access_scopes', [
            'role_key' => 'ravaq_manager',
            'scope_type' => 'hub',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('user_access_scopes', [
            'role_key' => 'shop_manager',
            'scope_type' => 'partner',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('user_access_scopes', [
            'role_key' => 'internal_sponsor',
            'scope_type' => 'partner',
            'status' => 'active',
        ]);
    }

    public function test_direct_hub_scope_expands_to_hub_partners_and_venue(): void
    {
        $hub = Hub::query()->where('code', 'ravaq-commercial-hub')->firstOrFail();
        $user = User::factory()->create(['role' => UserRole::HubManager]);

        UserAccessScope::query()->create([
            'user_id' => $user->id,
            'role_key' => 'hub_manager',
            'scope_type' => 'hub',
            'scope_id' => $hub->id,
            'status' => RecordStatus::Active,
        ]);

        $service = app(UserAccessScopeService::class);

        $this->assertTrue($service->hubIds($user)->contains($hub->id));
        $this->assertTrue($service->venueIds($user)->contains($hub->zone->venue_id));
        $this->assertTrue(
            $service->partnerIds($user)->contains(
                PartnerAccount::query()->where('code', 'ravaq-store')->value('id'),
            ),
        );
    }

    public function test_legacy_partner_assignment_still_grants_partner_scope(): void
    {
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();
        $partner = PartnerAccount::query()->where('code', 'cafe-eco')->firstOrFail();

        UserAccessScope::query()
            ->where('user_id', $partnerUser->id)
            ->delete();

        $service = app(UserAccessScopeService::class);

        $this->assertTrue($service->partnerIds($partnerUser)->contains($partner->id));
        $this->assertTrue($service->hasScope($partnerUser, 'partner', $partner->id));
    }

    public function test_admin_has_global_operational_scope(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $hubCount = Hub::query()->where('status', RecordStatus::Active)->count();
        $partnerCount = PartnerAccount::query()->where('status', RecordStatus::Active)->count();

        $service = app(UserAccessScopeService::class);

        $this->assertTrue($service->hasGlobalAccess($admin));
        $this->assertCount($hubCount, $service->hubIds($admin));
        $this->assertCount($partnerCount, $service->partnerIds($admin));
    }
}
