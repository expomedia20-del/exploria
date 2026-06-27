<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MissionRewardBlueprintService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class MissionRewardBlueprintController extends Controller
{
    public function page(MissionRewardBlueprintService $service): Response
    {
        return Inertia::render('admin/mission-blueprints/index', $service->overview());
    }

    public function index(MissionRewardBlueprintService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview()]);
    }
}
