<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewAdRequestRequest;
use App\Models\AdRequest;
use App\Services\StandaloneAdvertisingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdvertisingController extends Controller
{
    public function page(StandaloneAdvertisingService $service): Response
    {
        return Inertia::render('admin/ads/index', $service->adminOverview());
    }

    public function index(StandaloneAdvertisingService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->adminOverview()]);
    }

    public function approve(ReviewAdRequestRequest $request, AdRequest $adRequest, StandaloneAdvertisingService $service): JsonResponse|RedirectResponse
    {
        $adRequest = $service->approve($request->user(), $adRequest, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => [
                'id' => $adRequest->id,
                'code' => $adRequest->code,
                'status' => $adRequest->status,
            ]]);
        }

        return back()->with('success', 'درخواست تبلیغ تایید و برای انتشار زمان‌بندی شد.');
    }

    public function reject(ReviewAdRequestRequest $request, AdRequest $adRequest, StandaloneAdvertisingService $service): JsonResponse|RedirectResponse
    {
        $adRequest = $service->reject($request->user(), $adRequest, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => [
                'id' => $adRequest->id,
                'code' => $adRequest->code,
                'status' => $adRequest->status,
            ]]);
        }

        return back()->with('success', 'درخواست تبلیغ رد شد.');
    }
}
