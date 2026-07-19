<?php

namespace App\Actions\Visits;

use App\Actions\Events\RecordQrScanEventAction;
use App\Models\ConsentLog;
use App\Models\QrCode;
use App\Models\ScanEvent;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordVisitAction
{
    public function __construct(private readonly RecordQrScanEventAction $recordQrScan) {}

    public function execute(
        User $user,
        string $qrCode,
        ConsentLog $consentLog,
        string $sessionId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Visit {
        $qr = QrCode::query()
            ->with(['venue', 'touchpoint', 'campaign'])
            ->where('code', $qrCode)
            ->first();

        if (! $qr?->isAvailableForLanding()) {
            throw ValidationException::withMessages([
                'sourceQrCode' => 'QR انتخاب‌شده برای ثبت بازدید معتبر یا فعال نیست.',
            ]);
        }

        return DB::transaction(function () use ($user, $qr, $consentLog, $sessionId, $ipAddress, $userAgent): Visit {
            $windowSeconds = $qr->duplicate_window_seconds ?? 300;
            $scanLimit = $qr->max_scans_per_user_per_window ?? 1;
            $recentScans = ScanEvent::query()
                ->where('user_id', $user->id)
                ->where('qr_code_id', $qr->id)
                ->where('scanned_at', '>=', now()->subSeconds($windowSeconds))
                ->count();
            $isDuplicate = $recentScans >= $scanLimit;
            $visit = Visit::query()->firstOrCreate(
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

            $this->recordQrScan->record(
                $qr,
                $user,
                $isDuplicate ? 'duplicate' : 'accepted',
                $sessionId,
                $ipAddress,
                $userAgent,
                $isDuplicate ? 'scan_limit_window' : null,
            );

            return $visit;
        });
    }
}
