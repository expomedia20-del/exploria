<?php

namespace App\Services;

use App\Models\CampaignParticipant;
use App\Models\Campaign;
use App\Models\Hub;
use App\Models\PartnerAccount;
use App\Models\User;
use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignParticipantRegistryService
{
    public function __construct(private readonly UserAccessScopeService $accessScopes) {}

    /** @return array<string, mixed> */
    public function overview(?User $user = null, ?string $campaignId = null): array
    {
        $venueIds = $user ? $this->accessScopes->assignedVenueIds($user) : collect();
        $hubIds = $user ? $this->accessScopes->hubIds($user) : collect();
        $partnerIds = $user ? $this->accessScopes->partnerIds($user) : collect();
        $isGlobal = $user === null || $this->accessScopes->hasGlobalAccess($user);

        $participants = CampaignParticipant::query()
            ->when($campaignId, fn (Builder $query) => $query->where('campaign_id', $campaignId))
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
            'formOptions' => $this->formOptions($campaignId),
        ];
    }

    /** @return array<string, mixed> */
    private function formOptions(?string $campaignId): array
    {
        $campaign = $campaignId ? Campaign::query()->find($campaignId) : null;
        $venueId = $campaign?->venue_id;

        return [
            'hubs' => Hub::query()
                ->when($venueId, fn (Builder $query) => $query->whereHas('zone', fn (Builder $zone) => $zone->where('venue_id', $venueId)))
                ->orderBy('name')
                ->get(['id', 'code', 'name'])
                ->map(fn (Hub $hub): array => ['id' => $hub->id, 'code' => $hub->code, 'name' => $hub->name]),
            'partners' => PartnerAccount::query()
                ->when($venueId, fn (Builder $query) => $query->where('venue_id', $venueId))
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'partner_type'])
                ->map(fn (PartnerAccount $partner): array => [
                    'id' => $partner->id,
                    'code' => $partner->code,
                    'name' => $partner->name,
                    'partnerType' => $partner->partner_type,
                ]),
        ];
    }

    /** @param array<string, mixed> $data */
    public function createParticipant(array $data): CampaignParticipant
    {
        $campaign = Campaign::query()->findOrFail($data['campaign_id']);
        $this->assertSameVenueHub($campaign, $data['hub_id'] ?? null);
        $this->assertSameVenuePartner($campaign, $data['partner_account_id'] ?? null);

        $attributes = [
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'hub_id' => $data['hub_id'] ?? null,
            'partner_account_id' => $data['partner_account_id'] ?? null,
            'participant_type' => $data['participant_type'],
            'participation_role' => $data['participation_role'],
            'status' => $data['status'],
            'onboarding_status' => $data['onboarding_status'],
            'joined_at' => $data['joined_at'] ?? now(),
            'metadata' => [
                'source' => 'admin_campaign_builder',
                'connections' => [
                    'rewards' => (int) ($data['connections_rewards'] ?? 0),
                    'ads' => (int) ($data['connections_ads'] ?? 0),
                    'qr_codes' => (int) ($data['connections_qr_codes'] ?? 0),
                    'missions' => (int) ($data['connections_missions'] ?? 0),
                ],
            ],
        ];

        return DB::transaction(function () use ($data, $attributes): CampaignParticipant {
            if (! empty($data['participant_id'])) {
                $participant = CampaignParticipant::query()->findOrFail($data['participant_id']);
                $metadata = array_merge($participant->metadata ?? [], $attributes['metadata']);
                $participant->update(array_merge($attributes, ['metadata' => $metadata]));

                return $participant->refresh();
            }

            return CampaignParticipant::query()->create($attributes);
        });
    }

    public function deleteParticipant(CampaignParticipant $participant): void
    {
        $participant->delete();
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

    private function assertSameVenueHub(Campaign $campaign, ?string $hubId): void
    {
        if (! $hubId) {
            return;
        }

        $matches = Hub::query()
            ->whereKey($hubId)
            ->whereHas('zone', fn (Builder $query) => $query->where('venue_id', $campaign->venue_id))
            ->exists();

        if (! $matches) {
            throw ValidationException::withMessages(['hub_id' => 'هاب انتخاب‌شده به مکان کمپین تعلق ندارد.']);
        }
    }

    private function assertSameVenuePartner(Campaign $campaign, ?string $partnerId): void
    {
        if (! $partnerId) {
            return;
        }

        if (! PartnerAccount::query()->whereKey($partnerId)->where('venue_id', $campaign->venue_id)->exists()) {
            throw ValidationException::withMessages(['partner_account_id' => 'شریک انتخاب‌شده به مکان کمپین تعلق ندارد.']);
        }
    }
}
