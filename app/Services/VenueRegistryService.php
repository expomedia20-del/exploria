<?php

namespace App\Services;

use App\Models\Hub;
use App\Models\HubManagementAssignment;
use App\Models\User;
use App\Models\Venue;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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

    /** @param array<string, mixed> $data */
    public function updateProfile(Venue $venue, array $data): Venue
    {
        $metadata = is_array($venue->metadata) ? $venue->metadata : [];
        $profile = [
            'venue_type' => $data['venue_type'],
            'primary_audience' => $data['primary_audience'] ?? null,
            'official_website_url' => $data['official_website_url'] ?? null,
            'manual_research_notes' => $data['manual_research_notes'] ?? null,
            'facilities' => $this->facilityItems($data),
            'constraints' => $this->linesToItems($data['constraints_text'] ?? ''),
            'updated_at' => now()->toIso8601String(),
        ];

        $venue->update([
            'metadata' => [
                ...$metadata,
                'location_profile' => $profile,
            ],
        ]);

        return $venue->refresh();
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
            'locationProfile' => $this->locationProfile($venue),
            'zones' => $zones,
        ];
    }

    /** @return array<string, mixed> */
    private function locationProfile(Venue $venue): array
    {
        $profile = Arr::get(is_array($venue->metadata) ? $venue->metadata : [], 'location_profile', []);
        $facilities = $this->normalizeFacilities(Arr::get($profile, 'facilities', []));
        $constraints = collect(Arr::get($profile, 'constraints', []))->filter()->values();

        return [
            'venueType' => Arr::get($profile, 'venue_type'),
            'primaryAudience' => Arr::get($profile, 'primary_audience'),
            'officialWebsiteUrl' => Arr::get($profile, 'official_website_url'),
            'manualResearchNotes' => Arr::get($profile, 'manual_research_notes'),
            'facilities' => $facilities,
            'constraints' => $constraints,
            'updatedAt' => Arr::get($profile, 'updated_at'),
            'readinessScore' => $this->profileReadinessScore($profile, $facilities->count()),
        ];
    }

    /** @param array<string, mixed> $profile */
    private function profileReadinessScore(array $profile, int $facilitiesCount): int
    {
        $score = 0;
        $score += filled($profile['venue_type'] ?? null) ? 25 : 0;
        $score += filled($profile['primary_audience'] ?? null) ? 20 : 0;
        $score += filled($profile['official_website_url'] ?? null) ? 15 : 0;
        $score += filled($profile['manual_research_notes'] ?? null) ? 15 : 0;
        $score += min(25, $facilitiesCount * 5);

        return min(100, $score);
    }

    /** @param array<string, mixed> $data @return array<int, array<string, mixed>> */
    private function facilityItems(array $data): array
    {
        $structured = $this->normalizeFacilities($data['facilities'] ?? []);
        $textItems = collect($this->linesToItems($data['facilities_text'] ?? ''))
            ->map(fn (string $name): array => [
                'name' => $name,
                'function' => null,
                'campaignUses' => [],
                'priority' => 'secondary',
                'notes' => null,
            ]);

        return $structured
            ->merge($textItems)
            ->unique(fn (array $item): string => mb_strtolower($item['name']))
            ->values()
            ->all();
    }

    /** @param mixed $items @return Collection<int, array<string, mixed>> */
    private function normalizeFacilities(mixed $items): Collection
    {
        return collect(is_array($items) ? $items : [])
            ->map(function (mixed $item): array {
                if (is_string($item)) {
                    return [
                        'name' => trim($item),
                        'function' => null,
                        'campaignUses' => [],
                        'priority' => 'secondary',
                        'notes' => null,
                    ];
                }

                $item = is_array($item) ? $item : [];

                return [
                    'name' => trim((string) ($item['name'] ?? '')),
                    'function' => blank($item['function'] ?? null) ? null : trim((string) $item['function']),
                    'campaignUses' => collect($item['campaignUses'] ?? $item['campaign_uses'] ?? [])
                        ->filter()
                        ->map(fn (mixed $value): string => (string) $value)
                        ->unique()
                        ->values()
                        ->all(),
                    'priority' => in_array($item['priority'] ?? null, ['primary', 'secondary', 'low'], true) ? $item['priority'] : 'secondary',
                    'notes' => blank($item['notes'] ?? null) ? null : trim((string) $item['notes']),
                ];
            })
            ->filter(fn (array $item): bool => filled($item['name']))
            ->values();
    }

    /** @return array<int, string> */
    private function linesToItems(?string $value): array
    {
        return collect(preg_split('/\R/u', (string) $value) ?: [])
            ->map(fn (string $line): string => trim($line, " \t\n\r\0\x0B-•*"))
            ->filter()
            ->values()
            ->all();
    }
}
