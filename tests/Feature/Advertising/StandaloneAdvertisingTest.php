<?php

namespace Tests\Feature\Advertising;

use App\Enums\UserRole;
use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Models\User;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StandaloneAdvertisingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_seed_creates_display_inventory(): void
    {
        $this->assertDatabaseCount('display_devices', 2);
        $this->assertDatabaseHas('display_devices', [
            'code' => 'ecopark-entry-fixed-display',
            'device_type' => 'fixed_display',
            'status' => 'active',
        ]);
    }

    public function test_partner_can_open_ad_submission_page(): void
    {
        $this->withoutVite();
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();

        $this->actingAs($partnerUser)
            ->get(route('partner.ads.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('partner/ads')
                ->where('partner.code', 'cafe-eco')
                ->where('stats.requests', 0)
                ->has('hubOptions', 1));
    }

    public function test_partner_can_submit_standalone_ad_request(): void
    {
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.ads.api.store'), [
                'title' => 'ØªØ¨Ù„ÛŒØº Ù†ÙˆØ´ÛŒØ¯Ù†ÛŒ Ø®Ø§Ù†ÙˆØ§Ø¯Ù‡',
                'body_copy' => 'Ù†Ù…Ø§ÛŒØ´ Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯ ÙˆÛŒÚ˜Ù‡ Ú©Ø§ÙÙ‡ Ø§Ú©Ùˆ Ø¯Ø± Ù…Ø³ÛŒØ± Ø®Ø§Ù†ÙˆØ§Ø¯Ù‡.',
                'cta_text' => 'Ù…Ø´Ø§Ù‡Ø¯Ù‡ Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯',
                'target_url' => 'https://example.com/cafe-eco',
                'ad_type' => 'standalone',
                'creative_type' => 'image',
                'placement_type' => 'fixed_display',
                'asset_url' => 'https://example.com/ad.jpg',
                'budget_amount' => 1500000,
                'impression_cap' => 1000,
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending_review');

        $adRequest = AdRequest::query()
            ->where('title', 'ØªØ¨Ù„ÛŒØº Ù†ÙˆØ´ÛŒØ¯Ù†ÛŒ Ø®Ø§Ù†ÙˆØ§Ø¯Ù‡')
            ->with(['partnerAccount', 'creatives', 'placements'])
            ->firstOrFail();

        $this->assertSame('cafe-eco', $adRequest->partnerAccount->code);
        $this->assertSame('pending_review', $adRequest->status);
        $this->assertSame('image', $adRequest->creatives->first()->creative_type);
        $this->assertSame('fixed_display', $adRequest->placements->first()->placement_type);
    }

    public function test_admin_can_approve_ad_request_and_viewer_cannot_review(): void
    {
        $adRequest = $this->submitAdRequest();
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->postJson(route('admin.ads.api.approve', $adRequest))
            ->assertForbidden();

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->postJson(route('admin.ads.api.approve', $adRequest), [
                'notes' => 'Ù…Ø­ØªÙˆØ§ Ø¨Ø±Ø§ÛŒ Ù†Ù…Ø§ÛŒØ´Ú¯Ø± ÙˆØ±ÙˆØ¯ÛŒ ØªØ§ÛŒÛŒØ¯ Ø´Ø¯.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $adRequest->refresh();

        $this->assertSame('approved', $adRequest->status);
        $this->assertSame('approved', $adRequest->approvals()->firstOrFail()->action);
        $this->assertSame('approved', $adRequest->placements()->firstOrFail()->status);
    }

    public function test_hub_manager_can_reject_ad_request(): void
    {
        $adRequest = $this->submitAdRequest('ravaq.store@example.test', 'Ravaq scoped ad request');
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.ads.api.reject', $adRequest), [
                'notes' => 'Ù†ÛŒØ§Ø²Ù…Ù†Ø¯ Ø§ØµÙ„Ø§Ø­ Ù…Ø­ØªÙˆØ§.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $adRequest->refresh();

        $this->assertSame('rejected', $adRequest->status);
        $this->assertSame('rejected', $adRequest->creatives()->firstOrFail()->status);
        $this->assertSame('rejected', $adRequest->placements()->firstOrFail()->status);
    }

    public function test_admin_can_open_ad_moderation_page(): void
    {
        $this->withoutVite();
        $this->submitAdRequest();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.ads.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/ads/index')
                ->where('stats.requests', 1)
                ->where('stats.devices', 2)
                ->has('adRequests', 1)
                ->has('displayDevices', 2));
    }

    public function test_display_device_can_read_approved_schedule(): void
    {
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();
        $adRequest = $this->submitAdRequest('ravaq.store@example.test', 'Ravaq scheduled display ad', 'mobile_display');
        $device = DisplayDevice::query()->where('code', 'ecopark-mobile-promo-display')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.ads.api.approve', $adRequest))
            ->assertOk();

        $this->getJson(route('display.schedule', $device))
            ->assertOk()
            ->assertJsonCount(0, 'data.items');

        $this->actingAs($manager)
            ->postJson(route('hub.ads.api.schedule', $adRequest), [
                'display_device_id' => $device->id,
                'starts_at' => now()->subMinute()->toIso8601String(),
                'ends_at' => now()->addDay()->toIso8601String(),
                'priority' => 2,
            ])
            ->assertOk();

        $this->getJson(route('display.schedule', $device))
            ->assertOk()
            ->assertJsonPath('data.device.code', 'ecopark-mobile-promo-display')
            ->assertJsonPath('data.items.0.adRequestId', $adRequest->id)
            ->assertJsonPath('data.items.0.placementType', 'mobile_display');
    }

    public function test_display_device_can_record_ad_events(): void
    {
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();
        $adRequest = $this->submitAdRequest('ravaq.store@example.test', 'Ravaq event display ad', 'mobile_display');
        $device = DisplayDevice::query()->where('code', 'ecopark-mobile-promo-display')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.ads.api.approve', $adRequest))
            ->assertOk();

        $this->actingAs($manager)
            ->postJson(route('hub.ads.api.schedule', $adRequest), [
                'display_device_id' => $device->id,
                'starts_at' => now()->subMinute()->toIso8601String(),
                'ends_at' => now()->addDay()->toIso8601String(),
            ])
            ->assertOk();

        $this->postJson(route('display.events.store', $device), [
            'ad_request_id' => $adRequest->id,
            'event_type' => 'impression',
            'metadata' => ['slot' => 'entry-main'],
        ])
            ->assertCreated()
            ->assertJsonPath('data.eventType', 'impression');

        $this->assertDatabaseHas('ad_events', [
            'ad_request_id' => $adRequest->id,
            'display_device_id' => $device->id,
            'event_type' => 'impression',
        ]);
    }

    public function test_display_schedule_excludes_pending_ads(): void
    {
        $adRequest = $this->submitAdRequest();
        $device = DisplayDevice::query()->where('code', 'ecopark-entry-fixed-display')->firstOrFail();

        $this->getJson(route('display.schedule', $device))
            ->assertOk()
            ->assertJsonCount(0, 'data.items');

        $this->postJson(route('display.events.store', $device), [
            'ad_request_id' => $adRequest->id,
            'event_type' => 'impression',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ad_request_id');
    }

    public function test_hub_manager_cannot_review_foreign_ad_request(): void
    {
        $adRequest = $this->submitAdRequest();
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.ads.api.approve', $adRequest))
            ->assertForbidden();

        $this->assertSame('pending_review', $adRequest->fresh()->status);
    }

    private function submitAdRequest(string $email = 'cafe.eco@example.test', string $title = 'Test cafe ad', string $placementType = 'fixed_display'): AdRequest
    {
        $partnerUser = User::query()->where('email', $email)->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.ads.api.store'), [
                'title' => $title,
                'body_copy' => 'ÛŒÚ© Ø¯Ø±Ø®ÙˆØ§Ø³Øª ØªØ¨Ù„ÛŒØº Ù…Ø³ØªÙ‚Ù„ Ø¨Ø±Ø§ÛŒ ØªØ³Øª.',
                'ad_type' => 'standalone',
                'creative_type' => 'image',
                'placement_type' => $placementType,
            ])
            ->assertCreated();

        return AdRequest::query()->where('title', $title)->firstOrFail();
    }
}
