<?php

namespace App\Services;

use App\Models\CampaignParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CampaignParticipantRegistryService
{
    public function __construct(private readonly UserAccessScopeService $accessScopes) {}

    /** @return array<string, mixed> */
    public function overview(?User $user = null): array
    {
        $venueIds = $user ? $this->accessScopes->assignedVenueIds($user) : collect();
        $hubIds = $user ? $this->accessScopes->hubIds($user) : collect();
        $partnerIds = $user ? $this->accessScopes->partnerIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        $participants = CampaignParticipant::query()
            ->when(! $isGlobal, fn (Builder $query) => $query->where(function (Builder $query) use ($venueIds, $hubIds, $partnerIds): void {
                $query->whereIn('venue_id', $venueIds)
                    ->orWhereIn('hub_id', $hubIds)
                    ->orWhereIn('partner_account_id', $partnerIds);
            }))
            ->with([
                'campaign:id,code,name,status',
                'venue:id,code,name',
                'hub:id,code,name,hub_type',
                'partnerAccount:id,code,name,partner_type,status,contact_name,contact_mobile',
            ])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (CampaignParticipant $participant): array => $this->serializeParticipant($participant));

        return [
            'stats' => [
                'participants' => $participants->count(),
                'activeParticipants' => $participants->where('status', 'active')->count(),
                'invitedParticipants' => $participants->where('onboardingStatus', 'invited')->count(),
                'readyParticipants' => $participants->where('onboardingStatus', 'ready')->count(),
                'hubs' => $participants->pluck('hub.id')->filter()->unique()->count(),
                'campaigns' => $participants->pluck('campaign.id')->filter()->unique()->count(),
            ],
            'participants' => $participants,
            'campaignGroups' => $this->campaignGroups($participants),
            'hubGroups' => $this->hubGroups($participants),
        ];
    }

    /** @param Collection<int, array<string, mixed>> $participants */
    private function campaignGroups(Collection $participants): Collection
    {
        return $participants
            ->groupBy(fn (array $participant): string => (string) data_get($participant, 'campaign.id', 'unassigned'))
            ->map(fn (Collection $items): array => [
                'campaign' => $items->first()['campaign'] ?? null,
                'participantsCount' => $items->count(),
                'activeCount' => $items->where('status', 'active')->count(),
                'hubCount' => $items->pluck('hub.id')->filter()->unique()->count(),
            ])
            ->values();
    }

    /** @param Collection<int, array<string, mixed>> $participants */
    private function hubGroups(Collection $participants): Collection
    {
        return $participants
            ->groupBy(fn (array $participant): string => (string) data_get($participant, 'hub.id', 'unassigned'))
            ->map(fn (Collection $items): array => [
                'hub' => $items->first()['hub'] ?? null,
                'participantsCount' => $items->count(),
                'activeCount' => $items->where('status', 'active')->count(),
                'roles' => $items->pluck('participationRole')->unique()->values(),
            ])
            ->values();
    }

    /** @return array<string, mixed> */
    private function serializeParticipant(CampaignParticipant $participant): array
    {
        return [
            'id' => $participant->id,
            'participantType' => $participant->participant_type,
            'participationRole' => $participant->participation_role,
            'status' => $participant->status->value,
            'onboardingStatus' => $participant->onboarding_status,
            'joinedAt' => $participant->joined_at?->toIso8601String(),
            'campaign' => $participant->campaign ? [
                'id' => $participant->campaign->id,
                'code' => $participant->campaign->code,
                'name' => $participant->campaign->name,
                'status' => $participant->campaign->status->value,
            ] : null,
            'venue' => $participant->venue ? [
                'id' => $participant->venue->id,
                'code' => $participant->venue->code,
                'name' => $participant->venue->name,
            ] : null,
            'hub' => $participant->hub ? [
                'id' => $participant->hub->id,
                'code' => $participant->hub->code,
                'name' => $participant->hub->name,
                'hubType' => $participant->hub->hub_type,
            ] : null,
            'partner' => $participant->partnerAccount ? [
                'id' => $participant->partnerAccount->id,
                'code' => $participant->partnerAccount->code,
                'name' => $participant->partnerAccount->name,
                'partnerType' => $participant->partnerAccount->partner_type,
                'status' => $participant->partnerAccount->status->value,
                'contactName' => $participant->partnerAccount->contact_name,
                'contactMobile' => $participant->partnerAccount->contact_mobile,
            ] : null,
            'connections' => [
                'rewards' => (int) ($participant->metadata['connections']['rewards'] ?? 0),
                'ads' => (int) ($participant->metadata['connections']['ads'] ?? 0),
                'qrCodes' => (int) ($participant->metadata['connections']['qr_codes'] ?? 0),
                'missions' => (int) ($participant->metadata['connections']['missions'] ?? 0),
            ],
        ];
    }
}