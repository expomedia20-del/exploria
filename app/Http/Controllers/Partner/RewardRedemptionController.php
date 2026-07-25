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
        $validated = $request->validated();
        $redemption = $service->confirmRedemption(
            $request->user(),
            $validated['redemption_code'],
            $validated,
        );
        $redemption->loadMissing('userReward.rewardDefinition');
        $reward = $redemption->userReward?->rewardDefinition;
        $purchaseConfirmed = (bool) data_get($redemption->metadata, 'purchase_confirmed', false);
        $purchaseAmount = data_get($redemption->metadata, 'purchase_amount_irr');
        $recordEvent->execute('reward_redeemed', $request->user(), $request->session()->getId(), 'reward_redemption', $redemption->id, [
            'source' => 'partner_confirmation',
            'user_reward_id' => $redemption->user_reward_id,
            'reward_definition_id' => $reward?->id,
            'partner_account_id' => $redemption->partner_account_id,
            'conversion_type' => data_get($redemption->metadata, 'conversion_type', 'reward_only'),
            'purchase_confirmed' => $purchaseConfirmed,
            'purchase_amount_irr' => $purchaseAmount,
            'receipt_reference' => data_get($redemption->metadata, 'receipt_reference'),
            'quality_flag' => $reward === null,
        ], [
            'venue_id' => $reward?->venue_id,
            'campaign_id' => $redemption->userReward?->campaign_id,
        ]);
        $recordEvent->execute('merchant_visited', $request->user(), $request->session()->getId(), 'partner_account', $redemption->partner_account_id, [
            'source' => 'reward_redemption',
            'reward_redemption_id' => $redemption->id,
            'reward_definition_id' => $reward?->id,
            'partner_account_id' => $redemption->partner_account_id,
            'conversion_type' => data_get($redemption->metadata, 'conversion_type', 'reward_only'),
            'purchase_confirmed' => $purchaseConfirmed,
            'purchase_amount_irr' => $purchaseAmount,
            'receipt_reference' => data_get($redemption->metadata, 'receipt_reference'),
            'quality_flag' => $reward === null || $redemption->partner_account_id === null,
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
                    'purchaseConfirmed' => $purchaseConfirmed,
                    'purchaseAmountIrr' => $purchaseAmount,
                ],
            ]);
        }

        return back()->with('success', 'مصرف پاداش تایید شد.');
    }
}
