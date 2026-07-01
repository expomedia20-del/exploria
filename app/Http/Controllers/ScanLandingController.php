<?php

namespace App\Http\Controllers;

use App\Enums\RecordStatus;
use App\Models\MissionInstance;
use App\Models\QrCode;
use App\Models\RewardDefinition;
use Inertia\Inertia;
use Inertia\Response;

class ScanLandingController extends Controller
{
    public function __invoke(string $code): Response
    {
        $qr = QrCode::query()
            ->with(['venue', 'touchpoint.hub.zone', 'campaign'])
            ->where('code', $code)
            ->firstOrFail();

        abort_unless($qr->isAvailableForLanding(), 404);

        $venue = $qr->venue;
        $touchpoint = $qr->touchpoint;
        $hub = $touchpoint?->hub;
        $zone = $hub?->zone;
        $campaign = $qr->campaign;

        abort_unless($venue && $touchpoint && $hub && $zone && $campaign, 404);

        $rewardOptions = RewardDefinition::query()
            ->where('campaign_id', $campaign->id)
            ->where('status', RecordStatus::Active)
            ->where(function ($query): void {
                $query->where('metadata->approval_status', 'approved')
                    ->orWhereNull('metadata->approval_status');
            })
            ->with('partnerAccount:id,name')
            ->orderBy('created_at')
            ->get()
            ->map(fn (RewardDefinition $reward): array => [
                'id' => $reward->id,
                'name' => $reward->name,
                'tier' => $reward->metadata['reward_tier'] ?? null,
                'option' => $reward->metadata['reward_option'] ?? null,
                'partnerName' => $reward->partnerAccount?->name,
                'description' => $reward->metadata['description'] ?? null,
            ])
            ->values();
        $missionPreview = MissionInstance::query()
            ->with(['missionTemplate', 'hub:id,name', 'touchpoint:id,label', 'treasure:id,mission_instance_id,name'])
            ->where('campaign_id', $campaign->id)
            ->where('venue_id', $venue->id)
            ->where('status', RecordStatus::Active)
            ->get()
            ->sortBy(fn (MissionInstance $mission): int => (int) data_get($mission->metadata, 'cycle_step_index', 999))
            ->values()
            ->map(fn (MissionInstance $mission, int $index): array => [
                'id' => $mission->id,
                'title' => $mission->title_override ?? $mission->missionTemplate?->title,
                'description' => $mission->metadata['visitor_instruction'] ?? $mission->missionTemplate?->description,
                'points' => $mission->missionTemplate?->point_value ?? 0,
                'displayStep' => (int) data_get($mission->metadata, 'cycle_step_index', $index + 1),
                'cycleStep' => [
                    'index' => $mission->metadata['cycle_step_index'] ?? null,
                    'label' => $mission->metadata['cycle_step_label'] ?? null,
                ],
                'evidence' => $mission->metadata['completion_evidence'] ?? $this->triggerLabel($mission->missionTemplate?->trigger_type),
                'hubName' => $mission->hub?->name,
                'touchpointLabel' => $mission->touchpoint?->label,
                'treasureName' => $mission->treasure?->name,
            ])
            ->values();

        return Inertia::render('scan/landing', [
            'qr' => [
                'code' => $qr->code,
                'label' => $qr->label,
                'venueName' => $venue->name,
                'city' => $venue->city,
                'zoneName' => $zone->name,
                'hubName' => $hub->name,
                'touchpointLabel' => $touchpoint->label,
                'campaignName' => $campaign->name,
                'isDemo' => (bool) data_get($qr->metadata, 'is_demo', false),
            ],
            'missionPreview' => $missionPreview,
            'rewardOptions' => $rewardOptions,
        ]);
    }

    private function triggerLabel(?string $triggerType): string
    {
        return match ($triggerType) {
            'qr_scan' => 'اسکن QR معتبر',
            'location_hint' => 'حضور در نقطه راهنما',
            'content_view' => 'مشاهده محتوا یا پاسخ کوتاه',
            'admin_approval' => 'تایید مجری یا ادمین',
            default => 'ثبت انجام ماموریت',
        };
    }
}
