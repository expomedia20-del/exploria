<?php

namespace App\Actions\Visits;

use App\Models\ConsentLog;
use App\Models\QrCode;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Validation\ValidationException;

class RecordVisitAction
{
    public function execute(User $user, string $qrCode, ConsentLog $consentLog, string $sessionId): Visit
    {
        $qr = QrCode::query()
            ->with(['venue', 'touchpoint', 'campaign'])
            ->where('code', $qrCode)
            ->first();

        if (! $qr?->isAvailableForLanding()) {
            throw ValidationException::withMessages([
                'sourceQrCode' => 'QR انتخاب‌شده برای ثبت بازدید معتبر یا فعال نیست.',
            ]);
        }

        return Visit::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'qr_code_id' => $qr->id,
            ],
            [
                'venue_id' => $qr->venue_id,
                'touchpoint_id' => $qr->touchpoint_id,
                'campaign_id' => $qr->campaign_id,
                'consent_log_id' => $consentLog->id,
                'source' => 'qr_landing',
                'status' => 'confirmed',
                'session_hash' => hash('sha256', $sessionId),
                'occurred_at' => now(),
                'metadata' => ['qr_code' => $qr->code, 'is_demo' => (bool) data_get($qr->metadata, 'is_demo', false)],
            ],
        );
    }
}
