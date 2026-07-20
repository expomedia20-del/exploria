<?php

namespace Tests\Feature\Campaign;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\Hub;
use App\Models\MissionInstance;
use App\Models\MissionTemplate;
use App\Models\PartnerAccount;
use App\Models\QrCode;
use App\Models\RewardRedemption;
use App\Models\Touchpoint;
use App\Models\User;
use App\Models\Venue;
use App\Models\Visit;
use App\Services\MultiCampaignAssuranceService;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MultiCampaignAssuranceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_multi_campaign_assurance_proves_varied_campaigns_and_scope_isolation(): void
    {
        $visitor = User::factory()->create(['role' => UserRole::Visitor]);
        $pilotQr = QrCode::query()->where('code', PilotLocationSeeder::DEMO_QR_CODE)->firstOrFail();
        $pilotVisit = $this->visitForQr($visitor, $pilotQr);
        $pointsOnly = $this->createPointsOnlyCampaign();
        $pointsOnlyVisit = $this->visitForQr($visitor, $pointsOnly['qr']);

        foreach (['scan-entry-qr', 'discover-route-guide', 'watch-place-story', 'photo-memory-challenge'] as $code) {
            $mission = MissionInstance::query()->where('code', $code)->firstOrFail();

            $this->actingAs($visitor)
                ->post(route('visits.missions.complete', [$pilotVisit, $mission]))
                ->assertRedirect();
        }

        $this->actingAs($visitor)
            ->postJson(route('visits.missions.api.complete', [$pilotVisit, $pointsOnly['mission']]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('mission');

        $this->actingAs($visitor)
            ->post(route('visits.missions.complete', [$pointsOnlyVisit, $pointsOnly['mission']]))
            ->assertRedirect();

        $redemption = RewardRedemption::query()
            ->whereHas('partnerAccount', fn ($query) => $query->where('code', 'cafe-eco'))
            ->firstOrFail();
        $partnerUser = User::query()->where('email', 'cafe.eco@example.test')->firstOrFail();

        $this->actingAs($partnerUser)
            ->postJson(route('partner.redemptions.api.confirm'), ['redemption_code' => $redemption->redemption_code])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $this->actingAs($visitor)
            ->getJson(route('visits.missions.index', $pilotVisit))
            ->assertOk()
            ->assertJsonPath('data.stats.completedMissions', 4)
            ->assertJsonPath('data.stats.rewards', 4)
            ->assertJsonMissing(['code' => 'points-only-check-in']);

        $this->actingAs($visitor)
            ->getJson(route('visits.missions.index', $pointsOnlyVisit))
            ->assertOk()
            ->assertJsonPath('data.stats.totalPoints', 75)
            ->assertJsonPath('data.stats.completedMissions', 1)
            ->assertJsonPath('data.stats.rewards', 0)
            ->assertJsonPath('data.missions.0.code', 'points-only-check-in');

        $report = app(MultiCampaignAssuranceService::class)->report('ecopark-abbasabad', 2, true);
        $checks = collect($report['checks'])->keyBy('key');

        $this->assertTrue($report['summary']['ready']);
        $this->assertSame(0, $report['summary']['failCount']);
        $this->assertSame('pass', $checks['active_campaign_variety']['status']);
        $this->assertSame('pass', $checks['active_qr_campaign_coverage']['status']);
        $this->assertSame('pass', $checks['mission_type_variety']['status']);
        $this->assertSame('pass', $checks['reward_layer_variety']['status']);
        $this->assertSame('pass', $checks['locked_mission_rules']['status']);
        $this->assertSame('pass', $checks['treasure_connections']['status']);
        $this->assertSame('pass', $checks['campaign_scope_integrity']['status']);
        $this->assertSame('pass', $checks['executed_mission_campaigns']['status']);
        $this->assertSame('pass', $checks['issued_reward_evidence']['status']);
        $this->assertSame('pass', $checks['partner_redemption_evidence']['status']);

        $exitCode = Artisan::call('exploria:campaign-assurance', [
            '--venue' => 'ecopark-abbasabad',
            '--minimum-campaigns' => 2,
            '--require-execution' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('campaign_scope_integrity', Artisan::output());
    }

    public function test_assurance_command_fails_closed_when_only_one_campaign_is_available(): void
    {
        $report = app(MultiCampaignAssuranceService::class)->report('ecopark-abbasabad', 2, true);
        $checks = collect($report['checks'])->keyBy('key');

        $this->assertFalse($report['summary']['ready']);
        $this->assertSame('fail', $checks['active_campaign_variety']['status']);
        $this->assertSame('fail', $checks['active_qr_campaign_coverage']['status']);
        $this->assertSame('fail', $checks['executed_mission_campaigns']['status']);

        $exitCode = Artisan::call('exploria:campaign-assurance', [
            '--venue' => 'ecopark-abbasabad',
            '--minimum-campaigns' => 2,
            '--require-execution' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('active_campaign_variety', Artisan::output());
    }

    private function visitForQr(User $visitor, QrCode $qr): Visit
    {
        return Visit::query()->create([
            'user_id' => $visitor->id,
            'qr_code_id' => $qr->id,
            'venue_id' => $qr->venue_id,
            'touchpoint_id' => $qr->touchpoint_id,
            'campaign_id' => $qr->campaign_id,
            'source' => 'qr_landing',
            'status' => 'confirmed',
            'occurred_at' => now(),
            'metadata' => ['source' => 'multi_campaign_assurance_test'],
        ]);
    }

    /** @return array{campaign: Campaign, qr: QrCode, mission: MissionInstance} */
    private function createPointsOnlyCampaign(): array
    {
        $venue = Venue::query()->where('code', 'ecopark-abbasabad')->firstOrFail();
        $touchpoint = Touchpoint::query()->where('code', 'main-gate-qr-stand')->firstOrFail();
        $hub = Hub::query()->where('code', 'visitor-welcome-hub')->firstOrFail();
        $campaign = Campaign::query()->create([
            'venue_id' => $venue->id,
            'code' => 'points-only-family-route',
            'name' => 'Points Only Family Route',
            'campaign_type' => 'points_only_route',
            'status' => RecordStatus::Active,
            'start_at' => now()->subDay(),
            'end_at' => now()->addMonth(),
            'metadata' => ['source' => 'multi_campaign_assurance_test'],
        ]);
        $qr = QrCode::query()->create([
            'venue_id' => $venue->id,
            'touchpoint_id' => $touchpoint->id,
            'campaign_id' => $campaign->id,
            'code' => 'points-only-route-qr',
            'destination_url' => url('/scan/points-only-route-qr'),
            'label' => 'Points only route QR',
            'status' => RecordStatus::Active,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addMonth(),
            'max_scans_per_user_per_window' => 1,
            'duplicate_window_seconds' => 300,
            'metadata' => ['source' => 'multi_campaign_assurance_test'],
        ]);
        $template = MissionTemplate::query()->create([
            'code' => 'points-only-check-in-template',
            'title' => 'Points only check in',
            'description' => 'A mission that awards points without issuing rewards.',
            'mission_type' => 'points_only',
            'trigger_type' => 'qr_scan',
            'point_value' => 75,
            'status' => RecordStatus::Active,
            'metadata' => ['source' => 'multi_campaign_assurance_test'],
        ]);
        $mission = MissionInstance::query()->create([
            'mission_template_id' => $template->id,
            'campaign_id' => $campaign->id,
            'venue_id' => $venue->id,
            'hub_id' => $hub->id,
            'touchpoint_id' => $touchpoint->id,
            'code' => 'points-only-check-in',
            'title_override' => 'Points only check in',
            'status' => RecordStatus::Active,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'metadata' => [
                'source' => 'multi_campaign_assurance_test',
                'visitor_instruction' => 'Complete the points-only step.',
            ],
        ]);

        PartnerAccount::query()->where('code', 'ravaq-store')->firstOrFail();

        return ['campaign' => $campaign, 'qr' => $qr, 'mission' => $mission];
    }
}
