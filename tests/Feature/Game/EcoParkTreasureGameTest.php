<?php

namespace Tests\Feature\Game;

use App\Enums\UserRole;
use App\Models\MissionInstance;
use App\Models\QrCode;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EcoParkTreasureGameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PilotLocationSeeder::class);
    }

    public function test_guest_can_open_online_game_with_real_campaign_and_qr_context(): void
    {
        $this->withoutVite();

        $this->get(route('games.ecopark-treasure'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('games/ecopark-treasure')
                ->where('game.campaign.code', 'ecopark-pilot-1405')
                ->where('game.entryQr.code', PilotLocationSeeder::DEMO_QR_CODE)
                ->where('game.visitorState.isAuthenticated', false)
                ->where('game.visitorState.hasLinkedVisit', false)
                ->has('game.missionNodes', 4)
                ->where('game.missionFlow', null));
    }

    public function test_authenticated_visitor_sees_saved_mission_progress_in_online_game(): void
    {
        $this->withoutVite();

        $visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $qr = QrCode::query()->where('code', PilotLocationSeeder::DEMO_QR_CODE)->firstOrFail();
        $visit = Visit::query()->create([
            'user_id' => $visitor->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $qr->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $qr->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
            'metadata' => ['is_demo' => true],
        ]);
        $mission = MissionInstance::query()->where('code', 'scan-entry-qr')->firstOrFail();

        $this->actingAs($visitor)
            ->post(route('visits.missions.complete', [$visit, $mission]))
            ->assertRedirect();

        $this->actingAs($visitor)
            ->get(route('games.ecopark-treasure'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('games/ecopark-treasure')
                ->where('game.latestVisit.id', $visit->id)
                ->where('game.visitorState.isAuthenticated', true)
                ->where('game.visitorState.hasLinkedVisit', true)
                ->where('game.missionFlow.stats.totalPoints', 120)
                ->where('game.missionFlow.stats.completedMissions', 1)
                ->where('game.missionFlow.missions.0.code', 'scan-entry-qr')
                ->where('game.missionFlow.missions.0.status', 'completed'));
    }

    public function test_authenticated_visitor_can_open_online_game_for_a_specific_visit(): void
    {
        $this->withoutVite();

        $visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $qr = QrCode::query()->where('code', PilotLocationSeeder::DEMO_QR_CODE)->firstOrFail();
        $visit = Visit::query()->create([
            'user_id' => $visitor->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $qr->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $qr->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now()->subDay(),
            'metadata' => ['is_demo' => true],
        ]);

        $this->actingAs($visitor)
            ->get(route('games.ecopark-treasure', ['visit' => $visit->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('games/ecopark-treasure')
                ->where('game.latestVisit.id', $visit->id)
                ->where('game.latestVisit.showUrl', route('visits.show', ['visit' => $visit->id])));
    }
}
