<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\PartnerAccount;
use App\Models\PartnerUser;
use App\Models\SponsorAccount;
use App\Models\SponsorProposal;
use App\Models\SponsorProposalItem;
use App\Models\SponsorUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SponsorPortalService
{
    public function sponsorForUser(User $user): SponsorAccount
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::Operator], true)) {
            $sponsor = SponsorAccount::query()
                ->with('venue:id,code,name')
                ->orderBy('created_at')
                ->first();

            if ($sponsor) {
                return $sponsor;
            }
        }

        $sponsorUser = SponsorUser::query()
            ->with('sponsorAccount.venue:id,code,name')
            ->where('user_id', $user->id)
            ->where('status', RecordStatus::Active)
            ->first();

        if ($sponsorUser?->sponsorAccount) {
            return $sponsorUser->sponsorAccount;
        }

        $partnerUser = PartnerUser::query()
            ->with('partnerAccount.venue:id,code,name')
            ->where('user_id', $user->id)
            ->where('status', RecordStatus::Active)
            ->whereHas('partnerAccount', fn (Builder $query) => $query->where('partner_type', 'sponsor'))
            ->first();

        if ($partnerUser?->partnerAccount) {
            return $this->ensureSponsorFromPartner($partnerUser);
        }

        throw ValidationException::withMessages([
            'sponsor' => 'برای کاربر فعلی حساب اسپانسر فعال ثبت نشده است.',
        ]);
    }

    /** @return array<string, mixed> */
    public function overview(User $user): array
    {
        $sponsor = $this->sponsorForUser($user);
        $sponsor->load('venue:id,code,name');

        $proposals = $sponsor->proposals()
            ->with([
                'campaign:id,code,name,status',
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
            'sponsor' => $this->serializeSponsor($sponsor),
            'stats' => [
                'proposals' => $proposals->count(),
                'pendingProposals' => $proposals->where('status', 'pending_review')->count(),
                'approvedProposals' => $proposals->where('status', 'approved')->count(),
                'revisionRequested' => $proposals->where('status', 'revision_requested')->count(),
            ],
            'proposals' => $proposals,
            'formOptions' => $this->formOptions($sponsor),
        ];
    }

    /** @param array<string, mixed> $data */
    public function submitProposal(User $user, array $data): SponsorProposal
    {
        $sponsor = $this->sponsorForUser($user);
        $campaign = ! empty($data['campaign_id'])
            ? Campaign::query()->findOrFail($data['campaign_id'])
            : null;
        $partnerIds = $this->partnerIdsFromProposalData($data);
        $partners = PartnerAccount::query()
            ->whereIn('id', $partnerIds)
            ->get()
            ->keyBy('id');
        $partner = $partnerIds === [] ? null : $partners->get($partnerIds[0]);

        if ($campaign && $sponsor->venue_id !== null && $campaign->venue_id !== $sponsor->venue_id) {
            throw ValidationException::withMessages(['campaign_id' => 'کمپین انتخاب‌شده به مکان این اسپانسر تعلق ندارد.']);
        }

        foreach ($partners as $partnerAccount) {
            if ($sponsor->venue_id !== null && $partnerAccount->venue_id !== $sponsor->venue_id) {
                throw ValidationException::withMessages(['partner_account_ids' => 'واحدهای پیشنهادی باید به مکان این اسپانسر تعلق داشته باشند.']);
            }

            if ($campaign && $partnerAccount->venue_id !== $campaign->venue_id) {
                throw ValidationException::withMessages(['partner_account_ids' => 'واحدهای پیشنهادی باید با مکان کمپین انتخاب‌شده هم‌خوان باشند.']);
            }
        }

        $items = $this->proposalItemsFromData($data, $partnerIds);

        return DB::transaction(function () use ($campaign, $data, $items, $partner, $partnerIds, $sponsor, $user): SponsorProposal {
            $proposal = SponsorProposal::query()->create([
                'sponsor_account_id' => $sponsor->id,
                'campaign_id' => $campaign?->id,
                'preferred_partner_account_id' => $partner?->id,
                'code' => $this->uniqueProposalCode($sponsor),
                'title' => $data['title'],
                'proposal_type' => $data['proposal_type'],
                'objective' => $data['objective'],
                'status' => 'pending_review',
                'proposed_budget_amount' => $data['proposed_budget_amount'] ?? null,
                'estimated_value_amount' => $data['estimated_value_amount'] ?? null,
                'reward_offer' => $data['reward_offer'] ?? null,
                'discount_offer' => $data['discount_offer'] ?? null,
                'asset_url' => $data['asset_url'] ?? null,
                'target_audience' => $data['target_audience'] ?? null,
                'notes' => $data['notes'] ?? null,
                'metadata' => [
                    'source' => 'sponsor_self_service',
                    'submitted_by_user_id' => $user->id,
                    'submitted_at' => now()->toIso8601String(),
                    'partner_count' => count($partnerIds),
                    'item_count' => count($items),
                ],
            ]);

            foreach ($partnerIds as $index => $partnerId) {
                $proposal->partnerAccounts()->create([
                    'partner_account_id' => $partnerId,
                    'sort_order' => $index,
                    'metadata' => ['source' => 'sponsor_self_service'],
                ]);
            }

            foreach ($items as $item) {
                $proposal->items()->create($item);
            }

            return $proposal->refresh()->load([
                'campaign:id,code,name,status',
                'preferredPartnerAccount:id,venue_id,code,name,partner_type,status',
                'preferredPartnerAccount.venue:id,code,name',
                'partnerAccounts.partnerAccount:id,venue_id,code,name,partner_type,status',
                'partnerAccounts.partnerAccount.venue:id,code,name',
                'items',
            ]);
        });
    }

    /** @param array<string, mixed> $data */
    private function partnerIdsFromProposalData(array $data): array
    {
        $partnerIds = collect($data['partner_account_ids'] ?? [])
            ->filter()
            ->values();

        if (! empty($data['preferred_partner_account_id'])) {
            $partnerIds->prepend($data['preferred_partner_account_id']);
        }

        return $partnerIds
            ->unique()
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $data */
    private function proposalItemsFromData(array $data, array $partnerIds): array
    {
        $items = collect($data['items'] ?? [])
            ->filter(fn (array $item): bool => trim((string) ($item['title'] ?? '')) !== '')
            ->map(function (array $item) use ($partnerIds): array {
                $targetPartnerIds = collect($item['target_partner_account_ids'] ?? [])
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $invalidTargets = array_diff($targetPartnerIds, $partnerIds);
                if ($invalidTargets !== []) {
                    throw ValidationException::withMessages(['items' => 'واحدهای هدف هر آیتم باید از میان واحدهای انتخابی پیشنهاد باشند.']);
                }

                return [
                    'item_type' => $item['item_type'],
                    'title' => $item['title'],
                    'quantity' => $item['quantity'] ?? null,
                    'estimated_unit_value_amount' => $item['estimated_unit_value_amount'] ?? null,
                    'target_partner_account_ids' => $targetPartnerIds,
                    'description' => $item['description'] ?? null,
                    'metadata' => ['source' => 'sponsor_self_service'],
                ];
            })
            ->values();

        if ($items->isEmpty() && ! empty($data['reward_offer'])) {
            $items->push([
                'item_type' => 'reward',
                'title' => 'پیشنهاد جایزه/محصول',
                'quantity' => null,
                'estimated_unit_value_amount' => $data['estimated_value_amount'] ?? null,
                'target_partner_account_ids' => $partnerIds,
                'description' => $data['reward_offer'],
                'metadata' => ['source' => 'legacy_reward_offer'],
            ]);
        }

        if (! empty($data['discount_offer'])) {
            $items->push([
                'item_type' => 'discount',
                'title' => 'پیشنهاد تخفیف یا کد هدیه',
                'quantity' => null,
                'estimated_unit_value_amount' => null,
                'target_partner_account_ids' => $partnerIds,
                'description' => $data['discount_offer'],
                'metadata' => ['source' => 'legacy_discount_offer'],
            ]);
        }

        return $items->all();
    }

    /** @return array<string, mixed> */
    private function formOptions(SponsorAccount $sponsor): array
    {
        return [
            'campaigns' => Campaign::query()
                ->when($sponsor->venue_id !== null, fn (Builder $query) => $query->where('venue_id', $sponsor->venue_id))
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
            'partners' => PartnerAccount::query()
                ->when($sponsor->venue_id !== null, fn (Builder $query) => $query->where('venue_id', $sponsor->venue_id))
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
        ];
    }

    private function ensureSponsorFromPartner(PartnerUser $partnerUser): SponsorAccount
    {
        $partner = $partnerUser->partnerAccount;
        $sponsor = SponsorAccount::query()->firstOrCreate(
            ['code' => $partner->code],
            [
                'venue_id' => $partner->venue_id,
                'name' => $partner->name,
                'sponsor_type' => 'brand',
                'status' => RecordStatus::Active,
                'contact_name' => $partner->contact_name,
                'contact_mobile' => $partner->contact_mobile,
                'metadata' => [
                    'source' => 'sponsor_portal_partner_fallback',
                    'linked_partner_account_id' => $partner->id,
                ],
            ],
        );

        SponsorUser::query()->updateOrCreate(
            ['sponsor_account_id' => $sponsor->id, 'user_id' => $partnerUser->user_id],
            ['role' => 'manager', 'status' => RecordStatus::Active, 'metadata' => ['source' => 'sponsor_portal_partner_fallback']],
        );

        return $sponsor->fresh('venue:id,code,name') ?? $sponsor;
    }

    private function uniqueProposalCode(SponsorAccount $sponsor): string
    {
        $base = Str::slug('sp-'.$sponsor->code.'-'.now()->format('Ymd'), '-');

        for ($sequence = 1; $sequence <= 9999; $sequence++) {
            $candidate = sprintf('%s-%04d', $base, $sequence);

            if (! SponsorProposal::query()->where('code', $candidate)->exists()) {
                return $candidate;
            }
        }

        return Str::lower((string) Str::uuid());
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
            'venueName' => $sponsor->venue?->name,
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
                ->map(fn ($proposalPartner): array => $this->serializePartnerOption($proposalPartner->partnerAccount))
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
                    'description' => $item->description,
                ])
                ->values()
                ->all(),
            'campaign' => $proposal->campaign ? [
                'id' => $proposal->campaign->id,
                'code' => $proposal->campaign->code,
                'name' => $proposal->campaign->name,
                'status' => $proposal->campaign->status->value,
            ] : null,
            'preferredPartner' => $proposal->preferredPartnerAccount ? $this->serializePartnerOption($proposal->preferredPartnerAccount) : null,
        ];
    }

    /** @return array<string, mixed> */
    private function serializePartnerOption(?PartnerAccount $partner): array
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
}
