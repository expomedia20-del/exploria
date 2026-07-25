<?php

namespace Tests\Feature\Demo;

use App\Models\QrCode;
use App\Services\EcoParkDemoReadinessService;
use Database\Seeders\PilotLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcoParkDemoReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PilotLocationSeeder::class);
    }

    public function test_ecopark_pilot_seed_is_ready_for_an_end_to_end_demo(): void
    {
        $report = app(EcoParkDemoReadinessService::class)->report();

        $this->assertTrue($report['summary']['ready']);
        $this->assertSame(0, $report['summary']['failCount']);
        $this->assertSame(0, $report['summary']['warningCount']);
        $this->assertContains('ecopark-pilot-1405', collect($report['summary']['campaigns'])->pluck('code')->all());

        $checks = collect($report['checks'])->keyBy('key');

        $this->assertSame('pass', $checks['venue_active']['status']);
        $this->assertSame('pass', $checks['mission_chain']['status']);
        $this->assertSame('pass', $checks['treasure_connected']['status']);
        $this->assertSame('pass', $checks['sponsor_rewards']['status']);
        $this->assertSame('pass', $checks['inventory_allocations']['status']);
        $this->assertSame('pass', $checks['panel_routes']['status']);
        $this->assertSame('pass', $checks['venue_manager_scope']['status']);
    }

    public function test_demo_readiness_command_outputs_json_report(): void
    {
        $this->artisan('exploria:demo-readiness', ['--json' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('ready');
    }

    public function test_readiness_fails_when_an_active_game_checkpoint_is_missing(): void
    {
        $this->artisan('exploria:prepare-stress-demo')->assertSuccessful();

        QrCode::query()
            ->where('metadata->online_game_role', 'physical_checkpoint')
            ->where('metadata->checkpoint_key', 'mina')
            ->update(['status' => 'inactive']);

        $report = app(EcoParkDemoReadinessService::class)->report();
        $check = collect($report['checks'])->firstWhere('key', 'physical_game_qr_chain');

        $this->assertFalse($report['summary']['ready']);
        $this->assertSame('fail', $check['status']);
        $this->assertSame(5, $check['count']);
        $this->assertStringContainsString('گنبد مینا', $check['message']);
    }
}
