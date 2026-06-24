<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\QrCode;
use App\Models\Touchpoint;
use App\Models\Venue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'maxScansPerUserPerWindow' => $qr->max_scans_per_user_per_window,
            'duplicateWindowSeconds' => $qr->duplicate_window_seconds,
        ];
    }

    /**
     * @return array{
     *     venues: array<int, array{id: string, code: string, name: string}>,
     *     campaigns: array<int, array{id: string, code: string, name: string, venueId: string, venueName: string|null}>,
     *     touchpoints: array<int, array{id: string, code: string, label: string, venueId: string|null, venueName: string|null, hubName: string|null}>
     * }
     */
    public function formOptions(): array
    {
        return [
            'venues' => Venue::query()
                ->orderBy('created_at')
                ->get(['id', 'code', 'name'])
                ->toBase()
                ->map(fn (Venue $venue): array => [
                    'id' => $venue->id,
                    'code' => $venue->code,
                    'name' => $venue->name,
                ])
                ->values()
                ->all(),
            'campaigns' => Campaign::query()
                ->with('venue:id,code,name')
                ->orderBy('created_at')
                ->get()
                ->toBase()
                ->map(fn (Campaign $campaign): array => [
                    'id' => $campaign->id,
                    'code' => $campaign->code,
                    'name' => $campaign->name,
                    'venueId' => $campaign->venue_id,
                    'venueName' => $campaign->venue?->name,
                ])
                ->values()
                ->all(),
            'touchpoints' => Touchpoint::query()
                ->with('hub.zone.venue:id,code,name')
                ->orderBy('created_at')
                ->get()
                ->toBase()
                ->map(fn (Touchpoint $touchpoint): array => [
                    'id' => $touchpoint->id,
                    'code' => $touchpoint->code,
                    'label' => $touchpoint->label,
                    'venueId' => $touchpoint->hub?->zone?->venue_id,
                    'venueName' => $touchpoint->hub?->zone?->venue?->name,
                    'hubName' => $touchpoint->hub?->name,
                ])
                ->values()
                ->all(),
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): QrCode
    {
        $venueId = (string) $data['venue_id'];
        $touchpointId = (string) $data['touchpoint_id'];
        $campaignId = (string) $data['campaign_id'];
        $touchpoint = Touchpoint::query()->with('hub.zone')->findOrFail($touchpointId);
        $campaign = Campaign::query()->findOrFail($campaignId);

        if ($touchpoint->hub?->zone?->venue_id !== $venueId) {
            throw ValidationException::withMessages([
                'touchpoint_id' => 'نقطه تماس انتخاب‌شده متعلق به مکان انتخاب‌شده نیست.',
            ]);
        }

        if ($campaign->venue_id !== $venueId) {
            throw ValidationException::withMessages([
                'campaign_id' => 'کمپین انتخاب‌شده متعلق به مکان انتخاب‌شده نیست.',
            ]);
        }

        $code = Str::lower((string) $data['code']);

        return DB::transaction(fn (): QrCode => QrCode::query()->create([
            'code' => $code,
            'venue_id' => $venueId,
            'touchpoint_id' => $touchpoint->id,
            'campaign_id' => $campaign->id,
            'destination_url' => url('/scan/'.$code),
            'label' => $data['label'] ?: null,
            'status' => $data['status'],
            'valid_from' => $data['valid_from'] ?: null,
            'valid_until' => $data['valid_until'] ?: null,
            'max_scans_per_user_per_window' => $data['max_scans_per_user_per_window'],
            'duplicate_window_seconds' => $data['duplicate_window_seconds'],
            'metadata' => ['created_from' => 'admin_qr_registry'],
        ]));
    }
}
