<?php

namespace Tests\Feature\Campaign;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\QrCode;
use App\Models\Touchpoint;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CampaignQrCoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_viewer_can_open_campaign_registry_page(): void
    {
        $this->withoutVite();
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.campaigns.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/campaigns/index')
                ->has('campaigns', 1)
                ->has('venueOptions', 3)
                ->where('campaigns.0.code', 'ecopark-pilot-1405'));
    }

    public function test_operator_can_create_campaign(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();

        $this->actingAs($operator)
            ->post(route('admin.campaigns.store'), [
                'venue_id' => $venue->id,
                'code' => 'family-route-1405',
                'name' => 'مسیر خانوادگی ۱۴۰۵',
                'campaign_type' => 'family_route',
                'blueprint_code' => 'family-route',
                'status' => RecordStatus::Draft->value,
                'start_at' => '2026-07-01 09:00:00',
                'end_at' => '2026-08-01 22:00:00',
            ])
            ->assertRedirect(route('admin.campaign-builder.page', [
                'campaign' => 'family-route-1405',
                'blueprint' => 'family-route',
                'blueprint_action' => 'build',
            ]));

        $this->assertDatabaseHas('campaigns', [
            'venue_id' => $venue->id,
            'code' => 'family-route-1405',
            'campaign_type' => 'family_route',
            'status' => 'draft',
        ]);
    }

    public function test_viewer_can_open_campaign_builder_page(): void
    {
        $this->withoutVite();
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.campaign-builder.page', ['campaign' => 'ecopark-pilot-1405']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/campaign-builder/index')
                ->where('selectedCampaign.code', 'ecopark-pilot-1405')
                ->has('steps', 6)
                ->has('roleTracks', 4));
    }

    public function test_viewer_cannot_create_campaign_or_qr(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->post(route('admin.campaigns.store'), [])
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.qr-codes.store'), [])
            ->assertForbidden();
    }

    public function test_operator_can_create_qr_for_matching_venue_campaign_and_touchpoint(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $campaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $touchpoint = Touchpoint::query()->where('code', 'main-gate-qr-stand')->firstOrFail();

        $this->actingAs($operator)
            ->post(route('admin.qr-codes.store'), [
                'venue_id' => $venue->id,
                'campaign_id' => $campaign->id,
                'touchpoint_id' => $touchpoint->id,
                'code' => 'ep1405-main-gate-extra',
                'label' => 'QR تست عملیاتی ورودی',
                'status' => RecordStatus::Active->value,
                'valid_from' => '2026-07-01 09:00:00',
                'valid_until' => '2026-08-01 22:00:00',
                'max_scans_per_user_per_window' => 2,
                'duplicate_window_seconds' => 600,
            ])
            ->assertRedirect();

        $qr = QrCode::query()->where('code', 'ep1405-main-gate-extra')->firstOrFail();

        $this->assertSame($venue->id, $qr->venue_id);
        $this->assertSame($campaign->id, $qr->campaign_id);
        $this->assertSame($touchpoint->id, $qr->touchpoint_id);
        $this->assertSame(url('/scan/ep1405-main-gate-extra'), $qr->destination_url);
        $this->assertSame(2, $qr->max_scans_per_user_per_window);
        $this->assertSame(600, $qr->duplicate_window_seconds);
    }

    public function test_qr_creation_rejects_cross_venue_campaign(): void
    {
        $operator = User::factory()->create(['role' => UserRole::Operator]);
        $ecoPark = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $eram = Venue::query()->where('code', 'eram-park')->firstOrFail();
        $touchpoint = Touchpoint::query()->where('code', 'main-gate-qr-stand')->firstOrFail();
        $eramCampaign = Campaign::query()->create([
            'venue_id' => $eram->id,
            'code' => 'eram-campaign',
            'name' => 'کمپین ارم',
            'campaign_type' => 'pilot_visit',
            'status' => RecordStatus::Draft,
        ]);

        $this->actingAs($operator)
            ->from(route('admin.qr-codes.page'))
            ->post(route('admin.qr-codes.store'), [
                'venue_id' => $ecoPark->id,
                'campaign_id' => $eramCampaign->id,
                'touchpoint_id' => $touchpoint->id,
                'code' => 'bad-cross-venue-campaign',
                'status' => RecordStatus::Draft->value,
                'max_scans_per_user_per_window' => 1,
                'duplicate_window_seconds' => 300,
            ])
            ->assertRedirect(route('admin.qr-codes.page'))
            ->assertSessionHasErrors('campaign_id');

        $this->assertDatabaseMissing('qr_codes', ['code' => 'bad-cross-venue-campaign']);
    }

    public function test_qr_registry_page_includes_form_options(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.qr-codes.page'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/qr-codes/index')
                ->has('qrCodes', 1)
                ->has('formOptions.venues', 3)
                ->has('formOptions.campaigns', 1)
                ->has('formOptions.touchpoints', 1));
    }
}
