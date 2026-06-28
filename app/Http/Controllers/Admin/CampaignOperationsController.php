<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CampaignOperationsBlueprintService;
use App\Services\MissionRewardBlueprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignOperationsController extends Controller
{
    public function page(Request $request, CampaignOperationsBlueprintService $service, MissionRewardBlueprintService $blueprints): Response
    {
        $data = $service->overview($request->user());
        $data['selectedBlueprint'] = $blueprints->handoff($request->query('blueprint'));

        return Inertia::render('admin/campaign-operations/index', $data);
    }

    public function index(Request $request, CampaignOperationsBlueprintService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview($request->user())]);
    }
}