<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignSponsorship;
use App\Models\PartnerAccount;
use App\Models\SponsorAccount;
use App\Models\SponsorPartnerAssignment;
use App\Models\SponsorProposal;
use App\Models\SponsorProposalItem;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

        $partnerAssignments = SponsorPartnerAssignment::query()
            ->when($campaignId, fn (Builder $query) => $query->where('campaign_id', $campaignId))
            ->when(! $isGlobal, fn (Builder $query) => $query->whereHas('partnerAccount', fn (Builder $partner) => $partner->whereIn('venue_id', $venueIds)))
            ->with([
                'sponsorAccount:id,venue_id,code,name,sponsor_type,status',
                'partnerAccount:id,venue_id,code,name,partner_type,status',
                'partnerAccount.venue:id,code,name',
                'campaign:id,venue_id,code,name,status',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (SponsorPartnerAssignment $assignment): array => $this->serializePartnerAssignment($assignment));

        $proposals = SponsorProposal::query()
            ->when($campaignId, fn (Builder $query) => $query->where('campaign_id', $campaignId))
            ->when(! $isGlobal, fn (Builder $query) => $query->whereHas('sponsorAccount', fn (Builder $sponsor) => $sponsor->where(function (Builder $query) use ($venueIds): void {
                $query->whereNull('venue_id')->orWhereIn('venue_id', $venueIds);
            })))
            ->with([
                'sponsorAccount:id,venue_id,code,name,sponsor_type,status',
                'campaign:id,venue_id,code,name,status',
                'preferredPartnerAccount:id,venue_id,code,name,partner_type,status',
                'preferredPartnerAccount.venue:id,code,name',
                'partnerAccounts.partnerAccount:id,venue_id,code,name,partner_type,status',
                'partnerAccounts.partnerAccount.venue:id,code,name',
                'items',
            ])
            ->latest('created_at')
            ->get()
            ->map(fn (SponsorProposal $proposal): array => $this->serializeProposal($proposal));

        return [
            'stats' => [
                'sponsors' => $sponsors->count(),
                'activeSponsors' => $sponsors->where('status', 'active')->count(),
                'sponsorships' => $sponsorships->count(),
                'activeSponsorships' => $sponsorships->where('status', 'active')->count(),
                'partnerAssignments' => $partnerAssignments->count(),
                'activePartnerAssignments' => $partnerAssignments->where('status', 'active')->count(),
                'proposals' => $proposals->count(),
                'pendingProposals' => $proposals->where('status', 'pending_review')->count(),
                'plannedBudget' => $sponsorships->sum('budgetAmount'),
                'contractValue' => $sponsorships->sum('contractValue'),
            ],
            'sponsors' => $sponsors,
            'sponsorships' => $sponsorships,
            'partnerAssignments' => $partnerAssignments,
            'proposals' => $proposals,
            'formOptions' => $this->formOptions($user, $campaignId),
        ];
    }

    /** @param array<string, mixed> $data */
    public function storeSponsor(array $data): SponsorAccount
    {
        $code = trim((string) ($data['code'] ?? ''));

        $attributes = [
            'venue_id' => $data['venue_id'] ?? null,
            'code' => $code === '' ? $this->generateSponsorCode($data['venue_id'] ?? null, (string) $data['sponsor_type']) : strtolower($code),
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

    private function generateSponsorCode(?string $venueId, string $sponsorType): string
    {
        $venueCode = $venueId
            ? Venue::query()->whereKey($venueId)->value('code')
            : null;
        $base = Str::slug(($venueCode ?: 'global').'-'.$sponsorType, '-');
        $base = $base !== '' ? Str::limit($base, 84, '') : 'global-sponsor';

        for ($sequence = 1; $sequence <= 9999; $sequence++) {
            $candidate = sprintf('%s-%04d', $base, $sequence);

            if (! SponsorAccount::query()->where('code', $candidate)->exists()) {
                return $candidate;
            }
        }

        return Str::lower((string) Str::uuid());
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

    /** @param array<string, mixed> $data */
    public function storePartnerAssignment(array $data): SponsorPartnerAssignment
    {
        $sponsor = SponsorAccount::query()->findOrFail($data['sponsor_account_id']);
        $partner = PartnerAccount::query()->findOrFail($data['partner_account_id']);
        $campaign = ! empty($data['campaign_id'])
            ? Campaign::query()->findOrFail($data['campaign_id'])
            : null;

        if ($sponsor->venue_id !== null && $sponsor->venue_id !== $partner->venue_id) {
            throw ValidationException::withMessages(['partner_account_id' => 'واحد عضو انتخاب‌شده به مکان این اسپانسر تعلق ندارد.']);
        }

        if ($campaign && $partner->venue_id !== $campaign->venue_id) {
            throw ValidationException::withMessages(['partner_account_id' => 'واحد عضو انتخاب‌شده به مکان این کمپین تعلق ندارد.']);
        }

        if ($campaign && $sponsor->venue_id !== null && $sponsor->venue_id !== $campaign->venue_id) {
            throw ValidationException::withMessages(['sponsor_account_id' => 'اسپانسر انتخاب‌شده به مکان این کمپین تعلق ندارد.']);
        }

        $attributes = [
            'sponsor_account_id' => $sponsor->id,
            'partner_account_id' => $partner->id,
            'campaign_id' => $campaign?->id,
            'activation_role' => $data['activation_role'],
            'status' => $data['status'],
            'starts_at' => ($data['starts_at'] ?? null) ?: null,
            'ends_at' => ($data['ends_at'] ?? null) ?: null,
            'notes' => $data['notes'] ?? null,
            'metadata' => ['source' => 'admin_sponsor_activation'],
        ];

        return DB::transaction(function () use ($data, $attributes): SponsorPartnerAssignment {
            if (! empty($data['assignment_id'])) {
                $assignment = SponsorPartnerAssignment::query()->findOrFail($data['assignment_id']);
                $metadata = array_merge($assignment->metadata ?? [], $attributes['metadata']);
                $assignment->update(array_merge($attributes, ['metadata' => $metadata]));

                return $assignment->refresh();
            }

            return SponsorPartnerAssignment::query()->updateOrCreate(
                [
                    'sponsor_account_id' => $attributes['sponsor_account_id'],
                    'partner_account_id' => $attributes['partner_account_id'],
                    'campaign_id' => $attributes['campaign_id'],
                    'activation_role' => $attributes['activation_role'],
                ],
                $attributes,
            );
        });
    }

    /** @param array<string, mixed> $data */
    public function updateProposalStatus(SponsorProposal $proposal, array $data, User $reviewer): SponsorProposal
    {
        $metadata = array_merge($proposal->metadata ?? [], [
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now()->toIso8601String(),
        ]);

        $proposal->update([
            'status' => $data['status'],
            'metadata' => $metadata,
        ]);

        return $proposal->refresh();
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
            'partners' => PartnerAccount::query()
                ->when($campaign, fn (Builder $query) => $query->where('venue_id', $campaign->venue_id))
                ->when(! $campaign && ! $isGlobal, fn (Builder $query) => $query->whereIn('venue_id', $venueIds))
                ->with('venue:id,code,name')
                ->orderBy('name')
                ->get(['id', 'venue_id', 'code', 'name', 'partner_type', 'status'])
                ->map(fn (PartnerAccount $partner): array => [
                    'id' => $partner->id,
                    'code' => $partner->code,
                    'name' => $partner->name,
                    'partnerType' => $partner->partner_type,
                    'status' => $partner->status->value,
                    'venueName' => $partner->venue?->name,
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
    private function serializePartnerAssignment(SponsorPartnerAssignment $assignment): array
    {
        return [
            'id' => $assignment->id,
            'activationRole' => $assignment->activation_role,
            'status' => $assignment->status->value,
            'startsAt' => $assignment->starts_at?->toIso8601String(),
            'endsAt' => $assignment->ends_at?->toIso8601String(),
            'notes' => $assignment->notes,
            'sponsor' => $assignment->sponsorAccount ? [
                'id' => $assignment->sponsorAccount->id,
                'code' => $assignment->sponsorAccount->code,
                'name' => $assignment->sponsorAccount->name,
                'sponsorType' => $assignment->sponsorAccount->sponsor_type,
                'status' => $assignment->sponsorAccount->status->value,
            ] : null,
            'partner' => $assignment->partnerAccount ? [
                'id' => $assignment->partnerAccount->id,
                'code' => $assignment->partnerAccount->code,
                'name' => $assignment->partnerAccount->name,
                'partnerType' => $assignment->partnerAccount->partner_type,
                'status' => $assignment->partnerAccount->status->value,
                'venueName' => $assignment->partnerAccount->venue?->name,
            ] : null,
            'campaign' => $assignment->campaign ? [
                'id' => $assignment->campaign->id,
                'code' => $assignment->campaign->code,
                'name' => $assignment->campaign->name,
                'status' => $assignment->campaign->status->value,
            ] : null,
        ];
    }

    /** @return array<string, mixed> */
    private function serializeProposal(SponsorProposal $proposal): array
    {
        return [
            'id' => $proposal->id,
            'code' => $proposal->code,
            'title' => $proposal->title,
            'proposalType' => $proposal->proposal_type,
            'objective' => $proposal->objective,
            'status' => $proposal->status,
            'proposedBudgetAmount' => (int) ($proposal->proposed_budget_amount ?? 0),
            'estimatedValueAmount' => (int) ($proposal->estimated_value_amount ?? 0),
            'rewardOffer' => $proposal->reward_offer,
            'discountOffer' => $proposal->discount_offer,
            'assetUrl' => $proposal->asset_url,
            'targetAudience' => $proposal->target_audience,
            'notes' => $proposal->notes,
            'reviewNotes' => $proposal->metadata['review_notes'] ?? null,
            'createdAt' => $proposal->created_at?->toIso8601String(),
            'partners' => $proposal->partnerAccounts
                ->sortBy('sort_order')
                ->map(fn ($proposalPartner): array => $this->serializeProposalPartner($proposalPartner->partnerAccount))
                ->values()
                ->all(),
            'items' => $proposal->items
                ->map(fn (SponsorProposalItem $item): array => [
                    'id' => $item->id,
                    'itemType' => $item->item_type,
                    'title' => $item->title,
                    'quantity' => (int) ($item->quantity ?? 0),
                    'estimatedUnitValueAmount' => (int) ($item->estimated_unit_value_amount ?? 0),
                    'targetPartnerAccountIds' => $item->target_partner_account_ids ?? [],
                    'partnerAllocations' => $item->partner_allocations ?? [],
                    'description' => $item->description,
                ])
                ->values()
                ->all(),
            'sponsor' => $proposal->sponsorAccount ? [
                'id' => $proposal->sponsorAccount->id,
                'code' => $proposal->sponsorAccount->code,
                'name' => $proposal->sponsorAccount->name,
                'sponsorType' => $proposal->sponsorAccount->sponsor_type,
                'status' => $proposal->sponsorAccount->status->value,
            ] : null,
            'campaign' => $proposal->campaign ? [
                'id' => $proposal->campaign->id,
                'code' => $proposal->campaign->code,
                'name' => $proposal->campaign->name,
                'status' => $proposal->campaign->status->value,
            ] : null,
            'preferredPartner' => $proposal->preferredPartnerAccount ? $this->serializeProposalPartner($proposal->preferredPartnerAccount) : null,
        ];
    }

    /** @return array<string, mixed> */
    private function serializeProposalPartner(?PartnerAccount $partner): array
    {
        if (! $partner) {
            return [];
        }

        return [
            'id' => $partner->id,
            'code' => $partner->code,
            'name' => $partner->name,
            'partnerType' => $partner->partner_type,
            'status' => $partner->status->value,
            'venueName' => $partner->venue?->name,
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
