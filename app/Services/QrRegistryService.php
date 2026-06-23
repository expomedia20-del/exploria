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
            ->map(fn (QrCode $qr): array => [
                'id' => $qr->id,
                'code' => $qr->code,
                'label' => $qr->label,
                'status' => $qr->status->value,
                'destinationUrl' => $qr->destination_url,
                'venue' => $qr->venue?->only(['id', 'code', 'name']),
                'touchpoint' => $qr->touchpoint?->only(['id', 'code', 'label']),
                'campaign' => $qr->campaign?->only(['id', 'code', 'name']),
                'validFrom' => $qr->valid_from?->toIso8601String(),
                'validUntil' => $qr->valid_until?->toIso8601String(),
            ]);
    }
}
