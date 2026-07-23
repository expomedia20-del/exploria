<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\PartnerLocation;
use App\Models\RewardDefinition;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class HubManagerAccessService
{
    private const RAVAQ_HUB_CODES = [
        'ravaq-commercial-hub',
        'foodcourt-family-hub',
    ];

    public function __construct(private readonly UserAccessScopeService $accessScopes) {}

    /** @return Collection<int, string> */
    public function managedHubIds(User $user, bool $ravaqOnly = false): Collection
    {
        $hubIds = $this->accessScopes->hubIds($user);

        if (! $ravaqOnly) {
            return $hubIds;
        }

        $ravaqHubIds = Hub::query()
            ->whereIn('code', self::RAVAQ_HUB_CODES)
            ->pluck('id');

        return $hubIds
            ->intersect($ravaqHubIds)
            ->values();
    }

    /** @return Collection<int, string> */
    public function managedPartnerIds(User $user, bool $ravaqOnly = false): Collection
    {
        if ($ravaqOnly) {
            return PartnerLocation::query()
                ->whereIn('hub_id', $this->managedHubIds($user, true))
                ->where('status', 'active')
                ->pluck('partner_account_id')
                ->filter()
                ->unique()
                ->values();
        }

        return $this->accessScopes->partnerIds($user);
    }

    /** @return Collection<int, Hub> */
    public function managedHubs(User $user, bool $ravaqOnly = false): Collection
    {
        $hubIds = $this->managedHubIds($user, $ravaqOnly);

        return Hub::query()
            ->with(['zone.venue:id,code,name'])
            ->whereIn('id', $hubIds)
            ->orderBy('created_at')
            ->get()
            ->toBase();
    }

    public function ensureCanReviewAdRequest(User $user, AdRequest $adRequest): void
    {
        if ($this->isCentralReviewer($user)) {
            return;
        }

        if ($user->role === UserRole::RegionalAdmin && $this->canAccessAdRequest($user, $adRequest)) {
            return;
        }

        throw new AuthorizationException('تایید یا رد تبلیغ فقط با تیم داخلی اکسپلوریا و در محدوده مجاز انجام می‌شود.');
    }

    public function ensureCanScheduleAdRequest(User $user, AdRequest $adRequest): void
    {
        if ($this->isCentralReviewer($user) || $user->role === UserRole::RegionalAdmin) {
            return;
        }

        if ($user->role !== UserRole::HubManager) {
            throw new AuthorizationException('شما اجازه زمان‌بندی این تبلیغ را ندارید.');
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
        if ($this->isCentralReviewer($user) || $user->role === UserRole::RegionalAdmin) {
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
        if ($this->isCentralReviewer($user) || $user->role === UserRole::RegionalAdmin) {
            return;
        }

        if ($user->role !== UserRole::HubManager) {
            throw new AuthorizationException('شما اجازه بازبینی این پیشنهاد را ندارید.');
        }

        if (! $reward->partner_account_id || ! $this->managedPartnerIds($user)->contains($reward->partner_account_id)) {
            throw new AuthorizationException('این پیشنهاد خارج از محدوده رواق شماست.');
        }
    }

    private function isCentralReviewer(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Operator], true);
    }

    private function canAccessAdRequest(User $user, AdRequest $adRequest): bool
    {
        if ($this->hasDirectScope($user, 'global', null)) {
            return true;
        }

        if ($this->hasDirectScope($user, 'venue', $adRequest->venue_id)) {
            return true;
        }

        if ($adRequest->hub_id !== null && $this->hasDirectScope($user, 'hub', $adRequest->hub_id)) {
            return true;
        }

        if ($adRequest->partner_account_id !== null && $this->hasDirectScope($user, 'partner', $adRequest->partner_account_id)) {
            return true;
        }

        $venueCity = Venue::query()
            ->where('id', $adRequest->venue_id)
            ->value('city');

        return is_string($venueCity) && $this->hasDirectScope($user, 'region', $venueCity);
    }

    private function hasDirectScope(User $user, string $scopeType, ?string $scopeId): bool
    {
        return UserAccessScope::query()
            ->where('user_id', $user->id)
            ->where('scope_type', $scopeType)
            ->where('status', 'active')
            ->where(function ($query) use ($scopeId): void {
                if ($scopeId === null) {
                    $query->whereNull('scope_id');

                    return;
                }

                $query->where('scope_id', $scopeId);
            })
            ->exists();
    }
}
