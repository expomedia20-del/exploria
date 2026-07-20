<?php

namespace Tests\Feature\Auth;

use App\Infrastructure\Otp\HttpOtpProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class HttpOtpProviderTest extends TestCase
{
    public function test_http_provider_generates_and_sends_a_six_digit_code(): void
    {
        config([
            'otp.expires_minutes' => 7,
            'otp.http.endpoint' => 'https://sms-provider.example.test/otp',
            'otp.http.token' => 'test-token',
            'otp.http.sender' => 'EXPLORIA',
            'otp.http.timeout_seconds' => 3,
        ]);
        Http::fake([
            'sms-provider.example.test/*' => Http::response(['status' => 'accepted'], 202),
        ]);

        $code = app(HttpOtpProvider::class)->issue('09120000000');

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        Http::assertSent(function (Request $request) use ($code): bool {
            return $request->url() === 'https://sms-provider.example.test/otp'
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request['mobile'] === '09120000000'
                && $request['code'] === $code
                && $request['sender'] === 'EXPLORIA'
                && $request['expires_minutes'] === 7;
        });
    }

    public function test_http_provider_fails_closed_without_endpoint_or_token(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OTP HTTP provider is not configured.');

        app(HttpOtpProvider::class)->issue('09120000000');
    }
}
