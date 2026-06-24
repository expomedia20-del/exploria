<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\StorePartnerOfferRequest;
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

        return back()->with('success', 'پیشنهاد فروشگاه ثبت شد و برای تایید ارسال شد.');
    }
}
