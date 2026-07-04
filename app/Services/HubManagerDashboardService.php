<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\AdPlacement;
use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Models\PartnerLocation;
use App\Models\RewardDefinition;
use App\Models\User;

class HubManagerDashboardService
{
    public function __construct(private readonly HubManagerAccessService $access) {}

    /** @return array<string, mixed> */
    public function overview(User $user, bool $ravaqOnly = false): array
    {
        $hubs = $this->access->managedHubs($user, $ravaqOnly);
        $hubIds = $this->access->managedHubIds($user, $ravaqOnly);
        $partnerIds = $this->access->managedPartnerIds($user, $ravaqOnly);

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
            ->with(['partnerAccount:id,code,name,partner_type', 'hub:id,code,name', 'venue:id,code,name', 'creatives:id,ad_request_id,creative_type,status', 'placements.displayDevice:id,code,name,device_type', 'approvals:id,ad_request_id,action,notes,created_at'])
            ->where(function ($query) use ($hubIds, $partnerIds): void {
                $query->whereIn('hub_id', $hubIds)
                    ->orWhereIn('partner_account_id', $partnerIds);
            })
            ->latest('created_at')
            ->get()
            ->map(function (AdRequest $adRequest): array {
                $latestApproval = $adRequest->approvals->sortByDesc('created_at')->first();

                return [
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
                    'displayDeviceId' => $adRequest->placements->first()?->display_device_id,
                    'displayDeviceName' => $adRequest->placements->first()?->displayDevice?->name,
                    'displayDeviceCode' => $adRequest->placements->first()?->displayDevice?->code,
                    'startsAt' => $adRequest->placements->first()?->starts_at?->toIso8601String(),
                    'endsAt' => $adRequest->placements->first()?->ends_at?->toIso8601String(),
                    'priority' => $adRequest->placements->first()?->priority,
                    'reviewNotes' => $latestApproval?->notes,
                    'reviewedAt' => $latestApproval?->created_at?->toIso8601String(),
                ];
            });

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
                'reviewNotes' => $reward->metadata['review_notes'] ?? null,
                'reviewedAt' => $reward->metadata['approved_at'] ?? $reward->metadata['rejected_at'] ?? null,
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

        $displayScheduleItems = AdPlacement::query()
            ->with(['adRequest:id,code,title,status,partner_account_id', 'adRequest.partnerAccount:id,code,name', 'displayDevice:id,code,name,device_type,hub_id'])
            ->whereIn('display_device_id', $displayDevices->pluck('id'))
            ->where('status', 'scheduled')
            ->orderBy('priority')
            ->latest('updated_at')
            ->get()
            ->map(fn (AdPlacement $placement): array => [
                'id' => $placement->id,
                'adRequestId' => $placement->ad_request_id,
                'adTitle' => $placement->adRequest?->title,
                'adCode' => $placement->adRequest?->code,
                'partnerName' => $placement->adRequest?->partnerAccount?->name,
                'displayDeviceId' => $placement->display_device_id,
                'displayDeviceName' => $placement->displayDevice?->name,
                'displayDeviceCode' => $placement->displayDevice?->code,
                'placementType' => $placement->placement_type,
                'status' => $placement->status,
                'priority' => $placement->priority,
                'startsAt' => $placement->starts_at?->toIso8601String(),
                'endsAt' => $placement->ends_at?->toIso8601String(),
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
            'displayScheduleItems' => $displayScheduleItems,
        ];
    }
}
