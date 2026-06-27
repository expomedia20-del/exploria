<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MissionRewardRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MissionRewardRegistryController extends Controller
{
    public function page(Request $request, MissionRewardRegistryService $service): Response
    {
        return Inertia::render('admin/missions/index', $service->overview($request->user()));
    }

    public function index(Request $request, MissionRewardRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview($request->user())]);
    }
}
