<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\StoreAdRequestRequest;
use App\Services\StandaloneAdvertisingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartnerAdvertisingController extends Controller
{
    public function page(Request $request, StandaloneAdvertisingService $service): Response
    {
        return Inertia::render('partner/ads', $service->partnerOverview($request->user()));
    }

    public function index(Request $request, StandaloneAdvertisingService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->partnerOverview($request->user())]);
    }

    public function store(StoreAdRequestRequest $request, StandaloneAdvertisingService $service): JsonResponse|RedirectResponse
    {
        $adRequest = $service->createPartnerAdRequest($request->user(), $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $adRequest->id,
                    'code' => $adRequest->code,
                    'title' => $adRequest->title,
                    'status' => $adRequest->status,
                ],
            ], 201);
        }

        return back()->with('success', 'درخواست تبلیغ ثبت شد و برای تایید ارسال شد.');
    }
}
