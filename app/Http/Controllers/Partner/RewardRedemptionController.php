<?php

namespace App\Http\Controllers\Partner;

use App\Actions\Events\RecordDomainEventAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\ConfirmRewardRedemptionRequest;
use App\Services\PartnerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class RewardRedemptionController extends Controller
{
    public function confirm(ConfirmRewardRedemptionRequest $request, PartnerDashboardService $service, RecordDomainEventAction $recordEvent): JsonResponse|RedirectResponse
    {
        $redemption = $service->confirmRedemption($request->user(), $request->validated('redemption_code'));
        $redemption->loadMissing('userReward.rewardDefinition');
        $reward = $redemption->userReward?->rewardDefinition;
        $recordEvent->execute('reward_redeemed', $request->user(), $request->session()->getId(), 'reward_redemption', $redemption->id, [
            'source' => 'partner_confirmation',
            'user_reward_id' => $redemption->user_reward_id,
            'reward_definition_id' => $reward?->id,
            'partner_account_id' => $redemption->partner_account_id,
            'quality_flag' => $reward === null,
        ], [
            'venue_id' => $reward?->venue_id,
            'campaign_id' => $redemption->userReward?->campaign_id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $redemption->id,
                    'redemptionCode' => $redemption->redemption_code,
                    'status' => $redemption->status,
                ],
            ]);
        }

        return back()->with('success', 'مصرف پاداش تایید شد.');
    }
}
