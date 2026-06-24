<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Models\PartnerAccount;
use App\Models\PartnerUser;
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
            ->orderBy('created_at')
            ->get()
            ->map(fn ($reward): array => [
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
            ]);
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

        return [
            'partner' => [
                'id' => $partner->id,
                'code' => $partner->code,
                'name' => $partner->name,
                'partnerType' => $partner->partner_type,
                'venueName' => $partner->venue?->name,
            ],
            'stats' => [
                'rewardDefinitions' => $rewardDefinitions->count(),
                'issuedRewards' => $rewardDefinitions->sum('userRewardsCount'),
                'pendingRedemptions' => $redemptions->where('status', 'pending')->count(),
                'confirmedRedemptions' => $redemptions->where('status', 'confirmed')->count(),
            ],
            'rewardDefinitions' => $rewardDefinitions,
            'redemptions' => $redemptions,
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

    private function uniqueRedemptionCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (RewardRedemption::query()->where('redemption_code', $code)->exists());

        return $code;
    }
}
