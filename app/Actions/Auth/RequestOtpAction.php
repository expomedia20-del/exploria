<?php

namespace App\Actions\Auth;

use App\Contracts\OtpProvider;
use App\Models\OtpRequest;
use App\Models\QrCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RequestOtpAction
{
    public function __construct(private readonly OtpProvider $provider) {}

    public function execute(string $mobile, ?string $sourceQrCode = null): OtpRequest
    {
        if ($sourceQrCode !== null && $sourceQrCode !== '') {
            $qr = QrCode::query()
                ->with(['venue', 'touchpoint', 'campaign'])
                ->where('code', $sourceQrCode)
                ->first();

            if (! $qr?->isAvailableForLanding()) {
                throw ValidationException::withMessages([
                    'sourceQrCode' => 'کد QR معتبر یا فعال نیست. لطفاً دوباره اسکن کنید.',
                ]);
            }
        }

        $code = $this->provider->issue($mobile);

        return OtpRequest::create([
            'mobile' => $mobile,
            'mobile_hash' => hash('sha256', $mobile),
            'code_hash' => Hash::make($code),
            'source_qr_code' => $sourceQrCode,
            'expires_at' => now()->addMinutes(config('otp.expires_minutes')),
        ]);
    }
}
