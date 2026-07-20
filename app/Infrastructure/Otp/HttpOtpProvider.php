<?php

namespace App\Infrastructure\Otp;

use App\Contracts\OtpProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HttpOtpProvider implements OtpProvider
{
    public function issue(string $mobile): string
    {
        $endpoint = config('otp.http.endpoint');
        $token = config('otp.http.token');
        $sender = config('otp.http.sender');

        if (! is_string($endpoint) || trim($endpoint) === '' || ! is_string($token) || trim($token) === '') {
            throw new RuntimeException('OTP HTTP provider is not configured.');
        }

        $code = (string) random_int(100000, 999999);

        $response = Http::timeout((int) config('otp.http.timeout_seconds'))
            ->acceptJson()
            ->asJson()
            ->withToken($token)
            ->post($endpoint, [
                'mobile' => $mobile,
                'code' => $code,
                'sender' => is_string($sender) && trim($sender) !== '' ? $sender : null,
                'expires_minutes' => config('otp.expires_minutes'),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('OTP HTTP provider failed to send verification code.');
        }

        return $code;
    }
}
