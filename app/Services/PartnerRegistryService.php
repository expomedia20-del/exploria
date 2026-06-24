<?php

namespace App\Services;

use App\Models\PartnerAccount;
use App\Models\PartnerLocation;
use App\Models\PartnerUser;
use Illuminate\Support\Collection;

class PartnerRegistryService
{
    /** @return Collection<int, array<string, mixed>> */
    public function list(): Collection
    {
        return PartnerAccount::query()
            ->with([
                'venue:id,name,code',
                'locations.hub:id,name,code,hub_type',
                'locations.zone:id,name,code',
                'partnerUsers.user:id,name,email,role',
            ])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (PartnerAccount $partner): array => $this->serializePartner($partner));
    }

    /** @return array<string, mixed> */
    private function serializePartner(PartnerAccount $partner): array
    {
        return [
            'id' => $partner->id,
            'code' => $partner->code,
            'name' => $partner->name,
            'partnerType' => $partner->partner_type,
            'status' => $partner->status->value,
            'contactName' => $partner->contact_name,
            'contactMobile' => $partner->contact_mobile,
            'venue' => $partner->venue ? [
                'id' => $partner->venue->id,
                'code' => $partner->venue->code,
                'name' => $partner->venue->name,
            ] : null,
            'locations' => $partner->locations
                ->map(fn (PartnerLocation $location): array => [
                    'id' => $location->id,
                    'locationRole' => $location->location_role,
                    'status' => $location->status->value,
                    'hub' => $location->hub ? [
                        'id' => $location->hub->id,
                        'code' => $location->hub->code,
                        'name' => $location->hub->name,
                        'hubType' => $location->hub->hub_type,
                    ] : null,
                    'zone' => $location->zone ? [
                        'id' => $location->zone->id,
                        'code' => $location->zone->code,
                        'name' => $location->zone->name,
                    ] : null,
                ])
                ->values(),
            'users' => $partner->partnerUsers
                ->map(fn (PartnerUser $partnerUser): array => [
                    'id' => $partnerUser->id,
                    'role' => $partnerUser->role,
                    'status' => $partnerUser->status->value,
                    'user' => $partnerUser->user ? [
                        'id' => $partnerUser->user->id,
                        'name' => $partnerUser->user->name,
                        'email' => $partnerUser->user->email,
                        'role' => $partnerUser->user->role->value,
                    ] : null,
                ])
                ->values(),
        ];
    }
}
