<?php

namespace App\Services;

use App\Models\EventLog;
use App\Models\QrCode;
use Illuminate\Support\Str;

class ScanEventRegistryService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: list<array<string, mixed>>, summary: array<string, int>, filters: array<string, mixed>}
     */
    public function registry(array $filters = []): array
    {
        $result = is_string($filters['result'] ?? null) ? $filters['result'] : null;
        $eventType = is_string($filters['event_type'] ?? null) ? $filters['event_type'] : null;
        $from = is_string($filters['from'] ?? null) ? $filters['from'] : null;
        $to = is_string($filters['to'] ?? null) ? $filters['to'] : null;
        $events = EventLog::query()
            ->when($result, fn ($query) => $query->where('payload_json->result', $result))
            ->when($eventType, fn ($query) => $query->where('event_type', $eventType))
            ->when($from, fn ($query) => $query->whereDate('occurred_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('occurred_at', '<=', $to))
            ->latest('occurred_at')->limit(100)->get();
        $qrCodeIds = $events
            ->where('object_type', 'qr_code')
            ->pluck('object_id')
            ->filter(fn (?string $id): bool => is_string($id) && Str::isUuid($id));
        $qrCodes = QrCode::query()->whereIn('id', $qrCodeIds)->get()->keyBy('id');

        $items = array_values($events->map(function (EventLog $event) use ($qrCodes): array {
            $qr = $event->object_type === 'qr_code' ? $qrCodes->get($event->object_id) : null;
            $payload = $event->payload_json ?? [];
            $qrCode = $qr instanceof QrCode ? $qr->code : null;
            $qrLabel = $qr instanceof QrCode ? $qr->label : null;
            $payloadCode = is_string($payload['code'] ?? null) ? $payload['code'] : null;
            $payloadName = is_string($payload['name'] ?? null) ? $payload['name'] : null;

            return [
                'id' => $event->id,
                'eventType' => $event->event_type,
                'result' => $payload['result'] ?? null,
                'riskFlag' => (bool) ($payload['risk_flag'] ?? false),
                'riskReason' => $payload['risk_reason'] ?? null,
                'objectType' => $event->object_type,
                'objectId' => $event->object_id,
                'objectCode' => $qrCode ?? $payloadCode,
                'objectLabel' => $qrLabel ?? $payloadName,
                'actorLabel' => $event->actor_user_id ? 'کاربر #'.$event->actor_user_id : 'نشست ناشناس',
                'occurredAt' => $event->occurred_at->toIso8601String(),
            ];
        })->values()->all());

        return [
            'items' => $items,
            'summary' => [
                'total' => EventLog::query()->count(),
                'scans' => EventLog::query()->whereIn('event_type', ['qr_scanned', 'invalid_scan', 'duplicate_scan_flagged'])->count(),
                'auth' => EventLog::query()->whereIn('event_type', ['otp_requested', 'otp_verified'])->count(),
                'consent' => EventLog::query()->whereIn('event_type', ['consent_viewed', 'consent_accepted'])->count(),
                'journey' => EventLog::query()->whereIn('event_type', ['user_registered', 'mission_started', 'mission_completed', 'reward_issued', 'reward_redeemed'])->count(),
                'audit' => EventLog::query()->where('event_type', 'like', 'audit.%')->count(),
            ],
            'filters' => ['result' => $result, 'eventType' => $eventType, 'from' => $from, 'to' => $to],
        ];
    }
}
