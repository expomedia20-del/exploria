<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\AdPlacement;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\PartnerAccount;
use App\Models\RewardDefinition;
use App\Models\RewardRedemption;
use App\Models\Treasure;
use App\Models\User;

class VenueManagerDashboardService
{
    public function __construct(private readonly VenueManagerAccessService $access) {}

    /** @return array<string, mixed> */
    public function overview(User $user): array
    {
        $venues = $this->access->managedVenues($user);
        $venueIds = $venues->pluck('id')->values();

        if ($venueIds->isEmpty()) {
            return [
                'stats' => [
                    'venues' => 0,
                    'activeCampaigns' => 0,
                    'hubs' => 0,
                    'partners' => 0,
                    'pendingAds' => 0,
                    'displayDevices' => 0,
                    'rewards' => 0,
                    'treasures' => 0,
                    'redemptions' => 0,
                ],
                'venues' => [],
                'campaigns' => [],
                'hubs' => [],
                'partners' => [],
                'adRequests' => [],
                'displayDevices' => [],
                'displayScheduleItems' => [],
                'rewards' => [],
                'treasures' => [],
            ];
        }

        $partnerIds = PartnerAccount::query()
            ->whereIn('venue_id', $venueIds)
            ->pluck('id');

        $campaigns = Campaign::query()
            ->withCount(['missionInstances', 'rewardDefinitions', 'treasures', 'campaignParticipants'])
            ->whereIn('venue_id', $venueIds)
            ->latest('created_at')
            ->get()
            ->map(fn (Campaign $campaign): array => [
                'id' => $campaign->id,
                'code' => $campaign->code,
                'name' => $campaign->name,
                'campaignType' => $campaign->campaign_type,
                'status' => $campaign->status->value,
                'startsAt' => $campaign->start_at?->toIso8601String(),
                'endsAt' => $campaign->end_at?->toIso8601String(),
                'missionCount' => $campaign->mission_instances_count,
                'rewardCount' => $campaign->reward_definitions_count,
                'treasureCount' => $campaign->treasures_count,
                'participantCount' => $campaign->campaign_participants_count,
            ]);

        $hubs = Hub::query()
            ->with(['zone:id,venue_id,name', 'zone.venue:id,code,name'])
            ->withCount(['partnerLocations', 'displayDevices', 'missionInstances'])
            ->whereHas('zone', fn ($query) => $query->whereIn('venue_id', $venueIds))
            ->orderBy('created_at')
            ->get()
            ->map(fn (Hub $hub): array => [
                'id' => $hub->id,
                'code' => $hub->code,
                'name' => $hub->name,
                'hubType' => $hub->hub_type,
                'status' => $hub->status->value,
                'venueName' => $hub->zone?->venue?->name,
                'zoneName' => $hub->zone?->name,
                'partnerCount' => $hub->partner_locations_count,
                'displayCount' => $hub->display_devices_count,
                'missionCount' => $hub->mission_instances_count,
            ]);

        $partnerAccounts = PartnerAccount::query()
            ->with(['locations.hub:id,code,name', 'venue:id,code,name'])
            ->withCount(['rewardDefinitions', 'rewardRedemptions', 'adRequests'])
            ->whereIn('venue_id', $venueIds)
            ->orderBy('created_at')
            ->get();

        $partners = $partnerAccounts
            ->groupBy(fn (PartnerAccount $partner): string => $partner->locations->first()->hub->name ?? 'بدون هاب مشخص')
            ->map(fn ($group, string $hubName): array => [
                'id' => 'hub-partner-summary-'.md5($hubName),
                'code' => 'summary',
                'name' => $hubName,
                'partnerType' => $group->count().' واحد تجاری/حامی',
                'status' => RecordStatus::Active->value,
                'venueName' => $group->first()?->venue?->name,
                'hubName' => $hubName,
                'rewardCount' => $group->sum('reward_definitions_count'),
                'redemptionCount' => $group->sum('reward_redemptions_count'),
                'adCount' => $group->sum('ad_requests_count'),
            ])
            ->values();

        $adRequests = AdRequest::query()
            ->with(['partnerAccount:id,code,name', 'hub:id,code,name', 'placements.displayDevice:id,code,name,device_type'])
            ->whereIn('venue_id', $venueIds)
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->map(fn (AdRequest $adRequest): array => [
                'id' => $adRequest->id,
                'code' => $adRequest->code,
                'title' => $adRequest->title,
                'advertiserType' => $adRequest->advertiser_type,
                'adType' => $adRequest->ad_type,
                'status' => $adRequest->status,
                'partnerName' => $adRequest->partnerAccount?->name,
                'hubName' => $adRequest->hub?->name,
                'placementStatus' => $adRequest->placements->first()?->status,
                'displayDeviceName' => $adRequest->placements->first()?->displayDevice?->name,
                'startsAt' => $adRequest->starts_at?->toIso8601String(),
                'endsAt' => $adRequest->ends_at?->toIso8601String(),
            ]);

        $displayDevices = DisplayDevice::query()
            ->with(['hub:id,code,name', 'venue:id,code,name'])
            ->whereIn('venue_id', $venueIds)
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
                'playbackStatus' => $device->playback_status,
                'lastHeartbeatAt' => $device->last_heartbeat_at?->toIso8601String(),
            ]);

        $displayScheduleItems = AdPlacement::query()
            ->with(['adRequest:id,code,title,partner_account_id', 'adRequest.partnerAccount:id,code,name', 'displayDevice:id,venue_id,code,name,device_type'])
            ->whereIn('display_device_id', $displayDevices->pluck('id'))
            ->where('status', 'scheduled')
            ->orderBy('priority')
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(fn (AdPlacement $placement): array => [
                'id' => $placement->id,
                'adTitle' => $placement->adRequest?->title,
                'adCode' => $placement->adRequest?->code,
                'partnerName' => $placement->adRequest?->partnerAccount?->name,
                'displayDeviceName' => $placement->displayDevice?->name,
                'placementType' => $placement->placement_type,
                'status' => $placement->status,
                'priority' => $placement->priority,
                'startsAt' => $placement->starts_at?->toIso8601String(),
                'endsAt' => $placement->ends_at?->toIso8601String(),
            ]);

        $rewards = RewardDefinition::query()
            ->with(['campaign:id,code,name', 'partnerAccount:id,code,name'])
            ->whereIn('venue_id', $venueIds)
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->map(fn (RewardDefinition $reward): array => [
                'id' => $reward->id,
                'code' => $reward->code,
                'name' => $reward->name,
                'rewardType' => $reward->reward_type,
                'status' => $reward->status->value,
                'approvalStatus' => $reward->metadata['approval_status'] ?? $reward->status->value,
                'stockQuantity' => $reward->stock_quantity,
                'pointCost' => $reward->point_cost,
                'campaignName' => $reward->campaign?->name,
                'partnerName' => $reward->partnerAccount?->name,
            ]);

        $treasures = Treasure::query()
            ->with(['campaign:id,code,name', 'missionInstance:id,code,title_override'])
            ->whereIn('venue_id', $venueIds)
            ->latest('created_at')
            ->limit(12)
            ->get()
            ->map(fn (Treasure $treasure): array => [
                'id' => $treasure->id,
                'code' => $treasure->code,
                'name' => $treasure->name,
                'treasureType' => $treasure->treasure_type,
                'status' => $treasure->status->value,
                'campaignName' => $treasure->campaign?->name,
                'missionCode' => $treasure->missionInstance?->code,
            ]);

        $redemptionsCount = RewardRedemption::query()
            ->whereIn('partner_account_id', $partnerIds)
            ->count();

        return [
            'stats' => [
                'venues' => $venues->count(),
                'activeCampaigns' => $campaigns->where('status', RecordStatus::Active->value)->count(),
                'hubs' => $hubs->count(),
                'partners' => $partnerAccounts->count(),
                'pendingAds' => $adRequests->where('status', 'pending_review')->count(),
                'displayDevices' => $displayDevices->count(),
                'rewards' => $rewards->count(),
                'treasures' => $treasures->count(),
                'redemptions' => $redemptionsCount,
            ],
            'venues' => $venues->map(fn ($venue): array => [
                'id' => $venue->id,
                'code' => $venue->code,
                'name' => $venue->name,
                'city' => $venue->city,
                'status' => $venue->status->value,
                'profileStatus' => $venue->profile_status->value,
            ])->values(),
            'campaigns' => $campaigns,
            'hubs' => $hubs,
            'partners' => $partners,
            'adRequests' => $adRequests,
            'displayDevices' => $displayDevices,
            'displayScheduleItems' => $displayScheduleItems,
            'rewards' => $rewards,
            'treasures' => $treasures,
        ];
    }
}
