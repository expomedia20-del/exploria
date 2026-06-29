<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignRouteReviewRequest;
use App\Services\CampaignRegistryService;
use App\Services\CampaignOperationsBlueprintService;
use App\Services\MissionRewardBlueprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignOperationsController extends Controller
{
    public function page(Request $request, CampaignOperationsBlueprintService $service, MissionRewardBlueprintService $blueprints, CampaignRegistryService $campaigns): Response
    {
        $selectedCampaign = $campaigns->context($request->user(), $request->query('campaign'));
        $data = $service->overview($request->user(), $selectedCampaign['id'] ?? null);
        $blueprintCode = $request->query('blueprint')
            ?: ($selectedCampaign['blueprintCode'] ?? null)
            ?: (($selectedCampaign['campaignType'] ?? null) === 'pilot_visit' ? 'ecopark-pilot-treasure-route' : null);
        $data['selectedCampaign'] = $selectedCampaign;
        $data['selectedBlueprint'] = $blueprints->handoff($blueprintCode);

        return Inertia::render('admin/campaign-operations/index', $data);
    }

    public function index(Request $request, CampaignOperationsBlueprintService $service, CampaignRegistryService $campaigns): JsonResponse
    {
        $selectedCampaign = $campaigns->context($request->user(), $request->query('campaign'));

        return response()->json(['status' => 'success', 'data' => $service->overview($request->user(), $selectedCampaign['id'] ?? null)]);
    }

    public function review(StoreCampaignRouteReviewRequest $request, CampaignOperationsBlueprintService $service): JsonResponse|RedirectResponse
    {
        $campaign = $service->markRouteReviewed($request->user(), $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $campaign->id, 'code' => $campaign->code]]);
        }

        return back()->with('success', 'مسیر عملیاتی کمپین بازبینی و تایید شد.');
    }

    public function resetReview(StoreCampaignRouteReviewRequest $request, CampaignOperationsBlueprintService $service): JsonResponse|RedirectResponse
    {
        $campaign = $service->resetRouteReview($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $campaign->id, 'code' => $campaign->code]]);
        }

        return back()->with('success', 'بازبینی مسیر عملیات حذف شد.');
    }
}
