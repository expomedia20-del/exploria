<?php

namespace Tests\Feature;

use App\Models\ConsentVersion;
use App\Models\MissionInstance;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\ConsentVersionSeeder;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_shows_pilot_operational_stats(): void
    {
        $this->withoutVite();
        $this->seed([ConsentVersionSeeder::class, PilotLocationSeeder::class]);

        $user = User::factory()->create();
        $version = ConsentVersion::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($user)->postJson('/api/v1/consents/accept', [
            'consentVersionId' => $version->id,
            'source' => 'qr_landing',
            'sourceQrCode' => PilotLocationSeeder::DEMO_QR_CODE,
        ])->assertCreated();
        $visit = Visit::query()->where('user_id', $user->id)->firstOrFail();
        $entryMission = MissionInstance::query()->where('code', 'scan-entry-qr')->firstOrFail();
        $partnerRewardMission = MissionInstance::query()->where('code', 'discover-route-guide')->firstOrFail();

        $this->actingAs($user)
            ->post(route('visits.missions.complete', [$visit, $entryMission]))
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('visits.missions.complete', [$visit, $partnerRewardMission]))
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('stats.venues', 3)
                ->where('stats.activeQrCodes', 1)
                ->where('stats.consents', 1)
                ->where('stats.visits', 1)
                ->where('stats.activeCampaigns', 1)
                ->where('stats.activeMissions', 4)
                ->where('stats.missionCompletions', 2)
                ->where('stats.issuedRewards', 2)
                ->where('stats.pendingRedemptions', 1)
                ->where('stats.confirmedRedemptions', 0)
                ->has('latestVisits', 1)
                ->has('latestRedemptions', 1)
                ->where('latestRedemptions.0.status', 'pending')
                ->where('latestRedemptions.0.campaignCode', 'ecopark-pilot-1405')
                ->has('latestRedemptions.0.redemptionCode')
                ->has('latestRedemptions.0.partnerName')
                ->has('operationalAlerts', 1)
                ->where('operationalAlerts.0.severity', 'attention')
                ->where('operationalAlerts.0.actionHref', '/partner/dashboard?campaign=ecopark-pilot-1405')
                ->has('campaignPerformance', 1)
                ->where('campaignPerformance.0.visits', 1)
                ->where('campaignPerformance.0.completedMissions', 2)
                ->where('campaignPerformance.0.rewards', 2)
                ->where('campaignPerformance.0.pendingRedemptions', 1)
                ->where('campaignPerformance.0.confirmedRedemptions', 0)
                ->where('campaignPerformance.0.progressPercent', 50));
    }
}
