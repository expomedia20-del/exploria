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
                ->where('summary.accepted', 1)
                ->where('filters.result', 'accepted')
                ->has('items', 1)
                ->where('items.0.result', 'accepted')
                ->missing('items.0.mobile')
                ->missing('items.0.ipHash')
                ->missing('items.0.sessionHash'));
    }

    public function test_scan_log_requires_internal_read_access(): void
    {
        $this->getJson(route('admin.events.scan-log.index'))->assertUnauthorized();

        $visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $this->actingAs($visitor)->getJson(route('admin.events.scan-log.index'))->assertForbidden();
    }

    public function test_scan_log_rejects_unknown_result_filter_in_persian(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->getJson(route('admin.events.scan-log.index', ['result' => 'other']))
            ->assertUnprocessable()
            ->assertJsonPath('errors.result.0', 'فیلتر نتیجه اسکن معتبر نیست.');
    }
}
