<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Infrastructure\Otp\LocalFixedOtpProvider;
use App\Models\ConsentVersion;
use App\Models\OtpRequest;
use App\Models\QrCode;
use App\Models\Visit;
use Database\Seeders\ConsentVersionSeeder;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Tests\TestCase;

class OtpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('otp.driver', 'local');
        config()->set('otp.fixed_code', '123456');
    }

    public function test_valid_mobile_can_request_an_otp_without_exposing_sensitive_data(): void
    {
        $response = $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000000']);

        $response->assertOk()->assertJsonPath('status', 'success')->assertJsonMissing(['code' => '123456']);
        $this->assertDatabaseCount('otp_requests', 1);
    }

    public function test_invalid_mobile_is_rejected_with_a_persian_message(): void
    {
        $this->postJson('/api/v1/auth/otp/request', ['mobile' => '123'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.mobile.0', 'شماره موبایل معتبر نیست.');
    }

    public function test_unknown_source_qr_is_rejected_before_issuing_an_otp(): void
    {
        $this->postJson('/api/v1/auth/otp/request', [
            'mobile' => '09120000000',
            'sourceQrCode' => 'unknown-qr',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.sourceQrCode.0', 'کد QR معتبر یا فعال نیست. لطفاً دوباره اسکن کنید.');

        $this->assertDatabaseCount('otp_requests', 0);
    }

    public function test_unavailable_source_qr_is_rejected_before_issuing_an_otp(): void
    {
        $this->seed(PilotLocationSeeder::class);
        QrCode::query()->firstOrFail()->update(['valid_until' => now()->subMinute()]);

        $this->postJson('/api/v1/auth/otp/request', [
            'mobile' => '09120000000',
            'sourceQrCode' => PilotLocationSeeder::DEMO_QR_CODE,
        ])->assertUnprocessable()
            ->assertJsonPath('errors.sourceQrCode.0', 'کد QR معتبر یا فعال نیست. لطفاً دوباره اسکن کنید.');

        $this->assertDatabaseCount('otp_requests', 0);
    }

    public function test_fixed_local_code_creates_an_authenticated_session(): void
    {
        $otpId = $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000000'])->json('data.otpRequestId');

        $this->postJson('/api/v1/auth/otp/verify', ['otpRequestId' => $otpId, 'code' => '123456'])
            ->assertOk()
            ->assertJsonPath('data.consentRequired', true)
            ->assertJsonPath('data.nextUrl', route('visitor.consent'));

        $this->assertAuthenticated();
        $this->assertSame(UserRole::Visitor, auth()->user()->role);
        $this->assertNotNull(OtpRequest::query()->findOrFail($otpId)->verified_at);
    }

    public function test_returning_user_skips_an_already_accepted_active_consent(): void
    {
        $this->seed(ConsentVersionSeeder::class);
        $firstOtpId = $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000000'])
            ->json('data.otpRequestId');
        $this->postJson('/api/v1/auth/otp/verify', ['otpRequestId' => $firstOtpId, 'code' => '123456'])
            ->assertOk();
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();
        $this->postJson('/api/v1/consents/accept', [
            'consentVersionId' => $version->id,
            'source' => 'pwa',
        ])->assertCreated();
        Auth::logout();

        $returningOtpId = $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000000'])
            ->json('data.otpRequestId');

        $this->postJson('/api/v1/auth/otp/verify', ['otpRequestId' => $returningOtpId, 'code' => '123456'])
            ->assertOk()
            ->assertJsonPath('data.consentRequired', false)
            ->assertJsonPath('data.nextUrl', route('dashboard'));

        $this->assertDatabaseCount('consent_logs', 1);
    }

    public function test_returning_user_with_qr_skips_consent_and_continues_to_visit(): void
    {
        $this->seed([ConsentVersionSeeder::class, PilotLocationSeeder::class]);
        $firstOtpId = $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000000'])
            ->json('data.otpRequestId');
        $this->postJson('/api/v1/auth/otp/verify', ['otpRequestId' => $firstOtpId, 'code' => '123456'])
            ->assertOk();
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();
        $this->postJson('/api/v1/consents/accept', [
            'consentVersionId' => $version->id,
            'source' => 'pwa',
        ])->assertCreated();
        Auth::logout();

        $returningOtpId = $this->postJson('/api/v1/auth/otp/request', [
            'mobile' => '09120000000',
            'sourceQrCode' => PilotLocationSeeder::DEMO_QR_CODE,
        ])->json('data.otpRequestId');
        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'otpRequestId' => $returningOtpId,
            'code' => '123456',
        ])->assertOk()->assertJsonPath('data.consentRequired', false);

        $visit = Visit::query()->sole();
        $response->assertJsonPath('data.nextUrl', route('games.ecopark-treasure', ['visit' => $visit->id]));
        $this->assertDatabaseCount('consent_logs', 1);
    }

    public function test_wrong_code_is_rejected_and_does_not_create_a_session(): void
    {
        $otpId = $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000000'])->json('data.otpRequestId');

        $this->postJson('/api/v1/auth/otp/verify', ['otpRequestId' => $otpId, 'code' => '000000'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.code.0', 'کد تأیید صحیح نیست.');

        $this->assertGuest();
    }

    public function test_otp_requests_are_rate_limited(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000000'])->assertOk();
        }

        $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000000'])->assertTooManyRequests();
    }

    public function test_fixed_provider_fails_closed_outside_local_and_testing(): void
    {
        app()->detectEnvironment(fn (): string => 'production');

        try {
            $this->expectException(RuntimeException::class);
            app(LocalFixedOtpProvider::class)->issue('09120000000');
        } finally {
            app()->detectEnvironment(fn (): string => 'testing');
        }
    }
}
