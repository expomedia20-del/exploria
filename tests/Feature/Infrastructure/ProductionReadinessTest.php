<?php

namespace Tests\Feature\Infrastructure;

use App\Contracts\OtpProvider;
use App\Services\ProductionReadinessService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    /** @var list<string> */
    private array $temporaryEvidenceFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryEvidenceFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_local_environment_fails_closed(): void
    {
        $report = app(ProductionReadinessService::class)->report('local');

        $this->assertFalse($report['summary']['ready']);
        $this->assertGreaterThan(0, $report['summary']['failCount']);
        $this->assertContains('environment', collect($report['checks'])->where('status', 'fail')->pluck('key'));
    }

    public function test_plausible_staging_configuration_without_runtime_or_external_evidence_fails_closed(): void
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
            'session.encrypt' => true,
            'session.same_site' => 'lax',
            'mail.default' => 'smtp',
            'logging.default' => 'stderr',
            'production_readiness.evidence_path' => null,
        ]);
        $this->app->bind(OtpProvider::class, fn (): OtpProvider => new class implements OtpProvider
        {
            public function issue(string $mobile): string
            {
                return 'provider-reference';
            }
        });

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $failedKeys = collect($report['checks'])->where('status', 'fail')->pluck('key');

        $this->assertFalse($report['summary']['ready']);
        $this->assertContains('database_runtime', $failedKeys);
        $this->assertContains('reward_governance', $failedKeys);
        $this->assertContains('operational_pause', $failedKeys);
        $this->assertContains('operational_evidence', $failedKeys);
        $this->assertContains('storage', $failedKeys);
        $this->assertContains('queue', $failedKeys);
        $this->assertContains('cache', $failedKeys);
        $this->assertContains('session_driver', $failedKeys);
        $this->assertContains('scheduler', $failedKeys);
        $this->assertSame(
            'not-checked',
            collect($report['checks'])->firstWhere('key', 'database_runtime')['actual'],
        );
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

    public function test_http_otp_driver_requires_secure_runtime_configuration(): void
    {
        config(['otp.driver' => 'http']);
        $this->app->forgetInstance(OtpProvider::class);
        $this->app->forgetInstance(ProductionReadinessService::class);

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $otpCheck = collect($report['checks'])->firstWhere('key', 'otp');

        $this->assertIsArray($otpCheck);
        $this->assertSame('fail', $otpCheck['status']);
        $this->assertSame('HttpOtpProvider', $otpCheck['actual']['provider']);
        $this->assertSame('missing', $otpCheck['actual']['endpoint']);
        $this->assertSame('missing', $otpCheck['actual']['token']);
    }

    public function test_http_otp_driver_rejects_an_insecure_endpoint(): void
    {
        config([
            'otp.driver' => 'http',
            'otp.http.endpoint' => 'http://sms-provider.example.test/otp',
            'otp.http.token' => 'configured-outside-repository',
        ]);
        $this->app->forgetInstance(OtpProvider::class);
        $this->app->forgetInstance(ProductionReadinessService::class);

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $otpCheck = collect($report['checks'])->firstWhere('key', 'otp');

        $this->assertIsArray($otpCheck);
        $this->assertSame('fail', $otpCheck['status']);
        $this->assertSame('insecure-or-invalid', $otpCheck['actual']['endpoint']);
    }

    public function test_unencrypted_session_cannot_satisfy_staging_readiness(): void
    {
        config([
            'session.secure' => true,
            'session.http_only' => true,
            'session.encrypt' => false,
            'session.same_site' => 'lax',
        ]);

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $sessionCheck = collect($report['checks'])->firstWhere('key', 'secure_cookie');

        $this->assertIsArray($sessionCheck);
        $this->assertSame('fail', $sessionCheck['status']);
        $this->assertFalse($sessionCheck['actual']['encrypted']);
    }

    public function test_configured_http_otp_driver_passes_its_gate_but_cannot_claim_full_readiness(): void
    {
        config([
            'app.debug' => false,
            'app.key' => 'base64:test-only-readiness-key',
            'app.url' => 'https://staging.exploria.test',
            'database.default' => 'pgsql',
            'otp.driver' => 'http',
            'otp.http.endpoint' => 'https://sms-provider.example.test/otp',
            'otp.http.token' => 'configured-outside-repository',
            'queue.default' => 'database',
            'cache.default' => 'database',
            'session.driver' => 'database',
            'session.secure' => true,
            'session.http_only' => true,
            'session.encrypt' => true,
            'session.same_site' => 'lax',
            'logging.default' => 'stderr',
        ]);
        $this->app->forgetInstance(OtpProvider::class);
        $this->app->forgetInstance(ProductionReadinessService::class);

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $otpCheck = collect($report['checks'])->firstWhere('key', 'otp');

        $this->assertIsArray($otpCheck);
        $this->assertSame('pass', $otpCheck['status']);
        $this->assertFalse($report['summary']['ready']);
        $this->assertContains(
            'operational_evidence',
            collect($report['checks'])->where('status', 'fail')->pluck('key'),
        );
    }

    public function test_log_mailer_cannot_pass_even_with_valid_external_evidence(): void
    {
        $this->useValidOperationalEvidence();
        config(['mail.default' => 'log']);

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $mailCheck = collect($report['checks'])->firstWhere('key', 'mail');
        $evidenceCheck = collect($report['checks'])->firstWhere('key', 'operational_evidence');

        $this->assertIsArray($mailCheck);
        $this->assertIsArray($evidenceCheck);
        $this->assertSame('pass', $evidenceCheck['status']);
        $this->assertSame('fail', $mailCheck['status']);
        $this->assertSame('log', $mailCheck['actual']['configuration']['transport']);
    }

    public function test_mail_failover_to_log_cannot_pass(): void
    {
        $this->useValidOperationalEvidence();
        config([
            'mail.default' => 'unsafe-failover',
            'mail.mailers.unsafe-failover' => [
                'transport' => 'failover',
                'mailers' => ['smtp', 'log'],
            ],
        ]);

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $mailCheck = collect($report['checks'])->firstWhere('key', 'mail');

        $this->assertIsArray($mailCheck);
        $this->assertSame('fail', $mailCheck['status']);
        $this->assertSame('failover-unsafe', $mailCheck['actual']['configuration']['transport']);
    }

    public function test_an_arbitrary_registered_mail_transport_cannot_create_a_false_pass(): void
    {
        $this->useValidOperationalEvidence();
        config([
            'mail.default' => 'invented-mailer',
            'mail.mailers.invented-mailer' => ['transport' => 'invented-transport'],
        ]);

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $mailCheck = collect($report['checks'])->firstWhere('key', 'mail');

        $this->assertIsArray($mailCheck);
        $this->assertSame('fail', $mailCheck['status']);
        $this->assertSame('unresolvable', $mailCheck['actual']['configuration']['transport']);
    }

    public function test_a_resolvable_nonlocal_mailer_with_valid_evidence_passes_only_the_mail_gate(): void
    {
        $this->useValidOperationalEvidence();
        config(['mail.default' => 'smtp']);

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $mailCheck = collect($report['checks'])->firstWhere('key', 'mail');

        $this->assertIsArray($mailCheck);
        $this->assertSame('pass', $mailCheck['status']);
        $this->assertFalse($report['summary']['ready']);
    }

    public function test_unregistered_queue_and_cache_names_cannot_create_a_false_pass(): void
    {
        $this->useValidOperationalEvidence();
        config([
            'queue.default' => 'invented-queue',
            'cache.default' => 'invented-cache',
        ]);

        $report = app(ProductionReadinessService::class)->report('staging', true);
        $queueCheck = collect($report['checks'])->firstWhere('key', 'queue');
        $cacheCheck = collect($report['checks'])->firstWhere('key', 'cache');

        $this->assertIsArray($queueCheck);
        $this->assertIsArray($cacheCheck);
        $this->assertSame('fail', $queueCheck['status']);
        $this->assertSame('unregistered', $queueCheck['actual']['runtime']['driver']);
        $this->assertSame('fail', $cacheCheck['status']);
        $this->assertSame('unregistered', $cacheCheck['actual']['runtime']['driver']);
    }

    public function test_arbitrary_registered_queue_and_cache_drivers_cannot_create_a_false_pass(): void
    {
        $this->useValidOperationalEvidence();
        config([
            'queue.default' => 'invented-queue',
            'queue.connections.invented-queue' => ['driver' => 'invented-driver'],
            'cache.default' => 'invented-cache',
            'cache.stores.invented-cache' => ['driver' => 'invented-driver'],
        ]);

        $report = app(ProductionReadinessService::class)->report('staging', true);
        $queueCheck = collect($report['checks'])->firstWhere('key', 'queue');
        $cacheCheck = collect($report['checks'])->firstWhere('key', 'cache');

        $this->assertIsArray($queueCheck);
        $this->assertIsArray($cacheCheck);
        $this->assertSame('fail', $queueCheck['status']);
        $this->assertSame('backend-unavailable', $queueCheck['actual']['runtime']['runtime']);
        $this->assertSame('fail', $cacheCheck['status']);
        $this->assertSame('backend-unavailable', $cacheCheck['actual']['runtime']['runtime']);
    }

    public function test_local_only_logging_cannot_satisfy_monitoring(): void
    {
        $this->useValidOperationalEvidence();
        config([
            'logging.default' => 'stack',
            'logging.channels.stack.channels' => ['single'],
        ]);

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $monitoringCheck = collect($report['checks'])->firstWhere('key', 'monitoring');

        $this->assertIsArray($monitoringCheck);
        $this->assertSame('fail', $monitoringCheck['status']);
        $this->assertSame('stack-local-only', $monitoringCheck['actual']['configuration']['sink']);
    }

    public function test_an_arbitrary_logging_driver_cannot_satisfy_monitoring(): void
    {
        $this->useValidOperationalEvidence();
        config([
            'logging.default' => 'invented-channel',
            'logging.channels.invented-channel' => ['driver' => 'invented-driver'],
        ]);

        $report = app(ProductionReadinessService::class)->report('staging', false);
        $monitoringCheck = collect($report['checks'])->firstWhere('key', 'monitoring');

        $this->assertIsArray($monitoringCheck);
        $this->assertSame('fail', $monitoringCheck['status']);
        $this->assertSame('unsupported', $monitoringCheck['actual']['configuration']['sink']);
    }

    public function test_storage_and_cache_require_successful_runtime_probes(): void
    {
        $this->useValidOperationalEvidence();
        Storage::fake('public');
        config(['cache.default' => 'file']);

        $report = app(ProductionReadinessService::class)->report('staging', true);
        $storageCheck = collect($report['checks'])->firstWhere('key', 'storage');
        $cacheCheck = collect($report['checks'])->firstWhere('key', 'cache');

        $this->assertIsArray($storageCheck);
        $this->assertIsArray($cacheCheck);
        $this->assertSame('pass', $storageCheck['status']);
        $this->assertSame('write-read-delete-ok', $storageCheck['actual']['runtime']['public']);
        $this->assertSame('pass', $cacheCheck['status']);
        $this->assertSame('write-read-delete-ok', $cacheCheck['actual']['runtime']['runtime']);
    }

    public function test_scheduler_requires_a_real_registered_task_and_external_evidence(): void
    {
        $this->useValidOperationalEvidence();

        $withoutTask = app(ProductionReadinessService::class)->report('staging', true);
        $withoutTaskCheck = collect($withoutTask['checks'])->firstWhere('key', 'scheduler');

        $this->assertIsArray($withoutTaskCheck);
        $this->assertSame('fail', $withoutTaskCheck['status']);
        $this->assertSame(0, $withoutTaskCheck['actual']['runtime']['tasks']);

        app(Schedule::class)->call(static fn (): null => null)->everyMinute()->name('readiness-test-task');

        $withTask = app(ProductionReadinessService::class)->report('staging', true);
        $withTaskCheck = collect($withTask['checks'])->firstWhere('key', 'scheduler');

        $this->assertIsArray($withTaskCheck);
        $this->assertSame('pass', $withTaskCheck['status']);
        $this->assertSame(1, $withTaskCheck['actual']['runtime']['tasks']);
    }

    public function test_evidence_inside_repository_or_with_a_stale_timestamp_is_refused(): void
    {
        $insideRepository = storage_path('framework/testing/readiness-evidence.json');
        $this->writeEvidence($insideRepository, now()->toIso8601String());
        config(['production_readiness.evidence_path' => $insideRepository]);

        $insideReport = app(ProductionReadinessService::class)->report('staging', false);
        $insideCheck = collect($insideReport['checks'])->firstWhere('key', 'operational_evidence');

        $this->assertIsArray($insideCheck);
        $this->assertSame('repository-evidence-refused', $insideCheck['actual']);

        $stalePath = $this->temporaryEvidencePath();
        $this->writeEvidence($stalePath, now()->subDays(2)->toIso8601String());
        config(['production_readiness.evidence_path' => $stalePath]);

        $staleReport = app(ProductionReadinessService::class)->report('staging', false);
        $staleCheck = collect($staleReport['checks'])->firstWhere('key', 'operational_evidence');

        $this->assertIsArray($staleCheck);
        $this->assertSame('stale-or-invalid-timestamp', $staleCheck['actual']);
    }

    public function test_readiness_command_returns_failure_for_test_environment(): void
    {
        $this->artisan('exploria:production-readiness', ['--json' => true])
            ->expectsOutputToContain('"ready": false')
            ->assertFailed();
    }

    private function useValidOperationalEvidence(): void
    {
        $path = $this->temporaryEvidencePath();
        $this->writeEvidence($path, now()->toIso8601String());

        config([
            'production_readiness.evidence_path' => $path,
            'production_readiness.evidence_max_age_minutes' => 1440,
        ]);
    }

    private function temporaryEvidencePath(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'exploria-evidence-');

        $this->assertIsString($path);
        $this->temporaryEvidenceFiles[] = $path;

        return $path;
    }

    private function writeEvidence(string $path, string $verifiedAt): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $checks = [];

        foreach (['mail', 'storage', 'monitoring', 'queue', 'cache', 'session', 'scheduler'] as $key) {
            $checks[$key] = [
                'status' => 'pass',
                'reference' => "runbook-{$key}-20260821",
            ];
        }

        $encoded = json_encode([
            'environment' => 'staging',
            'verified_at' => $verifiedAt,
            'checks' => $checks,
        ], JSON_THROW_ON_ERROR);

        file_put_contents($path, $encoded);
        $this->temporaryEvidenceFiles[] = $path;
    }
}
