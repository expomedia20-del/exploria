<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\RecordStatus;
use App\Models\Campaign;
use App\Models\RewardRedemption;
use App\Models\Treasure;
use App\Models\User;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Visit;
use App\Services\MissionFlowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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

    /** @param array<string, mixed>|null $flow */
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
            ->with('partnerAccount:id,name,partner_type')
            ->where('user_id', $user->id)
            ->whereNotNull('partner_account_id')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (RewardRedemption $redemption): array => [
                'name' => $redemption->partnerAccount?->name ?? 'واحد تجاری',
                'type' => $redemption->partnerAccount?->partner_type,
                'status' => $redemption->status,
                'redeemedAt' => $redemption->redeemed_at?->toIso8601String(),
            ])
            ->values();

        $activeCampaigns = Campaign::query()
            ->with(['venue:id,name,city', 'qrCodes' => fn ($query) => $query->with(['venue', 'touchpoint', 'campaign'])->orderBy('created_at')])
            ->where('status', RecordStatus::Active)
            ->orderByDesc('start_at')
            ->limit(6)
            ->get()
            ->map(function (Campaign $campaign): array {
                $qr = $campaign->qrCodes->first(fn ($qr): bool => $qr->isAvailableForLanding());

                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'code' => $campaign->code,
                    'venueName' => $campaign->venue?->name,
                    'city' => $campaign->venue?->city,
                    'scanUrl' => $qr ? route('scan.landing', ['code' => $qr->code]) : null,
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

        $nextMission = collect($flow['missions'] ?? [])
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
}
