<?php

namespace Tests\Feature\Hub;

use App\Models\AdRequest;
use App\Models\RewardDefinition;
use App\Models\User;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HubManagerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_hub_manager_can_open_scoped_dashboard(): void
    {
        $this->withoutVite();
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->get(route('hub.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hub/dashboard')
                ->where('stats.hubs', 1)
                ->where('stats.partners', 1)
                ->where('stats.displayDevices', 1)
                ->has('hubs', 1)
                ->has('partners', 1)
                ->has('displayDevices', 1));
    }

    public function test_hub_dashboard_api_only_returns_managed_scope(): void
    {
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->submitAdRequest('cafe.eco@example.test', 'Out of scope cafe ad');
        $this->submitAdRequest('ravaq.store@example.test', 'Scoped ravaq ad');
        $this->submitPartnerOffer('ravaq.store@example.test', 'Scoped ravaq offer');

        $this->actingAs($manager)
            ->getJson(route('hub.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.pendingAds', 1)
            ->assertJsonPath('data.stats.pendingRewards', 1)
            ->assertJsonPath('data.adRequests.0.title', 'Scoped ravaq ad')
            ->assertJsonPath('data.rewards.0.name', 'Scoped ravaq offer')
            ->assertJsonMissing(['title' => 'Out of scope cafe ad']);
    }

    public function test_hub_dashboard_api_reports_review_notes_after_scoped_decisions(): void
    {
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();
        $adRequest = $this->submitAdRequest('ravaq.store@example.test', 'Reviewed ravaq ad');
        $offer = $this->submitPartnerOffer('ravaq.store@example.test', 'Reviewed ravaq offer');

        $this->actingAs($manager)
            ->postJson(route('admin.ads.api.approve', $adRequest), [
                'notes' => 'Approved for mobile ravaq display.',
            ])
            ->assertOk();

        $this->actingAs($manager)
            ->postJson(route('admin.rewards.api.reject', $offer), [
                'notes' => 'Partner must clarify terms before publishing.',
            ])
            ->assertOk()
            ->assertJsonPath('data.reviewNotes', 'Partner must clarify terms before publishing.');

        $response = $this->actingAs($manager)
            ->getJson(route('hub.dashboard.index'))
            ->assertOk();

        $this->assertSame('Approved for mobile ravaq display.', $response->json('data.adRequests.0.reviewNotes'));
        $this->assertNotNull($response->json('data.adRequests.0.reviewedAt'));
        $this->assertSame('Partner must clarify terms before publishing.', $response->json('data.rewards.0.reviewNotes'));
        $this->assertNotNull($response->json('data.rewards.0.reviewedAt'));
    }

    private function submitAdRequest(string $email, string $title): AdRequest
    {
        $partnerUser = User::query()->where('email', $email)->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.ads.api.store'), [
                'title' => $title,
                'body_copy' => 'Dashboard scope test ad.',
                'ad_type' => 'standalone',
                'creative_type' => 'image',
                'placement_type' => 'fixed_display',
            ])
            ->assertCreated();

        return AdRequest::query()->where('title', $title)->firstOrFail();
    }

    private function submitPartnerOffer(string $email, string $name): RewardDefinition
    {
        $partnerUser = User::query()->where('email', $email)->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.offers.api.store'), [
                'name' => $name,
                'reward_type' => 'partner_coupon',
                'point_cost' => 120,
                'stock_quantity' => 10,
            ])
            ->assertCreated();

        return RewardDefinition::query()->where('name', $name)->firstOrFail();
    }
}
