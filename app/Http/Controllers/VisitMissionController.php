<?php

namespace App\Http\Controllers;

use App\Models\MissionInstance;
use App\Models\User;
use App\Models\Visit;
use App\Services\MissionFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VisitMissionController extends Controller
{
    public function index(Request $request, Visit $visit, MissionFlowService $service): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        return response()->json([
            'status' => 'success',
            'data' => $service->visitMissionSummary($user, $visit),
        ]);
    }

    public function start(Request $request, Visit $visit, MissionInstance $mission, MissionFlowService $service): JsonResponse|RedirectResponse
    {
        $service->start($this->authenticatedUser($request), $visit, $mission);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'ماموریت شروع شد.']);
        }

        return back()->with('success', 'ماموریت شروع شد.');
    }

    public function complete(Request $request, Visit $visit, MissionInstance $mission, MissionFlowService $service): JsonResponse|RedirectResponse
    {
        $result = $service->complete($this->authenticatedUser($request), $visit, $mission);
        $message = $result['reward'] ? 'ماموریت تکمیل شد و پاداش صادر شد.' : 'ماموریت تکمیل شد.';

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        return $user;
    }
}
