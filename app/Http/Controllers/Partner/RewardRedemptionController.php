<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\ConfirmRewardRedemptionRequest;
use App\Services\PartnerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class RewardRedemptionController extends Controller
{
    public function confirm(ConfirmRewardRedemptionRequest $request, PartnerDashboardService $service): JsonResponse|RedirectResponse
    {
        $redemption = $service->confirmRedemption($request->user(), $request->validated('redemption_code'));

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
