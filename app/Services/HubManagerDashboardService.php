<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Models\PartnerLocation;
use App\Models\RewardDefinition;
use App\Models\User;

class HubManagerDashboardService
{
    public function __construct(private readonly HubManagerAccessService $access) {}

    /** @return array<string, mixed> */
    public function overview(User $user): array
    {
        $hubs = $this->access->managedHubs($user);
        $hubIds = $this->access->managedHubIds($user);
        $partnerIds = $this->access->managedPartnerIds($user);

        $partners = PartnerLocation::query()
            ->with(['partnerAccount:id,code,name,partner_type,status', 'hub:id,code,name', 'venue:id,code,name'])
            ->whereIn('hub_id', $hubIds)
            ->where('status', RecordStatus::Active)
            ->orderBy('created_at')
            ->get()
            ->map(fn (PartnerLocation $location): array => [
                'id' => $location->partnerAccount?->id,
                'code' => $location->partnerAccount?->code,
                'name' => $location->partnerAccount?->name,
                'partnerType' => $location->partnerAccount?->partner_type,
                'status' => $location->partnerAccount?->status->value,
                'hubName' => $location->hub?->name,
                'venueName' => $location->venue?->name,
                'locationRole' => $location->location_role,
            ]);

        $adRequests = AdRequest::query()
            ->with(['partnerAccount:id,code,name,partner_type', 'hub:id,code,name', 'venue:id,code,name', 'creatives:id,ad_request_id,creative_type,status', 'placements:id,ad_request_id,placement_type,status'])
            ->where(function ($query) use ($hubIds, $partnerIds): void {
                $query->whereIn('hub_id', $hubIds)
                    ->orWhereIn('partner_account_id', $partnerIds);
            })
            ->latest('created_at')
            ->get()
            ->map(fn (AdRequest $adRequest): array => [
                'id' => $adRequest->id,
                'code' => $adRequest->code,
                'title' => $adRequest->title,
                'status' => $adRequest->status,
                'partnerName' => $adRequest->partnerAccount?->name,
                'hubName' => $adRequest->hub?->name,
                'venueName' => $adRequest->venue?->name,
                'creativeType' => $adRequest->creatives->first()?->creative_type,
                'placementType' => $adRequest->placements->first()?->placement_type,
                'placementStatus' => $adRequest->placements->first()?->status,
            ]);

        $rewards = RewardDefinition::query()
            ->with(['partnerAccount:id,code,name,partner_type', 'campaign:id,code,name'])
            ->whereIn('partner_account_id', $partnerIds)
            ->latest('created_at')
            ->get()
            ->map(fn (RewardDefinition $reward): array => [
                'id' => $reward->id,
                'code' => $reward->code,
                'name' => $reward->name,
                'rewardType' => $reward->reward_type,
                'status' => $reward->status->value,
                'approvalStatus' => $reward->metadata['approval_status'] ?? $reward->status->value,
                'partnerName' => $reward->partnerAccount?->name,
                'campaignName' => $reward->campaign?->name,
            ]);

        $displayDevices = DisplayDevice::query()
            ->with(['hub:id,code,name', 'venue:id,code,name'])
            ->whereIn('hub_id', $hubIds)
            ->orderBy('created_at')
            ->get()
            ->map(fn (DisplayDevice $device): array => [
                'id' => $device->id,
                'code' => $device->code,
                'name' => $device->name,
                'deviceType' => $device->device_type,
                'status' => $device->status->value,
                'hubName' => $device->hub?->name,
                'venueName' => $device->venue?->name,
            ]);

        return [
            'stats' => [
                'hubs' => $hubs->count(),
                'partners' => $partners->count(),
                'pendingAds' => $adRequests->where('status', 'pending_review')->count(),
                'pendingRewards' => $rewards->where('approvalStatus', 'pending_review')->count(),
                'displayDevices' => $displayDevices->count(),
            ],
            'hubs' => $hubs->map(fn ($hub): array => [
                'id' => $hub->id,
                'code' => $hub->code,
                'name' => $hub->name,
                'hubType' => $hub->hub_type,
                'venueName' => $hub->zone?->venue?->name,
            ])->values(),
            'partners' => $partners,
            'adRequests' => $adRequests,
            'rewards' => $rewards,
            'displayDevices' => $displayDevices,
        ];
    }
}
