<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\StorePartnerOfferRequest;
use App\Http\Requests\Partner\UpdatePartnerOfferRequest;
use App\Models\RewardDefinition;
use App\Services\PartnerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PartnerOfferController extends Controller
{
    public function store(StorePartnerOfferRequest $request, PartnerDashboardService $service): JsonResponse|RedirectResponse
    {
        $reward = $service->createOffer($request->user(), $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $reward->id,
                    'code' => $reward->code,
                    'name' => $reward->name,
                    'status' => $reward->status->value,
                ],
            ], 201);
        }

        return back()->with('success', 'پیشنهاد پاداش برای بررسی ادمین ثبت شد.');
    }

    public function update(UpdatePartnerOfferRequest $request, RewardDefinition $reward, PartnerDashboardService $service): JsonResponse|RedirectResponse
    {
        $reward = $service->updateOffer($request->user(), $reward, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $service->serializeRewardDefinition($reward),
            ]);
        }

        return back()->with('success', 'تنظیمات پیشنهاد پاداش ذخیره شد.');
    }
}
