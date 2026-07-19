<?php

namespace App\Http\Controllers;

use App\Actions\Events\RecordDomainEventAction;
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

    public function start(Request $request, Visit $visit, MissionInstance $mission, MissionFlowService $service, RecordDomainEventAction $recordEvent): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $progress = $service->start($user, $visit, $mission);

        if ($progress->wasRecentlyCreated) {
            $recordEvent->execute('mission_started', $user, $request->session()->getId(), 'mission', $mission->id, $this->missionPayload($visit, $mission), $this->attribution($visit));
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'ماموریت شروع شد.']);
        }

        return back()->with('success', 'ماموریت شروع شد.');
    }

    public function complete(Request $request, Visit $visit, MissionInstance $mission, MissionFlowService $service, RecordDomainEventAction $recordEvent): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $result = $service->complete($user, $visit, $mission);

        if ($result['completedNow']) {
            $recordEvent->execute('mission_completed', $user, $request->session()->getId(), 'mission', $mission->id, [
                ...$this->missionPayload($visit, $mission),
                'points_awarded' => $result['progress']->points_awarded,
            ], $this->attribution($visit));
        }

        if ($result['rewardIssuedNow'] && $result['reward']) {
            $recordEvent->execute('reward_issued', $user, $request->session()->getId(), 'user_reward', $result['reward']->id, [
                'source' => 'mission_completed',
                'mission_id' => $mission->id,
                'reward_definition_id' => $result['reward']->reward_definition_id,
                'quality_flag' => false,
            ], $this->attribution($visit));
        }
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

    /** @return array<string, mixed> */
    private function missionPayload(Visit $visit, MissionInstance $mission): array
    {
        return [
            'source' => 'visit_mission_flow',
            'visit_id' => $visit->id,
            'hub_id' => $mission->hub_id,
            'quality_flag' => false,
        ];
    }

    /** @return array{venue_id: string, touchpoint_id: string, campaign_id: string} */
    private function attribution(Visit $visit): array
    {
        return [
            'venue_id' => $visit->venue_id,
            'touchpoint_id' => $visit->touchpoint_id,
            'campaign_id' => $visit->campaign_id,
        ];
    }
}
