<?php

namespace Tests\Feature\Mission;

use App\Enums\UserRole;
use App\Models\MissionInstance;
use App\Models\QrCode;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VisitMissionFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $visitor;

    private Visit $visit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);

        $this->visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $qr = QrCode::query()->firstOrFail();
        $this->visit = Visit::query()->create([
            'user_id' => $this->visitor->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $qr->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $qr->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
            'metadata' => ['is_demo' => true],
        ]);
    }

    public function test_visit_page_includes_real_mission_flow(): void
    {
        $this->withoutVite();

        $this->actingAs($this->visitor)
            ->get(route('visits.show', $this->visit))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('visits/show')
                ->where('missionFlow.stats.totalPoints', 0)
                ->where('missionFlow.stats.rewards', 0)
                ->has('missionFlow.missions', 4)
                ->where('missionFlow.missions.0.code', 'scan-entry-qr'));
    }

    public function test_user_can_start_and_complete_a_visit_mission_and_receive_reward(): void
    {
        $mission = MissionInstance::query()->where('code', 'scan-entry-qr')->firstOrFail();

        $this->actingAs($this->visitor)
            ->post(route('visits.missions.start', [$this->visit, $mission]))
            ->assertRedirect();

        $this->assertDatabaseHas('user_mission_progress', [
            'user_id' => $this->visitor->id,
            'mission_instance_id' => $mission->id,
            'status' => 'started',
            'points_awarded' => 0,
        ]);

        $this->actingAs($this->visitor)
            ->post(route('visits.missions.complete', [$this->visit, $mission]))
            ->assertRedirect();

        $this->assertDatabaseHas('user_mission_progress', [
            'user_id' => $this->visitor->id,
            'mission_instance_id' => $mission->id,
            'status' => 'completed',
            'points_awarded' => 120,
        ]);
        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $this->visitor->id,
            'status' => 'awarded',
        ]);
    }

    public function test_locked_challenge_requires_enough_completed_points(): void
    {
        $challenge = MissionInstance::query()->where('code', 'photo-memory-challenge')->firstOrFail();

        $this->actingAs($this->visitor)
            ->postJson(route('visits.missions.api.complete', [$this->visit, $challenge]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('mission');

        $this->assertDatabaseMissing('user_mission_progress', [
            'user_id' => $this->visitor->id,
            'mission_instance_id' => $challenge->id,
        ]);
    }

    public function test_completing_first_three_missions_unlocks_challenge_and_wallet(): void
    {
        foreach (['scan-entry-qr', 'discover-route-guide', 'watch-place-story'] as $code) {
            $mission = MissionInstance::query()->where('code', $code)->firstOrFail();
            $this->actingAs($this->visitor)
                ->post(route('visits.missions.complete', [$this->visit, $mission]))
                ->assertRedirect();
        }

        $challenge = MissionInstance::query()->where('code', 'photo-memory-challenge')->firstOrFail();

        $this->actingAs($this->visitor)
            ->postJson(route('visits.missions.api.complete', [$this->visit, $challenge]))
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->actingAs($this->visitor)
            ->getJson(route('visits.missions.index', $this->visit))
            ->assertOk()
            ->assertJsonPath('data.stats.totalPoints', 780)
            ->assertJsonPath('data.stats.completedMissions', 4)
            ->assertJsonPath('data.stats.rewards', 4);

        $this->actingAs($this->visitor)
            ->getJson(route('rewards.wallet'))
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    public function test_other_user_cannot_mutate_visit_missions(): void
    {
        $other = User::factory()->create(['role' => UserRole::Visitor]);
        $mission = MissionInstance::query()->where('code', 'scan-entry-qr')->firstOrFail();

        $this->actingAs($other)
            ->postJson(route('visits.missions.api.start', [$this->visit, $mission]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('visit');
    }
}
