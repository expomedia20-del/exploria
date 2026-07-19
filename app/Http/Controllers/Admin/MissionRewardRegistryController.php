<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\RecordAdminAuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignSponsorIncentiveRequest;
use App\Http\Requests\Admin\StoreMissionInstanceRequest;
use App\Http\Requests\Admin\StoreRewardDefinitionRequest;
use App\Http\Requests\Admin\StoreTreasureRequest;
use App\Models\MissionInstance;
use App\Models\RewardDefinition;
use App\Models\Treasure;
use App\Services\CampaignRegistryService;
use App\Services\MissionRewardBlueprintService;
use App\Services\MissionRewardRegistryService;
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

    public function storeMission(StoreMissionInstanceRequest $request, MissionRewardRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $mission = $service->createMission($request->validated());
        $audit->execute($request->user(), $mission->wasRecentlyCreated ? 'mission_created' : 'mission_updated', 'mission', $mission->id, $request->session()->getId(), [
            'code' => $mission->code,
            'name' => $mission->title_override,
            'status' => $mission->status->value,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $mission->id, 'code' => $mission->code]], 201);
        }

        return back()->with('success', 'مأموریت کمپین ثبت شد.');
    }

    public function storeReward(StoreRewardDefinitionRequest $request, MissionRewardRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $reward = $service->createReward($request->validated());
        $audit->execute($request->user(), $reward->wasRecentlyCreated ? 'reward_created' : 'reward_updated', 'reward', $reward->id, $request->session()->getId(), [
            'code' => $reward->code,
            'name' => $reward->name,
            'status' => $reward->status->value,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $reward->id, 'code' => $reward->code]], 201);
        }

        return back()->with('success', 'پاداش کمپین ثبت شد.');
    }

    public function storeTreasure(StoreTreasureRequest $request, MissionRewardRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $treasure = $service->createTreasure($request->validated());
        $audit->execute($request->user(), $treasure->wasRecentlyCreated ? 'treasure_created' : 'treasure_updated', 'treasure', $treasure->id, $request->session()->getId(), [
            'code' => $treasure->code,
            'name' => $treasure->name,
            'status' => $treasure->status->value,
        ], [
            'venue_id' => $treasure->venue_id,
            'campaign_id' => $treasure->campaign_id,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $treasure->id, 'code' => $treasure->code]], 201);
        }

        return back()->with('success', 'گنج کمپین ثبت شد.');
    }

    public function assignSponsorIncentive(AssignSponsorIncentiveRequest $request, RewardDefinition $reward, MissionRewardRegistryService $service): JsonResponse|RedirectResponse
    {
        $reward = $service->assignSponsorIncentive($reward, $request->validated(), $request->user());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $reward->id, 'code' => $reward->code]]);
        }

        return back()->with('success', 'مشوق اسپانسری به ماموریت، سطح پاداش و ردیابی موجودی وصل شد.');
    }

    public function destroyMission(Request $request, MissionInstance $mission, MissionRewardRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $missionId = $mission->id;
        $payload = ['code' => $mission->code, 'name' => $mission->title_override];
        $service->deleteMission($mission);
        $audit->execute($request->user(), 'mission_deleted', 'mission', $missionId, $request->session()->getId(), $payload);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('success', 'مأموریت کمپین حذف شد.');
    }

    public function destroyReward(Request $request, RewardDefinition $reward, MissionRewardRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $rewardId = $reward->id;
        $payload = ['code' => $reward->code, 'name' => $reward->name];
        $service->deleteReward($reward);
        $audit->execute($request->user(), 'reward_deleted', 'reward', $rewardId, $request->session()->getId(), $payload);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('success', 'پاداش کمپین حذف شد.');
    }

    public function destroyTreasure(Request $request, Treasure $treasure, MissionRewardRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $treasureId = $treasure->id;
        $payload = ['code' => $treasure->code, 'name' => $treasure->name];
        $attribution = ['venue_id' => $treasure->venue_id, 'campaign_id' => $treasure->campaign_id];
        $service->deleteTreasure($treasure);
        $audit->execute($request->user(), 'treasure_deleted', 'treasure', $treasureId, $request->session()->getId(), $payload, $attribution);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('success', 'گنج کمپین حذف شد.');
    }
}
