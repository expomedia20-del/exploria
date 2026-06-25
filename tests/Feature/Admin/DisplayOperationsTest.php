<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Models\User;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DisplayOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_admin_can_open_display_operations_console(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.display-operations.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/display-operations/index')
                ->where('stats.devices', 2)
                ->where('stats.activeDevices', 2)
                ->has('displayDevices', 2)
                ->has('scheduledPlacements', 0));
    }

    public function test_admin_can_schedule_ready_ad_to_any_compatible_display(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $adRequest = $this->submitAdRequest('cafe.eco@example.test', 'Admin scheduled cafe ad', 'fixed_display');
        $displayDevice = DisplayDevice::query()->where('code', 'ecopark-entry-fixed-display')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.ads.api.approve', $adRequest))
            ->assertOk();

        $placement = $adRequest->placements()->firstOrFail();
        $this->assertSame('approved', $placement->status);
        $this->assertNull($placement->display_device_id);

        $this->actingAs($admin)
            ->postJson(route('admin.display-operations.placements.api.schedule', $placement), [
                'display_device_id' => $displayDevice->id,
                'starts_at' => now()->subMinute()->toIso8601String(),
                'ends_at' => now()->addDay()->toIso8601String(),
                'priority' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'scheduled')
            ->assertJsonPath('data.displayDeviceCode', 'ecopark-entry-fixed-display')
            ->assertJsonPath('data.priority', 1);

        $this->assertSame($displayDevice->id, $placement->fresh()->display_device_id);

        $this->actingAs($admin)
            ->getJson(route('admin.display-operations.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.scheduledPlacements', 1)
            ->assertJsonPath('data.stats.readyPlacements', 0)
            ->assertJsonPath('data.scheduledPlacements.0.adRequestId', $adRequest->id);

        $this->getJson(route('display.schedule', $displayDevice))
            ->assertOk()
            ->assertJsonPath('data.items.0.adRequestId', $adRequest->id);
    }

    public function test_admin_can_cancel_global_display_schedule(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $adRequest = $this->submitAdRequest('cafe.eco@example.test', 'Admin cancel cafe ad', 'fixed_display');
        $displayDevice = DisplayDevice::query()->where('code', 'ecopark-entry-fixed-display')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.ads.api.approve', $adRequest))
            ->assertOk();

        $placement = $adRequest->placements()->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.display-operations.placements.api.schedule', $placement), [
                'display_device_id' => $displayDevice->id,
                'priority' => 2,
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->postJson(route('admin.display-operations.placements.api.cancel', $placement))
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertNull($placement->fresh()->display_device_id);
        $this->assertSame('approved', $placement->fresh()->status);

        $this->getJson(route('display.schedule', $displayDevice))
            ->assertOk()
            ->assertJsonCount(0, 'data.items');
    }

    public function test_admin_cannot_schedule_ad_to_incompatible_display_type(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $adRequest = $this->submitAdRequest('cafe.eco@example.test', 'Incompatible display ad', 'fixed_display');
        $mobileDisplay = DisplayDevice::query()->where('code', 'ecopark-mobile-promo-display')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.ads.api.approve', $adRequest))
            ->assertOk();

        $this->actingAs($admin)
            ->postJson(route('admin.display-operations.placements.api.schedule', $adRequest->placements()->firstOrFail()), [
                'display_device_id' => $mobileDisplay->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('display_device_id');
    }

    private function submitAdRequest(string $email, string $title, string $placementType): AdRequest
    {
        $partnerUser = User::query()->where('email', $email)->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.ads.api.store'), [
                'title' => $title,
                'body_copy' => 'Admin display operations test ad.',
                'ad_type' => 'standalone',
                'creative_type' => 'image',
                'placement_type' => $placementType,
            ])
            ->assertCreated();

        return AdRequest::query()->where('title', $title)->firstOrFail();
    }
}
