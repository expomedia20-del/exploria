<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignSponsorship;
use App\Models\SponsorAccount;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SponsorActivationService
{
    public function __construct(private readonly UserAccessScopeService $accessScopes) {}

    /** @return array<string, mixed> */
    public function overview(?User $user = null, ?string $campaignId = null): array
    {
        $venueIds = $user ? $this->accessScopes->venueIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        $sponsorships = CampaignSponsorship::query()
            ->when($campaignId, fn (Builder $query) => $query->where('campaign_id', $campaignId))
            ->when(! $isGlobal, fn (Builder $query) => $query->whereHas('campaign', fn (Builder $campaign) => $campaign->whereIn('venue_id', $venueIds)))
            ->with([
                'campaign:id,venue_id,code,name,status',
                'campaign.venue:id,code,name',
                'sponsorAccount:id,venue_id,code,name,sponsor_type,status,contact_name,contact_mobile',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (CampaignSponsorship $sponsorship): array => $this->serializeSponsorship($sponsorship));

        $sponsors = SponsorAccount::query()
            ->when(! $isGlobal, fn (Builder $query) => $query->where(function (Builder $query) use ($venueIds): void {
                $query->whereNull('venue_id')->orWhereIn('venue_id', $venueIds);
            }))
            ->with('venue:id,code,name')
            ->orderBy('name')
            ->get()
            ->map(fn (SponsorAccount $sponsor): array => $this->serializeSponsor($sponsor));

        return [
            'stats' => [
                'sponsors' => $sponsors->count(),
                'activeSponsors' => $sponsors->where('status', 'active')->count(),
                'sponsorships' => $sponsorships->count(),
                'activeSponsorships' => $sponsorships->where('status', 'active')->count(),
                'plannedBudget' => $sponsorships->sum('budgetAmount'),
                'contractValue' => $sponsorships->sum('contractValue'),
            ],
            'sponsors' => $sponsors,
            'sponsorships' => $sponsorships,
            'formOptions' => $this->formOptions($user, $campaignId),
        ];
    }

    /** @param array<string, mixed> $data */
    public function storeSponsor(array $data): SponsorAccount
    {
        $attributes = [
            'venue_id' => $data['venue_id'] ?? null,
            'code' => strtolower((string) $data['code']),
            'name' => $data['name'],
            'sponsor_type' => $data['sponsor_type'],
            'status' => $data['status'],
            'contact_name' => $data['contact_name'] ?? null,
            'contact_mobile' => $data['contact_mobile'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'metadata' => array_filter([
                'source' => 'admin_sponsor_activation',
                'notes' => $data['notes'] ?? null,
            ]),
        ];

        return DB::transaction(function () use ($data, $attributes): SponsorAccount {
            if (! empty($data['sponsor_id'])) {
                $sponsor = SponsorAccount::query()->findOrFail($data['sponsor_id']);
                $metadata = array_merge($sponsor->metadata ?? [], $attributes['metadata']);
                $sponsor->update(array_merge($attributes, ['metadata' => $metadata]));

                return $sponsor->refresh();
            }

            return SponsorAccount::query()->create($attributes);
        });
    }

    /** @param array<string, mixed> $data */
    public function storeSponsorship(array $data): CampaignSponsorship
    {
        $campaign = Campaign::query()->findOrFail($data['campaign_id']);
        $sponsor = SponsorAccount::query()->findOrFail($data['sponsor_account_id']);

        if ($sponsor->venue_id !== null && $sponsor->venue_id !== $campaign->venue_id) {
            throw ValidationException::withMessages(['sponsor_account_id' => 'اسپانسر انتخاب‌شده به مکان این کمپین تعلق ندارد.']);
        }

        $attributes = [
            'campaign_id' => $campaign->id,
            'sponsor_account_id' => $sponsor->id,
            'sponsorship_goal' => $data['sponsorship_goal'],
            'package_type' => $data['package_type'],
            'status' => $data['status'],
            'budget_amount' => $data['budget_amount'] ?? null,
            'contract_value' => $data['contract_value'] ?? null,
            'starts_at' => ($data['starts_at'] ?? null) ?: null,
            'ends_at' => ($data['ends_at'] ?? null) ?: null,
            'notes' => $data['notes'] ?? null,
            'metadata' => ['source' => 'admin_sponsor_activation'],
        ];

        return DB::transaction(function () use ($data, $attributes): CampaignSponsorship {
            if (! empty($data['sponsorship_id'])) {
                $sponsorship = CampaignSponsorship::query()->findOrFail($data['sponsorship_id']);
                $metadata = array_merge($sponsorship->metadata ?? [], $attributes['metadata']);
                $sponsorship->update(array_merge($attributes, ['metadata' => $metadata]));

                return $sponsorship->refresh();
            }

            return CampaignSponsorship::query()->updateOrCreate(
                [
                    'campaign_id' => $attributes['campaign_id'],
                    'sponsor_account_id' => $attributes['sponsor_account_id'],
                ],
                $attributes,
            );
        });
    }

    /** @return array<string, mixed> */
    private function formOptions(?User $user, ?string $campaignId): array
    {
        $venueIds = $user ? $this->accessScopes->venueIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);
        $campaign = $campaignId ? Campaign::query()->find($campaignId) : null;

        return [
            'campaigns' => Campaign::query()
                ->when($campaign, fn (Builder $query) => $query->whereKey($campaign->id))
                ->when(! $campaign && ! $isGlobal, fn (Builder $query) => $query->whereIn('venue_id', $venueIds))
                ->with('venue:id,code,name')
                ->orderByDesc('created_at')
                ->get(['id', 'venue_id', 'code', 'name', 'status'])
                ->map(fn (Campaign $campaign): array => [
                    'id' => $campaign->id,
                    'code' => $campaign->code,
                    'name' => $campaign->name,
                    'status' => $campaign->status->value,
                    'venueName' => $campaign->venue?->name,
                ]),
            'sponsors' => SponsorAccount::query()
                ->when(! $isGlobal, fn (Builder $query) => $query->where(function (Builder $query) use ($venueIds): void {
                    $query->whereNull('venue_id')->orWhereIn('venue_id', $venueIds);
                }))
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'sponsor_type', 'status'])
                ->map(fn (SponsorAccount $sponsor): array => [
                    'id' => $sponsor->id,
                    'code' => $sponsor->code,
                    'name' => $sponsor->name,
                    'sponsorType' => $sponsor->sponsor_type,
                    'status' => $sponsor->status->value,
                ]),
            'venues' => Venue::query()
                ->when(! $isGlobal, fn (Builder $query) => $query->whereIn('id', $venueIds))
                ->orderBy('name')
                ->get(['id', 'code', 'name'])
                ->map(fn (Venue $venue): array => ['id' => $venue->id, 'code' => $venue->code, 'name' => $venue->name]),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeSponsor(SponsorAccount $sponsor): array
    {
        return [
            'id' => $sponsor->id,
            'code' => $sponsor->code,
            'name' => $sponsor->name,
            'sponsorType' => $sponsor->sponsor_type,
            'status' => $sponsor->status->value,
            'contactName' => $sponsor->contact_name,
            'contactMobile' => $sponsor->contact_mobile,
            'websiteUrl' => $sponsor->website_url,
            'notes' => $sponsor->metadata['notes'] ?? null,
            'venue' => $sponsor->venue ? ['id' => $sponsor->venue->id, 'code' => $sponsor->venue->code, 'name' => $sponsor->venue->name] : null,
        ];
    }

    /** @return array<string, mixed> */
    private function serializeSponsorship(CampaignSponsorship $sponsorship): array
    {
        return [
            'id' => $sponsorship->id,
            'sponsorshipGoal' => $sponsorship->sponsorship_goal,
            'packageType' => $sponsorship->package_type,
            'status' => $sponsorship->status->value,
            'budgetAmount' => (int) ($sponsorship->budget_amount ?? 0),
            'contractValue' => (int) ($sponsorship->contract_value ?? 0),
            'startsAt' => $sponsorship->starts_at?->toIso8601String(),
            'endsAt' => $sponsorship->ends_at?->toIso8601String(),
            'notes' => $sponsorship->notes,
            'campaign' => $sponsorship->campaign ? [
                'id' => $sponsorship->campaign->id,
                'code' => $sponsorship->campaign->code,
                'name' => $sponsorship->campaign->name,
                'status' => $sponsorship->campaign->status->value,
                'venueName' => $sponsorship->campaign->venue?->name,
            ] : null,
            'sponsor' => $sponsorship->sponsorAccount ? [
                'id' => $sponsorship->sponsorAccount->id,
                'code' => $sponsorship->sponsorAccount->code,
                'name' => $sponsorship->sponsorAccount->name,
                'sponsorType' => $sponsorship->sponsorAccount->sponsor_type,
                'status' => $sponsorship->sponsorAccount->status->value,
            ] : null,
        ];
    }
}
