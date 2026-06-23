<?php

namespace App\Services;

use App\Models\QrCode;
use Illuminate\Support\Collection;

class QrRegistryService
{
    /** @return Collection<int, array<string, mixed>> */
    public function list(): Collection
    {
        return QrCode::query()
            ->with(['venue:id,name,code', 'touchpoint:id,label,code', 'campaign:id,name,code'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (QrCode $qr): array => $this->serializeQrCode($qr));
    }

    /** @return array<string, mixed> */
    private function serializeQrCode(QrCode $qr): array
    {
        return [
            'id' => $qr->id,
            'code' => $qr->code,
            'label' => $qr->label,
            'status' => $qr->status->value,
            'destinationUrl' => $qr->destination_url,
            'venue' => $qr->venue ? [
                'id' => $qr->venue->id,
                'code' => $qr->venue->code,
                'name' => $qr->venue->name,
            ] : null,
            'touchpoint' => $qr->touchpoint ? [
                'id' => $qr->touchpoint->id,
                'code' => $qr->touchpoint->code,
                'label' => $qr->touchpoint->label,
            ] : null,
            'campaign' => $qr->campaign ? [
                'id' => $qr->campaign->id,
                'code' => $qr->campaign->code,
                'name' => $qr->campaign->name,
            ] : null,
            'validFrom' => $qr->valid_from?->toIso8601String(),
            'validUntil' => $qr->valid_until?->toIso8601String(),
        ];
    }
}
