<?php

namespace Tests\Feature\Advertising;

use App\Enums\UserRole;
use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\PartnerAccount;
use App\Models\PartnerLocation;
use App\Models\PartnerUser;
use App\Models\User;
use App\Models\UserAccessScope;
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

    public function test_partner_ad_submission_rejects_sponsor_only_ad_types(): void
    {
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.ads.api.store'), [
                'title' => 'Sponsor style ad from store panel',
                'ad_type' => 'route_sponsor',
                'creative_type' => 'image',
                'placement_type' => 'fixed_display',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ad_type');

        $this->assertDatabaseMissing('ad_requests', [
            'title' => 'Sponsor style ad from store panel',
        ]);
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
        $this->assertDatabaseHas('event_log', [
            'event_type' => 'audit.ad_approved',
            'actor_user_id' => $admin->id,
            'object_type' => 'ad_request',
            'object_id' => $adRequest->id,
            'venue_id' => $adRequest->venue_id,
        ]);
    }

    public function test_regional_admin_can_review_only_scoped_ad_requests(): void
    {
        $scopedAdRequest = $this->submitAdRequest('ravaq.store@example.test', 'Regional scoped ad request');
        $foreignPartner = $this->createScienceShopPartnerUser('regional-foreign-shop@example.test');
        $foreignAdRequest = $this->submitAdRequest($foreignPartner->email, 'Regional foreign ad request');
        $regionalAdmin = User::factory()->create(['role' => UserRole::RegionalAdmin]);

        UserAccessScope::query()->create([
            'user_id' => $regionalAdmin->id,
            'role_key' => 'regional_admin',
            'scope_type' => 'partner',
            'scope_id' => $scopedAdRequest->partner_account_id,
            'status' => 'active',
        ]);

        $this->actingAs($regionalAdmin)
            ->postJson(route('admin.ads.api.approve', $scopedAdRequest))
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->actingAs($regionalAdmin)
            ->postJson(route('admin.ads.api.approve', $foreignAdRequest))
            ->assertForbidden();

        $this->assertSame('pending_review', $foreignAdRequest->fresh()->status);
    }

    public function test_hub_manager_cannot_reject_ad_request(): void
    {
        $adRequest = $this->submitAdRequest('ravaq.store@example.test', 'Ravaq scoped ad request');
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.ads.api.reject', $adRequest), [
                'notes' => 'Ù†ÛŒØ§Ø²Ù…Ù†Ø¯ Ø§ØµÙ„Ø§Ø­ Ù…Ø­ØªÙˆØ§.',
            ])
            ->assertForbidden();

        $adRequest->refresh();

        $this->assertSame('pending_review', $adRequest->status);
        $this->assertSame('pending_review', $adRequest->creatives()->firstOrFail()->status);
        $this->assertSame('pending_review', $adRequest->placements()->firstOrFail()->status);
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
                ->where('canReviewAds', true)
                ->where('stats.requests', 1)
                ->where('stats.devices', 2)
                ->has('adRequests', 1)
                ->has('displayDevices', 2));
    }

    public function test_hub_manager_opens_ad_page_without_review_permission(): void
    {
        $this->withoutVite();
        $this->submitAdRequest('ravaq.store@example.test', 'Hub visible ad request');
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->get(route('admin.ads.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/ads/index')
                ->where('canReviewAds', false)
                ->has('adRequests', 1));
    }

    public function test_display_device_can_read_approved_schedule(): void
    {
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();
        $adRequest = $this->submitAdRequest('ravaq.store@example.test', 'Ravaq scheduled display ad', 'mobile_display');
        $device = DisplayDevice::query()->where('code', 'ecopark-mobile-promo-display')->firstOrFail();

        $this->approveAdRequest($adRequest);

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

    public function test_partner_ad_can_complete_display_online_offers_and_game_journey(): void
    {
        $this->withoutVite();

        $adRequest = $this->submitAdRequest(
            'cafe.eco@example.test',
            'Full journey cafe campaign ad',
            'fixed_display',
            [
                'body_copy' => 'یک تبلیغ چندکاناله برای نمایشگر، پیشنهادهای امروز و نقشه بازی.',
                'cta_text' => 'دیدن پیشنهاد',
                'target_url' => 'https://example.com/full-journey',
                'online_placements' => ['qr_landing', 'map_route'],
                'impression_cap' => 20,
                'click_cap' => 5,
            ],
        );
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $device = DisplayDevice::query()->where('code', 'ecopark-entry-fixed-display')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.ads.api.approve', $adRequest), [
                'notes' => 'تایید برای اجرای چندکاناله دمو.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        foreach (['fixed_display', 'qr_landing', 'map_route'] as $placementType) {
            $this->assertDatabaseHas('ad_placements', [
                'ad_request_id' => $adRequest->id,
                'placement_type' => $placementType,
                'status' => 'approved',
            ]);
        }

        $this->actingAs($admin)
            ->getJson(route('admin.display-operations.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data.readyPlacements')
            ->assertJsonPath('data.readyPlacements.0.adRequestId', $adRequest->id)
            ->assertJsonPath('data.readyPlacements.0.placementType', 'fixed_display');

        $fixedPlacement = $adRequest->placements()->where('placement_type', 'fixed_display')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.display-operations.placements.api.schedule', $fixedPlacement), [
                'display_device_id' => $device->id,
                'starts_at' => now()->subMinute()->toIso8601String(),
                'ends_at' => now()->addDay()->toIso8601String(),
                'priority' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'scheduled');

        $this->getJson(route('display.schedule', $device))
            ->assertOk()
            ->assertJsonPath('data.items.0.adRequestId', $adRequest->id)
            ->assertJsonPath('data.items.0.title', 'Full journey cafe campaign ad');

        $this->getJson(route('offers.index'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Full journey cafe campaign ad']);

        $this->get(route('games.ecopark-treasure'))
            ->assertOk()
            ->assertSee('Full journey cafe campaign ad');

        $this->postJson(route('offers.game-events.store'), [
            'ad_request_id' => $adRequest->id,
            'event_type' => 'game_offer_click',
            'mission_code' => 'scan-entry-qr',
            'metadata' => ['surface' => 'full_journey_test'],
        ])
            ->assertCreated()
            ->assertJsonPath('data.eventType', 'game_offer_click');

        $this->assertDatabaseHas('ad_events', [
            'ad_request_id' => $adRequest->id,
            'display_device_id' => null,
            'event_type' => 'game_offer_click',
        ]);
    }

    public function test_display_device_can_record_ad_events(): void
    {
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();
        $adRequest = $this->submitAdRequest('ravaq.store@example.test', 'Ravaq event display ad', 'mobile_display');
        $device = DisplayDevice::query()->where('code', 'ecopark-mobile-promo-display')->firstOrFail();

        $this->approveAdRequest($adRequest);

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
        $foreignPartner = $this->createScienceShopPartnerUser();
        $adRequest = $this->submitAdRequest($foreignPartner->email, 'Science hub shop ad request');
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.ads.api.approve', $adRequest))
            ->assertForbidden();

        $this->assertSame('pending_review', $adRequest->fresh()->status);
    }

    /** @param array<string, mixed> $overrides */
    private function submitAdRequest(string $email = 'cafe.eco@example.test', string $title = 'Test cafe ad', string $placementType = 'fixed_display', array $overrides = []): AdRequest
    {
        $partnerUser = User::query()->where('email', $email)->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.ads.api.store'), [
                'title' => $title,
                'body_copy' => 'ÛŒÚ© Ø¯Ø±Ø®ÙˆØ§Ø³Øª ØªØ¨Ù„ÛŒØº Ù…Ø³ØªÙ‚Ù„ Ø¨Ø±Ø§ÛŒ ØªØ³Øª.',
                'ad_type' => 'standalone',
                'creative_type' => 'image',
                'placement_type' => $placementType,
                ...$overrides,
            ])
            ->assertCreated();

        return AdRequest::query()->where('title', $title)->firstOrFail();
    }

    private function approveAdRequest(AdRequest $adRequest): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->postJson(route('admin.ads.api.approve', $adRequest))
            ->assertOk();
    }

    private function createScienceShopPartnerUser(string $email = 'science.shop@example.test'): User
    {
        $hub = Hub::query()->where('code', 'gonbad-mina-science-hub')->with('zone')->firstOrFail();
        $user = User::factory()->create([
            'email' => $email,
            'role' => UserRole::ShopPartner,
        ]);
        $partner = PartnerAccount::query()->create([
            'venue_id' => $hub->zone->venue_id,
            'code' => 'science-shop-test',
            'name' => 'Science Shop Test',
            'partner_type' => 'member_shop',
            'status' => 'active',
        ]);
        PartnerLocation::query()->create([
            'partner_account_id' => $partner->id,
            'venue_id' => $hub->zone->venue_id,
            'zone_id' => $hub->zone_id,
            'hub_id' => $hub->id,
            'location_role' => 'shop',
            'status' => 'active',
        ]);
        PartnerUser::query()->create([
            'partner_account_id' => $partner->id,
            'user_id' => $user->id,
            'role' => 'manager',
            'status' => 'active',
        ]);

        return $user;
    }
}
