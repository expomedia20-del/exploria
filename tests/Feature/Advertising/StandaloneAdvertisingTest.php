<?php

namespace Tests\Feature\Advertising;

use App\Enums\UserRole;
use App\Models\AdRequest;
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
                'title' => 'تبلیغ نوشیدنی خانواده',
                'body_copy' => 'نمایش پیشنهاد ویژه کافه اکو در مسیر خانواده.',
                'cta_text' => 'مشاهده پیشنهاد',
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
            ->where('title', 'تبلیغ نوشیدنی خانواده')
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
                'notes' => 'محتوا برای نمایشگر ورودی تایید شد.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $adRequest->refresh();

        $this->assertSame('approved', $adRequest->status);
        $this->assertSame('approved', $adRequest->approvals()->firstOrFail()->action);
        $this->assertSame('scheduled', $adRequest->placements()->firstOrFail()->status);
    }

    public function test_hub_manager_can_reject_ad_request(): void
    {
        $adRequest = $this->submitAdRequest();
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.ads.api.reject', $adRequest), [
                'notes' => 'نیازمند اصلاح محتوا.',
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

    private function submitAdRequest(): AdRequest
    {
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.ads.api.store'), [
                'title' => 'تبلیغ تستی کافه',
                'body_copy' => 'یک درخواست تبلیغ مستقل برای تست.',
                'ad_type' => 'standalone',
                'creative_type' => 'image',
                'placement_type' => 'fixed_display',
            ])
            ->assertCreated();

        return AdRequest::query()->where('title', 'تبلیغ تستی کافه')->firstOrFail();
    }
}
