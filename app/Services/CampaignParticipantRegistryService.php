<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\Hub;
use App\Models\PartnerAccount;
use App\Models\RewardDefinition;
use App\Models\User;
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

        $offerStats = $this->partnerOfferStats($campaignId);

        return [
            'stats' => [
                'participants' => $participants->count(),
                'activeParticipants' => $participants->where('status', 'active')->count(),
                'invitedParticipants' => $participants->where('onboardingStatus', 'invited')->count(),
                'readyParticipants' => $participants->where('onboardingStatus', 'ready')->count(),
                'partnerRewardOffers' => $offerStats['total'],
                'pendingRewardOffers' => $offerStats['pending'],
                'approvedRewardOffers' => $offerStats['approved'],
                'rejectedRewardOffers' => $offerStats['rejected'],
                'hubs' => $participants->pluck('hub.id')->filter()->unique()->count(),
                'campaigns' => $participants->pluck('campaign.id')->filter()->unique()->count(),
            ],
            'participants' => $participants,
            'campaignGroups' => $this->campaignGroups($participants),
            'hubGroups' => $this->hubGroups($participants),
            'formOptions' => $this->formOptions($campaignId),
        ];
    }

    /** @return array{total: int, pending: int, approved: int, rejected: int} */
    private function partnerOfferStats(?string $campaignId): array
    {
        $offers = RewardDefinition::query()
            ->when($campaignId, fn (Builder $query) => $query->where('campaign_id', $campaignId))
            ->where('metadata->source', 'partner_offer_submission')
            ->get(['metadata']);

        return [
            'total' => $offers->count(),
            'pending' => $offers->where('metadata.approval_status', 'pending_review')->count(),
            'approved' => $offers->where('metadata.approval_status', 'approved')->count(),
            'rejected' => $offers->where('metadata.approval_status', 'rejected')->count(),
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
        $campaign = Campaign::query()->findOrFail($this->requiredId($data, 'campaign_id'));
        $hubId = $this->optionalString($data, 'hub_id');
        $partnerId = $this->optionalString($data, 'partner_account_id');
        $this->assertSameVenueHub($campaign, $hubId);
        $this->assertSameVenuePartner($campaign, $partnerId);

        $attributes = [
            'campaign_id' => $campaign->id,
            'venue_id' => $campaign->venue_id,
            'hub_id' => $hubId,
            'partner_account_id' => $partnerId,
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
                $participant = CampaignParticipant::query()->findOrFail($this->requiredId($data, 'participant_id'));
                $metadata = array_merge($participant->metadata ?? [], $attributes['metadata']);
                $participant->update(array_merge($attributes, ['metadata' => $metadata]));

                return $participant->refresh();
            }

            $participant = $this->matchingParticipant($attributes);
            if ($participant) {
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

    /** @param array<string, mixed> $attributes */
    private function matchingParticipant(array $attributes): ?CampaignParticipant
    {
        $query = CampaignParticipant::query()->where('campaign_id', $attributes['campaign_id']);

        if (! empty($attributes['partner_account_id'])) {
            return $query
                ->where('partner_account_id', $attributes['partner_account_id'])
                ->latest()
                ->first();
        }

        return $query
            ->whereNull('partner_account_id')
            ->where('hub_id', $attributes['hub_id'])
            ->where('participant_type', $attributes['participant_type'])
            ->where('participation_role', $attributes['participation_role'])
            ->latest()
            ->first();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $participants
     * @return Collection<int, covariant array<string, mixed>>
     */
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

    /**
     * @param  Collection<int, array<string, mixed>>  $participants
     * @return Collection<int, covariant array<string, mixed>>
     */
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

    /**
     * @param  array<string, mixed>  $data
     */
    private function requiredId(array $data, string $key): int|string
    {
        $value = $data[$key] ?? null;

        if (! is_int($value) && ! is_string($value)) {
            throw ValidationException::withMessages([$key => 'شناسه انتخاب‌شده معتبر نیست.']);
        }

        return $value;
    }

    /** @param array<string, mixed> $data */
    private function optionalString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value)) {
            throw ValidationException::withMessages([$key => 'شناسه انتخاب‌شده معتبر نیست.']);
        }

        return $value;
    }
}
