<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Visit;
use App\Services\MissionFlowService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VisitExperienceController extends Controller
{
    public function __invoke(Request $request, Visit $visit, MissionFlowService $missionFlow): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        $participant = $user;

        if ($user->id !== $visit->user_id) {
            abort_unless(in_array($user->role, [UserRole::Admin, UserRole::Operator], true), 403);

            $participant = User::query()->whereKey($visit->user_id)->firstOrFail();
        }

        $visit->load(['venue', 'touchpoint.hub.zone', 'campaign', 'qrCode']);

        return Inertia::render('visits/show', [
            'visit' => [
                'id' => $visit->id,
                'status' => $visit->status,
                'occurredAt' => $visit->occurred_at->toIso8601String(),
                'qrCode' => $visit->qrCode?->code,
                'venueName' => $visit->venue?->name,
                'city' => $visit->venue?->city,
                'zoneName' => $visit->touchpoint?->hub?->zone?->name,
                'hubName' => $visit->touchpoint?->hub?->name,
                'touchpointLabel' => $visit->touchpoint?->label,
                'campaignName' => $visit->campaign?->name,
                'isDemo' => (bool) data_get($visit->metadata, 'is_demo', false),
            ],
            'missionFlow' => $missionFlow->visitMissionSummary($participant, $visit),
            'viewerMode' => [
                'isAdminPreview' => $user->id !== $participant->id,
                'participantName' => $participant->name,
            ],
        ]);
    }
}
