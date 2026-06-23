<?php

namespace Tests\Feature\Auth;

use App\Infrastructure\Otp\LocalFixedOtpProvider;
use App\Models\OtpRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_fixed_local_code_creates_an_authenticated_session(): void
    {
        $otpId = $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09120000000'])->json('data.otpRequestId');

        $this->postJson('/api/v1/auth/otp/verify', ['otpRequestId' => $otpId, 'code' => '123456'])
            ->assertOk()->assertJsonPath('data.consentRequired', true);

        $this->assertAuthenticated();
        $this->assertNotNull(OtpRequest::find($otpId)->verified_at);
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
