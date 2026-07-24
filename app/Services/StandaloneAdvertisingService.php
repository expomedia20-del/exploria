<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\AdEvent;
use App\Models\AdPlacement;
use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\PartnerAccount;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StandaloneAdvertisingService
{
    private const ONLINE_PLACEMENT_TYPES = [
        'public_feed',
        'qr_landing',
        'reward_page',
        'map_route',
        'post_mission',
    ];

    public function __construct(private readonly PartnerDashboardService $partnerDashboardService, private readonly UserAccessScopeService $accessScopes) {}

    /** @return array<string, mixed> */
    public function partnerOverview(User $user): array
    {
        $partner = $this->partnerDashboardService->partnerForUser($user);
        $partner->load(['venue:id,code,name', 'locations.hub:id,code,name']);

        $adRequests = $this->adRequestsForPartner($partner);

        return [
            'partner' => [
                'id' => $partner->id,
                'code' => $partner->code,
                'name' => $partner->name,
                'partnerType' => $partner->partner_type,
                'venueName' => $partner->venue?->name,
            ],
            'stats' => [
                'requests' => $adRequests->count(),
                'pending' => $adRequests->where('status', 'pending_review')->count(),
                'approved' => $adRequests->where('status', 'approved')->count(),
                'rejected' => $adRequests->where('status', 'rejected')->count(),
            ],
            'hubOptions' => $this->hubOptionsForPartner($partner),
            'adRequests' => $adRequests,
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function adRequestsForPartner(PartnerAccount $partner): Collection
    {
        return $partner->adRequests()
            ->with(['venue:id,code,name', 'hub:id,code,name', 'placements.displayDevice:id,code,name,device_type', 'creatives:id,ad_request_id,creative_type,asset_url,status'])
            ->withCount(['events as impressions_count' => fn ($query) => $query->where('event_type', 'impression')])
            ->latest('created_at')
            ->get()
            ->map(fn (AdRequest $adRequest): array => $this->serializeAdRequest($adRequest));
    }

    /** @return array<string, mixed> */
    public function adminOverview(?User $user = null): array
    {
        $venueIds = $user ? $this->accessScopes->assignedVenueIds($user) : collect();
        $hubIds = $user ? $this->accessScopes->hubIds($user) : collect();
        $partnerIds = $user ? $this->accessScopes->partnerIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        $adRequests = AdRequest::query()
            ->when(! $isGlobal, fn ($query) => $query->where(function ($query) use ($venueIds, $hubIds, $partnerIds): void {
                $query->whereIn('venue_id', $venueIds)
                    ->orWhereIn('hub_id', $hubIds)
                    ->orWhereIn('partner_account_id', $partnerIds);
            }))
            ->with([
                'venue:id,code,name',
                'partnerAccount:id,code,name,partner_type',
                'hub:id,code,name',
                'touchpoint:id,code,label',
                'placements.displayDevice:id,code,name,device_type',
                'creatives:id,ad_request_id,creative_type,asset_url,status',
                'approvals.reviewer:id,name,email',
            ])
            ->withCount(['events as impressions_count' => fn ($query) => $query->where('event_type', 'impression')])
            ->latest('created_at')
            ->get()
            ->map(fn (AdRequest $adRequest): array => $this->serializeAdRequest($adRequest));

        $devices = DisplayDevice::query()
            ->when(! $isGlobal, fn ($query) => $query->where(function ($query) use ($venueIds, $hubIds): void {
                $query->whereIn('venue_id', $venueIds)
                    ->orWhereIn('hub_id', $hubIds);
            }))
            ->with(['venue:id,code,name', 'hub:id,code,name', 'touchpoint:id,code,label'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (DisplayDevice $device): array => [
                'id' => $device->id,
                'code' => $device->code,
                'name' => $device->name,
                'deviceType' => $device->device_type,
                'status' => $device->status->value,
                'formats' => $device->supported_media_formats ?? [],
                'venueName' => $device->venue?->name,
                'hubName' => $device->hub?->name,
                'touchpointLabel' => $device->touchpoint?->label,
            ]);

        return [
            'canReviewAds' => $user === null || $this->canReviewAds($user),
            'governance' => [
                'reviewOwner' => 'تیم داخلی اکسپلوریا',
                'localExecutionOwner' => 'مدیر مکان/هاب در محدوده نمایشگرهای خودش',
                'policy' => 'مدیران مکان و هاب می‌توانند اجرای محلی تبلیغ تاییدشده را زمان‌بندی کنند، اما اختیار تایید یا رد نهایی تبلیغ با تیم اکسپلوریا است.',
            ],
            'stats' => [
                'requests' => $adRequests->count(),
                'pending' => $adRequests->where('status', 'pending_review')->count(),
                'approved' => $adRequests->where('status', 'approved')->count(),
                'rejected' => $adRequests->where('status', 'rejected')->count(),
                'devices' => $devices->count(),
            ],
            'adRequests' => $adRequests,
            'displayDevices' => $devices,
        ];
    }

    /** @param array<string, mixed> $data */
    public function createPartnerAdRequest(User $user, array $data): AdRequest
    {
        $partner = $this->partnerDashboardService->partnerForUser($user);

        return $this->createAdRequestForPartner($user, $partner, $data, 'partner_ad_submission');
    }

    /** @param array<string, mixed> $data */
    public function createSponsorAdRequest(User $user, PartnerAccount $partner, array $data): AdRequest
    {
        if ($partner->partner_type !== 'sponsor') {
            throw ValidationException::withMessages([
                'sponsor' => 'حساب تبلیغاتی انتخاب‌شده از نوع اسپانسر نیست.',
            ]);
        }

        return $this->createAdRequestForPartner($user, $partner, $data, 'sponsor_ad_submission');
    }

    /** @param array<string, mixed> $data */
    private function createAdRequestForPartner(User $user, PartnerAccount $partner, array $data, string $source): AdRequest
    {
        $hub = $this->hubForPartner($partner, $data['hub_id'] ?? null);

        return DB::transaction(function () use ($data, $hub, $partner, $source, $user): AdRequest {
            $adRequest = AdRequest::query()->create([
                'venue_id' => $partner->venue_id,
                'partner_account_id' => $partner->id,
                'hub_id' => $hub?->id,
                'touchpoint_id' => null,
                'submitted_by_user_id' => $user->id,
                'code' => $this->uniqueAdCode($partner->code),
                'title' => $data['title'],
                'body_copy' => $data['body_copy'] ?? null,
                'cta_text' => $data['cta_text'] ?? null,
                'target_url' => $data['target_url'] ?? null,
                'advertiser_type' => $partner->partner_type === 'sponsor' ? 'sponsor' : 'member_partner',
                'ad_type' => $data['ad_type'],
                'status' => 'pending_review',
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'budget_amount' => $data['budget_amount'] ?? null,
                'impression_cap' => $data['impression_cap'] ?? null,
                'click_cap' => $data['click_cap'] ?? null,
                'metadata' => ['source' => $source],
            ]);

            $adRequest->creatives()->create([
                'creative_type' => $data['creative_type'],
                'asset_url' => $data['asset_url'] ?? null,
                'headline' => $data['title'],
                'body_copy' => $data['body_copy'] ?? null,
                'cta_text' => $data['cta_text'] ?? null,
                'status' => 'pending_review',
                'metadata' => ['source' => $source],
            ]);

            $placementTypes = collect([$data['placement_type']])
                ->merge($data['online_placements'] ?? [])
                ->filter(fn (mixed $placementType): bool => is_string($placementType) && $placementType !== '')
                ->unique()
                ->values();

            $placementTypes->each(function (string $placementType) use ($adRequest, $data, $hub): void {
                $adRequest->placements()->create([
                    'placement_type' => $placementType,
                    'status' => 'pending_review',
                    'starts_at' => $data['starts_at'] ?? null,
                    'ends_at' => $data['ends_at'] ?? null,
                    'priority' => $data['priority'] ?? ($this->isOnlinePlacement($placementType) ? 6 : 5),
                    'metadata' => [
                        'requested_hub_id' => $hub?->id,
                        'channel' => $this->isOnlinePlacement($placementType) ? 'online' : 'display',
                    ],
                ]);
            });

            return $adRequest;
        });
    }

    /** @param array<string, mixed> $data */
    public function approve(User $reviewer, AdRequest $adRequest, array $data): AdRequest
    {
        return $this->review($reviewer, $adRequest, 'approved', $data);
    }

    /** @param array<string, mixed> $data */
    public function reject(User $reviewer, AdRequest $adRequest, array $data): AdRequest
    {
        return $this->review($reviewer, $adRequest, 'rejected', $data);
    }

    /** @param array<string, mixed> $data */
    private function review(User $reviewer, AdRequest $adRequest, string $status, array $data): AdRequest
    {
        return DB::transaction(function () use ($adRequest, $data, $reviewer, $status): AdRequest {
            $adRequest->update([
                'status' => $status,
                'metadata' => [
                    ...$this->metadataArray($adRequest->metadata),
                    'reviewed_by_user_id' => $reviewer->id,
                    'reviewed_at' => now()->toIso8601String(),
                ],
            ]);
            $adRequest->creatives()->update(['status' => $status]);
            $adRequest->placements()->update(['status' => $status === 'approved' ? 'approved' : 'rejected']);
            $adRequest->approvals()->create([
                'reviewer_user_id' => $reviewer->id,
                'action' => $status,
                'notes' => $data['notes'] ?? null,
                'metadata' => ['source' => 'admin_ad_review'],
            ]);

            $freshAdRequest = $adRequest->fresh([
                'venue:id,code,name',
                'partnerAccount:id,code,name,partner_type',
                'hub:id,code,name',
                'placements.displayDevice:id,code,name,device_type',
                'creatives:id,ad_request_id,creative_type,asset_url,status',
            ]);

            if (! $freshAdRequest) {
                throw ValidationException::withMessages([
                    'ad_request' => 'درخواست تبلیغ بعد از بازبینی پیدا نشد.',
                ]);
            }

            return $freshAdRequest;
        });
    }

    /** @return array<string, mixed> */
    public function displaySchedule(DisplayDevice $displayDevice): array
    {
        if ($displayDevice->status !== RecordStatus::Active) {
            throw ValidationException::withMessages([
                'display_device' => 'نمایشگر فعال نیست.',
            ]);
        }

        $now = now();
        $placements = $displayDevice->placements()
            ->with(['adRequest.creatives', 'adRequest.partnerAccount:id,code,name,partner_type'])
            ->where('status', 'scheduled')
            ->where(function ($query) use ($now): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('priority')
            ->get();

        $items = $placements
            ->filter(fn ($placement): bool => $placement->adRequest?->status === 'approved')
            ->filter(fn ($placement): bool => $this->withinCaps($placement->adRequest))
            ->values()
            ->map(fn ($placement): array => $this->serializeDisplayPlacement($placement));

        return [
            'device' => [
                'id' => $displayDevice->id,
                'code' => $displayDevice->code,
                'name' => $displayDevice->name,
                'deviceType' => $displayDevice->device_type,
                'formats' => $displayDevice->supported_media_formats ?? [],
            ],
            'generatedAt' => now()->toIso8601String(),
            'items' => $items,
        ];
    }

    /** @param array<string, mixed> $data */
    public function recordDisplayEvent(DisplayDevice $displayDevice, array $data): AdEvent
    {
        $adRequest = AdRequest::query()
            ->where('id', $data['ad_request_id'])
            ->where('status', 'approved')
            ->first();

        if (! $adRequest) {
            throw ValidationException::withMessages([
                'ad_request_id' => 'تبلیغ برای ثبت رویداد نمایشگر معتبر نیست.',
            ]);
        }

        return AdEvent::query()->create([
            'ad_request_id' => $adRequest->id,
            'display_device_id' => $displayDevice->id,
            'event_type' => $data['event_type'],
            'occurred_at' => $data['occurred_at'] ?? now(),
            'metadata' => [
                ...$this->metadataArray($data['metadata'] ?? []),
                'device_code' => $displayDevice->code,
                'source' => 'display_client_api',
            ],
        ]);
    }

    /** @param array<string, mixed> $data */
    public function recordDisplayHeartbeat(DisplayDevice $displayDevice, array $data): DisplayDevice
    {
        $heartbeatAt = isset($data['reported_at']) ? now()->parse($data['reported_at']) : now();
        $metadata = $this->metadataArray($displayDevice->metadata);

        $displayDevice->update([
            'last_heartbeat_at' => $heartbeatAt,
            'playback_status' => $data['playback_status'],
            'current_slot' => $data['current_slot'] ?? null,
            'last_playback_result' => $data['last_playback_result'] ?? null,
            'last_playback_error' => $data['last_playback_error'] ?? null,
            'metadata' => [
                ...$metadata,
                'last_heartbeat' => [
                    'source' => 'display_client_api',
                    'current_ad_request_id' => $data['current_ad_request_id'] ?? null,
                    'current_placement_id' => $data['current_placement_id'] ?? null,
                    'metadata' => $this->metadataArray($data['metadata'] ?? []),
                    'received_at' => now()->toIso8601String(),
                ],
            ],
        ]);

        return $displayDevice->fresh() ?? $displayDevice;
    }

    private function withinCaps(?AdRequest $adRequest): bool
    {
        if (! $adRequest) {
            return false;
        }

        if ($adRequest->impression_cap === null) {
            return true;
        }

        $impressions = $adRequest->events()
            ->where('event_type', 'impression')
            ->count();

        return $impressions < $adRequest->impression_cap;
    }

    private function canReviewAds(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::RegionalAdmin, UserRole::Operator], true);
    }

    /** @return array<string, mixed> */
    private function serializeDisplayPlacement(AdPlacement $placement): array
    {
        $adRequest = $placement->adRequest;
        $creative = $adRequest?->creatives->first();

        return [
            'placementId' => $placement->id,
            'adRequestId' => $adRequest?->id,
            'code' => $adRequest?->code,
            'title' => $adRequest?->title,
            'bodyCopy' => $adRequest?->body_copy,
            'ctaText' => $adRequest?->cta_text,
            'targetUrl' => $adRequest?->target_url,
            'creativeType' => $creative?->creative_type,
            'assetUrl' => $creative?->asset_url,
            'placementType' => $placement->placement_type,
            'priority' => $placement->priority,
            'startsAt' => $placement->starts_at?->toIso8601String(),
            'endsAt' => $placement->ends_at?->toIso8601String(),
            'partnerName' => $adRequest?->partnerAccount?->name,
        ];
    }

    /** @return Collection<int, array{id: string, code: string, name: string}> */
    private function hubOptionsForPartner(PartnerAccount $partner): Collection
    {
        $hubIds = $partner->locations()
            ->where('status', RecordStatus::Active)
            ->whereNotNull('hub_id')
            ->pluck('hub_id')
            ->unique()
            ->values();

        return Hub::query()
            ->whereIn('id', $hubIds)
            ->orderBy('created_at')
            ->get(['id', 'code', 'name'])
            ->toBase()
            ->map(fn (Hub $hub): array => [
                'id' => $hub->id,
                'code' => $hub->code,
                'name' => $hub->name,
            ])
            ->values();
    }

    private function hubForPartner(PartnerAccount $partner, ?string $hubId): ?Hub
    {
        if (! $hubId) {
            return null;
        }

        $hub = $partner->locations()
            ->where('status', RecordStatus::Active)
            ->where('hub_id', $hubId)
            ->first()
            ?->hub;

        if (! $hub) {
            throw ValidationException::withMessages([
                'hub_id' => 'هاب انتخاب‌شده برای این شریک معتبر نیست.',
            ]);
        }

        return $hub;
    }

    private function uniqueAdCode(string $partnerCode): string
    {
        do {
            $code = Str::slug($partnerCode).'-ad-'.Str::lower(Str::random(6));
        } while (AdRequest::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * @return array<string, mixed>
     */
    private function metadataArray(mixed $metadata): array
    {
        return is_array($metadata) ? $metadata : [];
    }

    /** @return array<string, mixed> */
    private function serializeAdRequest(AdRequest $adRequest): array
    {
        $placement = $adRequest->placements->first();
        $creative = $adRequest->creatives->first();

        return [
            'id' => $adRequest->id,
            'code' => $adRequest->code,
            'title' => $adRequest->title,
            'bodyCopy' => $adRequest->body_copy,
            'ctaText' => $adRequest->cta_text,
            'targetUrl' => $adRequest->target_url,
            'advertiserType' => $adRequest->advertiser_type,
            'adType' => $adRequest->ad_type,
            'status' => $adRequest->status,
            'startsAt' => $adRequest->starts_at?->toIso8601String(),
            'endsAt' => $adRequest->ends_at?->toIso8601String(),
            'budgetAmount' => $adRequest->budget_amount,
            'impressionCap' => $adRequest->impression_cap,
            'clickCap' => $adRequest->click_cap,
            'impressionsCount' => (int) $adRequest->getAttribute('impressions_count'),
            'venueName' => $adRequest->venue?->name,
            'partnerName' => $adRequest->partnerAccount?->name,
            'partnerType' => $adRequest->partnerAccount?->partner_type,
            'hubName' => $adRequest->hub?->name,
            'touchpointLabel' => $adRequest->touchpoint?->label,
            'placementType' => $placement?->placement_type,
            'placementTypes' => $adRequest->placements->pluck('placement_type')->values()->all(),
            'placementStatus' => $placement?->status,
            'creativeType' => $creative?->creative_type,
            'assetUrl' => $creative?->asset_url,
        ];
    }

    private function isOnlinePlacement(string $placementType): bool
    {
        return in_array($placementType, self::ONLINE_PLACEMENT_TYPES, true);
    }
}
