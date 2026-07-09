<?php

namespace Tests\Feature\Consent;

use App\Models\ConsentVersion;
use App\Models\User;
use Database\Seeders\ConsentVersionSeeder;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ConsentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ConsentVersionSeeder::class);
    }

    public function test_current_persian_demo_consent_is_publicly_available(): void
    {
        $this->getJson('/api/v1/consents/current?language=fa')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.language', 'fa')
            ->assertJsonPath('data.is_demo', true)
            ->assertJsonPath('data.accepted', false);
    }

    public function test_current_consent_reports_when_authenticated_user_already_accepted_active_version(): void
    {
        $user = User::factory()->create();
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($user)
            ->postJson('/api/v1/consents/accept', [
                'consentVersionId' => $version->id,
                'source' => 'pwa',
            ])
            ->assertCreated();

        $this->actingAs($user)
            ->getJson('/api/v1/consents/current?language=fa')
            ->assertOk()
            ->assertJsonPath('data.id', $version->id)
            ->assertJsonPath('data.accepted', true);
    }

    public function test_consent_page_requires_authentication(): void
    {
        $this->get('/consent')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_consent_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/consent')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('consent'));
    }

    public function test_acceptance_records_version_user_timestamp_session_and_source(): void
    {
        $user = User::factory()->create();
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($user)
            ->postJson('/api/v1/consents/accept', [
                'consentVersionId' => $version->id,
                'source' => 'qr_landing',
            ])
            ->assertCreated()
            ->assertJsonPath('data.consentVersionId', $version->id);

        $this->assertDatabaseHas('consent_logs', [
            'consent_version_id' => $version->id,
            'user_id' => $user->id,
            'source' => 'qr_landing',
        ]);

        $log = $user->consentLogs()->firstOrFail();
        $this->assertNotNull($log->accepted_at);
        $this->assertSame(64, strlen($log->getRawOriginal('session_hash')));
    }

    public function test_acceptance_from_qr_records_a_visit_and_returns_next_url(): void
    {
        $this->withoutVite();
        $this->seed(PilotLocationSeeder::class);

        $user = User::factory()->create();
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/consents/accept', [
                'consentVersionId' => $version->id,
                'source' => 'qr_landing',
                'sourceQrCode' => PilotLocationSeeder::DEMO_QR_CODE,
            ])
            ->assertCreated()
            ->assertJsonPath('data.consentVersionId', $version->id)
            ->assertJsonStructure(['data' => ['visitId', 'nextUrl']]);

        $this->assertDatabaseCount('visits', 1);
        $this->assertDatabaseHas('visits', [
            'user_id' => $user->id,
            'status' => 'confirmed',
            'source' => 'qr_landing',
        ]);

        $this->get($response->json('data.nextUrl'))->assertOk();
    }

    public function test_accepting_the_same_version_twice_is_idempotent(): void
    {
        $user = User::factory()->create();
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();
        $payload = ['consentVersionId' => $version->id, 'source' => 'pwa'];

        $this->actingAs($user)->postJson('/api/v1/consents/accept', $payload)->assertCreated();
        $this->actingAs($user)->postJson('/api/v1/consents/accept', $payload)->assertCreated();

        $this->assertDatabaseCount('consent_logs', 1);
    }

    public function test_accepting_the_same_qr_visit_twice_is_idempotent(): void
    {
        $this->seed(PilotLocationSeeder::class);

        $user = User::factory()->create();
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();
        $payload = [
            'consentVersionId' => $version->id,
            'source' => 'qr_landing',
            'sourceQrCode' => PilotLocationSeeder::DEMO_QR_CODE,
        ];

        $this->actingAs($user)->postJson('/api/v1/consents/accept', $payload)->assertCreated();
        $this->actingAs($user)->postJson('/api/v1/consents/accept', $payload)->assertCreated();

        $this->assertDatabaseCount('consent_logs', 1);
        $this->assertDatabaseCount('visits', 1);
    }

    public function test_inactive_consent_cannot_be_accepted(): void
    {
        $user = User::factory()->create();
        $version = ConsentVersion::query()->firstOrFail();
        $version->update(['is_active' => false]);

        $this->actingAs($user)
            ->postJson('/api/v1/consents/accept', ['consentVersionId' => $version->id])
            ->assertUnprocessable()
            ->assertJsonPath('errors.consentVersionId.0', 'این نسخه رضایت‌نامه فعال نیست.');
    }
}
