<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\AdEvent;
use App\Models\AdPlacement;
use App\Models\DisplayDevice;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminDisplayOperationsService
{
    private const DEVICE_PLACEMENT_TYPES = [
        'fixed_display',
        'mobile_display',
    ];

    /** @return array<string, mixed> */
    public function overview(): array
    {
        $displayDevices = DisplayDevice::query()
            ->with(['venue:id,code,name', 'hub:id,code,name', 'touchpoint:id,code,label'])
            ->withCount([
                'placements as scheduled_placements_count' => fn ($query) => $query->where('status', 'scheduled'),
            ])
            ->orderBy('created_at')
            ->get();

        $deviceEventStats = AdEvent::query()
            ->select('display_device_id')
            ->selectRaw('COUNT(*) as events_count')
            ->selectRaw("SUM(CASE WHEN event_type = 'impression' THEN 1 ELSE 0 END) as impressions_count")
            ->selectRaw("SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks_count")
            ->selectRaw('MAX(occurred_at) as last_event_at')
            ->whereNotNull('display_device_id')
            ->groupBy('display_device_id')
            ->get()
            ->keyBy('display_device_id');

        $adEventStats = AdEvent::query()
            ->select('ad_request_id')
            ->selectRaw('COUNT(*) as events_count')
            ->selectRaw("SUM(CASE WHEN event_type = 'impression' THEN 1 ELSE 0 END) as impressions_count")
            ->selectRaw("SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) as clicks_count")
            ->groupBy('ad_request_id')
            ->get()
            ->keyBy('ad_request_id');

        $scheduledPlacements = AdPlacement::query()
            ->with([
                'adRequest:id,code,title,status,partner_account_id,impression_cap,click_cap',
                'adRequest.partnerAccount:id,code,name,partner_type',
                'displayDevice:id,venue_id,hub_id,code,name,device_type,status',
                'displayDevice.venue:id,code,name',
                'displayDevice.hub:id,code,name',
            ])
            ->where('status', 'scheduled')
            ->whereNotNull('display_device_id')
            ->orderBy('priority')
            ->latest('updated_at')
            ->get()
            ->map(fn (AdPlacement $placement): array => $this->serializePlacement($placement, $adEventStats->get($placement->ad_request_id)));

        $readyPlacements = AdPlacement::query()
            ->with([
                'adRequest:id,code,title,status,partner_account_id,hub_id,venue_id,impression_cap,click_cap',
                'adRequest.partnerAccount:id,code,name,partner_type',
                'adRequest.hub:id,code,name',
                'adRequest.venue:id,code,name',
            ])
            ->where('status', 'approved')
            ->whereIn('placement_type', self::DEVICE_PLACEMENT_TYPES)
            ->whereNull('display_device_id')
            ->whereHas('adRequest', fn ($query) => $query->where('status', 'approved'))
            ->latest('updated_at')
            ->get()
            ->map(fn (AdPlacement $placement): array => $this->serializeReadyPlacement($placement, $displayDevices));

        $devices = $displayDevices
            ->map(fn (DisplayDevice $device): array => $this->serializeDevice($device, $deviceEventStats->get($device->id)))
            ->values();

        return [
            'stats' => [
                'devices' => $devices->count(),
                'activeDevices' => $devices->where('status', RecordStatus::Active->value)->count(),
                'scheduledPlacements' => $scheduledPlacements->count(),
                'readyPlacements' => $readyPlacements->count(),
                'eventsToday' => AdEvent::query()->whereDate('occurred_at', today())->count(),
                'impressions' => AdEvent::query()->where('event_type', 'impression')->count(),
                'onlineDevices' => $devices->where('isOnline', true)->count(),
                'errorDevices' => $devices->where('playbackStatus', 'error')->count(),
            ],
            'displayDevices' => $devices,
            'scheduledPlacements' => $scheduledPlacements,
            'readyPlacements' => $readyPlacements,
        ];
    }

    /** @param array<string, mixed> $data */
    public function schedulePlacement(User $user, AdPlacement $placement, array $data): AdPlacement
    {
        return DB::transaction(function () use ($data, $placement, $user): AdPlacement {
            $placement->load(['adRequest:id,status,title', 'displayDevice:id,code,name,device_type']);

            if (! $placement->adRequest || $placement->adRequest->status !== 'approved') {
                throw ValidationException::withMessages([
                    'ad_placement' => 'فقط جایگاه تبلیغ تاییدشده قابل زمان‌بندی است.',
                ]);
            }

            if (! in_array($placement->status, ['approved', 'scheduled'], true)) {
                throw ValidationException::withMessages([
                    'ad_placement' => 'وضعیت این جایگاه برای زمان‌بندی معتبر نیست.',
                ]);
            }

            $displayDevice = DisplayDevice::query()
                ->where('id', $data['display_device_id'])
                ->where('status', RecordStatus::Active)
                ->firstOrFail();

            if ($placement->placement_type !== $displayDevice->device_type) {
                throw ValidationException::withMessages([
                    'display_device_id' => 'نوع نمایشگر با جایگاه تبلیغ سازگار نیست.',
                ]);
            }

            $placement->update([
                'display_device_id' => $displayDevice->id,
                'status' => 'scheduled',
                'starts_at' => $data['starts_at'] ?? $placement->starts_at,
                'ends_at' => $data['ends_at'] ?? $placement->ends_at,
                'priority' => $data['priority'] ?? $placement->priority,
                'metadata' => [
                    ...($placement->metadata ?? []),
                    'scheduled_by_user_id' => $user->id,
                    'scheduled_at' => now()->toIso8601String(),
                    'source' => 'admin_display_operations',
                ],
            ]);

            return $placement->fresh([
                'adRequest:id,code,title,status,partner_account_id,impression_cap,click_cap',
                'adRequest.partnerAccount:id,code,name,partner_type',
                'displayDevice:id,venue_id,hub_id,code,name,device_type,status',
                'displayDevice.venue:id,code,name',
                'displayDevice.hub:id,code,name',
            ]) ?? $placement;
        });
    }

    public function cancelPlacement(User $user, AdPlacement $placement): AdPlacement
    {
        return DB::transaction(function () use ($placement, $user): AdPlacement {
            $placement->load(['displayDevice:id,code,name,device_type']);

            if (! $placement->displayDevice) {
                throw ValidationException::withMessages([
                    'ad_placement' => 'این جایگاه روی نمایشگر زمان‌بندی نشده است.',
                ]);
            }

            $placement->update([
                'display_device_id' => null,
                'status' => 'approved',
                'metadata' => [
                    ...($placement->metadata ?? []),
                    'cancelled_by_user_id' => $user->id,
                    'cancelled_at' => now()->toIso8601String(),
                    'cancelled_source' => 'admin_display_operations',
                ],
            ]);

            return $placement->fresh(['adRequest:id,code,title,status,partner_account_id']) ?? $placement;
        });
    }

    /** @return array<string, mixed> */
    public function serializePlacement(AdPlacement $placement, ?Model $eventStats = null): array
    {
        return [
            'id' => $placement->id,
            'adRequestId' => $placement->ad_request_id,
            'adCode' => $placement->adRequest?->code,
            'adTitle' => $placement->adRequest?->title,
            'adStatus' => $placement->adRequest?->status,
            'partnerName' => $placement->adRequest?->partnerAccount?->name,
            'placementType' => $placement->placement_type,
            'status' => $placement->status,
            'priority' => $placement->priority,
            'startsAt' => $placement->starts_at?->toIso8601String(),
            'endsAt' => $placement->ends_at?->toIso8601String(),
            'displayDeviceId' => $placement->display_device_id,
            'displayDeviceCode' => $placement->displayDevice?->code,
            'displayDeviceName' => $placement->displayDevice?->name,
            'displayDeviceType' => $placement->displayDevice?->device_type,
            'venueName' => $placement->displayDevice?->venue?->name,
            'hubName' => $placement->displayDevice?->hub?->name,
            'eventsCount' => $this->aggregateInt($eventStats, 'events_count'),
            'impressionsCount' => $this->aggregateInt($eventStats, 'impressions_count'),
            'clicksCount' => $this->aggregateInt($eventStats, 'clicks_count'),
            'impressionCap' => $placement->adRequest?->impression_cap,
            'clickCap' => $placement->adRequest?->click_cap,
        ];
    }

    /** @return array<string, mixed> */
    private function serializeDevice(DisplayDevice $device, ?Model $eventStats = null): array
    {
        $lastHeartbeatAt = $device->last_heartbeat_at;
        $isOnline = $lastHeartbeatAt !== null && $lastHeartbeatAt->greaterThanOrEqualTo(now()->subMinutes(2));

        return [
            'id' => $device->id,
            'code' => $device->code,
            'name' => $device->name,
            'deviceType' => $device->device_type,
            'status' => $device->status->value,
            'formats' => $device->supported_media_formats ?? [],
            'venueName' => $device->venue?->name,
            'hubName' => $device->hub?->name,
            'touchpointLabel' => $device->touchpoint?->label,
            'scheduledPlacementsCount' => (int) ($device->scheduled_placements_count ?? 0),
            'eventsCount' => $this->aggregateInt($eventStats, 'events_count'),
            'impressionsCount' => $this->aggregateInt($eventStats, 'impressions_count'),
            'clicksCount' => $this->aggregateInt($eventStats, 'clicks_count'),
            'lastEventAt' => $this->aggregateString($eventStats, 'last_event_at'),
            'isOnline' => $isOnline,
            'lastHeartbeatAt' => $lastHeartbeatAt?->toIso8601String(),
            'playbackStatus' => $device->playback_status,
            'currentSlot' => $device->current_slot,
            'lastPlaybackResult' => $device->last_playback_result,
            'lastPlaybackError' => $device->last_playback_error,
        ];
    }

    /**
     * @param  Collection<int, DisplayDevice>  $displayDevices
     * @return array<string, mixed>
     */
    private function serializeReadyPlacement(AdPlacement $placement, Collection $displayDevices): array
    {
        return [
            'id' => $placement->id,
            'adRequestId' => $placement->ad_request_id,
            'adCode' => $placement->adRequest?->code,
            'adTitle' => $placement->adRequest?->title,
            'partnerName' => $placement->adRequest?->partnerAccount?->name,
            'placementType' => $placement->placement_type,
            'priority' => $placement->priority,
            'startsAt' => $placement->starts_at?->toIso8601String(),
            'endsAt' => $placement->ends_at?->toIso8601String(),
            'venueName' => $placement->adRequest?->venue?->name,
            'hubName' => $placement->adRequest?->hub?->name,
            'impressionCap' => $placement->adRequest?->impression_cap,
            'clickCap' => $placement->adRequest?->click_cap,
            'compatibleDisplayIds' => $displayDevices
                ->filter(fn (DisplayDevice $device): bool => $device->status === RecordStatus::Active && $device->device_type === $placement->placement_type)
                ->pluck('id')
                ->values()
                ->all(),
        ];
    }

    private function aggregateInt(?Model $eventStats, string $key): int
    {
        return (int) ($eventStats?->getAttribute($key) ?? 0);
    }

    private function aggregateString(?Model $eventStats, string $key): ?string
    {
        $value = $eventStats?->getAttribute($key);

        return is_string($value) ? $value : null;
    }
}
