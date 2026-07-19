<?php

namespace App\Actions\Events;

use App\Models\EventLog;
use App\Models\QrCode;
use App\Models\ScanEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecordQrScanEventAction
{
    public function record(
        QrCode $qr,
        ?User $user,
        string $result,
        string $sessionId,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $riskReason = null,
    ): ScanEvent {
        return DB::transaction(function () use ($qr, $user, $result, $sessionId, $ipAddress, $userAgent, $riskReason): ScanEvent {
            $riskFlag = $result !== 'accepted';
            $occurredAt = now();
            $sessionHash = $this->hashOrNull($sessionId);
            $scanEvent = ScanEvent::query()->create([
                'qr_code_id' => $qr->id,
                'venue_id' => $qr->venue_id,
                'touchpoint_id' => $qr->touchpoint_id,
                'campaign_id' => $qr->campaign_id,
                'user_id' => $user?->id,
                'session_hash' => $sessionHash,
                'result' => $result,
                'risk_flag' => $riskFlag,
                'risk_reason' => $riskReason,
                'ip_hash' => $this->hashOrNull($ipAddress),
                'user_agent_hash' => $this->hashOrNull($userAgent),
                'payload_json' => ['source' => 'qr_landing'],
                'scanned_at' => $occurredAt,
            ]);

            EventLog::query()->create([
                'event_type' => match ($result) {
                    'accepted' => 'qr_scanned',
                    'duplicate' => 'duplicate_scan_flagged',
                    default => 'invalid_scan',
                },
                'actor_user_id' => $user?->id,
                'session_hash' => $sessionHash,
                'venue_id' => $qr->venue_id,
                'touchpoint_id' => $qr->touchpoint_id,
                'campaign_id' => $qr->campaign_id,
                'object_type' => 'qr_code',
                'object_id' => $qr->id,
                'payload_json' => [
                    'scan_event_id' => $scanEvent->id,
                    'result' => $result,
                    'risk_flag' => $riskFlag,
                    'risk_reason' => $riskReason,
                ],
                'occurred_at' => $occurredAt,
            ]);

            return $scanEvent;
        });
    }

    public function recordUnknown(
        string $qrCode,
        string $sessionId,
        ?string $ipAddress,
        ?string $userAgent,
    ): EventLog {
        return EventLog::query()->create([
            'event_type' => 'invalid_scan',
            'session_hash' => $this->hashOrNull($sessionId),
            'object_type' => 'qr_code',
            'object_id' => $qrCode,
            'payload_json' => [
                'result' => 'invalid',
                'risk_flag' => true,
                'risk_reason' => 'unknown_qr',
                'ip_hash' => $this->hashOrNull($ipAddress),
                'user_agent_hash' => $this->hashOrNull($userAgent),
            ],
            'occurred_at' => now(),
        ]);
    }

    private function hashOrNull(?string $value): ?string
    {
        return $value !== null && $value !== '' ? hash('sha256', $value) : null;
    }
}
