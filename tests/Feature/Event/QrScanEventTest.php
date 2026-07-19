<?php

namespace Tests\Feature\Event;

use App\Models\ConsentVersion;
use App\Models\EventLog;
use App\Models\QrCode;
use App\Models\ScanEvent;
use App\Models\User;
use Database\Seeders\ConsentVersionSeeder;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class QrScanEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ConsentVersionSeeder::class, PilotLocationSeeder::class]);
    }

    public function test_accepted_and_duplicate_scans_are_attributed_without_creating_duplicate_visits(): void
    {
        $user = User::factory()->create();
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();
        $payload = [
            'consentVersionId' => $version->id,
            'source' => 'qr_landing',
            'sourceQrCode' => PilotLocationSeeder::DEMO_QR_CODE,
        ];

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10', 'HTTP_USER_AGENT' => 'Exploria Test Agent'])
            ->actingAs($user)
            ->postJson('/api/v1/consents/accept', $payload)
            ->assertCreated();
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10', 'HTTP_USER_AGENT' => 'Exploria Test Agent'])
            ->actingAs($user)
            ->postJson('/api/v1/consents/accept', $payload)
            ->assertCreated();

        $qr = QrCode::query()->firstOrFail();
        $accepted = ScanEvent::query()->where('result', 'accepted')->sole();
        $duplicate = ScanEvent::query()->where('result', 'duplicate')->sole();

        $this->assertSame($qr->id, $accepted->qr_code_id);
        $this->assertSame($qr->venue_id, $accepted->venue_id);
        $this->assertSame($qr->touchpoint_id, $accepted->touchpoint_id);
        $this->assertSame($qr->campaign_id, $accepted->campaign_id);
        $this->assertSame($user->id, $accepted->user_id);
        $this->assertFalse($accepted->risk_flag);
        $this->assertTrue($duplicate->risk_flag);
        $this->assertSame('scan_limit_window', $duplicate->risk_reason);
        $this->assertSame(hash('sha256', '192.0.2.10'), $accepted->ip_hash);
        $this->assertSame(hash('sha256', 'Exploria Test Agent'), $accepted->user_agent_hash);
        $this->assertDatabaseCount('visits', 1);
        $this->assertDatabaseHas('event_log', ['event_type' => 'qr_scanned']);
        $this->assertDatabaseHas('event_log', ['event_type' => 'duplicate_scan_flagged']);
        $this->assertDatabaseHas('event_log', ['event_type' => 'consent_accepted']);
    }

    public function test_otp_and_consent_events_contain_hashes_but_no_raw_mobile_or_code(): void
    {
        $otpId = $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000000'])
            ->assertOk()
            ->json('data.otpRequestId');
        $this->postJson('/api/v1/auth/otp/verify', ['otpRequestId' => $otpId, 'code' => '123456'])->assertOk();
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();
        $this->postJson('/api/v1/consents/accept', ['consentVersionId' => $version->id, 'source' => 'pwa'])->assertCreated();

        $events = EventLog::query()->whereIn('event_type', ['otp_requested', 'otp_verified', 'consent_accepted'])->get();
        $serialized = json_encode($events->toArray(), JSON_THROW_ON_ERROR);

        $this->assertCount(3, $events);
        $this->assertStringNotContainsString('09120000000', $serialized);
        $this->assertStringNotContainsString('123456', $serialized);
        $this->assertStringContainsString(hash('sha256', '09120000000'), $serialized);
    }

    public function test_unknown_qr_creates_a_privacy_safe_invalid_event(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.20', 'HTTP_USER_AGENT' => 'Unknown Scanner'])
            ->get('/scan/unknown-qr')
            ->assertNotFound();

        $event = EventLog::query()->where('event_type', 'invalid_scan')->sole();

        $this->assertSame('unknown-qr', $event->object_id);
        $this->assertSame('unknown_qr', $event->payload_json['risk_reason']);
        $this->assertSame(hash('sha256', '198.51.100.20'), $event->payload_json['ip_hash']);
        $this->assertSame(hash('sha256', 'Unknown Scanner'), $event->payload_json['user_agent_hash']);
        $this->assertStringNotContainsString('198.51.100.20', json_encode($event->toArray(), JSON_THROW_ON_ERROR));
        $this->assertDatabaseCount('scan_events', 0);
    }

    public function test_known_unavailable_qr_creates_an_attributed_invalid_scan(): void
    {
        QrCode::query()->firstOrFail()->update(['valid_until' => now()->subSecond()]);

        $this->get('/scan/'.PilotLocationSeeder::DEMO_QR_CODE)->assertNotFound();

        $scan = ScanEvent::query()->where('result', 'invalid')->sole();

        $this->assertTrue($scan->risk_flag);
        $this->assertSame('unavailable_qr', $scan->risk_reason);
        $this->assertDatabaseHas('event_log', [
            'event_type' => 'invalid_scan',
            'object_id' => $scan->qr_code_id,
        ]);
    }

    public function test_scan_events_and_event_logs_are_append_only(): void
    {
        $this->get('/scan/unknown-qr')->assertNotFound();
        $event = EventLog::query()->sole();

        $this->expectException(LogicException::class);
        $event->update(['event_type' => 'changed']);
    }

    public function test_scan_event_migration_can_be_rolled_back_and_reapplied(): void
    {
        $migration = require database_path('migrations/2026_07_19_000001_create_scan_events_and_event_log_tables.php');

        $migration->down();

        $this->assertFalse(Schema::hasTable('scan_events'));
        $this->assertFalse(Schema::hasTable('event_log'));

        $migration->up();

        $this->assertTrue(Schema::hasTable('scan_events'));
        $this->assertTrue(Schema::hasTable('event_log'));
    }
}
