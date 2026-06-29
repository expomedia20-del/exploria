<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMissionInstanceRequest;
use App\Http\Requests\Admin\StoreRewardDefinitionRequest;
use App\Http\Requests\Admin\StoreTreasureRequest;
use App\Services\CampaignRegistryService;
use App\Services\MissionRewardRegistryService;
use App\Services\MissionRewardBlueprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MissionRewardRegistryController extends Controller
{
    public function page(Request $request, MissionRewardRegistryService $service, MissionRewardBlueprintService $blueprints, CampaignRegistryService $campaigns): Response
    {
        $selectedCampaign = $campaigns->context($request->user(), $request->query('campaign'));
        $data = $service->overview($request->user(), $selectedCampaign['id'] ?? null);
        $blueprintCode = $request->query('blueprint')
            ?: ($selectedCampaign['blueprintCode'] ?? null)
            ?: (($selectedCampaign['campaignType'] ?? null) === 'pilot_visit' ? 'ecopark-pilot-treasure-route' : null);
        $data['selectedCampaign'] = $selectedCampaign;
        $data['selectedBlueprint'] = $blueprints->handoff($blueprintCode);

        return Inertia::render('admin/missions/index', $data);
    }

    public function index(Request $request, MissionRewardRegistryService $service, CampaignRegistryService $campaigns): JsonResponse
    {
        $selectedCampaign = $campaigns->context($request->user(), $request->query('campaign'));

        return response()->json(['status' => 'success', 'data' => $service->overview($request->user(), $selectedCampaign['id'] ?? null)]);
    }

    public function storeMission(StoreMissionInstanceRequest $request, MissionRewardRegistryService $service): JsonResponse|RedirectResponse
    {
        $mission = $service->createMission($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $mission->id, 'code' => $mission->code]], 201);
        }

        return back()->with('success', 'مأموریت کمپین ثبت شد.');
    }

    public function storeReward(StoreRewardDefinitionRequest $request, MissionRewardRegistryService $service): JsonResponse|RedirectResponse
    {
        $reward = $service->createReward($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $reward->id, 'code' => $reward->code]], 201);
        }

        return back()->with('success', 'پاداش کمپین ثبت شد.');
    }

    public function storeTreasure(StoreTreasureRequest $request, MissionRewardRegistryService $service): JsonResponse|RedirectResponse
    {
        $treasure = $service->createTreasure($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $treasure->id, 'code' => $treasure->code]], 201);
        }

        return back()->with('success', 'گنج کمپین ثبت شد.');
    }
}
