<?php

namespace App\Http\Controllers;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\MissionInstance;
use App\Models\RewardDefinition;
use App\Models\RewardRedemption;
use App\Models\Treasure;
use App\Models\User;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Visit;
use App\Services\MissionFlowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantDashboardController extends Controller
{
    public function __invoke(Request $request, MissionFlowService $missionFlow): Response
    {
        $viewer = $request->user();

        abort_unless($viewer instanceof User, 401);

        $user = $this->resolveParticipant($request, $viewer);

        $latestVisit = Visit::query()
            ->with(['venue', 'touchpoint.hub.zone', 'campaign', 'qrCode'])
            ->where('user_id', $user->id)
            ->latest('occurred_at')
            ->first();

        $flow = $latestVisit ? $missionFlow->visitMissionSummary($user, $latestVisit) : null;
        $participation = $this->participationProfile($latestVisit);

        return Inertia::render('participant/dashboard', [
            'participant' => [
                'name' => $user->name,
                'email' => $user->email,
                'mode' => $participation['mode'],
                'modeLabel' => $participation['modeLabel'],
                'members' => $participation['members'],
                'teamName' => $participation['teamName'],
                'publicStatus' => $this->publicParticipationStatus($user, $latestVisit),
                'publicStatusLabel' => $this->publicParticipationStatusLabel($user, $latestVisit),
            ],
            'latestVisit' => $latestVisit ? [
                'id' => $latestVisit->id,
                'status' => $latestVisit->status,
                'occurredAt' => $latestVisit->occurred_at->toIso8601String(),
                'qrCode' => $latestVisit->qrCode?->code,
                'qrLandingUrl' => $latestVisit->qrCode?->isAvailableForLanding()
                    ? route('scan.landing', ['code' => $latestVisit->qrCode->code])
                    : null,
                'venueName' => $latestVisit->venue?->name,
                'city' => $latestVisit->venue?->city,
                'zoneName' => $latestVisit->touchpoint?->hub?->zone?->name,
                'hubName' => $latestVisit->touchpoint?->hub?->name,
                'touchpointLabel' => $latestVisit->touchpoint?->label,
                'campaignName' => $latestVisit->campaign?->name,
                'isDemo' => (bool) data_get($latestVisit->metadata, 'is_demo', false),
            ] : null,
            'missionFlow' => $flow,
            'journey' => $this->journeySummary($user, $latestVisit, $flow),
            'viewerMode' => [
                'canPreviewVisitors' => $this->canPreviewVisitors($viewer),
                'isAdminPreview' => $viewer->id !== $user->id,
                'currentVisitorId' => $user->role === UserRole::Visitor ? $user->id : null,
                'previewOptions' => $this->visitorPreviewOptions($viewer),
            ],
        ]);
    }

    public function startParticipation(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);
        abort_unless($user->role === UserRole::Visitor, 403);

        $data = $request->validate([
            'mode' => ['required', 'string', Rule::in(['individual', 'family', 'team'])],
        ]);

        $user->update([
            'public_participation_status' => 'participant',
            'public_participation_mode' => $data['mode'],
        ]);

        return back()->with('success', 'وضعیت شما به مشارکت‌کننده فعال تغییر کرد. حالا می‌توانید کمپین را انتخاب یا QR را اسکن کنید.');
    }

    /**
     * @param  array<string, mixed>|null  $flow
     * @return array<string, mixed>
     */
    private function journeySummary(User $user, ?Visit $latestVisit, ?array $flow): array
    {
        $earnedPoints = (int) UserMissionProgress::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('points_awarded');
        $spentPoints = (int) UserReward::query()
            ->where('user_rewards.user_id', $user->id)
            ->join('reward_definitions', 'reward_definitions.id', '=', 'user_rewards.reward_definition_id')
            ->whereIn('user_rewards.status', ['redeemed', 'consumed'])
            ->sum('reward_definitions.point_cost');
        $redeemedCount = RewardRedemption::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['confirmed', 'redeemed'])
            ->count();

        $visits = Visit::query()
            ->with(['venue:id,name,city', 'touchpoint.hub:id,name', 'campaign:id,name,code'])
            ->where('user_id', $user->id)
            ->latest('occurred_at')
            ->limit(8)
            ->get();
        $visitPoints = UserMissionProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('visit_id', $visits->pluck('id'))
            ->where('status', 'completed')
            ->selectRaw('visit_id, sum(points_awarded) as points')
            ->groupBy('visit_id')
            ->pluck('points', 'visit_id');

        $partners = RewardRedemption::query()
            ->with([
                'partnerAccount:id,name,partner_type',
                'userReward.rewardDefinition:id,name,reward_type,point_cost',
                'userReward.campaign:id,name',
            ])
            ->where('user_id', $user->id)
            ->whereNotNull('partner_account_id')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (RewardRedemption $redemption): array => [
                'name' => $redemption->partnerAccount->name ?? 'واحد تجاری',
                'type' => $redemption->partnerAccount?->partner_type,
                'rewardName' => $redemption->userReward?->rewardDefinition?->name,
                'rewardType' => $redemption->userReward?->rewardDefinition?->reward_type,
                'rewardTypeLabel' => $this->rewardTypeLabel($redemption->userReward?->rewardDefinition?->reward_type),
                'campaignName' => $redemption->userReward?->campaign?->name,
                'pointCost' => $redemption->userReward?->rewardDefinition?->point_cost,
                'status' => $redemption->status,
                'redeemedAt' => $redemption->redeemed_at?->toIso8601String(),
            ])
            ->values();

        $activeCampaignModels = $this->publicActiveCampaigns($latestVisit);
        $activeCampaignIds = $activeCampaignModels->pluck('id');
        $latestVisitsByCampaign = Visit::query()
            ->where('user_id', $user->id)
            ->whereIn('campaign_id', $activeCampaignIds)
            ->latest('occurred_at')
            ->get()
            ->unique('campaign_id')
            ->keyBy('campaign_id');
        $missionTotalsByCampaign = MissionInstance::query()
            ->whereIn('campaign_id', $activeCampaignIds)
            ->where('status', RecordStatus::Active)
            ->selectRaw('campaign_id, count(*) as total')
            ->groupBy('campaign_id')
            ->pluck('total', 'campaign_id');
        $completedMissionsByCampaign = UserMissionProgress::query()
            ->join('mission_instances', 'mission_instances.id', '=', 'user_mission_progress.mission_instance_id')
            ->where('user_mission_progress.user_id', $user->id)
            ->where('user_mission_progress.status', 'completed')
            ->whereIn('mission_instances.campaign_id', $activeCampaignIds)
            ->selectRaw('mission_instances.campaign_id, count(*) as completed')
            ->groupBy('mission_instances.campaign_id')
            ->pluck('completed', 'mission_instances.campaign_id');

        $activeCampaigns = $activeCampaignModels
            ->map(function (Campaign $campaign) use ($latestVisitsByCampaign, $missionTotalsByCampaign, $completedMissionsByCampaign): array {
                $qr = $campaign->qrCodes->first(fn ($qr): bool => $qr->isAvailableForLanding());
                $visit = $latestVisitsByCampaign->get($campaign->id);
                $totalMissions = (int) ($missionTotalsByCampaign[$campaign->id] ?? 0);
                $completedMissions = (int) ($completedMissionsByCampaign[$campaign->id] ?? 0);

                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'code' => $campaign->code,
                    'venueName' => $campaign->venue?->name,
                    'city' => $campaign->venue?->city,
                    'scanUrl' => $qr ? route('scan.landing', ['code' => $qr->code]) : null,
                    'hasVisit' => $visit !== null,
                    'latestVisitId' => $visit?->id,
                    'lastVisitedAt' => $visit?->occurred_at?->toIso8601String(),
                    'completedMissions' => $completedMissions,
                    'totalMissions' => $totalMissions,
                    'progressPercent' => $totalMissions > 0
                        ? min(100, (int) round(($completedMissions / $totalMissions) * 100))
                        : 0,
                ];
            })
            ->values();

        $rewardCatalog = RewardDefinition::query()
            ->with(['campaign:id,name,code', 'partnerAccount:id,name,partner_type'])
            ->withCount('userRewards')
            ->whereIn('campaign_id', $activeCampaignIds)
            ->where('status', RecordStatus::Active)
            ->orderBy('point_cost')
            ->limit(10)
            ->get()
            ->map(fn (RewardDefinition $reward): array => [
                'id' => $reward->id,
                'name' => $reward->name,
                'rewardType' => $reward->reward_type,
                'rewardTypeLabel' => $this->rewardTypeLabel($reward->reward_type),
                'campaignName' => $reward->campaign?->name,
                'campaignCode' => $reward->campaign?->code,
                'partnerName' => $reward->partnerAccount?->name,
                'partnerType' => $reward->partnerAccount?->partner_type,
                'pointCost' => $reward->point_cost,
                'stockQuantity' => $reward->stock_quantity,
                'remainingStock' => $reward->stock_quantity === null
                    ? null
                    : max(0, (int) $reward->stock_quantity - (int) $reward->getAttribute('user_rewards_count')),
                'source' => $reward->metadata['source'] ?? null,
                'tier' => $reward->metadata['reward_tier'] ?? null,
            ])
            ->values();

        $rewardWallet = UserReward::query()
            ->with([
                'rewardDefinition:id,name,reward_type,point_cost,partner_account_id',
                'rewardDefinition.partnerAccount:id,name,partner_type',
                'campaign:id,name,code',
                'redemptions.partnerAccount:id,name,partner_type',
            ])
            ->where('user_id', $user->id)
            ->latest('awarded_at')
            ->limit(12)
            ->get()
            ->map(function (UserReward $userReward): array {
                $redemption = $userReward->redemptions->sortByDesc('redeemed_at')->first();

                return [
                    'id' => $userReward->id,
                    'status' => $userReward->status,
                    'awardedAt' => $userReward->awarded_at?->toIso8601String(),
                    'expiresAt' => $userReward->expires_at?->toIso8601String(),
                    'campaignName' => $userReward->campaign?->name,
                    'campaignCode' => $userReward->campaign?->code,
                    'rewardName' => $userReward->rewardDefinition?->name,
                    'rewardType' => $userReward->rewardDefinition?->reward_type,
                    'rewardTypeLabel' => $this->rewardTypeLabel($userReward->rewardDefinition?->reward_type),
                    'pointCost' => $userReward->rewardDefinition?->point_cost,
                    'partnerName' => $redemption?->partnerAccount->name ?? $userReward->rewardDefinition?->partnerAccount?->name,
                    'redemptionCode' => $redemption?->redemption_code,
                    'redemptionStatus' => $redemption?->status,
                    'redeemedAt' => $redemption?->redeemed_at?->toIso8601String(),
                ];
            })
            ->values();

        $treasures = Treasure::query()
            ->with(['campaign:id,name', 'missionInstance.progressRecords' => fn ($query) => $query->where('user_id', $user->id)])
            ->whereHas('missionInstance.progressRecords', fn ($query) => $query
                ->where('user_id', $user->id)
                ->where('status', 'completed'))
            ->limit(8)
            ->get()
            ->map(fn (Treasure $treasure): array => [
                'name' => $treasure->name,
                'type' => $treasure->treasure_type,
                'campaignName' => $treasure->campaign?->name,
            ])
            ->values();

        $missions = is_array($flow['missions'] ?? null) ? $flow['missions'] : [];
        $nextMission = collect($missions)
            ->first(fn ($mission): bool => in_array($mission['status'] ?? null, ['started', 'available', 'locked'], true));
        $potentialNextPoints = (int) ($nextMission['points'] ?? 0);

        return [
            'points' => [
                'earned' => $earnedPoints,
                'spent' => $spentPoints,
                'stored' => max(0, $earnedPoints - $spentPoints),
                'redeemedRewards' => $redeemedCount,
                'nextPotential' => $potentialNextPoints,
            ],
            'activeCampaigns' => $activeCampaigns,
            'rewardCatalog' => $rewardCatalog,
            'rewardWallet' => $rewardWallet,
            'history' => $visits->map(fn (Visit $visit): array => [
                'id' => $visit->id,
                'venueName' => $visit->venue?->name,
                'city' => $visit->venue?->city,
                'campaignName' => $visit->campaign?->name,
                'campaignCode' => $visit->campaign?->code,
                'hubName' => $visit->touchpoint?->hub?->name,
                'status' => $visit->status,
                'occurredAt' => $visit->occurred_at->toIso8601String(),
                'points' => (int) ($visitPoints[$visit->id] ?? 0),
            ])->values(),
            'partners' => $partners,
            'treasures' => $treasures,
            'nextAction' => [
                'label' => $nextMission['title'] ?? ($latestVisit ? 'ادامه مسیر همین کمپین' : 'انتخاب یا اسکن یک کمپین'),
                'description' => $latestVisit
                    ? 'برای دریافت امتیاز و فعال شدن پاداش بعدی، مسیر همین بازدید را ادامه دهید.'
                    : 'با انتخاب یک کمپین یا اسکن QR، مسیر مشارکت و کیف پاداش فعال می‌شود.',
                'href' => $latestVisit ? route('visits.show', ['visit' => $latestVisit->id]) : null,
            ],
        ];
    }

    /** @return Collection<int, Campaign> */
    private function publicActiveCampaigns(?Visit $latestVisit): Collection
    {
        return Campaign::query()
            ->with(['venue:id,name,city', 'qrCodes' => fn ($query) => $query->with(['venue', 'touchpoint', 'campaign'])->orderBy('created_at')])
            ->where('status', RecordStatus::Active)
            ->orderByDesc('start_at')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn (Campaign $campaign): string => $this->publicCampaignGroupKey($campaign))
            ->map(function (Collection $campaigns) use ($latestVisit): Campaign {
                $latestVisitCampaign = $latestVisit
                    ? $campaigns->first(fn (Campaign $campaign): bool => $campaign->id === $latestVisit->campaign_id)
                    : null;

                if ($latestVisitCampaign instanceof Campaign) {
                    return $latestVisitCampaign;
                }

                return $campaigns
                    ->sortBy(fn (Campaign $campaign): int => $this->publicCampaignPriority($campaign))
                    ->first();
            })
            ->values()
            ->take(6);
    }

    private function publicCampaignGroupKey(Campaign $campaign): string
    {
        return implode('|', [
            $campaign->venue_id,
            trim($campaign->name),
        ]);
    }

    private function isStressDemoCampaign(Campaign $campaign): bool
    {
        return (bool) data_get($campaign->metadata, 'stress_demo', false)
            || $campaign->code === 'ecopark-online-treasure-map-game-campaign'
            || str_contains($campaign->code, 'stress-demo');
    }

    private function publicCampaignPriority(Campaign $campaign): int
    {
        if ($campaign->code === 'ecopark-pilot-1405') {
            return 0;
        }

        return $this->isStressDemoCampaign($campaign) ? 2 : 1;
    }

    private function rewardTypeLabel(?string $type): string
    {
        return match ($type) {
            'partner_coupon' => 'کوپن فروشگاهی',
            'sponsor_discount', 'sponsor_discount_treasure' => 'تخفیف اسپانسری',
            'sponsor_product', 'sponsor_product_treasure' => 'هدیه محصولی',
            'sponsor_prize_draw' => 'قرعه‌کشی اسپانسری',
            'sponsor_reward' => 'پاداش اسپانسری',
            'badge' => 'نشان تجربه',
            'mission_unlock' => 'باز شدن مرحله',
            default => $type ?: 'پاداش',
        };
    }

    private function resolveParticipant(Request $request, User $viewer): User
    {
        $previewId = $request->integer('visitor_id');

        if ($previewId > 0) {
            abort_unless($this->canPreviewVisitors($viewer), 403);

            return User::query()
                ->whereKey($previewId)
                ->where('role', UserRole::Visitor)
                ->firstOrFail();
        }

        return $viewer;
    }

    private function canPreviewVisitors(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Operator], true);
    }

    /** @return array<int, array{id: int, name: string, email: string, visitsCount: int}> */
    private function visitorPreviewOptions(User $viewer): array
    {
        if (! $this->canPreviewVisitors($viewer)) {
            return [];
        }

        return User::query()
            ->where('role', UserRole::Visitor)
            ->whereHas('visits', fn (Builder $query): Builder => $query)
            ->select(['id', 'name', 'email'])
            ->withCount('visits')
            ->orderByDesc('visits_count')
            ->orderBy('name')
            ->limit(30)
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'visitsCount' => (int) $user->visits_count,
            ])
            ->values()
            ->all();
    }

    /** @return array{mode: string, modeLabel: string, teamName: string|null, members: array<int, string>} */
    private function participationProfile(?Visit $visit): array
    {
        $mode = (string) data_get($visit?->metadata, 'participation_mode', 'individual');
        $members = data_get($visit?->metadata, 'participants', []);

        if (! is_array($members) || $members === []) {
            $members = match ($mode) {
                'family' => ['سرپرست خانواده', 'همراه کودک', 'عضو خانواده'],
                'team' => ['عضو اول تیم', 'عضو دوم تیم'],
                default => ['بازدیدکننده اصلی'],
            };
        }

        return [
            'mode' => $mode,
            'modeLabel' => match ($mode) {
                'family' => 'خانوادگی',
                'team' => 'تیمی',
                default => 'انفرادی',
            },
            'teamName' => data_get($visit?->metadata, 'team_name'),
            'members' => array_values(array_map('strval', $members)),
        ];
    }

    private function publicParticipationStatus(User $user, ?Visit $visit): string
    {
        if ($visit || $user->visits()->exists()) {
            return 'participant';
        }

        return (string) ($user->public_participation_status ?? 'registered');
    }

    private function publicParticipationStatusLabel(User $user, ?Visit $visit): string
    {
        $status = $this->publicParticipationStatus($user, $visit);
        $mode = (string) data_get($visit?->metadata, 'participation_mode', $user->public_participation_mode ?? 'individual');

        if ($status !== 'participant') {
            return 'کاربر عادی';
        }

        return match ($mode) {
            'family' => 'مشارکت‌کننده خانوادگی',
            'team' => 'مشارکت‌کننده تیمی',
            default => 'مشارکت‌کننده فردی',
        };
    }
}
