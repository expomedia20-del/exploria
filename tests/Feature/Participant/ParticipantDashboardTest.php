<?php

namespace Tests\Feature\Participant;

use App\Enums\UserRole;
use App\Models\QrCode;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ParticipantDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_visitor_can_open_participant_dashboard_with_latest_family_visit(): void
    {
        $this->withoutVite();

        $visitor = User::factory()->create([
            'name' => 'خانواده کاشف',
            'role' => UserRole::Visitor,
        ]);
        $qr = QrCode::query()->firstOrFail();

        $visit = Visit::query()->create([
            'user_id' => $visitor->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $qr->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $qr->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
            'metadata' => [
                'is_demo' => true,
                'participation_mode' => 'family',
                'team_name' => 'تیم خانواده کاشف',
                'participants' => ['والد', 'کودک', 'همراه'],
            ],
        ]);

        $this->actingAs($visitor)
            ->get(route('participant.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('participant/dashboard')
                ->where('participant.mode', 'family')
                ->where('participant.modeLabel', 'خانوادگی')
                ->where('participant.teamName', 'تیم خانواده کاشف')
                ->has('participant.members', 3)
                ->where('latestVisit.id', $visit->id)
                ->has('missionFlow.missions', 4));
    }

    public function test_participant_dashboard_handles_user_without_visit(): void
    {
        $this->withoutVite();

        $visitor = User::factory()->create(['role' => UserRole::Visitor]);

        $this->actingAs($visitor)
            ->get(route('participant.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('participant/dashboard')
                ->where('latestVisit', null)
                ->where('missionFlow', null)
                ->where('participant.mode', 'individual'));
    }
}
