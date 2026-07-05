<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Visit;
use App\Services\MissionFlowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantDashboardController extends Controller
{
    public function __invoke(Request $request, MissionFlowService $missionFlow): Response
    {
        $viewer = $request->user();

        abort_unless($viewer instanceof User, 401);

        $user = $this->resolveParticipant($request, $viewer);

        $latestVisit = Visit::query()
            ->with(['venue', 'touchpoint.hub.zone', 'campaign', 'qrCode'])
            ->where('user_id', $user->id)
            ->latest('occurred_at')
            ->first();

        $flow = $latestVisit ? $missionFlow->visitMissionSummary($user, $latestVisit) : null;
        $participation = $this->participationProfile($latestVisit);

        return Inertia::render('participant/dashboard', [
            'participant' => [
                'name' => $user->name,
                'email' => $user->email,
                'mode' => $participation['mode'],
                'modeLabel' => $participation['modeLabel'],
                'members' => $participation['members'],
                'teamName' => $participation['teamName'],
            ],
            'latestVisit' => $latestVisit ? [
                'id' => $latestVisit->id,
                'status' => $latestVisit->status,
                'occurredAt' => $latestVisit->occurred_at->toIso8601String(),
                'qrCode' => $latestVisit->qrCode?->code,
                'venueName' => $latestVisit->venue?->name,
                'city' => $latestVisit->venue?->city,
                'zoneName' => $latestVisit->touchpoint?->hub?->zone?->name,
                'hubName' => $latestVisit->touchpoint?->hub?->name,
                'touchpointLabel' => $latestVisit->touchpoint?->label,
                'campaignName' => $latestVisit->campaign?->name,
                'isDemo' => (bool) data_get($latestVisit->metadata, 'is_demo', false),
            ] : null,
            'missionFlow' => $flow,
            'viewerMode' => [
                'canPreviewVisitors' => $this->canPreviewVisitors($viewer),
                'isAdminPreview' => $viewer->id !== $user->id,
                'currentVisitorId' => $user->role === UserRole::Visitor ? $user->id : null,
                'previewOptions' => $this->visitorPreviewOptions($viewer),
            ],
        ]);
    }

    private function resolveParticipant(Request $request, User $viewer): User
    {
        $previewId = $request->integer('visitor_id');

        if ($previewId > 0) {
            abort_unless($this->canPreviewVisitors($viewer), 403);

            return User::query()
                ->whereKey($previewId)
                ->where('role', UserRole::Visitor)
                ->firstOrFail();
        }

        return $viewer;
    }

    private function canPreviewVisitors(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Operator], true);
    }

    /** @return array<int, array{id: int, name: string, email: string, visitsCount: int}> */
    private function visitorPreviewOptions(User $viewer): array
    {
        if (! $this->canPreviewVisitors($viewer)) {
            return [];
        }

        return User::query()
            ->where('role', UserRole::Visitor)
            ->whereHas('visits', fn (Builder $query): Builder => $query)
            ->select(['id', 'name', 'email'])
            ->withCount('visits')
            ->orderByDesc('visits_count')
            ->orderBy('name')
            ->limit(30)
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'visitsCount' => (int) $user->visits_count,
            ])
            ->values()
            ->all();
    }

    /** @return array{mode: string, modeLabel: string, teamName: string|null, members: array<int, string>} */
    private function participationProfile(?Visit $visit): array
    {
        $mode = (string) data_get($visit?->metadata, 'participation_mode', 'individual');
        $members = data_get($visit?->metadata, 'participants', []);

        if (! is_array($members) || $members === []) {
            $members = match ($mode) {
                'family' => ['سرپرست خانواده', 'همراه کودک', 'عضو خانواده'],
                'team' => ['عضو اول تیم', 'عضو دوم تیم'],
                default => ['بازدیدکننده اصلی'],
            };
        }

        return [
            'mode' => $mode,
            'modeLabel' => match ($mode) {
                'family' => 'خانوادگی',
                'team' => 'تیمی',
                default => 'انفرادی',
            },
            'teamName' => data_get($visit?->metadata, 'team_name'),
            'members' => array_values(array_map('strval', $members)),
        ];
    }
}
