<?php

namespace App\Infrastructure\Otp;

use App\Contracts\OtpProvider;
use RuntimeException;

class UnavailableOtpProvider implements OtpProvider
{
    public function issue(string $mobile): string
    {
        throw new RuntimeException('ارائه‌دهنده پیامک برای این محیط تنظیم نشده است.');
    }
}
