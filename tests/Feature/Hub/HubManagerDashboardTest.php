<?php

namespace Tests\Feature\Hub;

use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\User;
use App\Services\MissionRewardBlueprintService;
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
                ->where('stats.hubs', 2)
                ->where('stats.partners', 2)
                ->where('stats.displayDevices', 1)
                ->has('hubs', 2)
                ->has('partners', 2)
                ->has('displayDevices', 1));
    }

    public function test_ravaq_manager_can_open_ravaq_named_dashboard(): void
    {
        $this->withoutVite();
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->actingAs($manager)
            ->get(route('ravaq.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hub/dashboard')
                ->where('stats.hubs', 2)
                ->where('hubs.0.code', 'ravaq-commercial-hub')
                ->where('hubs.1.code', 'foodcourt-family-hub'));

        $this->actingAs($manager)
            ->getJson(route('ravaq.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.hubs.0.code', 'ravaq-commercial-hub')
            ->assertJsonPath('data.hubs.1.code', 'foodcourt-family-hub')
            ->assertJsonMissing(['code' => 'gonbad-mina-science-hub'])
            ->assertJsonMissing(['code' => 'family-route-sponsor']);
    }

    public function test_admin_can_open_hub_dashboard_for_support(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => 'admin']);
        $activeHubCount = Hub::query()->where('status', 'active')->count();

        $this->actingAs($admin)
            ->get(route('hub.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hub/dashboard')
                ->where('stats.hubs', $activeHubCount)
                ->has('hubs', $activeHubCount));

        $this->actingAs($admin)
            ->getJson(route('hub.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.hubs', $activeHubCount);
    }


    public function test_hub_dashboard_api_only_returns_managed_scope(): void
    {
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();

        $this->submitAdRequest('family.sponsor@example.test', 'Out of scope science sponsor ad');
        $this->submitAdRequest('ravaq.store@example.test', 'Scoped ravaq ad');
        $this->submitPartnerOffer('ravaq.store@example.test', 'Scoped ravaq offer');

        $this->actingAs($manager)
            ->getJson(route('hub.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.stats.pendingAds', 1)
            ->assertJsonPath('data.stats.pendingRewards', 1)
            ->assertJsonPath('data.adRequests.0.title', 'Scoped ravaq ad')
            ->assertJsonPath('data.rewards.0.name', 'Scoped ravaq offer')
            ->assertJsonMissing(['title' => 'Out of scope science sponsor ad']);
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

        $reviewedAd = collect($response->json('data.adRequests'))->firstWhere('title', 'Reviewed ravaq ad');
        $reviewedReward = collect($response->json('data.rewards'))->firstWhere('name', 'Reviewed ravaq offer');

        $this->assertSame('Approved for mobile ravaq display.', $reviewedAd['reviewNotes'] ?? null);
        $this->assertNotNull($reviewedAd['reviewedAt'] ?? null);
        $this->assertSame('Partner must clarify terms before publishing.', $reviewedReward['reviewNotes'] ?? null);
        $this->assertNotNull($reviewedReward['reviewedAt'] ?? null);
    }

    public function test_hub_manager_can_schedule_approved_ad_to_managed_display(): void
    {
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();
        $adRequest = $this->submitAdRequest('ravaq.store@example.test', 'Scheduled ravaq mobile ad', 'mobile_display');
        $displayDevice = DisplayDevice::query()->where('code', 'ecopark-mobile-promo-display')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.ads.api.approve', $adRequest), [
                'notes' => 'Ready for ravaq mobile display.',
            ])
            ->assertOk();

        $this->actingAs($manager)
            ->postJson(route('hub.ads.api.schedule', $adRequest), [
                'display_device_id' => $displayDevice->id,
                'starts_at' => now()->subMinute()->toIso8601String(),
                'ends_at' => now()->addDay()->toIso8601String(),
                'priority' => 2,
            ])
            ->assertOk()
            ->assertJsonPath('data.displayDeviceCode', 'ecopark-mobile-promo-display')
            ->assertJsonPath('data.priority', 2)
            ->assertJsonPath('data.status', 'scheduled');

        $placement = $adRequest->placements()->firstOrFail();
        $this->assertSame($displayDevice->id, $placement->display_device_id);
        $this->assertSame(2, $placement->priority);

        $this->getJson(route('display.schedule', $displayDevice))
            ->assertOk()
            ->assertJsonPath('data.items.0.adRequestId', $adRequest->id)
            ->assertJsonPath('data.items.0.priority', 2);

        $this->actingAs($manager)
            ->getJson(route('hub.dashboard.index'))
            ->assertOk()
            ->assertJsonPath('data.displayScheduleItems.0.adRequestId', $adRequest->id)
            ->assertJsonPath('data.displayScheduleItems.0.displayDeviceCode', 'ecopark-mobile-promo-display');
    }

    public function test_hub_manager_can_cancel_managed_display_schedule(): void
    {
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();
        $adRequest = $this->submitAdRequest('ravaq.store@example.test', 'Cancelable ravaq mobile ad', 'mobile_display');
        $displayDevice = DisplayDevice::query()->where('code', 'ecopark-mobile-promo-display')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.ads.api.approve', $adRequest))
            ->assertOk();

        $this->actingAs($manager)
            ->postJson(route('hub.ads.api.schedule', $adRequest), [
                'display_device_id' => $displayDevice->id,
                'starts_at' => now()->subMinute()->toIso8601String(),
                'ends_at' => now()->addDay()->toIso8601String(),
                'priority' => 2,
            ])
            ->assertOk();

        $placement = $adRequest->placements()->firstOrFail();

        $response = $this->actingAs($manager)
            ->postJson(route('hub.ad-placements.api.cancel', $placement))
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertNull($response->json('data.displayDeviceId'));
        $this->assertNull($placement->fresh()->display_device_id);
        $this->assertSame('approved', $placement->fresh()->status);

        $this->getJson(route('display.schedule', $displayDevice))
            ->assertOk()
            ->assertJsonCount(0, 'data.items');

        $this->actingAs($manager)
            ->getJson(route('hub.dashboard.index'))
            ->assertOk()
            ->assertJsonCount(0, 'data.displayScheduleItems');
    }

    public function test_hub_manager_cannot_schedule_ad_to_foreign_display(): void
    {
        $manager = User::query()->where('email', 'ravaq.manager@example.test')->firstOrFail();
        $adRequest = $this->submitAdRequest('ravaq.store@example.test', 'Foreign display ravaq ad', 'mobile_display');
        $foreignDisplay = DisplayDevice::query()->where('code', 'ecopark-entry-fixed-display')->firstOrFail();

        $this->actingAs($manager)
            ->postJson(route('admin.ads.api.approve', $adRequest))
            ->assertOk();

        $this->actingAs($manager)
            ->postJson(route('hub.ads.api.schedule', $adRequest), [
                'display_device_id' => $foreignDisplay->id,
                'priority' => 3,
            ])
            ->assertForbidden();

        $this->assertNull($adRequest->placements()->firstOrFail()->display_device_id);
    }

    private function submitAdRequest(string $email, string $title, string $placementType = 'fixed_display'): AdRequest
    {
        $partnerUser = User::query()->where('email', $email)->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.ads.api.store'), [
                'title' => $title,
                'body_copy' => 'Dashboard scope test ad.',
                'ad_type' => 'standalone',
                'creative_type' => 'image',
                'placement_type' => $placementType,
            ])
            ->assertCreated();

        return AdRequest::query()->where('title', $title)->firstOrFail();
    }

    private function submitPartnerOffer(string $email, string $name): RewardDefinition
    {
        $partnerUser = User::query()->where('email', $email)->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.offers.api.store'), [
                ...$this->rewardStepPayload('bronze'),
                'name' => $name,
                'reward_type' => 'partner_coupon',
                'point_cost' => 120,
                'stock_quantity' => 10,
            ])
            ->assertCreated();

        return RewardDefinition::query()->where('name', $name)->firstOrFail();
    }

    /** @return array{cycle_step_index: int, cycle_step_label: string, reward_tier: string, reward_option: string|null} */
    private function rewardStepPayload(string $tierKey): array
    {
        $campaign = QrCode::query()->firstOrFail()->campaign;
        $blueprintCode = $campaign?->metadata['blueprint_code'] ?? null;
        if (! is_string($blueprintCode) && $campaign?->campaign_type === 'pilot_visit') {
            $blueprintCode = 'ecopark-pilot-treasure-route';
        }
        $blueprint = app(MissionRewardBlueprintService::class)->handoff(is_string($blueprintCode) ? $blueprintCode : null);
        $step = collect($blueprint['missionPlan'] ?? [])->firstWhere('rewardTier', $tierKey)
            ?? collect($blueprint['missionPlan'] ?? [])->first();
        $tier = collect($blueprint['rewardDesign']['tiers'] ?? [])->firstWhere('tierKey', $step['rewardTier'] ?? $tierKey);

        return [
            'cycle_step_index' => (int) ($step['index'] ?? 1),
            'cycle_step_label' => (string) ($step['userStep'] ?? 'campaign step'),
            'reward_tier' => (string) ($step['rewardTier'] ?? $tierKey),
            'reward_option' => $tier['options'][0] ?? null,
        ];
    }
}
