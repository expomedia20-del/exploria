<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Enums\RewardInventoryMode;
use App\Models\CampaignSponsorship;
use App\Models\FinancialAccount;
use App\Models\PartnerAccount;
use App\Models\RewardDefinition;
use App\Models\RewardInventoryAllocation;
use App\Models\SponsorAccount;
use App\Models\User;
use App\Models\UserReward;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RewardGovernanceService
{
    public function assertOperationalReadiness(RewardDefinition $reward): RewardDefinition
    {
        return DB::transaction(function () use ($reward): RewardDefinition {
            $lockedReward = RewardDefinition::query()->lockForUpdate()->findOrFail($reward->id);
            $this->ensureDirectPartnerAllocation($lockedReward);
            $lockedReward->load(['costOwnerFinancialAccount', 'inventoryAllocations.partnerAccount']);
            $this->assertConfiguration($lockedReward);

            return $lockedReward;
        });
    }

    public function activate(RewardDefinition $reward, User $actor, ?string $reviewNotes = null): RewardDefinition
    {
        return DB::transaction(function () use ($actor, $reviewNotes, $reward): RewardDefinition {
            $lockedReward = RewardDefinition::query()->lockForUpdate()->findOrFail($reward->id);
            $this->ensureDirectPartnerAllocation($lockedReward);
            $lockedReward->load(['costOwnerFinancialAccount', 'inventoryAllocations.partnerAccount']);
            $this->assertConfiguration($lockedReward);

            if ($lockedReward->inventory_mode === RewardInventoryMode::Finite) {
                $lockedReward->inventoryAllocations()
                    ->whereIn('status', ['planned', RecordStatus::Active->value])
                    ->update(['status' => RecordStatus::Active->value]);
            }

            $lockedReward->update([
                'status' => RecordStatus::Active,
                'metadata' => [
                    ...($lockedReward->metadata ?? []),
                    'approval_status' => 'approved',
                    'approved_by_user_id' => $actor->id,
                    'approved_at' => now()->toIso8601String(),
                    'review_notes' => $reviewNotes,
                ],
            ]);

            return $lockedReward->refresh()->load(['costOwnerFinancialAccount', 'inventoryAllocations.partnerAccount']);
        });
    }

    /** @param array<string, mixed> $metadata */
    public function issue(User $user, RewardDefinition $reward, array $metadata): UserReward
    {
        return DB::transaction(function () use ($metadata, $reward, $user): UserReward {
            $lockedReward = RewardDefinition::query()
                ->with(['campaign:id,status', 'costOwnerFinancialAccount', 'inventoryAllocations.partnerAccount'])
                ->lockForUpdate()
                ->findOrFail($reward->id);

            $existing = UserReward::query()
                ->where('user_id', $user->id)
                ->where('reward_definition_id', $lockedReward->id)
                ->where('campaign_id', $lockedReward->campaign_id)
                ->first();

            if ($existing) {
                return $existing;
            }

            $this->assertConfiguration($lockedReward);
            $this->assertIssuanceWindow($lockedReward);

            if ($lockedReward->campaign?->status !== RecordStatus::Active) {
                throw ValidationException::withMessages(['campaign' => 'کمپین این پاداش فعال نیست و صدور جدید متوقف است.']);
            }

            if ($lockedReward->status !== RecordStatus::Active) {
                throw ValidationException::withMessages(['reward' => 'این پاداش در وضعیت قابل صدور نیست.']);
            }

            if ($lockedReward->per_user_award_limit !== 1) {
                throw ValidationException::withMessages(['per_user_award_limit' => 'در نسخه فعلی، سقف صدور هر پاداش برای هر کاربر باید دقیقاً یک باشد.']);
            }

            if (
                $lockedReward->inventory_mode === RewardInventoryMode::Finite
                && $lockedReward->userRewards()->count() >= (int) $lockedReward->stock_quantity
            ) {
                throw ValidationException::withMessages(['stock_quantity' => 'ظرفیت کل این پاداش تکمیل شده است.']);
            }

            if ($lockedReward->inventory_mode === RewardInventoryMode::Finite && ! $this->hasAvailableInventory($lockedReward)) {
                throw ValidationException::withMessages(['stock_quantity' => 'موجودی قابل رزرو برای این پاداش وجود ندارد.']);
            }

            $costOwner = $lockedReward->costOwnerFinancialAccount;
            $expiresAt = $this->issuanceExpiry($lockedReward);

            return UserReward::query()->create([
                'user_id' => $user->id,
                'reward_definition_id' => $lockedReward->id,
                'campaign_id' => $lockedReward->campaign_id,
                'status' => 'awarded',
                'awarded_at' => now(),
                'expires_at' => $expiresAt,
                'metadata' => [
                    ...$metadata,
                    'cost_owner_financial_account_id' => $costOwner?->id,
                    'cost_owner_account_key' => $costOwner?->account_key,
                    'cost_owner_account_type' => $costOwner?->account_type,
                    'inventory_mode' => $lockedReward->inventory_mode?->value,
                    'issued_at' => now()->toIso8601String(),
                ],
            ]);
        });
    }

    public function assertCanRedeem(UserReward $userReward): void
    {
        $userReward->loadMissing('rewardDefinition');

        if ($userReward->status !== 'awarded') {
            throw ValidationException::withMessages(['redemption_code' => 'این پاداش در وضعیت قابل مصرف نیست.']);
        }

        if ($userReward->expires_at === null || ! $userReward->expires_at->isFuture()) {
            throw ValidationException::withMessages(['redemption_code' => 'مهلت مصرف این پاداش به پایان رسیده است.']);
        }

        if ($userReward->rewardDefinition?->inventory_mode !== RewardInventoryMode::Finite) {
            throw ValidationException::withMessages([
                'redemption_code' => 'پاداش «'.$userReward->rewardDefinition->code.'» نیازمند مصرف در واحد تجاری نیست.',
            ]);
        }
    }

    public function isRedeemable(RewardDefinition $reward): bool
    {
        return $reward->inventory_mode === RewardInventoryMode::Finite;
    }

    /** @return array<string, mixed> */
    public function issuanceEventPayload(UserReward $userReward): array
    {
        $userReward->loadMissing('rewardDefinition', 'redemptions');

        return [
            'reward_id' => $userReward->reward_definition_id,
            'user_id' => $userReward->user_id,
            'merchant_id' => $userReward->redemptions->first()?->partner_account_id,
            'cost_owner' => data_get($userReward->metadata, 'cost_owner_account_key'),
            'cost_owner_financial_account_id' => data_get($userReward->metadata, 'cost_owner_financial_account_id'),
            'expiry_time' => $userReward->expires_at?->toIso8601String(),
            'inventory_mode' => data_get($userReward->metadata, 'inventory_mode'),
        ];
    }

    public function matchingPartnerFinancialAccount(PartnerAccount $partner): ?FinancialAccount
    {
        return FinancialAccount::query()
            ->where('status', RecordStatus::Active->value)
            ->where('account_type', 'partner')
            ->where('owner_reference_type', 'partner_code')
            ->where('owner_reference_id', $partner->code)
            ->first();
    }

    /** @return array<string, string> */
    public function configurationErrors(RewardDefinition $reward): array
    {
        $reward->loadMissing(['costOwnerFinancialAccount', 'inventoryAllocations.partnerAccount']);

        $errors = [];
        $costOwner = $reward->costOwnerFinancialAccount;

        if (! $costOwner || $costOwner->status !== RecordStatus::Active->value) {
            $errors['cost_owner_financial_account_id'] = 'برای فعال‌سازی پاداش، حساب مالی فعال مسئول هزینه الزامی است.';
        }

        if (! $reward->inventory_mode) {
            $errors['inventory_mode'] = 'نوع کنترل موجودی پاداش باید مشخص شود.';
        }

        if (! $reward->available_from || ! $reward->available_until || ! $reward->available_until->greaterThan($reward->available_from)) {
            $errors['available_until'] = 'بازه معتبر شروع و پایان پاداش الزامی است.';
        }

        if ($reward->per_user_award_limit !== 1) {
            $errors['per_user_award_limit'] = 'سقف صدور هر پاداش برای هر کاربر در MVP باید یک باشد.';
        }

        if ($costOwner && ! $this->costOwnerMatchesReward($reward, $costOwner)) {
            $errors['cost_owner_financial_account_id'] = 'حساب مالی مسئول هزینه با تأمین‌کننده یا اسپانسر همین کمپین تطابق ندارد.';
        }

        if ($reward->inventory_mode === RewardInventoryMode::Finite) {
            $allocations = $reward->inventoryAllocations;
            $allocated = (int) $allocations->sum('allocated_quantity');

            if (! $reward->stock_quantity || $reward->stock_quantity < 1) {
                $errors['stock_quantity'] = 'پاداش موجودی‌محور باید ظرفیت کل مثبت داشته باشد.';
            }

            if ($reward->expires_after_minutes === null || $reward->expires_after_minutes < 1) {
                $errors['expires_after_minutes'] = 'مهلت مصرف پاداش موجودی‌محور باید مشخص شود.';
            }

            if ($allocations->isEmpty() || $allocations->contains(fn (RewardInventoryAllocation $allocation): bool => $allocation->partnerAccount?->status !== RecordStatus::Active)) {
                $errors['partner_account_id'] = 'پاداش موجودی‌محور باید تأمین‌کننده فعال داشته باشد.';
            }

            if ($reward->stock_quantity && $allocated !== $reward->stock_quantity) {
                $errors['stock_quantity'] = 'ظرفیت کل پاداش باید با مجموع تخصیص تأمین‌کنندگان برابر باشد.';
            }
        }

        if ($reward->inventory_mode === RewardInventoryMode::NonInventory) {
            if ($reward->stock_quantity !== null || $reward->inventoryAllocations->isNotEmpty()) {
                $errors['inventory_mode'] = 'پاداش غیرموجودی نباید ظرفیت یا تخصیص تأمین‌کننده داشته باشد.';
            }
        }

        return $errors;
    }

    private function assertConfiguration(RewardDefinition $reward): void
    {
        $errors = $this->configurationErrors($reward);

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function assertIssuanceWindow(RewardDefinition $reward): void
    {
        $now = now();

        if (! $reward->available_from || ! $reward->available_until || $reward->available_from->isFuture() || ! $reward->available_until->isFuture()) {
            throw ValidationException::withMessages(['reward' => 'این پاداش خارج از بازه مجاز صدور است.']);
        }

        if ($reward->available_until->lessThanOrEqualTo($now)) {
            throw ValidationException::withMessages(['reward' => 'مهلت صدور این پاداش به پایان رسیده است.']);
        }
    }

    private function issuanceExpiry(RewardDefinition $reward): CarbonImmutable
    {
        $windowExpiry = CarbonImmutable::instance($reward->available_until);

        if (! $reward->expires_after_minutes) {
            return $windowExpiry;
        }

        $relativeExpiry = CarbonImmutable::now()->addMinutes($reward->expires_after_minutes);

        return $relativeExpiry->lessThan($windowExpiry) ? $relativeExpiry : $windowExpiry;
    }

    private function ensureDirectPartnerAllocation(RewardDefinition $reward): void
    {
        if (
            $reward->inventory_mode !== RewardInventoryMode::Finite
            || ! $reward->partner_account_id
            || ! $reward->stock_quantity
        ) {
            return;
        }

        $allocations = $reward->inventoryAllocations()->lockForUpdate()->get();

        if ($allocations->isNotEmpty()) {
            $allocation = $allocations->count() === 1 ? $allocations->first() : null;
            $isDirectPartnerOffer = ($reward->metadata['source'] ?? null) === 'partner_offer_submission';

            if ($allocation && $isDirectPartnerOffer && $allocation->partner_account_id === $reward->partner_account_id) {
                $used = $allocation->reserved_quantity + $allocation->redeemed_quantity;

                if ($reward->stock_quantity < $used) {
                    throw ValidationException::withMessages([
                        'stock_quantity' => 'ظرفیت کل نمی‌تواند از مجموع رزروشده و مصرف‌شده کمتر باشد.',
                    ]);
                }

                $allocation->update(['allocated_quantity' => $reward->stock_quantity]);
            }

            return;
        }

        RewardInventoryAllocation::query()->create([
            'reward_definition_id' => $reward->id,
            'campaign_id' => $reward->campaign_id,
            'partner_account_id' => $reward->partner_account_id,
            'allocated_quantity' => $reward->stock_quantity,
            'reserved_quantity' => 0,
            'redeemed_quantity' => 0,
            'status' => 'planned',
            'metadata' => ['source' => 'reward_governance_activation'],
        ]);
    }

    private function hasAvailableInventory(RewardDefinition $reward): bool
    {
        return $reward->inventoryAllocations()
            ->where('status', RecordStatus::Active->value)
            ->whereRaw('allocated_quantity > reserved_quantity + redeemed_quantity')
            ->exists();
    }

    private function costOwnerMatchesReward(RewardDefinition $reward, FinancialAccount $account): bool
    {
        if ($account->account_type === 'platform' || $account->account_type === 'venue') {
            return true;
        }

        if ($account->account_type === 'partner') {
            if ($account->owner_reference_type !== 'partner_code' || ! $account->owner_reference_id) {
                return false;
            }

            $partnerIds = $reward->inventoryAllocations->pluck('partner_account_id')
                ->when($reward->partner_account_id, fn ($ids) => $ids->push($reward->partner_account_id))
                ->unique();

            return PartnerAccount::query()
                ->whereIn('id', $partnerIds)
                ->where('code', $account->owner_reference_id)
                ->exists();
        }

        if ($account->account_type === 'sponsor') {
            if ($account->owner_reference_type !== 'sponsor_code' || ! $account->owner_reference_id) {
                return false;
            }

            $sponsor = SponsorAccount::query()
                ->where('code', $account->owner_reference_id)
                ->where('status', RecordStatus::Active->value)
                ->first();

            return $sponsor !== null && CampaignSponsorship::query()
                ->where('campaign_id', $reward->campaign_id)
                ->where('sponsor_account_id', $sponsor->id)
                ->where('status', RecordStatus::Active->value)
                ->exists();
        }

        return false;
    }
}
