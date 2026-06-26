<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Models\Hub;
use App\Models\HubManagementAssignment;
use App\Models\PartnerLocation;
use App\Models\RewardDefinition;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class HubManagerAccessService
{
    /** @return Collection<int, string> */
    public function managedHubIds(User $user): Collection
    {
        if ($this->isPlatformReviewer($user)) {
            return Hub::query()
                ->where('status', RecordStatus::Active)
                ->pluck('id')
                ->values();
        }

        return HubManagementAssignment::query()
            ->where('user_id', $user->id)
            ->where('status', RecordStatus::Active)
            ->pluck('hub_id')
            ->values();
    }

    /** @return Collection<int, string> */
    public function managedPartnerIds(User $user): Collection
    {
        $hubIds = $this->managedHubIds($user);

        if ($hubIds->isEmpty()) {
            return collect();
        }

        return PartnerLocation::query()
            ->whereIn('hub_id', $hubIds)
            ->where('status', RecordStatus::Active)
            ->pluck('partner_account_id')
            ->unique()
            ->values();
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
            throw new AuthorizationException('Ø´Ù…Ø§ Ø§Ø¬Ø§Ø²Ù‡ Ø¨Ø§Ø²Ø¨ÛŒÙ†ÛŒ Ø§ÛŒÙ† ØªØ¨Ù„ÛŒØº Ø±Ø§ Ù†Ø¯Ø§Ø±ÛŒØ¯.');
        }

        $hubIds = $this->managedHubIds($user);
        $partnerIds = $this->managedPartnerIds($user);

        $isDirectHubAd = $adRequest->hub_id !== null && $hubIds->contains($adRequest->hub_id);
        $isPartnerAd = $adRequest->partner_account_id !== null && $partnerIds->contains($adRequest->partner_account_id);

        if (! $isDirectHubAd && ! $isPartnerAd) {
            throw new AuthorizationException('Ø§ÛŒÙ† ØªØ¨Ù„ÛŒØº Ø®Ø§Ø±Ø¬ Ø§Ø² Ù…Ø­Ø¯ÙˆØ¯Ù‡ Ø±ÙˆØ§Ù‚ Ø´Ù…Ø§Ø³Øª.');
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
            throw new AuthorizationException('Ø´Ù…Ø§ Ø§Ø¬Ø§Ø²Ù‡ Ø¨Ø§Ø²Ø¨ÛŒÙ†ÛŒ Ø§ÛŒÙ† Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯ Ø±Ø§ Ù†Ø¯Ø§Ø±ÛŒØ¯.');
        }

        if (! $reward->partner_account_id || ! $this->managedPartnerIds($user)->contains($reward->partner_account_id)) {
            throw new AuthorizationException('Ø§ÛŒÙ† Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯ Ø®Ø§Ø±Ø¬ Ø§Ø² Ù…Ø­Ø¯ÙˆØ¯Ù‡ Ø±ÙˆØ§Ù‚ Ø´Ù…Ø§Ø³Øª.');
        }
    }

    private function isPlatformReviewer(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Operator], true);
    }
}
