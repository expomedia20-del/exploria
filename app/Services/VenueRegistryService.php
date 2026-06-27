<?php

namespace App\Services;

use App\Models\Hub;
use App\Models\HubManagementAssignment;
use App\Models\User;
use App\Models\Venue;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VenueRegistryService
{
    public function __construct(private readonly UserAccessScopeService $accessScopes) {}

    /** @return Collection<int, array<string, mixed>> */
    public function list(?User $user = null): Collection
    {
        $hubIds = $user ? $this->accessScopes->hubIds($user) : collect();
        $assignedVenueIds = $user ? $this->accessScopes->assignedVenueIds($user) : collect();
        $venueIds = $user ? $this->accessScopes->venueIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        return Venue::query()
            ->when(! $isGlobal, fn (Builder $query) => $query->whereIn('id', $venueIds))
            ->with([
                'zones.hubs' => fn ($query) => $query->when(! $isGlobal, fn ($query) => $query->where(function ($query) use ($hubIds, $assignedVenueIds): void {
                    $query->whereIn('id', $hubIds)
                        ->orWhereHas('zone', fn ($query) => $query->whereIn('venue_id', $assignedVenueIds));
                })),
                'zones.hubs.touchpoints:id,hub_id,code,label,type,status',
                'zones.hubs.partnerLocations.partnerAccount:id,code,name,partner_type,status',
                'zones.hubs.managementAssignments.user:id,name,email,role',
            ])
            ->withCount(['zones', 'campaigns', 'qrCodes', 'partnerAccounts'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (Venue $venue): array => $this->serializeVenue($venue));
    }

    /** @return array<string, mixed> */
    private function serializeVenue(Venue $venue): array
    {
        $zones = $venue->zones->map(fn (Zone $zone): array => [
            'id' => $zone->id,
            'code' => $zone->code,
            'name' => $zone->name,
            'status' => $zone->status->value,
            'hubs' => $zone->hubs->map(fn (Hub $hub): array => [
                'id' => $hub->id,
                'code' => $hub->code,
                'name' => $hub->name,
                'hubType' => $hub->hub_type,
                'status' => $hub->status->value,
                'touchpointsCount' => $hub->touchpoints->count(),
                'partnersCount' => $hub->partnerLocations->count(),
                'managerNames' => $hub->managementAssignments
                    ->map(fn (HubManagementAssignment $assignment): ?string => $assignment->user?->name)
                    ->filter()
                    ->values(),
            ])->values(),
        ])->values();

        $hubsCount = $zones->sum(fn (array $zone): int => count($zone['hubs']));
        $touchpointsCount = $zones->sum(
            fn (array $zone): int => collect($zone['hubs'])->sum('touchpointsCount'),
        );

        return [
            'id' => $venue->id,
            'code' => $venue->code,
            'name' => $venue->name,
            'city' => $venue->city,
            'status' => $venue->status->value,
            'profileStatus' => $venue->profile_status->value,
            'zonesCount' => (int) $venue->getAttribute('zones_count'),
            'hubsCount' => $hubsCount,
            'touchpointsCount' => $touchpointsCount,
            'campaignsCount' => (int) $venue->getAttribute('campaigns_count'),
            'qrCodesCount' => (int) $venue->getAttribute('qr_codes_count'),
            'partnerAccountsCount' => (int) $venue->getAttribute('partner_accounts_count'),
            'zones' => $zones,
        ];
    }
}