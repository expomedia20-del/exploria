<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignParticipantRequest;
use App\Models\CampaignParticipant;
use App\Services\CampaignParticipantRegistryService;
use App\Services\CampaignRegistryService;
use App\Services\MissionRewardBlueprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignParticipantController extends Controller
{
    public function page(Request $request, CampaignParticipantRegistryService $service, MissionRewardBlueprintService $blueprints, CampaignRegistryService $campaigns): Response
    {
        $selectedCampaign = $campaigns->context($request->user(), $request->query('campaign'));
        $data = $service->overview($request->user(), $selectedCampaign['id'] ?? null);
        $data['selectedCampaign'] = $selectedCampaign;
        $data['selectedBlueprint'] = $blueprints->handoff($request->query('blueprint'));

        return Inertia::render('admin/campaign-participants/index', $data);
    }

    public function index(Request $request, CampaignParticipantRegistryService $service, CampaignRegistryService $campaigns): JsonResponse
    {
        $selectedCampaign = $campaigns->context($request->user(), $request->query('campaign'));

        return response()->json(['status' => 'success', 'data' => $service->overview($request->user(), $selectedCampaign['id'] ?? null)]);
    }

    public function store(StoreCampaignParticipantRequest $request, CampaignParticipantRegistryService $service): JsonResponse|RedirectResponse
    {
        $participant = $service->createParticipant($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => ['id' => $participant->id],
            ], 201);
        }

        return back()->with('success', 'عضو کمپین ثبت شد.');
    }

    public function destroy(Request $request, CampaignParticipant $participant, CampaignParticipantRegistryService $service): JsonResponse|RedirectResponse
    {
        $service->deleteParticipant($participant);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('success', 'عضو کمپین حذف شد.');
    }
}
