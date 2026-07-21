<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\ConsentLog;
use App\Models\MissionInstance;
use App\Models\OtpRequest;
use App\Models\QrCode;
use App\Models\RewardRedemption;
use App\Models\ScanEvent;
use App\Models\UserMissionProgress;
use App\Models\UserReward;
use App\Models\Venue;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user?->role === UserRole::Visitor) {
            return redirect()->route('participant.dashboard');
        }

        if ($user?->role === UserRole::ShopPartner) {
            return redirect()->route('partner.dashboard');
        }

        if ($user?->role === UserRole::Sponsor) {
            return redirect()->route('sponsor.dashboard');
        }

        if ($user?->role === UserRole::HubManager) {
            return redirect()->route('ravaq.dashboard');
        }

        abort_unless(in_array($user?->role, [
            UserRole::Admin,
            UserRole::RegionalAdmin,
            UserRole::Operator,
            UserRole::Viewer,
        ], true), 403);

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
        $operationalAlerts = $campaignPerformance
            ->flatMap(function (array $campaign): array {
                $alerts = [];

                if ($campaign['visits'] === 0) {
                    $alerts[] = [
                        'key' => 'no_visits_'.$campaign['id'],
                        'severity' => 'warning',
                        'title' => 'کمپین فعال هنوز بازدید ندارد',
                        'message' => 'QR و مسیر ورود کمپین '.$campaign['name'].' را بررسی کنید.',
                        'actionLabel' => 'بررسی QR و نقشه عملیات',
                        'actionHref' => '/admin/campaign-operations?campaign='.$campaign['code'],
                    ];
                }

                if ($campaign['pendingRedemptions'] > 0) {
                    $alerts[] = [
                        'key' => 'pending_redemptions_'.$campaign['id'],
                        'severity' => 'attention',
                        'title' => 'پاداش در انتظار تحویل',
                        'message' => $campaign['pendingRedemptions'].' پاداش در کمپین '.$campaign['name'].' منتظر تایید فروشگاه یا اسپانسر است.',
                        'actionLabel' => 'مشاهده پنل شریک',
                        'actionHref' => '/partner/dashboard?campaign='.$campaign['code'],
                    ];
                }

                if ($campaign['visits'] > 0 && $campaign['progressPercent'] < 25) {
                    $alerts[] = [
                        'key' => 'low_progress_'.$campaign['id'],
                        'severity' => 'warning',
                        'title' => 'پیشرفت مأموریت‌ها پایین است',
                        'message' => 'کاربران وارد کمپین '.$campaign['name'].' شده‌اند اما تکمیل مأموریت‌ها هنوز پایین است.',
                        'actionLabel' => 'بازبینی مأموریت‌ها',
                        'actionHref' => '/admin/missions?campaign='.$campaign['code'],
                    ];
                }

                return $alerts;
            })
            ->take(8)
            ->values();

        return Inertia::render('dashboard', [
            'stats' => [
                'venues' => Venue::query()->count(),
                'activeQrCodes' => QrCode::query()->where('status', 'active')->count(),
                'otpRequests' => OtpRequest::query()->count(),
                'consents' => ConsentLog::query()->count(),
                'scans' => ScanEvent::query()->count(),
                'acceptedScans' => ScanEvent::query()->where('result', 'accepted')->count(),
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
            'operationalAlerts' => $operationalAlerts,
            'campaignPerformance' => $campaignPerformance,
        ]);
    }
}
