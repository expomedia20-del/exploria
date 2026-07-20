<?php

return [
    'driver' => env('OTP_DRIVER', 'unavailable'),
    'fixed_code' => env('OTP_FIXED_CODE'),
    'expires_minutes' => (int) env('OTP_EXPIRES_MINUTES', 5),
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    'http' => [
        'endpoint' => env('OTP_HTTP_ENDPOINT'),
        'token' => env('OTP_HTTP_TOKEN'),
        'timeout_seconds' => (int) env('OTP_HTTP_TIMEOUT_SECONDS', 5),
        'sender' => env('OTP_HTTP_SENDER', 'EXPLORIA'),
    ],
];
