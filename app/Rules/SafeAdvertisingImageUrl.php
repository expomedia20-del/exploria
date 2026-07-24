<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeAdvertisingImageUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $parts = parse_url($value);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = strtolower((string) ($parts['path'] ?? ''));

        if ($scheme !== 'https' || $host === '' || $host === 'localhost' || str_ends_with($host, '.local')) {
            $fail('نشانی تصویر باید HTTPS عمومی و قابل دسترس باشد.');

            return;
        }

        if (
            filter_var($host, FILTER_VALIDATE_IP)
            && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
        ) {
            $fail('نشانی‌های محلی یا خصوصی برای تصویر تبلیغ مجاز نیستند.');

            return;
        }

        if (! preg_match('/\.(?:jpe?g|webp)$/i', $path)) {
            $fail('نشانی تصویر باید مستقیماً به فایل JPEG یا WebP ختم شود.');
        }
    }
}
