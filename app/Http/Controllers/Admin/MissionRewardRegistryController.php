<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MissionRewardRegistryService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class MissionRewardRegistryController extends Controller
{
    public function page(MissionRewardRegistryService $service): Response
    {
        return Inertia::render('admin/missions/index', $service->overview());
    }

    public function index(MissionRewardRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview()]);
    }
}
