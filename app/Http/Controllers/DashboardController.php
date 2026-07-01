<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\ConsentLog;
use App\Models\MissionInstance;
use App\Models\OtpRequest;
use App\Models\QrCode;
use App\Models\RewardRedemption;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Venue;
use App\Models\Visit;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $latestVisits = Visit::query()
            ->with(['venue', 'touchpoint', 'campaign', 'user'])
            ->latest('occurred_at')
            ->limit(8)
            ->get()
            ->map(fn (Visit $visit): array => [
                'id' => $visit->id,
                'venueName' => $visit->venue->name,
                'touchpointLabel' => $visit->touchpoint->label,
                'campaignName' => $visit->campaign->name,
                'visitorName' => $visit->user->name,
                'status' => $visit->status,
                'occurredAt' => $visit->occurred_at->toIso8601String(),
            ]);
        $latestRedemptions = RewardRedemption::query()
            ->with(['partnerAccount:id,name', 'user:id,name', 'userReward.rewardDefinition:id,name,campaign_id', 'userReward.campaign:id,code,name'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (RewardRedemption $redemption): array => [
                'id' => $redemption->id,
                'redemptionCode' => $redemption->redemption_code,
                'status' => $redemption->status,
                'rewardName' => $redemption->userReward?->rewardDefinition?->name,
                'campaignName' => $redemption->userReward?->campaign?->name,
                'campaignCode' => $redemption->userReward?->campaign?->code,
                'partnerName' => $redemption->partnerAccount?->name,
                'visitorName' => $redemption->user?->name,
                'redeemedAt' => $redemption->redeemed_at?->toIso8601String(),
                'createdAt' => $redemption->created_at?->toIso8601String(),
            ]);
        $campaignPerformance = Campaign::query()
            ->with('venue:id,name')
            ->withCount(['visits', 'qrCodes', 'missionInstances', 'userRewards'])
            ->where('status', 'active')
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->map(function (Campaign $campaign): array {
                $completedMissions = UserMissionProgress::query()
                    ->where('status', 'completed')
                    ->whereHas('missionInstance', fn ($query) => $query->where('campaign_id', $campaign->id))
                    ->count();
                $visitsCount = (int) $campaign->getAttribute('visits_count');
                $missionInstancesCount = (int) $campaign->getAttribute('mission_instances_count');
                $expectedMissionRuns = $visitsCount * max($missionInstancesCount, 1);
                $pendingRedemptions = RewardRedemption::query()
                    ->where('status', 'pending')
                    ->whereHas('userReward', fn ($query) => $query->where('campaign_id', $campaign->id))
                    ->count();
                $confirmedRedemptions = RewardRedemption::query()
                    ->where('status', 'confirmed')
                    ->whereHas('userReward', fn ($query) => $query->where('campaign_id', $campaign->id))
                    ->count();

                return [
                    'id' => $campaign->id,
                    'code' => $campaign->code,
                    'name' => $campaign->name,
                    'venueName' => $campaign->venue?->name,
                    'visits' => $visitsCount,
                    'qrCodes' => (int) $campaign->getAttribute('qr_codes_count'),
                    'missions' => $missionInstancesCount,
                    'completedMissions' => $completedMissions,
                    'rewards' => (int) $campaign->getAttribute('user_rewards_count'),
                    'pendingRedemptions' => $pendingRedemptions,
                    'confirmedRedemptions' => $confirmedRedemptions,
                    'progressPercent' => $expectedMissionRuns > 0 ? min(100, (int) round(($completedMissions / $expectedMissionRuns) * 100)) : 0,
                ];
            })
            ->values();

        return Inertia::render('dashboard', [
            'stats' => [
                'venues' => Venue::query()->count(),
                'activeQrCodes' => QrCode::query()->where('status', 'active')->count(),
                'otpRequests' => OtpRequest::query()->count(),
                'consents' => ConsentLog::query()->count(),
                'visits' => Visit::query()->count(),
                'activeCampaigns' => Campaign::query()->where('status', 'active')->count(),
                'missionCompletions' => UserMissionProgress::query()->where('status', 'completed')->count(),
                'issuedRewards' => UserReward::query()->count(),
                'pendingRedemptions' => RewardRedemption::query()->where('status', 'pending')->count(),
                'confirmedRedemptions' => RewardRedemption::query()->where('status', 'confirmed')->count(),
                'activeMissions' => MissionInstance::query()->where('status', 'active')->count(),
            ],
            'latestVisits' => $latestVisits,
            'latestRedemptions' => $latestRedemptions,
            'campaignPerformance' => $campaignPerformance,
        ]);
    }
}
