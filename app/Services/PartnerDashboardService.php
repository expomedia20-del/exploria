<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\PartnerAccount;
use App\Models\PartnerUser;
use App\Models\RewardDefinition;
use App\Models\RewardRedemption;
use App\Models\User;
use App\Models\UserReward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PartnerDashboardService
{
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

        $partnerUser = PartnerUser::query()
            ->with('partnerAccount.venue:id,code,name')
            ->where('user_id', $user->id)
            ->where('status', RecordStatus::Active)
            ->first();

        if (! $partnerUser?->partnerAccount) {
            throw ValidationException::withMessages([
                'partner' => 'برای کاربر فعلی حساب فروشگاه/شریک فعال ثبت نشده است.',
            ]);
        }

        return $partnerUser->partnerAccount;
    }

    /** @return array<string, mixed> */
    public function overview(User $user): array
    {
        $partner = $this->partnerForUser($user);
        $partner->load(['venue:id,code,name']);
        $rewardDefinitions = $partner->rewardDefinitions()
            ->with(['campaign:id,code,name'])
            ->withCount(['userRewards', 'userRewards as awarded_count' => fn ($query) => $query->where('status', 'awarded')])
            ->latest('created_at')
            ->get()
            ->map(fn (RewardDefinition $reward): array => $this->serializeRewardDefinition($reward));
        $redemptions = $partner->rewardRedemptions()
            ->with(['user:id,name,email', 'userReward.rewardDefinition:id,code,name,reward_type'])
            ->latest('created_at')
            ->limit(20)
            ->get()
            ->map(fn (RewardRedemption $redemption): array => [
                'id' => $redemption->id,
                'redemptionCode' => $redemption->redemption_code,
                'status' => $redemption->status,
                'redeemedAt' => $redemption->redeemed_at?->toIso8601String(),
                'createdAt' => $redemption->created_at?->toIso8601String(),
                'visitorName' => $redemption->user?->name,
                'rewardName' => $redemption->userReward?->rewardDefinition?->name,
                'rewardType' => $redemption->userReward?->rewardDefinition?->reward_type,
            ]);
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
                'pendingRedemptions' => $redemptions->where('status', 'pending')->count(),
                'confirmedRedemptions' => $redemptions->where('status', 'confirmed')->count(),
                'adRequests' => $adRequests->count(),
                'pendingAds' => $adRequests->where('status', 'pending_review')->count(),
                'scheduledAds' => $adRequests->where('placementStatus', 'scheduled')->count(),
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
        $campaign = $this->activeCampaignForPartner($partner);

        return DB::transaction(fn (): RewardDefinition => RewardDefinition::query()->create([
            'campaign_id' => $campaign->id,
            'venue_id' => $partner->venue_id,
            'partner_account_id' => $partner->id,
            'code' => $this->uniqueOfferCode($campaign->id, $partner->code),
            'name' => $data['name'],
            'reward_type' => $data['reward_type'],
            'point_cost' => $data['point_cost'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? null,
            'status' => RecordStatus::Draft,
            'metadata' => [
                'source' => 'partner_offer_submission',
                'approval_status' => 'pending_review',
                'submitted_by_user_id' => $partnerUser->id,
                'submitted_at' => now()->toIso8601String(),
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

        $metadata = $this->metadataArray($reward->metadata);
        $approvalStatus = $metadata['approval_status'] ?? $reward->status->value;
        $isPaused = $data['availability_status'] === 'paused';

        $status = $reward->status;
        if ($approvalStatus === 'approved') {
            $status = $isPaused ? RecordStatus::Inactive : RecordStatus::Active;
        }

        $reward->update([
            'point_cost' => $data['point_cost'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? null,
            'status' => $status,
            'metadata' => [
                ...$metadata,
                'availability_status' => $data['availability_status'],
                'is_paused' => $isPaused,
                'available_from' => $data['available_from'] ?? null,
                'available_until' => $data['available_until'] ?? null,
                'description' => $data['description'] ?? ($metadata['description'] ?? null),
                'terms' => $data['terms'] ?? ($metadata['terms'] ?? null),
                'updated_by_partner_user_id' => $partnerUser->id,
                'partner_updated_at' => now()->toIso8601String(),
            ],
        ]);

        return $reward->fresh(['campaign:id,code,name']) ?? $reward;
    }

    /** @return array<string, mixed> */
    public function serializeRewardDefinition(RewardDefinition $reward): array
    {
        return [
            'id' => $reward->id,
            'code' => $reward->code,
            'name' => $reward->name,
            'rewardType' => $reward->reward_type,
            'status' => $reward->status->value,
            'pointCost' => $reward->point_cost,
            'stockQuantity' => $reward->stock_quantity,
            'userRewardsCount' => (int) $reward->getAttribute('user_rewards_count'),
            'awardedCount' => (int) $reward->getAttribute('awarded_count'),
            'campaignName' => $reward->campaign?->name,
            'approvalStatus' => $reward->metadata['approval_status'] ?? $reward->status->value,
            'availabilityStatus' => $reward->metadata['availability_status'] ?? ($reward->status === RecordStatus::Inactive ? 'paused' : 'active'),
            'availableFrom' => $reward->metadata['available_from'] ?? null,
            'availableUntil' => $reward->metadata['available_until'] ?? null,
            'description' => $reward->metadata['description'] ?? null,
            'terms' => $reward->metadata['terms'] ?? null,
            'reviewNotes' => $reward->metadata['review_notes'] ?? null,
        ];
    }

    public function ensureRedemptionForReward(UserReward $userReward): RewardRedemption
    {
        $userReward->loadMissing('rewardDefinition');

        return RewardRedemption::query()->firstOrCreate(
            [
                'user_reward_id' => $userReward->id,
                'user_id' => $userReward->user_id,
            ],
            [
                'partner_account_id' => $userReward->rewardDefinition?->partner_account_id,
                'redemption_code' => $this->uniqueRedemptionCode(),
                'status' => 'pending',
                'metadata' => ['source' => 'reward_awarded'],
            ],
        );
    }

    public function confirmRedemption(User $partnerUser, string $redemptionCode): RewardRedemption
    {
        $partner = $this->partnerForUser($partnerUser);

        return DB::transaction(function () use ($partner, $redemptionCode): RewardRedemption {
            $redemption = RewardRedemption::query()
                ->with('userReward')
                ->where('redemption_code', Str::upper($redemptionCode))
                ->lockForUpdate()
                ->first();

            if (! $redemption || $redemption->partner_account_id !== $partner->id) {
                throw ValidationException::withMessages([
                    'redemption_code' => 'کد مصرف برای این فروشگاه معتبر نیست.',
                ]);
            }

            if ($redemption->status === 'confirmed') {
                return $redemption;
            }

            if ($redemption->status !== 'pending') {
                throw ValidationException::withMessages([
                    'redemption_code' => 'این پاداش در وضعیت قابل مصرف نیست.',
                ]);
            }

            $redemption->update([
                'status' => 'confirmed',
                'redeemed_at' => now(),
                'metadata' => [
                    ...($redemption->metadata ?? []),
                    'confirmed_by_partner_id' => $partner->id,
                ],
            ]);
            $redemption->userReward?->update(['status' => 'redeemed']);

            return $redemption;
        });
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

    private function activeCampaignForPartner(PartnerAccount $partner): Campaign
    {
        $campaign = Campaign::query()
            ->where('venue_id', $partner->venue_id)
            ->where('status', RecordStatus::Active)
            ->latest('created_at')
            ->first();

        if (! $campaign) {
            throw ValidationException::withMessages([
                'campaign' => 'برای مکان این فروشگاه کمپین فعال ثبت نشده است.',
            ]);
        }

        return $campaign;
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
