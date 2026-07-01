<?php

namespace App\Services;

use App\Models\AdRequest;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\DisplayDevice;
use App\Models\MissionInstance;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use App\Models\RewardRedemption;
use App\Models\Treasure;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignOperationsBlueprintService
{
    public function __construct(
        private readonly UserAccessScopeService $accessScopes,
        private readonly CampaignBlueprintConsistencyService $blueprintConsistency,
    ) {}

    /** @return array<string, mixed> */
    public function overview(?User $user = null, ?string $campaignId = null): array
    {
        $scope = $this->scope($user);

        $campaigns = Campaign::query()
            ->when($campaignId, fn (Builder $query) => $query->where('id', $campaignId))
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->whereIn('venue_id', $scope['venueIds']))
            ->with('venue:id,code,name')
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (Campaign $campaign): array => $this->serializeCampaign($campaign, $scope));

        return [
            'stats' => [
                'campaigns' => $campaigns->count(),
                'participants' => $campaigns->sum('stats.participants'),
                'internalSponsors' => $campaigns->sum('stats.internalSponsors'),
                'externalSponsors' => $campaigns->sum('stats.externalSponsors'),
                'missions' => $campaigns->sum('stats.missions'),
                'incentives' => $campaigns->sum('stats.rewards') + $campaigns->sum('stats.treasures'),
                'entryPoints' => $campaigns->sum('stats.qrCodes'),
                'adRequests' => $campaigns->sum('stats.adRequests'),
                'displayDevices' => $campaigns->sum('stats.displayDevices'),
            ],
            'campaigns' => $campaigns,
        ];
    }

    /** @param array<string, mixed> $data */
    public function markRouteReviewed(?User $user, array $data): Campaign
    {
        return DB::transaction(function () use ($user, $data): Campaign {
            $campaign = Campaign::query()->findOrFail($data['campaign_id']);
            $review = $this->blueprintConsistency->review($campaign);
            $stats = $this->routeStats($campaign, $this->scope($user));
            $operationalReview = $this->operationalReview($stats, $review);

            if (collect($operationalReview['issues'])->where('level', 'error')->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'campaign_id' => 'قبل از تایید مسیر عملیاتی، نقص‌های QR، مأموریت، مشوق، عضو آماده یا پیشنهادهای معلق همین کمپین را رفع کنید.',
                ]);
            }

            $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];

            $campaign->update([
                'metadata' => [
                    ...$metadata,
                    'route_reviewed_at' => now()->toIso8601String(),
                    'route_reviewed_by_user_id' => $user?->id,
                    'route_review_notes' => $data['route_notes'] ?? null,
                ],
            ]);

            return $campaign;
        });
    }

    /** @param array<string, mixed> $data */
    public function resetRouteReview(array $data): Campaign
    {
        return DB::transaction(function () use ($data): Campaign {
            $campaign = Campaign::query()->findOrFail($data['campaign_id']);
            $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];

            unset($metadata['route_reviewed_at'], $metadata['route_reviewed_by_user_id'], $metadata['route_review_notes']);
            $campaign->update(['metadata' => $metadata]);

            return $campaign;
        });
    }

    /** @param array<string, mixed> $scope @return array<string, int> */
    private function routeStats(Campaign $campaign, array $scope): array
    {
        $participants = $this->participants($campaign, $scope);
        $rewards = $this->rewards($campaign, $scope);

        return [
            'participants' => $participants->count(),
            'readyParticipants' => $participants->where('onboardingStatus', 'ready')->count(),
            'internalSponsors' => $participants->filter(fn (array $participant): bool => $participant['participantType'] === 'sponsor' && $participant['hub'] !== null)->count(),
            'externalSponsors' => $participants->filter(fn (array $participant): bool => $participant['participantType'] === 'sponsor' && $participant['hub'] === null)->count(),
            'missions' => $this->missions($campaign, $scope)->count(),
            'rewards' => $rewards->count(),
            'approvedRewards' => $rewards->where('approvalStatus', 'approved')->count(),
            'pendingRewards' => $rewards->where('approvalStatus', 'pending_review')->count(),
            'treasures' => $this->treasures($campaign, $scope)->count(),
            'qrCodes' => $this->qrCodes($campaign, $scope)->count(),
            'adRequests' => $this->adRequests($campaign, $scope)->count(),
            'displayDevices' => $this->displayDevices($campaign, $scope)->count(),
        ];
    }

    /**
     * @param array<string, int> $stats
     * @param array<string, mixed> $alignment
     * @return array{status: string, checks: array<int, array<string, mixed>>, issues: array<int, array<string, string>>}
     */
    private function operationalReview(array $stats, array $alignment): array
    {
        $checks = [
            $this->operationCheck('qr', 'QR ورودی', 'حداقل یک QR معتبر برای شروع مسیر ثبت شده باشد.', $stats['qrCodes'] > 0, $stats['qrCodes']),
            $this->operationCheck('missions', 'ماموریت', 'حداقل یک ماموریت به چرخه کاربر وصل شده باشد.', $stats['missions'] > 0, $stats['missions']),
            $this->operationCheck('incentives', 'مشوق و گنج', 'حداقل یک پاداش تاییدشده یا یک گنج برای کمپین ثبت شده باشد.', $stats['approvedRewards'] > 0 || $stats['treasures'] > 0, $stats['approvedRewards'] + $stats['treasures']),
            $this->operationCheck('participants', 'عضو آماده', 'حداقل یک فروشگاه، شریک یا اسپانسر آماده اجرا باشد.', $stats['readyParticipants'] > 0, $stats['readyParticipants']),
            $this->operationCheck('pending_rewards', 'پیشنهاد معلق', 'پیشنهاد پاداش در انتظار بررسی باقی نمانده باشد.', $stats['pendingRewards'] === 0, $stats['pendingRewards']),
            $this->operationCheck('alignment', 'همخوانی الگو', 'چرخه، ماموریت، پاداش و گنج با الگوی مرجع همخوان باشند.', collect($alignment['issues'] ?? [])->where('level', 'error')->isEmpty(), (int) ($alignment['completedSteps'] ?? 0)),
        ];

        if (($stats['adRequests'] + $stats['displayDevices']) === 0) {
            $checks[] = $this->operationCheck('media', 'رسانه و نمایشگر', 'برای اجرای میدانی بهتر، نمایشگر یا درخواست تبلیغاتی مرتبط مشخص شود.', false, 0, 'warning');
        }

        $issues = collect($checks)
            ->filter(fn (array $check): bool => ! $check['complete'])
            ->map(fn (array $check): array => [
                'level' => $check['severity'],
                'code' => $check['key'],
                'title' => $check['title'].' آماده نیست.',
                'action' => $check['description'],
            ])
            ->values()
            ->all();

        return [
            'status' => collect($issues)->where('level', 'error')->isEmpty() ? 'ready' : 'needs_attention',
            'checks' => $checks,
            'issues' => $issues,
        ];
    }

    /** @return array<string, mixed> */
    private function operationCheck(string $key, string $title, string $description, bool $complete, int $count, string $severity = 'error'): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'description' => $description,
            'complete' => $complete,
            'count' => $count,
            'severity' => $severity,
        ];
    }

    /** @return array{isGlobal: bool, venueIds: Collection<int, string>, assignedVenueIds: Collection<int, string>, hubIds: Collection<int, string>, partnerIds: Collection<int, string>} */
    private function scope(?User $user): array
    {
        return [
            'isGlobal' => $user === null || $this->accessScopes->hasGlobalAccess($user),
            'venueIds' => $user ? $this->accessScopes->venueIds($user) : collect(),
            'assignedVenueIds' => $user ? $this->accessScopes->assignedVenueIds($user) : collect(),
            'hubIds' => $user ? $this->accessScopes->hubIds($user) : collect(),
            'partnerIds' => $user ? $this->accessScopes->partnerIds($user) : collect(),
        ];
    }

    /** @param array<string, mixed> $scope @return array<string, mixed> */
    private function serializeCampaign(Campaign $campaign, array $scope): array
    {
        $participants = $this->participants($campaign, $scope);
        $missions = $this->missions($campaign, $scope);
        $rewards = $this->rewards($campaign, $scope);
        $treasures = $this->treasures($campaign, $scope);
        $qrCodes = $this->qrCodes($campaign, $scope);
        $adRequests = $this->adRequests($campaign, $scope);
        $displayDevices = $this->displayDevices($campaign, $scope);

        $internalSponsors = $participants->filter(fn (array $participant): bool => $participant['participantType'] === 'sponsor' && $participant['hub'] !== null);
        $externalSponsors = $participants->filter(fn (array $participant): bool => $participant['participantType'] === 'sponsor' && $participant['hub'] === null);
        $readyParticipants = $participants->where('onboardingStatus', 'ready')->count();
        $alignment = $this->blueprintConsistency->review($campaign);
        $stats = [
            'participants' => $participants->count(),
            'readyParticipants' => $readyParticipants,
            'internalSponsors' => $internalSponsors->count(),
            'externalSponsors' => $externalSponsors->count(),
            'missions' => $missions->count(),
            'rewards' => $rewards->count(),
            'approvedRewards' => $rewards->where('approvalStatus', 'approved')->count(),
            'pendingRewards' => $rewards->where('approvalStatus', 'pending_review')->count(),
            'treasures' => $treasures->count(),
            'qrCodes' => $qrCodes->count(),
            'adRequests' => $adRequests->count(),
            'displayDevices' => $displayDevices->count(),
        ];

        return [
            'id' => $campaign->id,
            'code' => $campaign->code,
            'name' => $campaign->name,
            'campaignType' => $campaign->campaign_type,
            'blueprintCode' => $campaign->metadata['blueprint_code'] ?? null,
            'routeReviewedAt' => $campaign->metadata['route_reviewed_at'] ?? null,
            'routeReviewNotes' => $campaign->metadata['route_review_notes'] ?? null,
            'alignment' => $alignment,
            'operationalReview' => $this->operationalReview($stats, $alignment),
            'status' => $campaign->status->value,
            'startAt' => $campaign->start_at?->toIso8601String(),
            'endAt' => $campaign->end_at?->toIso8601String(),
            'venue' => $campaign->venue ? ['id' => $campaign->venue->id, 'code' => $campaign->venue->code, 'name' => $campaign->venue->name] : null,
            'stats' => $stats,
            'participantsByHub' => $this->participantsByHub($participants),
            'redemptionOverview' => $this->redemptionOverview($campaign),
            'sponsors' => [
                'internal' => $internalSponsors->values(),
                'external' => $externalSponsors->values(),
            ],
            'journey' => [
                'entry' => ['title' => 'شروع بازدید', 'items' => $qrCodes],
                'missions' => ['title' => 'مأموریت ها', 'items' => $missions],
                'incentives' => ['title' => 'مشوق ها و گنج ها', 'items' => $rewards->merge($treasures)->values()],
                'commercial' => ['title' => 'فعال سازی تجاری', 'items' => $participants->values()],
                'media' => ['title' => 'تبلیغات و نمایشگرها', 'items' => $adRequests->merge($displayDevices)->values()],
            ],
            'operationTimeline' => $this->operationTimeline($qrCodes, $missions, $rewards, $treasures, $participants),
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $qrCodes
     * @param Collection<int, array<string, mixed>> $missions
     * @param Collection<int, array<string, mixed>> $rewards
     * @param Collection<int, array<string, mixed>> $treasures
     * @param Collection<int, array<string, mixed>> $participants
     * @return Collection<int, array<string, mixed>>
     */
    private function operationTimeline(Collection $qrCodes, Collection $missions, Collection $rewards, Collection $treasures, Collection $participants): Collection
    {
        $stepIndexes = $missions
            ->merge($rewards)
            ->merge($treasures)
            ->pluck('cycleStep.index')
            ->filter(fn (mixed $index): bool => is_numeric($index) && (int) $index > 0)
            ->map(fn (mixed $index): int => (int) $index)
            ->unique()
            ->sort()
            ->values();

        if ($stepIndexes->isEmpty()) {
            $stepIndexes = collect([1]);
        }

        $readyParticipants = $participants->where('onboardingStatus', 'ready')->values();

        return $stepIndexes->map(function (int $stepIndex) use ($qrCodes, $missions, $rewards, $treasures, $readyParticipants): array {
            $stepMissions = $missions->filter(fn (array $item): bool => (int) ($item['cycleStep']['index'] ?? 0) === $stepIndex)->values();
            $stepRewards = $rewards->filter(fn (array $item): bool => (int) ($item['cycleStep']['index'] ?? 0) === $stepIndex)->values();
            $stepTreasures = $treasures->filter(fn (array $item): bool => (int) ($item['cycleStep']['index'] ?? 0) === $stepIndex)->values();
            $approvedRewards = $stepRewards->where('approvalStatus', 'approved')->count();
            $pendingRewards = $stepRewards->where('approvalStatus', 'pending_review')->count();

            $checks = [
                ['key' => 'entry', 'title' => 'QR ورود', 'complete' => $stepIndex !== 1 || $qrCodes->isNotEmpty(), 'count' => $stepIndex === 1 ? $qrCodes->count() : 0, 'action' => $stepIndex === 1 ? 'برای شروع مسیر حداقل یک QR معتبر ثبت کنید.' : 'ورود مسیر از گام اول کنترل می‌شود.'],
                ['key' => 'mission', 'title' => 'ماموریت گام', 'complete' => $stepMissions->isNotEmpty(), 'count' => $stepMissions->count(), 'action' => 'برای این گام حداقل یک ماموریت مرتبط با چرخه ثبت کنید.'],
                ['key' => 'incentive', 'title' => 'پاداش یا گنج', 'complete' => $approvedRewards > 0 || $stepTreasures->isNotEmpty(), 'count' => $approvedRewards + $stepTreasures->count(), 'action' => 'برای این گام یک پاداش تاییدشده یا گنج قابل کشف تعریف کنید.'],
                ['key' => 'participant', 'title' => 'عضو آماده اجرا', 'complete' => $readyParticipants->isNotEmpty(), 'count' => $readyParticipants->count(), 'action' => 'حداقل یک فروشگاه، شریک یا اسپانسر آماده اجرا لازم است.'],
            ];

            $label = (string) ($stepMissions->first()['cycleStep']['label']
                ?? $stepRewards->first()['cycleStep']['label']
                ?? $stepTreasures->first()['cycleStep']['label']
                ?? ('گام '.$stepIndex));

            return [
                'index' => $stepIndex,
                'label' => $label,
                'status' => collect($checks)->every(fn (array $check): bool => (bool) $check['complete']) && $pendingRewards === 0 ? 'ready' : 'needs_attention',
                'entryItems' => $stepIndex === 1 ? $qrCodes->values() : collect(),
                'missions' => $stepMissions,
                'incentives' => $stepRewards->merge($stepTreasures)->values(),
                'participants' => $readyParticipants->take(4)->values(),
                'pendingRewards' => $pendingRewards,
                'checks' => $checks,
            ];
        })->values();
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function participants(Campaign $campaign, array $scope): Collection
    {
        return CampaignParticipant::query()
            ->where('campaign_id', $campaign->id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->where(function (Builder $query) use ($scope): void {
                $query->whereIn('venue_id', $scope['assignedVenueIds'])
                    ->orWhereIn('hub_id', $scope['hubIds'])
                    ->orWhereIn('partner_account_id', $scope['partnerIds']);
            }))
            ->with(['hub:id,code,name,hub_type', 'partnerAccount:id,code,name,partner_type,status'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (CampaignParticipant $participant): array => [
                'id' => $participant->id,
                'participantType' => $participant->participant_type,
                'participationRole' => $participant->participation_role,
                'status' => $participant->status->value,
                'onboardingStatus' => $participant->onboarding_status,
                'hub' => $participant->hub ? ['id' => $participant->hub->id, 'code' => $participant->hub->code, 'name' => $participant->hub->name, 'hubType' => $participant->hub->hub_type] : null,
                'partner' => $participant->partnerAccount ? ['id' => $participant->partnerAccount->id, 'code' => $participant->partnerAccount->code, 'name' => $participant->partnerAccount->name, 'partnerType' => $participant->partnerAccount->partner_type] : null,
                'connections' => [
                    'rewards' => (int) ($participant->metadata['connections']['rewards'] ?? 0),
                    'ads' => (int) ($participant->metadata['connections']['ads'] ?? 0),
                    'qrCodes' => (int) ($participant->metadata['connections']['qr_codes'] ?? 0),
                    'missions' => (int) ($participant->metadata['connections']['missions'] ?? 0),
                ],
            ]);
    }

    /** @param Collection<int, array<string, mixed>> $participants */
    private function participantsByHub(Collection $participants): Collection
    {
        return $participants
            ->groupBy(fn (array $participant): string => (string) data_get($participant, 'hub.id', 'external'))
            ->map(fn (Collection $items): array => [
                'hub' => $items->first()['hub'] ?? null,
                'participantsCount' => $items->count(),
                'sponsorsCount' => $items->where('participantType', 'sponsor')->count(),
                'roles' => $items->pluck('participationRole')->unique()->values(),
                'participants' => $items->values(),
            ])
            ->values();
    }

    /** @return array<string, mixed> */
    private function redemptionOverview(Campaign $campaign): array
    {
        $redemptions = RewardRedemption::query()
            ->whereHas('userReward', fn (Builder $query) => $query->where('campaign_id', $campaign->id))
            ->with([
                'partnerAccount:id,code,name,partner_type',
                'user:id,name',
                'userReward.rewardDefinition:id,code,name,reward_type',
            ])
            ->latest()
            ->get();

        return [
            'stats' => [
                'total' => $redemptions->count(),
                'pending' => $redemptions->where('status', 'pending')->count(),
                'confirmed' => $redemptions->where('status', 'confirmed')->count(),
            ],
            'latest' => $redemptions
                ->take(6)
                ->map(fn (RewardRedemption $redemption): array => [
                    'id' => $redemption->id,
                    'redemptionCode' => $redemption->redemption_code,
                    'status' => $redemption->status,
                    'rewardName' => $redemption->userReward?->rewardDefinition?->name,
                    'rewardType' => $redemption->userReward?->rewardDefinition?->reward_type,
                    'partnerName' => $redemption->partnerAccount?->name,
                    'visitorName' => $redemption->user?->name,
                    'redeemedAt' => $redemption->redeemed_at?->toIso8601String(),
                    'createdAt' => $redemption->created_at?->toIso8601String(),
                ])
                ->values(),
        ];
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function missions(Campaign $campaign, array $scope): Collection
    {
        return MissionInstance::query()
            ->where('campaign_id', $campaign->id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->where(function (Builder $query) use ($scope): void {
                $query->whereIn('venue_id', $scope['assignedVenueIds'])->orWhereIn('hub_id', $scope['hubIds']);
            }))
            ->with(['missionTemplate:id,code,title,mission_type,trigger_type,point_value', 'hub:id,code,name'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (MissionInstance $mission): array => [
                'id' => $mission->id,
                'type' => 'mission',
                'code' => $mission->code,
                'title' => $mission->title_override ?? $mission->missionTemplate?->title,
                'missionType' => $mission->missionTemplate?->mission_type,
                'triggerType' => $mission->missionTemplate?->trigger_type,
                'points' => $mission->missionTemplate?->point_value ?? 0,
                'status' => $mission->status->value,
                'cycleStep' => [
                    'index' => $mission->metadata['cycle_step_index'] ?? null,
                    'label' => $mission->metadata['cycle_step_label'] ?? null,
                ],
                'hub' => $mission->hub ? ['id' => $mission->hub->id, 'code' => $mission->hub->code, 'name' => $mission->hub->name] : null,
            ]);
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function rewards(Campaign $campaign, array $scope): Collection
    {
        return RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->where(function (Builder $query) use ($scope): void {
                $query->whereIn('venue_id', $scope['assignedVenueIds'])->orWhereIn('partner_account_id', $scope['partnerIds']);
            }))
            ->with('partnerAccount:id,code,name,partner_type')
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (RewardDefinition $reward): array => [
                'id' => $reward->id,
                'type' => 'reward',
                'code' => $reward->code,
                'name' => $reward->name,
                'rewardType' => $reward->reward_type,
                'status' => $reward->status->value,
                'approvalStatus' => $reward->metadata['approval_status'] ?? $reward->status->value,
                'availabilityStatus' => $reward->metadata['availability_status'] ?? ($reward->status->value === 'inactive' ? 'paused' : 'active'),
                'source' => $reward->metadata['source'] ?? null,
                'rewardTier' => $reward->metadata['reward_tier'] ?? null,
                'rewardOption' => $reward->metadata['reward_option'] ?? null,
                'cycleStep' => [
                    'index' => $reward->metadata['cycle_step_index'] ?? null,
                    'label' => $reward->metadata['cycle_step_label'] ?? null,
                ],
                'pointCost' => $reward->point_cost,
                'partner' => $reward->partnerAccount ? ['id' => $reward->partnerAccount->id, 'code' => $reward->partnerAccount->code, 'name' => $reward->partnerAccount->name, 'partnerType' => $reward->partnerAccount->partner_type] : null,
            ]);
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function treasures(Campaign $campaign, array $scope): Collection
    {
        return Treasure::query()
            ->where('campaign_id', $campaign->id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->whereIn('venue_id', $scope['assignedVenueIds']))
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (Treasure $treasure): array => [
                'id' => $treasure->id,
                'type' => 'treasure',
                'code' => $treasure->code,
                'name' => $treasure->name,
                'treasureType' => $treasure->treasure_type,
                'status' => $treasure->status->value,
                'cycleStep' => [
                    'index' => $treasure->metadata['cycle_step_index'] ?? null,
                    'label' => $treasure->metadata['cycle_step_label'] ?? null,
                ],
                'rewardTier' => $treasure->metadata['treasure_tier'] ?? null,
                'rewardOption' => $treasure->metadata['reveal_description'] ?? null,
            ]);
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function qrCodes(Campaign $campaign, array $scope): Collection
    {
        return QrCode::query()
            ->where('campaign_id', $campaign->id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->whereIn('venue_id', $scope['venueIds']))
            ->with('touchpoint:id,code,label')
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (QrCode $qrCode): array => [
                'id' => $qrCode->id,
                'type' => 'qr',
                'code' => $qrCode->code,
                'label' => $qrCode->label,
                'status' => $qrCode->status->value,
                'touchpoint' => $qrCode->touchpoint ? ['id' => $qrCode->touchpoint->id, 'code' => $qrCode->touchpoint->code, 'label' => $qrCode->touchpoint->label] : null,
            ]);
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function adRequests(Campaign $campaign, array $scope): Collection
    {
        return AdRequest::query()
            ->where('venue_id', $campaign->venue_id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->where(function (Builder $query) use ($scope): void {
                $query->whereIn('hub_id', $scope['hubIds'])->orWhereIn('partner_account_id', $scope['partnerIds'])->orWhereIn('venue_id', $scope['assignedVenueIds']);
            }))
            ->with(['hub:id,code,name', 'partnerAccount:id,code,name,partner_type'])
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (AdRequest $adRequest): array => [
                'id' => $adRequest->id,
                'type' => 'ad',
                'code' => $adRequest->code,
                'title' => $adRequest->title,
                'adType' => $adRequest->ad_type,
                'status' => $adRequest->status,
                'hub' => $adRequest->hub ? ['id' => $adRequest->hub->id, 'code' => $adRequest->hub->code, 'name' => $adRequest->hub->name] : null,
                'partner' => $adRequest->partnerAccount ? ['id' => $adRequest->partnerAccount->id, 'code' => $adRequest->partnerAccount->code, 'name' => $adRequest->partnerAccount->name, 'partnerType' => $adRequest->partnerAccount->partner_type] : null,
            ]);
    }

    /** @param array<string, mixed> $scope @return Collection<int, array<string, mixed>> */
    private function displayDevices(Campaign $campaign, array $scope): Collection
    {
        return DisplayDevice::query()
            ->where('venue_id', $campaign->venue_id)
            ->when(! $scope['isGlobal'], fn (Builder $query) => $query->where(function (Builder $query) use ($scope): void {
                $query->whereIn('hub_id', $scope['hubIds'])->orWhereIn('venue_id', $scope['assignedVenueIds']);
            }))
            ->with('hub:id,code,name')
            ->orderBy('created_at')
            ->get()
            ->toBase()
            ->map(fn (DisplayDevice $device): array => [
                'id' => $device->id,
                'type' => 'display',
                'code' => $device->code,
                'name' => $device->name,
                'deviceType' => $device->device_type,
                'status' => $device->status->value,
                'hub' => $device->hub ? ['id' => $device->hub->id, 'code' => $device->hub->code, 'name' => $device->hub->name] : null,
            ]);
    }
}
