<?php

namespace App\Console\Commands;

use App\Models\DisplayDevice;
use App\Services\DisplayDeviceTokenService;
use Illuminate\Console\Command;

class IssueDisplayDeviceTokenCommand extends Command
{
    protected $signature = 'exploria:display-token {code : Display device code}';

    protected $description = 'Issue the bearer token used to provision one Exploria display client.';

    public function handle(DisplayDeviceTokenService $tokens): int
    {
        $displayDevice = DisplayDevice::query()
            ->where('code', (string) $this->argument('code'))
            ->first();

        if (! $displayDevice) {
            $this->error('نمایشگر با این کد پیدا نشد.');

            return self::FAILURE;
        }

        $this->warn('این توکن را فقط در تنظیمات امن همان نمایشگر قرار دهید و در کد یا لاگ ثبت نکنید.');
        $this->line($tokens->tokenFor($displayDevice));

        return self::SUCCESS;
    }
}
