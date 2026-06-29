<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CampaignRegistryService
{
    public function __construct(private readonly UserAccessScopeService $accessScopes) {}

    /** @return Collection<int, array<string, mixed>> */
    public function list(?User $user = null): Collection
    {
        $venueIds = $user ? $this->accessScopes->venueIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        return Campaign::query()
            ->when(! $isGlobal, fn (Builder $query) => $query->whereIn('venue_id', $venueIds))
            ->with(['venue:id,code,name'])
            ->withCount(['qrCodes', 'visits'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (Campaign $campaign): array => $this->serializeCampaign($campaign));
    }

    /** @return Collection<int, array{id: string, code: string, name: string}> */
    public function venueOptions(?User $user = null): Collection
    {
        $venueIds = $user ? $this->accessScopes->venueIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        return Venue::query()
            ->when(! $isGlobal, fn (Builder $query) => $query->whereIn('id', $venueIds))
            ->orderBy('created_at')
            ->get(['id', 'code', 'name'])
            ->toBase()
            ->map(fn (Venue $venue): array => [
                'id' => $venue->id,
                'code' => $venue->code,
                'name' => $venue->name,
            ]);
    }

    /** @return array<string, mixed>|null */
    public function context(?User $user, ?string $campaignCode): ?array
    {
        if (! $campaignCode) {
            return null;
        }

        $venueIds = $user ? $this->accessScopes->venueIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        $campaign = Campaign::query()
            ->when(! $isGlobal, fn (Builder $query) => $query->whereIn('venue_id', $venueIds))
            ->with('venue:id,code,name')
            ->where('code', Str::lower($campaignCode))
            ->first();

        if (! $campaign) {
            return null;
        }

        return [
            'id' => $campaign->id,
            'code' => $campaign->code,
            'name' => $campaign->name,
            'campaignType' => $campaign->campaign_type,
            'blueprintCode' => $campaign->metadata['blueprint_code'] ?? null,
            'status' => $campaign->status->value,
            'venue' => $campaign->venue ? [
                'id' => $campaign->venue->id,
                'code' => $campaign->venue->code,
                'name' => $campaign->venue->name,
            ] : null,
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Campaign
    {
        $attributes = [
            'venue_id' => $data['venue_id'],
            'code' => Str::lower((string) $data['code']),
            'name' => $data['name'],
            'campaign_type' => Str::lower((string) $data['campaign_type']),
            'status' => $data['status'],
            'start_at' => ($data['start_at'] ?? null) ?: null,
            'end_at' => ($data['end_at'] ?? null) ?: null,
            'metadata' => array_filter([
                'created_from' => 'admin_campaign_registry',
                'blueprint_code' => $data['blueprint_code'] ?? null,
            ]),
        ];

        return DB::transaction(function () use ($data, $attributes): Campaign {
            if (! empty($data['campaign_id'])) {
                $campaign = Campaign::query()->findOrFail($data['campaign_id']);
                $metadata = array_filter(array_merge($campaign->metadata ?? [], $attributes['metadata']));
                $campaign->update(array_merge($attributes, ['metadata' => $metadata]));

                return $campaign->refresh();
            }

            return Campaign::query()->create($attributes);
        });
    }

    public function delete(Campaign $campaign): void
    {
        $hasDependencies = $campaign->qrCodes()->exists()
            || $campaign->visits()->exists()
            || $campaign->missionInstances()->exists()
            || $campaign->rewardDefinitions()->exists()
            || $campaign->treasures()->exists()
            || $campaign->campaignParticipants()->exists();

        if ($hasDependencies) {
            throw ValidationException::withMessages(['campaign' => 'این کمپین اجزای وابسته دارد و حذف مستقیم آن مجاز نیست. ابتدا اجزای متصل را حذف یا غیرفعال کنید.']);
        }

        $campaign->delete();
    }

    /** @return array<string, mixed> */
    private function serializeCampaign(Campaign $campaign): array
    {
        return [
            'id' => $campaign->id,
            'code' => $campaign->code,
            'name' => $campaign->name,
            'campaignType' => $campaign->campaign_type,
            'blueprintCode' => $campaign->metadata['blueprint_code'] ?? null,
            'status' => $campaign->status->value,
            'startAt' => $campaign->start_at?->toIso8601String(),
            'endAt' => $campaign->end_at?->toIso8601String(),
            'qrCodesCount' => (int) $campaign->getAttribute('qr_codes_count'),
            'visitsCount' => (int) $campaign->getAttribute('visits_count'),
            'venue' => $campaign->venue ? [
                'id' => $campaign->venue->id,
                'code' => $campaign->venue->code,
                'name' => $campaign->venue->name,
            ] : null,
        ];
    }
}
