<?php

namespace Tests\Feature\Participant;

use App\Enums\UserRole;
use App\Models\Campaign;
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
                ->where('participant.publicStatus', 'participant')
                ->where('participant.teamName', 'تیم خانواده کاشف')
                ->has('participant.members', 3)
                ->where('latestVisit.id', $visit->id)
                ->has('journey.activeCampaigns.0', fn (Assert $campaign) => $campaign
                    ->hasAll([
                        'id',
                        'name',
                        'code',
                        'venueName',
                        'city',
                        'scanUrl',
                        'hasVisit',
                        'latestVisitId',
                        'lastVisitedAt',
                        'completedMissions',
                        'totalMissions',
                        'progressPercent',
                    ])
                    ->etc())
                ->has('journey.rewardCatalog.0', fn (Assert $reward) => $reward
                    ->hasAll([
                        'id',
                        'name',
                        'rewardType',
                        'rewardTypeLabel',
                        'campaignName',
                        'campaignCode',
                        'partnerName',
                        'partnerType',
                        'pointCost',
                        'stockQuantity',
                        'remainingStock',
                    ])
                    ->etc())
                ->has('journey.rewardWallet')
                ->where('journey.history.0.id', $visit->id)
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
                ->where('participant.mode', 'individual')
                ->where('participant.publicStatus', 'registered')
                ->where('participant.publicStatusLabel', 'کاربر عادی'));
    }

    public function test_participant_dashboard_collapses_internal_stress_demo_duplicates(): void
    {
        $this->withoutVite();

        $baseCampaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $baseCampaign->update([
            'metadata' => [
                'is_demo' => true,
                'stress_demo' => true,
                'blueprint_code' => 'ecopark-online-treasure-map-game',
            ],
        ]);
        Campaign::query()->create([
            'venue_id' => $baseCampaign->venue_id,
            'code' => 'ecopark-online-treasure-map-game-campaign',
            'name' => $baseCampaign->name,
            'campaign_type' => 'treasure_route',
            'status' => 'active',
            'start_at' => now(),
            'end_at' => now()->addMonths(6),
            'metadata' => [
                'is_demo' => true,
                'stress_demo' => true,
            ],
        ]);
        $visitor = User::factory()->create(['role' => UserRole::Visitor]);

        $this->actingAs($visitor)
            ->get(route('participant.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('participant/dashboard')
                ->has('journey.activeCampaigns', 1)
                ->where('journey.activeCampaigns.0.code', 'ecopark-pilot-1405'));
    }

    public function test_participant_dashboard_keeps_latest_stress_demo_when_user_started_it(): void
    {
        $this->withoutVite();

        $baseCampaign = Campaign::query()->where('code', 'ecopark-pilot-1405')->firstOrFail();
        $stressCampaign = Campaign::query()->create([
            'venue_id' => $baseCampaign->venue_id,
            'code' => 'ecopark-online-treasure-map-game-campaign',
            'name' => $baseCampaign->name,
            'campaign_type' => 'treasure_route',
            'status' => 'active',
            'start_at' => now(),
            'end_at' => now()->addMonths(6),
            'metadata' => [
                'is_demo' => true,
                'stress_demo' => true,
            ],
        ]);
        $visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $qr = QrCode::query()->firstOrFail();
        $visit = Visit::query()->create([
            'user_id' => $visitor->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $stressCampaign->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $stressCampaign->id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
            'metadata' => ['stress_demo' => true],
        ]);

        $this->actingAs($visitor)
            ->get(route('participant.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('participant/dashboard')
                ->has('journey.activeCampaigns', 1)
                ->where('journey.activeCampaigns.0.code', $stressCampaign->code)
                ->where('journey.activeCampaigns.0.latestVisitId', $visit->id));

        $this->actingAs($visitor)
            ->get(route('visits.show', ['visit' => $visit]))
            ->assertRedirect(route('games.ecopark-treasure', ['visit' => $visit->id]));
    }

    public function test_visitor_can_start_participation_without_admin_approval(): void
    {
        $visitor = User::factory()->create(['role' => UserRole::Visitor]);

        $this->actingAs($visitor)
            ->post(route('participant.participation.start'), [
                'mode' => 'team',
            ])
            ->assertRedirect();

        $visitor->refresh();

        $this->assertSame('participant', $visitor->public_participation_status);
        $this->assertSame('team', $visitor->public_participation_mode);
    }

    public function test_admin_can_preview_a_real_participant_dashboard(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $visitor = User::factory()->create([
            'name' => 'Preview Visitor',
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
                'participation_mode' => 'team',
                'team_name' => 'Preview Team',
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('participant.dashboard', ['visitor_id' => $visitor->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('participant/dashboard')
                ->where('participant.mode', 'team')
                ->where('latestVisit.id', $visit->id)
                ->where('viewerMode.canPreviewVisitors', true)
                ->where('viewerMode.isAdminPreview', true)
                ->where('viewerMode.currentVisitorId', $visitor->id)
                ->has('viewerMode.previewOptions', 1));

        $this->actingAs($admin)
            ->get(route('visits.show', ['visit' => $visit]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('visits/show')
                ->where('visit.id', $visit->id)
                ->where('viewerMode.isAdminPreview', true));
    }

    public function test_visitor_cannot_preview_another_participant(): void
    {
        $this->withoutVite();

        $viewer = User::factory()->create(['role' => UserRole::Visitor]);
        $other = User::factory()->create(['role' => UserRole::Visitor]);

        $this->actingAs($viewer)
            ->get(route('participant.dashboard', ['visitor_id' => $other->id]))
            ->assertForbidden();
    }
}
