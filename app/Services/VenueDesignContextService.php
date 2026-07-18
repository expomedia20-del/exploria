<?php

namespace App\Services;

use App\Models\Venue;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class VenueDesignContextService
{
    private const CAMPAIGN_USE_LABELS = [
        'qr' => 'QR',
        'mission' => 'مأموریت',
        'treasure' => 'گنج',
        'reward' => 'پاداش',
        'sponsor' => 'اسپانسر',
        'ad' => 'تبلیغ',
        'display' => 'نمایشگر',
    ];

    /** @return array<string, mixed> */
    public function overview(): array
    {
        $venues = Venue::query()
            ->orderBy('created_at')
            ->get()
            ->map(fn (Venue $venue): array => $this->venueContext($venue))
            ->values();

        return [
            'totals' => [
                'venues' => $venues->count(),
                'facilities' => $venues->sum(fn (array $venue): int => (int) data_get($venue, 'locationProfile.facilitiesCount', 0)),
                'campaignUsableFacilities' => $venues->sum(fn (array $venue): int => (int) data_get($venue, 'locationProfile.campaignUsableFacilitiesCount', 0)),
            ],
            'campaignUseLabels' => self::CAMPAIGN_USE_LABELS,
            'venues' => $venues->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function venueContext(Venue $venue): array
    {
        $profile = Arr::get(is_array($venue->metadata) ? $venue->metadata : [], 'location_profile', []);
        $facilities = $this->normalizeFacilities(Arr::get($profile, 'facilities', []));
        $designAssets = $this->designAssets($facilities);
        $campaignUsableCount = $facilities
            ->filter(fn (array $facility): bool => count($facility['campaignUses']) > 0)
            ->count();

        return [
            'id' => $venue->id,
            'code' => $venue->code,
            'name' => $venue->name,
            'city' => $venue->city,
            'status' => $venue->status->value,
            'profileStatus' => $venue->profile_status->value,
            'locationProfile' => [
                'venueType' => Arr::get($profile, 'venue_type'),
                'primaryAudience' => Arr::get($profile, 'primary_audience'),
                'readinessScore' => $this->profileReadinessScore($profile, $facilities->count()),
                'facilitiesCount' => $facilities->count(),
                'campaignUsableFacilitiesCount' => $campaignUsableCount,
                'constraintsCount' => collect($this->valueList(Arr::get($profile, 'constraints', [])))->filter()->count(),
                'updatedAt' => Arr::get($profile, 'updated_at'),
            ],
            'designAssets' => $designAssets,
            'topFacilities' => $facilities
                ->sortBy(fn (array $facility): int => $this->priorityRank($facility['priority']))
                ->take(8)
                ->values()
                ->all(),
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

    /**
     * @param  Collection<int, covariant array<string, mixed>>  $facilities
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function designAssets(Collection $facilities): array
    {
        return collect(array_keys(self::CAMPAIGN_USE_LABELS))
            ->mapWithKeys(fn (string $use): array => [
                $use => $facilities
                    ->filter(fn (array $facility): bool => in_array($use, $facility['campaignUses'], true))
                    ->sortBy(fn (array $facility): int => $this->priorityRank($facility['priority']))
                    ->take(6)
                    ->values()
                    ->all(),
            ])
            ->all();
    }

    /** @return Collection<int, covariant array<string, mixed>> */
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
                    'campaignUses' => collect($this->stringList($item['campaignUses'] ?? $item['campaign_uses'] ?? null))
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

    private function priorityRank(?string $priority): int
    {
        return match ($priority) {
            'primary' => 0,
            'secondary' => 1,
            default => 2,
        };
    }

    /** @return list<mixed> */
    private function valueList(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        return is_array($value) ? array_values(array_filter($value, is_string(...))) : [];
    }
}
