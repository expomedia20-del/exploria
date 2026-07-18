<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignSponsorship;
use App\Models\PartnerAccount;
use App\Models\RewardDefinition;
use App\Models\SponsorAccount;
use App\Models\SponsorPartnerAssignment;
use App\Models\SponsorProposal;
use App\Models\SponsorProposalActivation;
use App\Models\SponsorProposalItem;
use App\Models\Treasure;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
                'activation:id,sponsor_proposal_id,campaign_id,campaign_sponsorship_id,status,reward_definition_ids,treasure_ids,partner_assignment_ids,metadata',
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
        $venueId = $this->optionalString($data, 'venue_id');

        $attributes = [
            'venue_id' => $venueId,
            'code' => $code === '' ? $this->generateSponsorCode($venueId, (string) $data['sponsor_type']) : strtolower($code),
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
                $sponsor = SponsorAccount::query()->findOrFail($this->requiredId($data, 'sponsor_id'));
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
        $campaign = Campaign::query()->findOrFail($this->requiredId($data, 'campaign_id'));
        $sponsor = SponsorAccount::query()->findOrFail($this->requiredId($data, 'sponsor_account_id'));

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
                $sponsorship = CampaignSponsorship::query()->findOrFail($this->requiredId($data, 'sponsorship_id'));
                $metadata = array_merge($sponsorship->metadata ?? [], $attributes['metadata']);
                $sponsorship->update(array_merge($attributes, ['metadata' => $metadata]));

                $sponsorship = $sponsorship->refresh();
                $this->syncManualSponsorshipIncentive($sponsorship);

                return $sponsorship;
            }

            $sponsorship = CampaignSponsorship::query()->updateOrCreate(
                [
                    'campaign_id' => $attributes['campaign_id'],
                    'sponsor_account_id' => $attributes['sponsor_account_id'],
                ],
                $attributes,
            );

            $this->syncManualSponsorshipIncentive($sponsorship->refresh());

            return $sponsorship;
        });
    }

    /** @param array<string, mixed> $data */
    public function storePartnerAssignment(array $data): SponsorPartnerAssignment
    {
        $sponsor = SponsorAccount::query()->findOrFail($this->requiredId($data, 'sponsor_account_id'));
        $partner = PartnerAccount::query()->findOrFail($this->requiredId($data, 'partner_account_id'));
        $campaign = ! empty($data['campaign_id'])
            ? Campaign::query()->findOrFail($this->requiredId($data, 'campaign_id'))
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
                $assignment = SponsorPartnerAssignment::query()->findOrFail($this->requiredId($data, 'assignment_id'));
                $metadata = array_merge($assignment->metadata ?? [], $attributes['metadata']);
                $assignment->update(array_merge($attributes, ['metadata' => $metadata]));

                $assignment = $assignment->refresh();
                $this->syncManualSponsorshipForAssignment($assignment);

                return $assignment;
            }

            $assignment = SponsorPartnerAssignment::query()->updateOrCreate(
                [
                    'sponsor_account_id' => $attributes['sponsor_account_id'],
                    'partner_account_id' => $attributes['partner_account_id'],
                    'campaign_id' => $attributes['campaign_id'],
                    'activation_role' => $attributes['activation_role'],
                ],
                $attributes,
            );

            $this->syncManualSponsorshipForAssignment($assignment->refresh());

            return $assignment;
        });
    }

    private function syncManualSponsorshipForAssignment(SponsorPartnerAssignment $assignment): void
    {
        if (! $assignment->campaign_id) {
            return;
        }

        $sponsorship = CampaignSponsorship::query()
            ->where('campaign_id', $assignment->campaign_id)
            ->where('sponsor_account_id', $assignment->sponsor_account_id)
            ->first();

        if ($sponsorship) {
            $this->syncManualSponsorshipIncentive($sponsorship);
        }
    }

    private function syncManualSponsorshipIncentive(CampaignSponsorship $sponsorship): RewardDefinition
    {
        $sponsorship->loadMissing(['campaign', 'sponsorAccount']);

        $campaign = $sponsorship->campaign;
        $sponsor = $sponsorship->sponsorAccount;
        $targetPartnerIds = SponsorPartnerAssignment::query()
            ->where('campaign_id', $sponsorship->campaign_id)
            ->where('sponsor_account_id', $sponsorship->sponsor_account_id)
            ->where('status', '!=', 'inactive')
            ->pluck('partner_account_id')
            ->unique()
            ->values()
            ->all();
        $partnerId = count($targetPartnerIds) === 1 ? $targetPartnerIds[0] : null;
        $code = $this->manualSponsorshipRewardCode($campaign, $sponsor);
        $metadata = [
            'source' => 'admin_sponsor_activation',
            'source_type' => 'sponsor',
            'approval_status' => 'approved',
            'availability_status' => 'pending_campaign_assignment',
            'sponsor_account_id' => $sponsor->id,
            'sponsor_account_code' => $sponsor->code,
            'campaign_sponsorship_id' => $sponsorship->id,
            'sponsorship_goal' => $sponsorship->sponsorship_goal,
            'package_type' => $sponsorship->package_type,
            'reward_tier' => null,
            'reward_option' => $sponsorship->package_type,
            'target_partner_account_ids' => $targetPartnerIds,
            'partner_allocations' => [],
            'description' => $sponsorship->notes,
            'terms' => 'این مشوق از اتصال دستی اسپانسر به کمپین ساخته شده و باید در صفحه ماموریت‌ها به گام، شرط دریافت و سهم اجرایی وصل شود.',
            'budget_amount' => $sponsorship->budget_amount,
            'contract_value' => $sponsorship->contract_value,
            'available_from' => $sponsorship->starts_at?->toIso8601String(),
            'available_until' => $sponsorship->ends_at?->toIso8601String(),
        ];

        $reward = RewardDefinition::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'code' => $code],
            [
                'venue_id' => $campaign->venue_id,
                'partner_account_id' => $partnerId,
                'name' => $this->manualSponsorshipRewardName($sponsorship),
                'reward_type' => 'sponsor_reward',
                'point_cost' => null,
                'stock_quantity' => null,
                'status' => 'draft',
                'metadata' => $metadata,
            ],
        );

        if ($sponsorship->package_type === 'treasure_sponsor') {
            $this->syncManualSponsorshipTreasure($sponsorship, $reward);
        } else {
            $this->deactivateManualSponsorshipTreasure($sponsorship);
        }

        return $reward->refresh();
    }

    private function syncManualSponsorshipTreasure(CampaignSponsorship $sponsorship, RewardDefinition $reward): Treasure
    {
        $campaign = $sponsorship->campaign;
        $sponsor = $sponsorship->sponsorAccount;
        $targetPartnerIds = $reward->metadata['target_partner_account_ids'] ?? [];
        $code = Str::limit($this->manualSponsorshipRewardCode($campaign, $sponsor).'-treasure', 120, '');

        return Treasure::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'code' => $code],
            [
                'venue_id' => $campaign->venue_id,
                'mission_instance_id' => null,
                'name' => $reward->name,
                'treasure_type' => 'sponsor_reward_treasure',
                'status' => 'draft',
                'reveal_rule' => [
                    'mode' => 'admin_assign_to_mission',
                    'reward_definition_id' => $reward->id,
                    'target_partner_account_ids' => $targetPartnerIds,
                ],
                'metadata' => [
                    'source' => 'admin_sponsor_activation',
                    'source_type' => 'sponsor',
                    'sponsor_account_id' => $sponsor->id,
                    'sponsor_account_code' => $sponsor->code,
                    'campaign_sponsorship_id' => $sponsorship->id,
                    'reward_definition_id' => $reward->id,
                    'treasure_tier' => null,
                    'reveal_description' => $sponsorship->notes,
                    'discovery_hint' => 'این گنج از اتصال دستی اسپانسر به کمپین ساخته شده و باید در صفحه ماموریت‌ها به گام چرخه وصل شود.',
                    'target_partner_account_ids' => $targetPartnerIds,
                ],
            ],
        );
    }

    private function deactivateManualSponsorshipTreasure(CampaignSponsorship $sponsorship): void
    {
        $code = Str::limit($this->manualSponsorshipRewardCode($sponsorship->campaign, $sponsorship->sponsorAccount).'-treasure', 120, '');

        Treasure::query()
            ->where('campaign_id', $sponsorship->campaign_id)
            ->where('code', $code)
            ->update(['status' => 'inactive']);
    }

    private function manualSponsorshipRewardCode(Campaign $campaign, SponsorAccount $sponsor): string
    {
        return Str::limit(Str::slug('manual-sp-'.$campaign->code.'-'.$sponsor->code, '-'), 120, '');
    }

    private function manualSponsorshipRewardName(CampaignSponsorship $sponsorship): string
    {
        $packageLabels = [
            'pilot_activation' => 'فعال‌سازی پایلوت',
            'display_media' => 'رسانه نمایشی',
            'treasure_sponsor' => 'اسپانسر گنج',
            'family_team_challenge' => 'چالش خانوادگی',
            'scientific_cultural_challenge' => 'چالش علمی/فرهنگی',
        ];

        $package = $packageLabels[$sponsorship->package_type] ?? $sponsorship->package_type;

        return trim(($sponsorship->sponsorAccount->name ?? 'اسپانسر').' - '.$package);
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

    /** @param array<string, mixed> $data */
    public function activateProposal(SponsorProposal $proposal, array $data, User $actor): SponsorProposalActivation
    {
        $proposal->loadMissing([
            'sponsorAccount:id,venue_id,code,name,sponsor_type,status',
            'campaign:id,venue_id,code,name,status',
            'partnerAccounts.partnerAccount:id,venue_id,code,name,partner_type,status',
            'items',
            'activation',
        ]);

        if ($proposal->status !== 'approved') {
            throw ValidationException::withMessages(['proposal' => 'فقط پیشنهاد تاییدشده اسپانسر قابل تبدیل به بسته اجرایی است.']);
        }

        $campaign = ! empty($data['campaign_id'])
            ? Campaign::query()->findOrFail($this->requiredId($data, 'campaign_id'))
            : $proposal->campaign;

        if (! $campaign) {
            throw ValidationException::withMessages(['campaign_id' => 'برای تبدیل پیشنهاد اسپانسر باید کمپین مشخص شود.']);
        }

        $sponsor = $proposal->sponsorAccount;

        if ($sponsor->venue_id !== null && $sponsor->venue_id !== $campaign->venue_id) {
            throw ValidationException::withMessages(['campaign_id' => 'کمپین انتخاب‌شده با مکان اسپانسر هم‌خوان نیست.']);
        }

        $partners = $this->proposalPartnerAccounts($proposal);

        foreach ($partners as $partner) {
            if ($partner->venue_id !== $campaign->venue_id) {
                throw ValidationException::withMessages(['proposal' => 'واحدهای هدف پیشنهاد باید به مکان کمپین انتخاب‌شده تعلق داشته باشند.']);
            }
        }

        return DB::transaction(function () use ($actor, $campaign, $data, $partners, $proposal, $sponsor): SponsorProposalActivation {
            $sponsorship = $this->storeSponsorship([
                'campaign_id' => $campaign->id,
                'sponsor_account_id' => $sponsor->id,
                'sponsorship_goal' => $this->sponsorshipGoalForProposal($proposal->objective),
                'package_type' => $this->sponsorshipPackageForProposal($proposal->proposal_type),
                'status' => 'draft',
                'budget_amount' => $proposal->proposed_budget_amount,
                'contract_value' => $proposal->estimated_value_amount,
                'notes' => trim((string) ($proposal->notes ?? '')."\n".(string) ($data['activation_notes'] ?? '')) ?: null,
            ]);

            $assignmentIds = [];
            foreach ($partners as $partner) {
                $assignment = $this->storePartnerAssignment([
                    'sponsor_account_id' => $sponsor->id,
                    'partner_account_id' => $partner->id,
                    'campaign_id' => $campaign->id,
                    'activation_role' => $this->activationRoleForProposal($proposal),
                    'status' => 'draft',
                    'notes' => 'Created from sponsor proposal '.$proposal->code,
                ]);
                $assignmentIds[] = $assignment->id;
            }

            $activation = SponsorProposalActivation::query()->updateOrCreate(
                ['sponsor_proposal_id' => $proposal->id],
                [
                    'campaign_id' => $campaign->id,
                    'campaign_sponsorship_id' => $sponsorship->id,
                    'status' => 'ready_for_campaign_design',
                    'partner_assignment_ids' => $assignmentIds,
                    'metadata' => [
                        'source' => 'sponsor_proposal_activation',
                        'activated_by_user_id' => $actor->id,
                        'activated_at' => now()->toIso8601String(),
                        'activation_notes' => $data['activation_notes'] ?? null,
                    ],
                ],
            );

            $rewardIds = [];
            $treasureIds = [];
            foreach ($proposal->items as $index => $item) {
                $reward = $this->upsertRewardFromSponsorItem($activation, $proposal, $item, $campaign, $sponsor, $index);
                $rewardIds[] = $reward->id;
                $treasureIds[] = $this->upsertTreasureFromSponsorItem($activation, $proposal, $item, $reward, $campaign, $sponsor, $index)->id;
            }

            $activation->update(['reward_definition_ids' => $rewardIds, 'treasure_ids' => $treasureIds]);
            $proposal->update([
                'campaign_id' => $campaign->id,
                'metadata' => array_merge($proposal->metadata ?? [], [
                    'activation_status' => 'ready_for_campaign_design',
                    'activation_id' => $activation->id,
                    'campaign_sponsorship_id' => $sponsorship->id,
                    'reward_definition_ids' => $rewardIds,
                    'treasure_ids' => $treasureIds,
                    'partner_assignment_ids' => $assignmentIds,
                ]),
            ]);

            return $activation->refresh();
        });
    }

    private function upsertRewardFromSponsorItem(
        SponsorProposalActivation $activation,
        SponsorProposal $proposal,
        SponsorProposalItem $item,
        Campaign $campaign,
        SponsorAccount $sponsor,
        int $index,
    ): RewardDefinition {
        $targetPartnerIds = $item->target_partner_account_ids ?? [];
        $partnerId = count($targetPartnerIds) === 1 ? $targetPartnerIds[0] : null;
        $code = $this->rewardCodeForSponsorItem($proposal, $item, $index);
        $metadata = [
            'source' => 'sponsor_proposal_activation',
            'source_type' => 'sponsor',
            'approval_status' => 'approved',
            'availability_status' => 'pending_campaign_assignment',
            'sponsor_account_id' => $sponsor->id,
            'sponsor_account_code' => $sponsor->code,
            'sponsor_proposal_id' => $proposal->id,
            'sponsor_proposal_code' => $proposal->code,
            'sponsor_proposal_activation_id' => $activation->id,
            'sponsor_proposal_item_id' => $item->id,
            'reward_tier' => null,
            'reward_option' => $item->item_type,
            'target_partner_account_ids' => $targetPartnerIds,
            'partner_allocations' => $item->partner_allocations ?? [],
            'description' => $item->description,
            'terms' => 'قابل اتصال به ماموریت یا سطح پاداش کمپین توسط ادمین.',
        ];

        return RewardDefinition::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'code' => $code],
            [
                'venue_id' => $campaign->venue_id,
                'partner_account_id' => $partnerId,
                'name' => $item->title,
                'reward_type' => $this->rewardTypeForSponsorItem($item->item_type),
                'point_cost' => null,
                'stock_quantity' => $item->quantity,
                'status' => 'draft',
                'metadata' => $metadata,
            ],
        );
    }

    private function upsertTreasureFromSponsorItem(
        SponsorProposalActivation $activation,
        SponsorProposal $proposal,
        SponsorProposalItem $item,
        RewardDefinition $reward,
        Campaign $campaign,
        SponsorAccount $sponsor,
        int $index,
    ): Treasure {
        $code = $this->treasureCodeForSponsorItem($proposal, $item, $index);

        return Treasure::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'code' => $code],
            [
                'venue_id' => $campaign->venue_id,
                'mission_instance_id' => null,
                'name' => $item->title,
                'treasure_type' => $this->treasureTypeForSponsorItem($item->item_type),
                'status' => 'draft',
                'reveal_rule' => [
                    'mode' => 'admin_assign_to_mission',
                    'reward_definition_id' => $reward->id,
                    'target_partner_account_ids' => $item->target_partner_account_ids ?? [],
                ],
                'metadata' => [
                    'source' => 'sponsor_proposal_activation',
                    'source_type' => 'sponsor',
                    'sponsor_account_id' => $sponsor->id,
                    'sponsor_account_code' => $sponsor->code,
                    'sponsor_proposal_id' => $proposal->id,
                    'sponsor_proposal_code' => $proposal->code,
                    'sponsor_proposal_activation_id' => $activation->id,
                    'sponsor_proposal_item_id' => $item->id,
                    'reward_definition_id' => $reward->id,
                    'treasure_tier' => null,
                    'reveal_description' => $item->description,
                    'discovery_hint' => 'این گنج از پیشنهاد اسپانسر آمده و باید به ماموریت یا گام چرخه وصل شود.',
                    'partner_allocations' => $item->partner_allocations ?? [],
                ],
            ],
        );
    }

    /** @return Collection<int, PartnerAccount> */
    private function proposalPartnerAccounts(SponsorProposal $proposal)
    {
        $partnerIds = $proposal->partnerAccounts
            ->pluck('partner_account_id')
            ->merge($proposal->items->flatMap(fn (SponsorProposalItem $item): array => $item->target_partner_account_ids ?? []))
            ->merge($proposal->items->flatMap(fn (SponsorProposalItem $item): array => collect($item->partner_allocations ?? [])->pluck('partner_account_id')->all()))
            ->filter()
            ->unique()
            ->values();

        return PartnerAccount::query()
            ->whereIn('id', $partnerIds)
            ->orderBy('name')
            ->get();
    }

    private function rewardCodeForSponsorItem(SponsorProposal $proposal, SponsorProposalItem $item, int $index): string
    {
        $proposalPart = Str::limit(Str::slug($proposal->code, '-'), 48, '');
        $itemPart = Str::limit(Str::slug($item->title, '-'), 28, '');
        $suffix = substr(str_replace('-', '', $item->id), 0, 8) ?: (string) ($index + 1);

        return Str::limit("sp-{$proposalPart}-{$itemPart}-{$suffix}", 96, '');
    }

    private function treasureCodeForSponsorItem(SponsorProposal $proposal, SponsorProposalItem $item, int $index): string
    {
        return Str::replaceStart('sp-', 'sp-treasure-', $this->rewardCodeForSponsorItem($proposal, $item, $index));
    }

    private function rewardTypeForSponsorItem(string $itemType): string
    {
        return match ($itemType) {
            'discount' => 'sponsor_discount',
            'product', 'sample' => 'sponsor_product',
            'media' => 'sponsor_media',
            'content' => 'sponsor_content',
            'cash_support' => 'sponsor_cash_support',
            default => 'sponsor_reward',
        };
    }

    private function treasureTypeForSponsorItem(string $itemType): string
    {
        return match ($itemType) {
            'content', 'media' => 'sponsor_story_treasure',
            'discount' => 'sponsor_discount_treasure',
            default => 'sponsor_reward_treasure',
        };
    }

    private function activationRoleForProposal(SponsorProposal $proposal): string
    {
        $itemTypes = $proposal->items->pluck('item_type')->all();

        if (in_array('discount', $itemTypes, true)) {
            return 'discount_redemption';
        }

        if (array_intersect(['product', 'sample'], $itemTypes) !== []) {
            return 'product_sampling';
        }

        if (array_intersect(['media', 'content'], $itemTypes) !== []) {
            return 'content_delivery';
        }

        return 'reward_redemption';
    }

    private function sponsorshipGoalForProposal(string $objective): string
    {
        return in_array($objective, ['awareness', 'footfall', 'lead_generation', 'sales', 'engagement'], true)
            ? $objective
            : 'engagement';
    }

    private function sponsorshipPackageForProposal(string $proposalType): string
    {
        return match ($proposalType) {
            'display_media' => 'display_media',
            'family_challenge' => 'family_team_challenge',
            'scientific_cultural_content' => 'scientific_cultural_challenge',
            'reward_offer', 'discount_offer', 'product_sampling' => 'treasure_sponsor',
            default => 'pilot_activation',
        };
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
            'activationStatus' => $proposal->metadata['activation_status'] ?? $proposal->activation?->status,
            'activation' => $proposal->activation ? [
                'id' => $proposal->activation->id,
                'status' => $proposal->activation->status,
                'rewardDefinitionIds' => $proposal->activation->reward_definition_ids ?? [],
                'treasureIds' => $proposal->activation->treasure_ids ?? [],
                'partnerAssignmentIds' => $proposal->activation->partner_assignment_ids ?? [],
            ] : null,
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

    /**
     * @param  array<string, mixed>  $data
     */
    private function requiredId(array $data, string $key): int|string
    {
        $value = $data[$key] ?? null;

        if (! is_int($value) && ! is_string($value)) {
            throw ValidationException::withMessages([$key => 'شناسه انتخاب‌شده معتبر نیست.']);
        }

        return $value;
    }

    /** @param array<string, mixed> $data */
    private function optionalString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value)) {
            throw ValidationException::withMessages([$key => 'شناسه انتخاب‌شده معتبر نیست.']);
        }

        return $value;
    }
}
