<?php

namespace Tests\Feature\Demo;

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
}
