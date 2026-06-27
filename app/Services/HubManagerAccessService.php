<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\RewardDefinition;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class HubManagerAccessService
{
    public function __construct(private readonly UserAccessScopeService $accessScopes) {}

    /** @return Collection<int, string> */
    public function managedHubIds(User $user): Collection
    {
        return $this->accessScopes->hubIds($user);
    }

    /** @return Collection<int, string> */
    public function managedPartnerIds(User $user): Collection
    {
        return $this->accessScopes->partnerIds($user);
    }

    /** @return Collection<int, Hub> */
    public function managedHubs(User $user): Collection
    {
        $hubIds = $this->managedHubIds($user);

        return Hub::query()
            ->with(['zone.venue:id,code,name'])
            ->whereIn('id', $hubIds)
            ->orderBy('created_at')
            ->get()
            ->toBase();
    }

    public function ensureCanReviewAdRequest(User $user, AdRequest $adRequest): void
    {
        if ($this->isPlatformReviewer($user)) {
            return;
        }

        if ($user->role !== UserRole::HubManager) {
            throw new AuthorizationException('شما اجازه بازبینی این تبلیغ را ندارید.');
        }

        $hubIds = $this->managedHubIds($user);
        $partnerIds = $this->managedPartnerIds($user);

        $isDirectHubAd = $adRequest->hub_id !== null && $hubIds->contains($adRequest->hub_id);
        $isPartnerAd = $adRequest->partner_account_id !== null && $partnerIds->contains($adRequest->partner_account_id);

        if (! $isDirectHubAd && ! $isPartnerAd) {
            throw new AuthorizationException('این تبلیغ خارج از محدوده رواق شماست.');
        }
    }

    public function ensureCanManageDisplayDevice(User $user, DisplayDevice $displayDevice): void
    {
        if ($this->isPlatformReviewer($user)) {
            return;
        }

        if ($user->role !== UserRole::HubManager) {
            throw new AuthorizationException('شما اجازه مدیریت این نمایشگر را ندارید.');
        }

        if (! $displayDevice->hub_id || ! $this->managedHubIds($user)->contains($displayDevice->hub_id)) {
            throw new AuthorizationException('این نمایشگر خارج از محدوده رواق شماست.');
        }
    }

    public function ensureCanReviewReward(User $user, RewardDefinition $reward): void
    {
        if ($this->isPlatformReviewer($user)) {
            return;
        }

        if ($user->role !== UserRole::HubManager) {
            throw new AuthorizationException('شما اجازه بازبینی این پیشنهاد را ندارید.');
        }

        if (! $reward->partner_account_id || ! $this->managedPartnerIds($user)->contains($reward->partner_account_id)) {
            throw new AuthorizationException('این پیشنهاد خارج از محدوده رواق شماست.');
        }
    }

    private function isPlatformReviewer(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Operator], true);
    }
}
