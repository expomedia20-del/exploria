<?php

namespace App\Services;

use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\DisplayDevice;
use App\Models\MissionInstance;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\Treasure;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CampaignOperationsBlueprintService
{
    public function __construct(private readonly UserAccessScopeService $accessScopes) {}

    /** @return array<string, mixed> */
    public function overview(?User $user = null, ?string $campaignId = null): array
    {
        $scope = $this->scope($user);

        $campaigns = Campaign::query()
            ->when($campaignId, fn (Builder $query) => $query->where('id', $campaignId))
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->whereIn('venue_id', $scope['venueIds']))
            ->with('venue:id,code,name')
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (Campaign $campaign): array => $this->serializeCampaign($campaign, $scope));

        return [
            'stats' => [
                'campaigns' => $campaigns->count(),
                'participants' => $campaigns->sum('stats.participants'),
                'internalSponsors' => $campaigns->sum('stats.internalSponsors'),
                'externalSponsors' => $campaigns->sum('stats.externalSponsors'),
                'missions' => $campaigns->sum('stats.missions'),
                'incentives' => $campaigns->sum('stats.rewards') + $campaigns->sum('stats.treasures'),
                'entryPoints' => $campaigns->sum('stats.qrCodes'),
                'adRequests' => $campaigns->sum('stats.adRequests'),
                'displayDevices' => $campaigns->sum('stats.displayDevices'),
            ],
            'campaigns' => $campaigns,
        ];
    }

    /** @param array<string, mixed> $data */
    public function markRouteReviewed(?User $user, array $data): Campaign
    {
        return DB::transaction(function () use ($user, $data): Campaign {
            $campaign = Campaign::query()->findOrFail($data['campaign_id']);
            $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];

            $campaign->update([
                'metadata' => [
                    ...$metadata,
                    'route_reviewed_at' => now()->toIso8601String(),
                    'route_reviewed_by_user_id' => $user?->id,
                    'route_review_notes' => $data['route_notes'] ?? null,
                ],
            ]);

            return $campaign;
        });
    }

    /** @return array{isGlobal: bool, venueIds: Collection<int, string>, assignedVenueIds: Collection<int, string>, hubIds: Collection<int, string>, partnerIds: Collection<int, string>} */
    private function scope(?User $user): array
    {
        return [
            'isGlobal' => $user === null || $this->accessScopes->hasGlobalAccess($user),
            'venueIds' => $user ? $this->accessScopes->venueIds($user) : collect(),
            'assignedVenueIds' => $user ? $this->accessScopes->assignedVenueIds($user) : collect(),
            'hubIds' => $user ? $this->accessScopes->hubIds($user) : collect(),
            'partnerIds' => $user ? $this->accessScopes->partnerIds($user) : collect(),
        ];
    }

    /** @param array<string, mixed> $scope @return array<string, mixed> */
    private function serializeCampaign(Campaign $campaign, array $scope): array
    {
        $participants = $this->participants($campaign, $scope);
        $missions = $this->missions($campaign, $scope);
        $rewards = $this->rewards($campaign, $scope);
        $treasures = $this->treasures($campaign, $scope);
        $qrCodes = $this->qrCodes($campaign, $scope);
        $adRequests = $this->adRequests($campaign, $scope);
        $displayDevices = $this->displayDevices($campaign, $scope);

        $internalSponsors = $participants->filter(fn (array $participant): bool => $participant['participantType'] === 'sponsor' && $participant['hub'] !== null);
        $externalSponsors = $participants->filter(fn (array $participant): bool => $participant['participantType'] === 'sponsor' && $participant['hub'] === null);

        return [
            'id' => $campaign->id,
            'code' => $campaign->code,
            'name' => $campaign->name,
            'campaignType' => $campaign->campaign_type,
            'blueprintCode' => $campaign->metadata['blueprint_code'] ?? null,
            'routeReviewedAt' => $campaign->metadata['route_reviewed_at'] ?? null,
            'routeReviewNotes' => $campaign->metadata['route_review_notes'] ?? null,
            'status' => $campaign->status->value,
            'startAt' => $campaign->start_at?->toIso8601String(),
            'endAt' => $campaign->end_at?->toIso8601String(),
            'venue' => $campaign->venue ? ['id' => $campaign->venue->id, 'code' => $campaign->venue->code, 'name' => $campaign->venue->name] : null,
            'stats' => [
                'participants' => $participants->count(),
                'internalSponsors' => $internalSponsors->count(),
                'externalSponsors' => $externalSponsors->count(),
                'missions' => $missions->count(),
                'rewards' => $rewards->count(),
                'treasures' => $treasures->count(),
                'qrCodes' => $qrCodes->count(),
                'adRequests' => $adRequests->count(),
                'displayDevices' => $displayDevices->count(),
            ],
            'participantsByHub' => $this->participantsByHub($participants),
            'sponsors' => [
                'internal' => $internalSponsors->values(),
                'external' => $externalSponsors->values(),
            ],
            'journey' => [
                'entry' => ['title' => 'شروع بازدید', 'items' => $qrCodes],
                'missions' => ['title' => 'مأموریت ها', 'items' => $missions],
                'incentives' => ['title' => 'مشوق ها و گنج ها', 'items' => $rewards->merge($treasures)->values()],
                'commercial' => ['title' => 'فعال سازی تجاری', 'items' => $participants->values()],
                'media' => ['title' => 'تبلیغات و نمایشگرها', 'items' => $adRequests->merge($displayDevices)->values()],
            ],
        ];
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function participants(Campaign $campaign, array $scope): Collection
    {
        return CampaignParticipant::query()
            ->where('campaign_id', $campaign->id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->where(function (Builder $query) use ($scope): void {
                $query->whereIn('venue_id', $scope['assignedVenueIds'])
                    ->orWhereIn('hub_id', $scope['hubIds'])
                    ->orWhereIn('partner_account_id', $scope['partnerIds']);
            }))
            ->with(['hub:id,code,name,hub_type', 'partnerAccount:id,code,name,partner_type,status'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (CampaignParticipant $participant): array => [
                'id' => $participant->id,
                'participantType' => $participant->participant_type,
                'participationRole' => $participant->participation_role,
                'status' => $participant->status->value,
                'onboardingStatus' => $participant->onboarding_status,
                'hub' => $participant->hub ? ['id' => $participant->hub->id, 'code' => $participant->hub->code, 'name' => $participant->hub->name, 'hubType' => $participant->hub->hub_type] : null,
                'partner' => $participant->partnerAccount ? ['id' => $participant->partnerAccount->id, 'code' => $participant->partnerAccount->code, 'name' => $participant->partnerAccount->name, 'partnerType' => $participant->partnerAccount->partner_type] : null,
                'connections' => [
                    'rewards' => (int) ($participant->metadata['connections']['rewards'] ?? 0),
                    'ads' => (int) ($participant->metadata['connections']['ads'] ?? 0),
                    'qrCodes' => (int) ($participant->metadata['connections']['qr_codes'] ?? 0),
                    'missions' => (int) ($participant->metadata['connections']['missions'] ?? 0),
                ],
            ]);
    }

    /** @param Collection<int, array<string, mixed>> $participants */
    private function participantsByHub(Collection $participants): Collection
    {
        return $participants
            ->groupBy(fn (array $participant): string => (string) data_get($participant, 'hub.id', 'external'))
            ->map(fn (Collection $items): array => [
                'hub' => $items->first()['hub'] ?? null,
                'participantsCount' => $items->count(),
                'sponsorsCount' => $items->where('participantType', 'sponsor')->count(),
                'roles' => $items->pluck('participationRole')->unique()->values(),
                'participants' => $items->values(),
            ])
            ->values();
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function missions(Campaign $campaign, array $scope): Collection
    {
        return MissionInstance::query()
            ->where('campaign_id', $campaign->id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->where(function (Builder $query) use ($scope): void {
                $query->whereIn('venue_id', $scope['assignedVenueIds'])->orWhereIn('hub_id', $scope['hubIds']);
            }))
            ->with(['missionTemplate:id,code,title,mission_type,trigger_type,point_value', 'hub:id,code,name'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (MissionInstance $mission): array => [
                'id' => $mission->id,
                'type' => 'mission',
                'code' => $mission->code,
                'title' => $mission->title_override ?? $mission->missionTemplate?->title,
                'missionType' => $mission->missionTemplate?->mission_type,
                'triggerType' => $mission->missionTemplate?->trigger_type,
                'points' => $mission->missionTemplate?->point_value ?? 0,
                'status' => $mission->status->value,
                'hub' => $mission->hub ? ['id' => $mission->hub->id, 'code' => $mission->hub->code, 'name' => $mission->hub->name] : null,
            ]);
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function rewards(Campaign $campaign, array $scope): Collection
    {
        return RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->where(function (Builder $query) use ($scope): void {
                $query->whereIn('venue_id', $scope['assignedVenueIds'])->orWhereIn('partner_account_id', $scope['partnerIds']);
            }))
            ->with('partnerAccount:id,code,name,partner_type')
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (RewardDefinition $reward): array => [
                'id' => $reward->id,
                'type' => 'reward',
                'code' => $reward->code,
                'name' => $reward->name,
                'rewardType' => $reward->reward_type,
                'status' => $reward->status->value,
                'pointCost' => $reward->point_cost,
                'partner' => $reward->partnerAccount ? ['id' => $reward->partnerAccount->id, 'code' => $reward->partnerAccount->code, 'name' => $reward->partnerAccount->name, 'partnerType' => $reward->partnerAccount->partner_type] : null,
            ]);
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function treasures(Campaign $campaign, array $scope): Collection
    {
        return Treasure::query()
            ->where('campaign_id', $campaign->id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->whereIn('venue_id', $scope['assignedVenueIds']))
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (Treasure $treasure): array => [
                'id' => $treasure->id,
                'type' => 'treasure',
                'code' => $treasure->code,
                'name' => $treasure->name,
                'treasureType' => $treasure->treasure_type,
                'status' => $treasure->status->value,
            ]);
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function qrCodes(Campaign $campaign, array $scope): Collection
    {
        return QrCode::query()
            ->where('campaign_id', $campaign->id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->whereIn('venue_id', $scope['venueIds']))
            ->with('touchpoint:id,code,label')
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (QrCode $qrCode): array => [
                'id' => $qrCode->id,
                'type' => 'qr',
                'code' => $qrCode->code,
                'label' => $qrCode->label,
                'status' => $qrCode->status->value,
                'touchpoint' => $qrCode->touchpoint ? ['id' => $qrCode->touchpoint->id, 'code' => $qrCode->touchpoint->code, 'label' => $qrCode->touchpoint->label] : null,
            ]);
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function adRequests(Campaign $campaign, array $scope): Collection
    {
        return AdRequest::query()
            ->where('venue_id', $campaign->venue_id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->where(function (Builder $query) use ($scope): void {
                $query->whereIn('hub_id', $scope['hubIds'])->orWhereIn('partner_account_id', $scope['partnerIds'])->orWhereIn('venue_id', $scope['assignedVenueIds']);
            }))
            ->with(['hub:id,code,name', 'partnerAccount:id,code,name,partner_type'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (AdRequest $adRequest): array => [
                'id' => $adRequest->id,
                'type' => 'ad',
                'code' => $adRequest->code,
                'title' => $adRequest->title,
                'adType' => $adRequest->ad_type,
                'status' => $adRequest->status,
                'hub' => $adRequest->hub ? ['id' => $adRequest->hub->id, 'code' => $adRequest->hub->code, 'name' => $adRequest->hub->name] : null,
                'partner' => $adRequest->partnerAccount ? ['id' => $adRequest->partnerAccount->id, 'code' => $adRequest->partnerAccount->code, 'name' => $adRequest->partnerAccount->name, 'partnerType' => $adRequest->partnerAccount->partner_type] : null,
            ]);
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function displayDevices(Campaign $campaign, array $scope): Collection
    {
        return DisplayDevice::query()
            ->where('venue_id', $campaign->venue_id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->where(function (Builder $query) use ($scope): void {
                $query->whereIn('hub_id', $scope['hubIds'])->orWhereIn('venue_id', $scope['assignedVenueIds']);
            }))
            ->with('hub:id,code,name')
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (DisplayDevice $device): array => [
                'id' => $device->id,
                'type' => 'display',
                'code' => $device->code,
                'name' => $device->name,
                'deviceType' => $device->device_type,
                'status' => $device->status->value,
                'hub' => $device->hub ? ['id' => $device->hub->id, 'code' => $device->hub->code, 'name' => $device->hub->name] : null,
            ]);
    }
}
