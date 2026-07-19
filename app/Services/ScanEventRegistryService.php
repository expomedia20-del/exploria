<?php

namespace App\Services;

use App\Models\ScanEvent;

class ScanEventRegistryService
{
    /** @return array{items: list<array<string, mixed>>, summary: array<string, int>, filters: array{result: string|null}} */
    public function registry(?string $result = null): array
    {
        $items = array_values(ScanEvent::query()
            ->with(['qrCode:id,code,label'])
            ->when($result, fn ($query) => $query->where('result', $result))
            ->latest('scanned_at')
            ->limit(100)
            ->get()
            ->map(fn (ScanEvent $event): array => [
                'id' => $event->id,
                'eventType' => match ($event->result) {
                    'accepted' => 'qr_scanned',
                    'duplicate' => 'duplicate_scan_flagged',
                    default => 'invalid_scan',
                },
                'result' => $event->result,
                'riskFlag' => $event->risk_flag,
                'riskReason' => $event->risk_reason,
                'qrCode' => $event->qrCode?->code,
                'qrLabel' => $event->qrCode?->label,
                'venueId' => $event->venue_id,
                'touchpointId' => $event->touchpoint_id,
                'campaignId' => $event->campaign_id,
                'actorLabel' => $event->user_id ? 'کاربر #'.$event->user_id : 'نشست ناشناس',
                'scannedAt' => $event->scanned_at->toIso8601String(),
            ])
            ->values()
            ->all());

        return [
            'items' => $items,
            'summary' => [
                'total' => ScanEvent::query()->count(),
                'accepted' => ScanEvent::query()->where('result', 'accepted')->count(),
                'invalid' => ScanEvent::query()->where('result', 'invalid')->count(),
                'duplicate' => ScanEvent::query()->where('result', 'duplicate')->count(),
            ],
            'filters' => ['result' => $result],
        ];
    }
}
