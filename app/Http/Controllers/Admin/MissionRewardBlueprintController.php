<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Services\MissionRewardBlueprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MissionRewardBlueprintController extends Controller
{
    public function page(Request $request, MissionRewardBlueprintService $service): Response
    {
        $this->authorizeCentralAdmin($request);

        return Inertia::render('admin/mission-blueprints/index', $service->overview());
    }

    public function index(Request $request, MissionRewardBlueprintService $service): JsonResponse
    {
        $this->authorizeCentralAdmin($request);

        return response()->json(['status' => 'success', 'data' => $service->overview()]);
    }

    private function authorizeCentralAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === UserRole::Admin, 403);
    }
}
