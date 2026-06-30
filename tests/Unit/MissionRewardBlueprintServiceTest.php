<?php

namespace Tests\Unit;

use App\Services\MissionRewardBlueprintService;
use Tests\TestCase;

class MissionRewardBlueprintServiceTest extends TestCase
{
    public function test_foodcourt_taste_tour_uses_step_specific_mission_templates(): void
    {
        $blueprint = app(MissionRewardBlueprintService::class)->handoff('foodcourt-taste-tour-quest');

        $this->assertNotNull($blueprint);

        $templateCodes = collect($blueprint['missionPlan'])
            ->pluck('recommendedTemplateCode')
            ->values()
            ->all();

        $this->assertSame([
            'scan-entry-qr',
            'discover-route-guide',
            'watch-place-story',
            'watch-place-story',
            'photo-memory-challenge',
        ], $templateCodes);

        $this->assertGreaterThan(2, collect($templateCodes)->unique()->count());
    }
}
