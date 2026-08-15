<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\PartnerAccount;
use App\Models\PartnerUser;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\UserReward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PartnerDashboardService
{
    public function __construct(
        private readonly UserAccessScopeService $accessScopes,
        private readonly MissionRewardBlueprintService $blueprints,
        private readonly CampaignBlueprintConsistencyService $blueprintConsistency,
        private readonly RewardGovernanceService $rewardGovernance,
    ) {}

    public function partnerForUser(User $user): PartnerAccount
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::Operator], true)) {
            $partner = PartnerAccount::query()
                ->with('venue:id,code,name')
                ->where('status', RecordStatus::Active)
                ->orderBy('created_at')
                ->first();

            if ($partner) {
                return $partner;
            }
        }

        $partnerIds = $this->accessScopes->partnerIds($user);
        $scopedPartner = PartnerAccount::query()
            ->with('venue:id,code,name')
            ->whereIn('id', $partnerIds)
            ->where('status', RecordStatus::Active)
            ->orderBy('created_at')
            ->first();

        if ($scopedPartner) {
            return $scopedPartner;
        }

        $partnerUser = PartnerUser::query()
            ->with('partnerAccount.venue:id,code,name')
            ->where('user_id', $user->id)
            ->where('status', RecordStatus::Active)
            ->first();

        if (! $partnerUser?->partnerAccount) {
            throw ValidationException::withMessages([
                'partner' => 'برای کاربر فعلی حساب فروشگاه/واحد تجاری فعال ثبت نشده است.',
            ]);
        }

        return $partnerUser->partnerAccount;
    }

    /** @return array<string, mixed> */
    public function overview(User $user, mixed $campaignCode = null): array
    {
        $partner = $this->partnerForUser($user);
        $partner->load(['venue:id,code,name']);
        $proposalCampaign = $this->proposalCampaignForPartner($partner, is_string($campaignCode) ? $campaignCode : null);
        $blueprintCode = $proposalCampaign?->metadata['blueprint_code'] ?? null;
        $blueprint = $this->blueprints->handoff(is_string($blueprintCode) ? $blueprintCode : null);
        $missionPlan = $blueprint['missionPlan'] ?? [];
        $rewardTiers = $blueprint['rewardDesign']['tiers'] ?? [];
        $rewardDefinitions = $partner->rewardDefinitions()
            ->with([
                'campaign:id,code,name',
                'inventoryAllocations' => fn ($query) => $query->where('partner_account_id', $partner->id),
            ])
            ->withCount(['userRewards', 'userRewards as awarded_count' => fn ($query) => $query->where('status', 'awarded')])
            ->latest('created_at')
            ->get()
            ->map(fn (RewardDefinition $reward): array => $this->serializeRewardDefinition($reward));
        $redemptionStatusCounts = $partner->rewardRedemptions()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $inventoryAllocations = RewardInventoryAllocation::query()
            ->where('partner_account_id', $partner->id)
            ->get();
        $inventorySummary = [
            'allocated' => (int) $inventoryAllocations->sum('allocated_quantity'),
            'reserved' => (int) $inventoryAllocations->sum('reserved_quantity'),
            'redeemed' => (int) $inventoryAllocations->sum('redeemed_quantity'),
            'remaining' => (int) $inventoryAllocations->sum(
                fn (RewardInventoryAllocation $allocation): int => max(
                    0,
                    $allocation->allocated_quantity - $allocation->reserved_quantity - $allocation->redeemed_quantity,
                ),
            ),
        ];
        $conversionEvidence = $partner->rewardRedemptions()
            ->whereIn('status', ['confirmed', 'redeemed'])
            ->get(['id', 'metadata']);
        $confirmedPurchases = $conversionEvidence
            ->filter(fn (RewardRedemption $redemption): bool => (bool) data_get($redemption->metadata, 'purchase_confirmed', false));
        $attributedSalesIrr = (int) $confirmedPurchases
            ->sum(fn (RewardRedemption $redemption): int => (int) data_get($redemption->metadata, 'purchase_amount_irr', 0));
        $redemptions = $partner->rewardRedemptions()
            ->with(['user:id,name,email', 'userReward.rewardDefinition.campaign:id,code,name'])
            ->latest('created_at')
            ->limit(20)
            ->get()
            ->map(function (RewardRedemption $redemption): array {
                $reward = $redemption->userReward?->rewardDefinition;

                return [
                    'id' => $redemption->id,
                    'redemptionCode' => $redemption->redemption_code,
                    'status' => $redemption->status,
                    'redeemedAt' => $redemption->redeemed_at?->toIso8601String(),
                    'createdAt' => $redemption->created_at?->toIso8601String(),
                    'visitorName' => $redemption->user?->name,
                    'rewardName' => $reward?->name,
                    'rewardCode' => $reward?->code,
                    'rewardType' => $reward?->reward_type,
                    'campaignName' => $reward?->campaign?->name,
                    'campaignCode' => $reward?->campaign?->code,
                    'conversionType' => $redemption->metadata['conversion_type'] ?? 'reward_only',
                    'purchaseConfirmed' => (bool) ($redemption->metadata['purchase_confirmed'] ?? false),
                    'purchaseAmountIrr' => isset($redemption->metadata['purchase_amount_irr'])
                        ? (int) $redemption->metadata['purchase_amount_irr']
                        : null,
                    'receiptReference' => $redemption->metadata['receipt_reference'] ?? null,
                ];
            });
        $adRequests = $partner->adRequests()
            ->with(['hub:id,code,name', 'placements.displayDevice:id,code,name,device_type', 'creatives:id,ad_request_id,creative_type,status'])
            ->withCount([
                'events as impressions_count' => fn ($query) => $query->where('event_type', 'impression'),
                'events as clicks_count' => fn ($query) => $query->where('event_type', 'click'),
            ])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function (AdRequest $adRequest): array {
                $placement = $adRequest->placements->first();

                return [
                    'id' => $adRequest->id,
                    'code' => $adRequest->code,
                    'title' => $adRequest->title,
                    'status' => $adRequest->status,
                    'adType' => $adRequest->ad_type,
                    'creativeType' => $adRequest->creatives->first()?->creative_type,
                    'placementType' => $placement?->placement_type,
                    'placementStatus' => $placement?->status,
                    'displayDeviceName' => $placement?->displayDevice?->name,
                    'displayDeviceCode' => $placement?->displayDevice?->code,
                    'hubName' => $adRequest->hub?->name,
                    'startsAt' => $placement?->starts_at?->toIso8601String(),
                    'endsAt' => $placement?->ends_at?->toIso8601String(),
                    'impressionsCount' => (int) $adRequest->getAttribute('impressions_count'),
                    'clicksCount' => (int) $adRequest->getAttribute('clicks_count'),
                ];
            });

        return [
            'partner' => [
                'id' => $partner->id,
                'code' => $partner->code,
                'name' => $partner->name,
                'partnerType' => $partner->partner_type,
                'venueName' => $partner->venue?->name,
                'contactName' => $partner->contact_name,
                'contactMobile' => $partner->contact_mobile,
                'category' => $partner->metadata['category'] ?? null,
                'operatingNotes' => $partner->metadata['operating_notes'] ?? null,
                'displayVisibility' => (bool) ($partner->metadata['display_visibility'] ?? true),
            ],
            'stats' => [
                'rewardDefinitions' => $rewardDefinitions->count(),
                'issuedRewards' => $rewardDefinitions->sum('userRewardsCount'),
                'pendingRedemptions' => (int) ($redemptionStatusCounts->get('pending') ?? 0),
                'confirmedRedemptions' => (int) ($redemptionStatusCounts->get('confirmed') ?? 0),
                'attributedVisits' => $conversionEvidence->count(),
                'confirmedPurchases' => $confirmedPurchases->count(),
                'attributedSalesIrr' => $attributedSalesIrr,
                'allocatedInventory' => $inventorySummary['allocated'],
                'reservedInventory' => $inventorySummary['reserved'],
                'redeemedInventory' => $inventorySummary['redeemed'],
                'remainingInventory' => $inventorySummary['remaining'],
                'adRequests' => $adRequests->count(),
                'pendingAds' => $adRequests->where('status', 'pending_review')->count(),
                'scheduledAds' => $adRequests->where('placementStatus', 'scheduled')->count(),
            ],
            'proposalContext' => [
                'campaign' => $proposalCampaign ? [
                    'id' => $proposalCampaign->id,
                    'code' => $proposalCampaign->code,
                    'name' => $proposalCampaign->name,
                    'status' => $proposalCampaign->status->value,
                ] : null,
                'missionPlan' => $missionPlan,
                'rewardTiers' => $rewardTiers,
            ],
            'rewardDefinitions' => $rewardDefinitions,
            'redemptions' => $redemptions,
            'adRequests' => $adRequests,
        ];
    }

    /** @param array<string, mixed> $data */
    public function updateProfile(User $user, array $data): PartnerAccount
    {
        $partner = $this->partnerForUser($user);
        $metadata = $this->metadataArray($partner->metadata);

        $partner->update([
            'name' => $data['name'],
            'contact_name' => $data['contact_name'] ?? null,
            'contact_mobile' => $data['contact_mobile'] ?? null,
            'metadata' => [
                ...$metadata,
                'category' => $data['category'] ?? null,
                'operating_notes' => $data['operating_notes'] ?? null,
                'display_visibility' => (bool) $data['display_visibility'],
                'profile_updated_by_user_id' => $user->id,
                'profile_updated_at' => now()->toIso8601String(),
            ],
        ]);

        return $partner->fresh(['venue:id,code,name']) ?? $partner;
    }

    /** @param array<string, mixed> $data */
    public function createOffer(User $partnerUser, array $data): RewardDefinition
    {
        $partner = $this->partnerForUser($partnerUser);
        $campaign = $this->proposalCampaignForPartner($partner, null, $data['campaign_id'] ?? null);

        if (! $campaign) {
            throw ValidationException::withMessages([
                'campaign_id' => 'برای مکان این فروشگاه هنوز کمپین قابل پیشنهاد ثبت نشده است.',
            ]);
        }

        $this->blueprintConsistency->assertPartnerOfferInput($campaign, $data);

        return DB::transaction(fn (): RewardDefinition => RewardDefinition::query()->create([
            'campaign_id' => $campaign->id,
            'venue_id' => $partner->venue_id,
            'partner_account_id' => $partner->id,
            'code' => $this->uniqueOfferCode($campaign->id, $partner->code),
            'name' => $data['name'],
            'reward_type' => $data['reward_type'],
            'inventory_mode' => 'finite',
            'point_cost' => $data['point_cost'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? null,
            'cost_owner_financial_account_id' => $this->rewardGovernance->matchingPartnerFinancialAccount($partner)?->id,
            'available_from' => $data['available_from'] ?? $campaign->start_at,
            'available_until' => $data['available_until'] ?? $campaign->end_at,
            'expires_after_minutes' => $data['expires_after_minutes'] ?? 10080,
            'per_user_award_limit' => 1,
            'status' => RecordStatus::Draft,
            'metadata' => [
                'source' => 'partner_offer_submission',
                'approval_status' => 'pending_review',
                'submitted_by_user_id' => $partnerUser->id,
                'submitted_at' => now()->toIso8601String(),
                'cycle_step_index' => $data['cycle_step_index'] ?? null,
                'cycle_step_label' => $data['cycle_step_label'] ?? null,
                'reward_tier' => $data['reward_tier'] ?? null,
                'reward_option' => $data['reward_option'] ?? null,
                'description' => $data['description'] ?? null,
                'terms' => $data['terms'] ?? null,
            ],
        ]));
    }

    /** @param array<string, mixed> $data */
    public function updateOffer(User $partnerUser, RewardDefinition $reward, array $data): RewardDefinition
    {
        $partner = $this->partnerForUser($partnerUser);

        if ($reward->partner_account_id !== $partner->id) {
            throw ValidationException::withMessages([
                'reward' => 'This offer cannot be updated by the current partner.',
            ]);
        }

        return DB::transaction(function () use ($data, $partner, $partnerUser, $reward): RewardDefinition {
            $lockedReward = RewardDefinition::query()->lockForUpdate()->findOrFail($reward->id);
            $metadata = $this->metadataArray($lockedReward->metadata);
            $approvalStatus = $metadata['approval_status'] ?? $lockedReward->status->value;
            $isPaused = $data['availability_status'] === 'paused';

            $status = $lockedReward->status;
            if ($approvalStatus === 'approved') {
                $status = $isPaused ? RecordStatus::Inactive : RecordStatus::Draft;
            }

            $lockedReward->update([
                'point_cost' => $data['point_cost'] ?? null,
                'stock_quantity' => $data['stock_quantity'] ?? null,
                'cost_owner_financial_account_id' => $lockedReward->cost_owner_financial_account_id ?? $this->rewardGovernance->matchingPartnerFinancialAccount($partner)?->id,
                'available_from' => $data['available_from'] ?? $lockedReward->available_from,
                'available_until' => $data['available_until'] ?? $lockedReward->available_until,
                'expires_after_minutes' => $data['expires_after_minutes'] ?? $lockedReward->expires_after_minutes ?? 10080,
                'per_user_award_limit' => 1,
                'status' => $status,
                'metadata' => [
                    ...$metadata,
                    'availability_status' => $data['availability_status'],
                    'is_paused' => $isPaused,
                    'description' => $data['description'] ?? ($metadata['description'] ?? null),
                    'terms' => $data['terms'] ?? ($metadata['terms'] ?? null),
                    'updated_by_partner_user_id' => $partnerUser->id,
                    'partner_updated_at' => now()->toIso8601String(),
                ],
            ]);

            if ($approvalStatus === 'approved' && ! $isPaused) {
                $this->rewardGovernance->assertOperationalReadiness($lockedReward);
                $lockedReward->update(['status' => RecordStatus::Active]);
            }

            return $lockedReward->fresh(['campaign:id,code,name']) ?? $lockedReward;
        });
    }

    /** @return array<string, mixed> */
    public function serializeRewardDefinition(RewardDefinition $reward): array
    {
        $inventoryAllocations = $reward->inventoryAllocations;
        $inventoryAllocated = (int) $inventoryAllocations->sum('allocated_quantity');
        $inventoryReserved = (int) $inventoryAllocations->sum('reserved_quantity');
        $inventoryRedeemed = (int) $inventoryAllocations->sum('redeemed_quantity');
        $inventoryRemaining = (int) $inventoryAllocations->sum(
            fn (RewardInventoryAllocation $allocation): int => max(
                0,
                $allocation->allocated_quantity - $allocation->reserved_quantity - $allocation->redeemed_quantity,
            ),
        );

        return [
            'id' => $reward->id,
            'code' => $reward->code,
            'name' => $reward->name,
            'rewardType' => $reward->reward_type,
            'status' => $reward->status->value,
            'pointCost' => $reward->point_cost,
            'stockQuantity' => $reward->stock_quantity,
            'inventoryMode' => $reward->inventory_mode?->value,
            'costOwnerFinancialAccountId' => $reward->cost_owner_financial_account_id,
            'expiresAfterMinutes' => $reward->expires_after_minutes,
            'perUserAwardLimit' => $reward->per_user_award_limit,
            'userRewardsCount' => (int) $reward->getAttribute('user_rewards_count'),
            'awardedCount' => (int) $reward->getAttribute('awarded_count'),
            'inventoryAllocated' => $inventoryAllocated,
            'inventoryReserved' => $inventoryReserved,
            'inventoryRedeemed' => $inventoryRedeemed,
            'inventoryRemaining' => $inventoryRemaining,
            'campaignName' => $reward->campaign?->name,
            'approvalStatus' => $reward->metadata['approval_status'] ?? $reward->status->value,
            'availabilityStatus' => $reward->metadata['availability_status'] ?? ($reward->status === RecordStatus::Inactive ? 'paused' : 'active'),
            'cycleStepIndex' => $reward->metadata['cycle_step_index'] ?? null,
            'cycleStepLabel' => $reward->metadata['cycle_step_label'] ?? null,
            'rewardTier' => $reward->metadata['reward_tier'] ?? null,
            'rewardOption' => $reward->metadata['reward_option'] ?? null,
            'availableFrom' => $reward->available_from?->toIso8601String(),
            'availableUntil' => $reward->available_until?->toIso8601String(),
            'description' => $reward->metadata['description'] ?? null,
            'terms' => $reward->metadata['terms'] ?? null,
            'reviewNotes' => $reward->metadata['review_notes'] ?? null,
        ];
    }

    public function ensureRedemptionForReward(UserReward $userReward): RewardRedemption
    {
        $userReward->loadMissing('rewardDefinition.costOwnerFinancialAccount');
        $this->rewardGovernance->assertCanRedeem($userReward);

        return DB::transaction(function () use ($userReward): RewardRedemption {
            $existing = RewardRedemption::query()
                ->where('user_reward_id', $userReward->id)
                ->where('user_id', $userReward->user_id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $allocation = $this->reserveInventoryForReward($userReward->rewardDefinition);
            $partnerId = $allocation->partner_account_id ?? $userReward->rewardDefinition?->partner_account_id;

            if (! $partnerId) {
                throw ValidationException::withMessages([
                    'reward' => 'برای این پاداش واحد تحویل یا موجودی قابل رزرو ثبت نشده است.',
                ]);
            }

            $redemption = RewardRedemption::query()->create([
                'user_reward_id' => $userReward->id,
                'user_id' => $userReward->user_id,
                'partner_account_id' => $partnerId,
                'redemption_code' => $this->uniqueRedemptionCode(),
                'status' => 'pending',
                'metadata' => [
                    'source' => 'reward_awarded',
                    'reward_inventory_allocation_id' => $allocation?->id,
                    'reserved_at' => $allocation ? now()->toIso8601String() : null,
                    'cost_owner_financial_account_id' => data_get($userReward->metadata, 'cost_owner_financial_account_id'),
                    'cost_owner_account_key' => data_get($userReward->metadata, 'cost_owner_account_key'),
                    'expires_at' => $userReward->expires_at?->toIso8601String(),
                ],
            ]);

            $userReward->update([
                'metadata' => [
                    ...($userReward->metadata ?? []),
                    'supplier_partner_account_id' => $partnerId,
                    'reward_inventory_allocation_id' => $allocation?->id,
                ],
            ]);

            return $redemption;
        });
    }

    /** @param array<string, mixed> $conversionEvidence */
    public function confirmRedemption(User $partnerUser, string $redemptionCode, array $conversionEvidence = []): RewardRedemption
    {
        $partner = $this->partnerForUser($partnerUser);

        $normalizedCode = Str::upper(trim($redemptionCode));
        $purchaseConfirmed = ($conversionEvidence['purchase_status'] ?? 'reward_only') === 'purchase_confirmed';
        $purchaseAmount = $purchaseConfirmed ? (int) ($conversionEvidence['purchase_amount'] ?? 0) : null;
        $receiptReference = $purchaseConfirmed && is_string($conversionEvidence['receipt_reference'] ?? null)
            ? trim($conversionEvidence['receipt_reference'])
            : null;

        return DB::transaction(function () use ($partner, $partnerUser, $normalizedCode, $purchaseConfirmed, $purchaseAmount, $receiptReference): RewardRedemption {
            $redemption = RewardRedemption::query()
                ->with('userReward.rewardDefinition')
                ->where('redemption_code', $normalizedCode)
                ->lockForUpdate()
                ->first();

            if (! $redemption || $redemption->partner_account_id !== $partner->id) {
                throw ValidationException::withMessages([
                    'redemption_code' => 'کد مصرف برای این فروشگاه معتبر نیست.',
                ]);
            }

            if ($redemption->status === 'confirmed') {
                throw ValidationException::withMessages([
                    'redemption_code' => 'این کد قبلا مصرف شده است.',
                ]);
            }

            if ($redemption->status !== 'pending') {
                throw ValidationException::withMessages([
                    'redemption_code' => 'این پاداش در وضعیت قابل مصرف نیست.',
                ]);
            }

            $this->rewardGovernance->assertCanRedeem($redemption->userReward);

            $allocation = $this->allocationForRedemption($redemption);

            if ($allocation) {
                $allocation->update([
                    'reserved_quantity' => max(0, $allocation->reserved_quantity - 1),
                    'redeemed_quantity' => $allocation->redeemed_quantity + 1,
                ]);
            }

            $redemption->update([
                'status' => 'confirmed',
                'redeemed_at' => now(),
                'metadata' => [
                    ...($redemption->metadata ?? []),
                    'confirmed_by_partner_id' => $partner->id,
                    'confirmed_by_user_id' => $partnerUser->id,
                    'confirmed_at' => now()->toIso8601String(),
                    'conversion_type' => $purchaseConfirmed ? 'verified_purchase' : 'reward_only',
                    'purchase_confirmed' => $purchaseConfirmed,
                    'purchase_amount_irr' => $purchaseAmount,
                    'receipt_reference' => $receiptReference,
                    'reward_inventory_allocation_id' => $allocation->id ?? ($redemption->metadata['reward_inventory_allocation_id'] ?? null),
                ],
            ]);
            $redemption->userReward?->update(['status' => 'redeemed']);

            return $redemption;
        });
    }

    private function reserveInventoryForReward(?RewardDefinition $reward): ?RewardInventoryAllocation
    {
        if (! $reward) {
            return null;
        }

        $query = RewardInventoryAllocation::query()
            ->where('reward_definition_id', $reward->id)
            ->where('status', RecordStatus::Active->value)
            ->whereRaw('allocated_quantity > reserved_quantity + redeemed_quantity')
            ->lockForUpdate();

        if ($reward->partner_account_id) {
            $query->where('partner_account_id', $reward->partner_account_id);
        }

        $allocation = $query
            ->orderByDesc(DB::raw('allocated_quantity - reserved_quantity - redeemed_quantity'))
            ->orderBy('created_at')
            ->first();

        if (! $allocation) {
            return null;
        }

        $allocation->update(['reserved_quantity' => $allocation->reserved_quantity + 1]);

        return $allocation->refresh();
    }

    private function allocationForRedemption(RewardRedemption $redemption): ?RewardInventoryAllocation
    {
        $allocationId = $redemption->metadata['reward_inventory_allocation_id'] ?? null;

        if (is_string($allocationId) && $allocationId !== '') {
            return RewardInventoryAllocation::query()
                ->whereKey($allocationId)
                ->where('partner_account_id', $redemption->partner_account_id)
                ->lockForUpdate()
                ->first();
        }

        $reward = $redemption->userReward?->rewardDefinition;

        if (! $reward) {
            return null;
        }

        return RewardInventoryAllocation::query()
            ->where('reward_definition_id', $reward->id)
            ->where('partner_account_id', $redemption->partner_account_id)
            ->where('reserved_quantity', '>', 0)
            ->lockForUpdate()
            ->first();
    }

    /** @return array<string, mixed> */
    private function metadataArray(mixed $metadata): array
    {
        return is_array($metadata) ? $metadata : [];
    }

    private function uniqueRedemptionCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (RewardRedemption::query()->where('redemption_code', $code)->exists());

        return $code;
    }

    private function proposalCampaignForPartner(PartnerAccount $partner, ?string $campaignCode = null, mixed $campaignId = null): ?Campaign
    {
        if (is_string($campaignId) && $campaignId !== '') {
            return Campaign::query()
                ->where('venue_id', $partner->venue_id)
                ->whereKey($campaignId)
                ->first();
        }

        if ($campaignCode) {
            return Campaign::query()
                ->where('venue_id', $partner->venue_id)
                ->where('code', Str::lower($campaignCode))
                ->first();
        }

        return Campaign::query()
            ->where('venue_id', $partner->venue_id)
            ->whereIn('status', [RecordStatus::Draft->value, RecordStatus::Active->value])
            ->latest('created_at')
            ->first();

    }

    private function uniqueOfferCode(string $campaignId, string $partnerCode): string
    {
        do {
            $code = Str::slug($partnerCode).'-offer-'.Str::lower(Str::random(6));
        } while (RewardDefinition::query()
            ->where('campaign_id', $campaignId)
            ->where('code', $code)
            ->exists());

        return $code;
    }
}
