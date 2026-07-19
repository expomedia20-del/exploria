<?php

namespace App\Actions\Auth;

use App\Actions\Events\RecordQrScanEventAction;
use App\Contracts\OtpProvider;
use App\Models\OtpRequest;
use App\Models\QrCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RequestOtpAction
{
    public function __construct(
        private readonly OtpProvider $provider,
        private readonly RecordQrScanEventAction $recordQrScan,
    ) {}

    public function execute(
        string $mobile,
        ?string $sourceQrCode = null,
        string $sessionId = '',
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): OtpRequest {
        if ($sourceQrCode !== null && $sourceQrCode !== '') {
            $qr = QrCode::query()
                ->with(['venue', 'touchpoint', 'campaign'])
                ->where('code', $sourceQrCode)
                ->first();

            if (! $qr?->isAvailableForLanding()) {
                if ($qr) {
                    $this->recordQrScan->record($qr, null, 'invalid', $sessionId, $ipAddress, $userAgent, 'unavailable_qr');
                } else {
                    $this->recordQrScan->recordUnknown($sourceQrCode, $sessionId, $ipAddress, $userAgent);
                }

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
