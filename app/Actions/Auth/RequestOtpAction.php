<?php

namespace App\Actions\Auth;

use App\Contracts\OtpProvider;
use App\Models\OtpRequest;
use Illuminate\Support\Facades\Hash;

class RequestOtpAction
{
    public function __construct(private readonly OtpProvider $provider) {}

    public function execute(string $mobile, ?string $sourceQrCode = null): OtpRequest
    {
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
