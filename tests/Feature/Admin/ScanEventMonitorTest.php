<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\ConsentVersion;
use App\Models\User;
use Database\Seeders\ConsentVersionSeeder;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ScanEventMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ConsentVersionSeeder::class, PilotLocationSeeder::class]);
    }

    public function test_viewer_can_read_filtered_privacy_safe_scan_log(): void
    {
        $this->withoutVite();
        $visitor = User::factory()->create(['mobile' => '09120000000']);
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();
        $this->actingAs($visitor)->postJson('/api/v1/consents/accept', [
            'consentVersionId' => $version->id,
            'source' => 'qr_landing',
            'sourceQrCode' => PilotLocationSeeder::DEMO_QR_CODE,
        ])->assertCreated();
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.events.scan-log.page', ['result' => 'accepted']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/events/index')
                ->where('summary.consent', 1)
                ->where('filters.result', 'accepted')
                ->has('items', 1)
                ->where('items.0.result', 'accepted')
                ->missing('items.0.mobile')
                ->missing('items.0.ipHash')
                ->missing('items.0.sessionHash'));
    }

    public function test_monitor_records_consent_view_and_filters_unified_events_by_type_and_date(): void
    {
        $this->withoutVite();
        $visitor = User::factory()->create();

        $this->actingAs($visitor)
            ->getJson('/api/v1/consents/current?language=fa&source=pwa')
            ->assertOk();

        $this->assertDatabaseHas('event_log', [
            'event_type' => 'consent_viewed',
            'actor_user_id' => $visitor->id,
            'object_type' => 'consent_version',
        ]);

        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $today = now()->toDateString();

        $this->actingAs($viewer)
            ->get(route('admin.events.scan-log.page', [
                'event_type' => 'consent_viewed',
                'from' => $today,
                'to' => $today,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.consent', 1)
                ->where('filters.eventType', 'consent_viewed')
                ->where('filters.from', $today)
                ->where('filters.to', $today)
                ->has('items', 1)
                ->where('items.0.eventType', 'consent_viewed'));
    }

    public function test_scan_log_requires_internal_read_access(): void
    {
        $this->getJson(route('admin.events.scan-log.index'))->assertUnauthorized();

        $visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $this->actingAs($visitor)->getJson(route('admin.events.scan-log.index'))->assertForbidden();
    }

    public function test_scan_log_handles_textual_qr_object_ids_without_uuid_lookup(): void
    {
        $this->get('/scan/unknown-textual-qr')->assertNotFound();

        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->getJson(route('admin.events.scan-log.index'))
            ->assertOk()
            ->assertJsonPath('data.items.0.eventType', 'invalid_scan')
            ->assertJsonPath('data.items.0.objectType', 'qr_code')
            ->assertJsonPath('data.items.0.objectId', 'unknown-textual-qr')
            ->assertJsonPath('data.items.0.riskReason', 'unknown_qr');
    }

    public function test_scan_log_rejects_unknown_result_filter_in_persian(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->getJson(route('admin.events.scan-log.index', ['result' => 'other']))
            ->assertUnprocessable()
            ->assertJsonPath('errors.result.0', 'فیلتر نتیجه اسکن معتبر نیست.');
    }

    public function test_scan_log_rejects_an_inverted_date_range(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->getJson(route('admin.events.scan-log.index', ['from' => '2026-07-20', 'to' => '2026-07-19']))
            ->assertUnprocessable();
    }
}
