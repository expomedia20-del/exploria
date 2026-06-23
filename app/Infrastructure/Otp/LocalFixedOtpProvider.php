<?php

namespace App\Infrastructure\Otp;

use App\Contracts\OtpProvider;
use RuntimeException;

class LocalFixedOtpProvider implements OtpProvider
{
    public function issue(string $mobile): string
    {
        if (config('otp.driver') !== 'local' || ! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('ارائه‌دهنده پیامک برای این محیط تنظیم نشده است.');
        }

        $code = config('otp.fixed_code');

        if (! is_string($code) || ! preg_match('/^\d{6}$/', $code)) {
            throw new RuntimeException('کد آزمایشی OTP به‌درستی تنظیم نشده است.');
        }

        return $code;
    }
}
