<?php

namespace Tests\Feature\Infrastructure;

use App\Contracts\OtpProvider;
use App\Services\ProductionReadinessService;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    public function test_local_environment_fails_closed(): void
    {
        $report = app(ProductionReadinessService::class)->report('local');

        $this->assertFalse($report['summary']['ready']);
        $this->assertGreaterThan(0, $report['summary']['failCount']);
        $this->assertContains('environment', collect($report['checks'])->where('status', 'fail')->pluck('key'));
    }

    public function test_hardened_staging_configuration_passes(): void
    {
        config([
            'app.debug' => false,
            'app.key' => 'base64:test-only-readiness-key',
            'app.url' => 'https://staging.exploria.test',
            'database.default' => 'pgsql',
            'otp.driver' => 'sms-provider',
            'queue.default' => 'database',
            'cache.default' => 'database',
            'session.driver' => 'database',
            'session.secure' => true,
            'session.http_only' => true,
            'logging.default' => 'stack',
        ]);
        $this->app->bind(OtpProvider::class, fn (): OtpProvider => new class implements OtpProvider
        {
            public function issue(string $mobile): string
            {
                return 'provider-reference';
            }
        });

        $report = app(ProductionReadinessService::class)->report('staging', false);

        $this->assertTrue($report['summary']['ready']);
        $this->assertSame(0, $report['summary']['failCount']);
        $this->assertSame([], $report['nextActions']);
    }

    public function test_an_arbitrary_otp_driver_cannot_create_a_false_pass(): void
    {
        config(['otp.driver' => 'unregistered-provider']);

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $otpCheck = collect($report['checks'])->firstWhere('key', 'otp');

        $this->assertIsArray($otpCheck);
        $this->assertSame('fail', $otpCheck['status']);
        $this->assertSame('UnavailableOtpProvider', $otpCheck['actual']);
    }

    public function test_readiness_command_returns_failure_for_test_environment(): void
    {
        $this->artisan('exploria:production-readiness', ['--json' => true])
            ->expectsOutputToContain('"ready": false')
            ->assertFailed();
    }
}
