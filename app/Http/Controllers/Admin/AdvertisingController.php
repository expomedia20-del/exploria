<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\RecordAdminAuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewAdRequestRequest;
use App\Models\AdRequest;
use App\Services\HubManagerAccessService;
use App\Services\StandaloneAdvertisingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdvertisingController extends Controller
{
    public function page(Request $request, StandaloneAdvertisingService $service): Response
    {
        return Inertia::render('admin/ads/index', $service->adminOverview($request->user()));
    }

    public function index(Request $request, StandaloneAdvertisingService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->adminOverview($request->user())]);
    }

    public function approve(ReviewAdRequestRequest $request, AdRequest $adRequest, StandaloneAdvertisingService $service, HubManagerAccessService $access, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $access->ensureCanReviewAdRequest($request->user(), $adRequest);

        $adRequest = $service->approve($request->user(), $adRequest, $request->validated());
        $this->audit($request, $adRequest, $audit, 'ad_approved');

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => [
                'id' => $adRequest->id,
                'code' => $adRequest->code,
                'status' => $adRequest->status,
            ]]);
        }

        return back()->with('success', 'درخواست تبلیغ تایید و برای انتشار زمان‌بندی شد.');
    }

    public function reject(ReviewAdRequestRequest $request, AdRequest $adRequest, StandaloneAdvertisingService $service, HubManagerAccessService $access, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $access->ensureCanReviewAdRequest($request->user(), $adRequest);

        $adRequest = $service->reject($request->user(), $adRequest, $request->validated());
        $this->audit($request, $adRequest, $audit, 'ad_rejected');

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => [
                'id' => $adRequest->id,
                'code' => $adRequest->code,
                'status' => $adRequest->status,
            ]]);
        }

        return back()->with('success', 'درخواست تبلیغ رد شد.');
    }

    private function audit(ReviewAdRequestRequest $request, AdRequest $adRequest, RecordAdminAuditAction $audit, string $action): void
    {
        $audit->execute($request->user(), $action, 'ad_request', $adRequest->id, $request->session()->getId(), [
            'code' => $adRequest->code,
            'name' => $adRequest->title,
            'status' => $adRequest->status,
        ], [
            'venue_id' => $adRequest->venue_id,
            'touchpoint_id' => $adRequest->touchpoint_id,
        ]);
    }
}
