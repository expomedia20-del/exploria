<?php

namespace App\Services;

use App\Enums\RecordStatus;
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
    public function __construct(private readonly PartnerDashboardService $partnerDashboardService) {}

    /** @return array<string, mixed> */
    public function partnerOverview(User $user): array
    {
        $partner = $this->partnerDashboardService->partnerForUser($user);
        $partner->load(['venue:id,code,name', 'locations.hub:id,code,name']);

        $adRequests = $partner->adRequests()
            ->with(['venue:id,code,name', 'hub:id,code,name', 'placements.displayDevice:id,code,name,device_type', 'creatives:id,ad_request_id,creative_type,asset_url,status'])
            ->withCount(['events as impressions_count' => fn ($query) => $query->where('event_type', 'impression')])
            ->latest('created_at')
            ->get()
            ->map(fn (AdRequest $adRequest): array => $this->serializeAdRequest($adRequest));

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

    /** @return array<string, mixed> */
    public function adminOverview(): array
    {
        $adRequests = AdRequest::query()
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
        $hub = $this->hubForPartner($partner, $data['hub_id'] ?? null);

        return DB::transaction(function () use ($data, $hub, $partner, $user): AdRequest {
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
                'metadata' => ['source' => 'partner_ad_submission'],
            ]);

            $adRequest->creatives()->create([
                'creative_type' => $data['creative_type'],
                'asset_url' => $data['asset_url'] ?? null,
                'headline' => $data['title'],
                'body_copy' => $data['body_copy'] ?? null,
                'cta_text' => $data['cta_text'] ?? null,
                'status' => 'pending_review',
                'metadata' => ['source' => 'partner_ad_submission'],
            ]);

            $adRequest->placements()->create([
                'placement_type' => $data['placement_type'],
                'status' => 'pending_review',
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'priority' => $data['priority'] ?? 5,
                'metadata' => ['requested_hub_id' => $hub?->id],
            ]);

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
            $adRequest->placements()->update(['status' => $status === 'approved' ? 'scheduled' : 'rejected']);
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
            'placementStatus' => $placement?->status,
            'creativeType' => $creative?->creative_type,
            'assetUrl' => $creative?->asset_url,
        ];
    }
}
