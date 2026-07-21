<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Hub;
use App\Models\HubManagementAssignment;
use App\Models\PartnerAccount;
use App\Models\PartnerLocation;
use App\Models\PartnerUser;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use Illuminate\Support\Collection;

class UserAccessScopeService
{
    public function hasGlobalAccess(User $user): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::Operator, UserRole::Viewer], true)) {
            return true;
        }

        return $this->directScopeIds($user, 'global')->contains('__global__');
    }

    /** @return Collection<int, string> */
    public function assignedVenueIds(User $user): Collection
    {
        if ($this->hasGlobalAccess($user)) {
            return Venue::query()
                ->where('status', RecordStatus::Active)
                ->pluck('id')
                ->values();
        }

        return $this->directScopeIds($user, 'venue')
            ->merge($this->regionVenueIds($user))
            ->unique()
            ->values();
    }

    /** @return Collection<int, string> */
    public function assignedHubIds(User $user): Collection
    {
        if ($this->hasGlobalAccess($user)) {
            return Hub::query()
                ->where('status', RecordStatus::Active)
                ->pluck('id')
                ->values();
        }

        $legacyHubIds = HubManagementAssignment::query()
            ->where('user_id', $user->id)
            ->where('status', RecordStatus::Active)
            ->pluck('hub_id');

        return $this->directScopeIds($user, 'hub')
            ->merge($legacyHubIds)
            ->filter()
            ->unique()
            ->values();
    }

    /** @return Collection<int, string> */
    public function assignedPartnerIds(User $user): Collection
    {
        if ($this->hasGlobalAccess($user)) {
            return PartnerAccount::query()
                ->where('status', RecordStatus::Active)
                ->pluck('id')
                ->values();
        }

        $legacyPartnerIds = PartnerUser::query()
            ->where('user_id', $user->id)
            ->where('status', RecordStatus::Active)
            ->pluck('partner_account_id');

        return $this->directScopeIds($user, 'partner')
            ->merge($legacyPartnerIds)
            ->filter()
            ->unique()
            ->values();
    }

    /** @return Collection<int, string> */
    public function venueIds(User $user): Collection
    {
        if ($this->hasGlobalAccess($user)) {
            return Venue::query()
                ->where('status', RecordStatus::Active)
                ->pluck('id')
                ->values();
        }

        $directVenueIds = $this->directScopeIds($user, 'venue')
            ->merge($this->regionVenueIds($user))
            ->filter()
            ->unique()
            ->values();
        $hubVenueIds = Hub::query()
            ->whereIn('id', $this->hubIds($user))
            ->whereHas('zone')
            ->with('zone:id,venue_id')
            ->get()
            ->pluck('zone.venue_id');
        $partnerVenueIds = PartnerAccount::query()
            ->whereIn('id', $this->partnerIds($user))
            ->pluck('venue_id');

        return $directVenueIds
            ->merge($hubVenueIds)
            ->merge($partnerVenueIds)
            ->filter()
            ->unique()
            ->values();
    }

    /** @return Collection<int, string> */
    public function hubIds(User $user): Collection
    {
        if ($this->hasGlobalAccess($user)) {
            return Hub::query()
                ->where('status', RecordStatus::Active)
                ->pluck('id')
                ->values();
        }

        $directVenueIds = $this->directScopeIds($user, 'venue')
            ->merge($this->regionVenueIds($user))
            ->filter()
            ->unique()
            ->values();
        $directHubIds = $this->directScopeIds($user, 'hub');
        $venueHubIds = Hub::query()
            ->whereHas('zone', fn ($query) => $query->whereIn('venue_id', $directVenueIds))
            ->where('status', RecordStatus::Active)
            ->pluck('id');
        $legacyHubIds = HubManagementAssignment::query()
            ->where('user_id', $user->id)
            ->where('status', RecordStatus::Active)
            ->pluck('hub_id');

        return $directHubIds
            ->merge($venueHubIds)
            ->merge($legacyHubIds)
            ->filter()
            ->unique()
            ->values();
    }

    /** @return Collection<int, string> */
    public function partnerIds(User $user): Collection
    {
        if ($this->hasGlobalAccess($user)) {
            return PartnerAccount::query()
                ->where('status', RecordStatus::Active)
                ->pluck('id')
                ->values();
        }

        $directVenueIds = $this->directScopeIds($user, 'venue')
            ->merge($this->regionVenueIds($user))
            ->filter()
            ->unique()
            ->values();
        $directPartnerIds = $this->directScopeIds($user, 'partner');
        $hubPartnerIds = PartnerLocation::query()
            ->whereIn('hub_id', $this->hubIds($user))
            ->where('status', RecordStatus::Active)
            ->pluck('partner_account_id');
        $venuePartnerIds = PartnerAccount::query()
            ->whereIn('venue_id', $directVenueIds)
            ->where('status', RecordStatus::Active)
            ->pluck('id');
        $legacyPartnerIds = PartnerUser::query()
            ->where('user_id', $user->id)
            ->where('status', RecordStatus::Active)
            ->pluck('partner_account_id');

        return $directPartnerIds
            ->merge($hubPartnerIds)
            ->merge($venuePartnerIds)
            ->merge($legacyPartnerIds)
            ->filter()
            ->unique()
            ->values();
    }

    public function hasScope(User $user, string $scopeType, ?string $scopeId = null): bool
    {
        if ($this->hasGlobalAccess($user)) {
            return true;
        }

        return match ($scopeType) {
            'venue' => $scopeId !== null && $this->venueIds($user)->contains($scopeId),
            'region' => $scopeId !== null && $this->regionIds($user)->contains($scopeId),
            'hub' => $scopeId !== null && $this->hubIds($user)->contains($scopeId),
            'partner' => $scopeId !== null && $this->partnerIds($user)->contains($scopeId),
            'global' => $this->directScopeIds($user, 'global')->contains('__global__'),
            default => $scopeId !== null && $this->directScopeIds($user, $scopeType)->contains($scopeId),
        };
    }

    /** @return Collection<int, string> */
    public function regionIds(User $user): Collection
    {
        if ($this->hasGlobalAccess($user)) {
            return Venue::query()
                ->whereNotNull('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city')
                ->filter()
                ->values();
        }

        return $this->directScopeIds($user, 'region');
    }

    /** @return Collection<int, string> */
    private function regionVenueIds(User $user): Collection
    {
        $regionIds = $this->directScopeIds($user, 'region');

        if ($regionIds->isEmpty()) {
            return collect();
        }

        return Venue::query()
            ->whereIn('city', $regionIds)
            ->where('status', RecordStatus::Active)
            ->pluck('id')
            ->values();
    }

    /** @return Collection<int, string> */
    private function directScopeIds(User $user, string $scopeType): Collection
    {
        return UserAccessScope::query()
            ->where('user_id', $user->id)
            ->where('scope_type', $scopeType)
            ->where('status', RecordStatus::Active)
            ->pluck('scope_id')
            ->map(fn (?string $scopeId): string => $scopeId ?? '__global__')
            ->unique()
            ->values();
    }
}
