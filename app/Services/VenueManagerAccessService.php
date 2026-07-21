<?php

namespace App\Services;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserAccessScope;
use App\Models\Venue;
use Illuminate\Support\Collection;

class VenueManagerAccessService
{
    /** @return Collection<int, string> */
    public function managedVenueIds(User $user): Collection
    {
        if ($this->isPlatformSupport($user)) {
            return Venue::query()
                ->where('status', RecordStatus::Active)
                ->pluck('id')
                ->values();
        }

        if ($user->role === UserRole::RegionalAdmin) {
            $regions = UserAccessScope::query()
                ->where('user_id', $user->id)
                ->where('scope_type', 'region')
                ->where('status', RecordStatus::Active)
                ->where('role_key', 'regional_admin')
                ->pluck('scope_id')
                ->filter()
                ->unique()
                ->values();

            return Venue::query()
                ->where('status', RecordStatus::Active)
                ->whereIn('city', $regions)
                ->pluck('id')
                ->values();
        }

        return UserAccessScope::query()
            ->where('user_id', $user->id)
            ->where('scope_type', 'venue')
            ->where('status', RecordStatus::Active)
            ->whereIn('role_key', ['venue_executive', 'project_admin', 'display_ads_manager'])
            ->pluck('scope_id')
            ->filter()
            ->unique()
            ->values();
    }

    /** @return Collection<int, Venue> */
    public function managedVenues(User $user): Collection
    {
        $venueIds = $this->managedVenueIds($user);

        if ($venueIds->isEmpty()) {
            return collect();
        }

        return Venue::query()
            ->whereIn('id', $venueIds)
            ->orderBy('created_at')
            ->get()
            ->toBase();
    }

    private function isPlatformSupport(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Operator], true);
    }
}
