<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignRequest;
use App\Models\Campaign;
use App\Services\CampaignRegistryService;
use App\Services\MissionRewardBlueprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignRegistryController extends Controller
{
    public function page(Request $request, CampaignRegistryService $service, MissionRewardBlueprintService $blueprints): Response
    {
        return Inertia::render('admin/campaigns/index', [
            'campaigns' => $service->list($request->user()),
            'venueOptions' => $service->venueOptions($request->user()),
            'selectedCampaign' => $service->context($request->user(), $request->query('campaign')),
            'selectedBlueprint' => $blueprints->handoff($request->query('blueprint')),
        ]);
    }

    public function index(Request $request, CampaignRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->list($request->user())]);
    }

    public function store(StoreCampaignRequest $request, CampaignRegistryService $service): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $campaign = $service->create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $campaign->id,
                    'code' => $campaign->code,
                    'name' => $campaign->name,
                ],
            ], 201);
        }

        if (! empty($validated['campaign_id'])) {
            return back()->with('success', 'کمپین ویرایش شد.');
        }

        return redirect()
            ->route('admin.campaign-builder.page', array_filter([
                'campaign' => $campaign->code,
                'blueprint' => $campaign->metadata['blueprint_code'] ?? null,
                'blueprint_action' => 'build',
            ]))
            ->with('success', 'کمپین جدید ثبت شد.');
    }

    public function destroy(Request $request, Campaign $campaign, CampaignRegistryService $service): JsonResponse|RedirectResponse
    {
        $service->delete($campaign);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('success', 'کمپین حذف شد.');
    }
}
