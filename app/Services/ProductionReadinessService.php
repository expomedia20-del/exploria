<?php

namespace App\Services;

use App\Contracts\OtpProvider;
use App\Enums\RecordStatus;
use App\Infrastructure\Otp\HttpOtpProvider;
use App\Infrastructure\Otp\LocalFixedOtpProvider;
use App\Infrastructure\Otp\UnavailableOtpProvider;
use App\Models\Campaign;
use App\Models\RewardDefinition;
use Illuminate\Cache\CacheManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Str;
use Throwable;

class ProductionReadinessService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly Migrator $migrator,
        private readonly OtpProvider $otpProvider,
        private readonly RewardGovernanceService $rewardGovernance,
        private readonly OperationalEvidenceService $operationalEvidence,
        private readonly CacheManager $cache,
        private readonly FilesystemManager $filesystems,
        private readonly Schedule $schedule,
    ) {}

    /**
     * @return array{
     *     summary: array{environment: string, ready: bool, passCount: int, failCount: int},
     *     checks: list<array{key: string, label: string, status: string, actual: mixed, message: string}>,
     *     nextActions: list<string>
     * }
     */
    public function report(?string $environment = null, bool $checkDatabaseRuntime = true): array
    {
        $environment ??= app()->environment();
        [$databaseRuntimeReady, $databaseRuntimeStatus] = $this->databaseRuntimeStatus($checkDatabaseRuntime);
        [$rewardGovernanceReady, $rewardGovernanceStatus] = $this->rewardGovernanceStatus($checkDatabaseRuntime, $databaseRuntimeReady);
        [$operationalControlReady, $operationalControlStatus] = $this->operationalControlStatus($checkDatabaseRuntime, $databaseRuntimeReady);
        [$otpProviderReady, $otpProviderStatus] = $this->otpProviderStatus();
        $evidence = $this->operationalEvidence->report($environment);
        [$mailReady, $mailStatus] = $this->mailConfigurationStatus();
        [$storageReady, $storageStatus] = $this->storageStatus($checkDatabaseRuntime);
        [$monitoringReady, $monitoringStatus] = $this->monitoringConfigurationStatus();
        [$queueReady, $queueStatus] = $this->queueStatus($checkDatabaseRuntime);
        [$cacheReady, $cacheStatus] = $this->cacheStatus($checkDatabaseRuntime);
        [$sessionReady, $sessionStatus] = $this->sessionStatus($checkDatabaseRuntime);
        [$schedulerReady, $schedulerStatus] = $this->schedulerStatus($checkDatabaseRuntime);

        $checks = [
            $this->check(
                'environment',
                'محیط استقرار',
                in_array($environment, ['staging', 'production'], true),
                $environment,
                'APP_ENV باید برای Gate استقرار staging یا production باشد.',
            ),
            $this->check(
                'debug',
                'حالت اشکال‌زدایی',
                config('app.debug') === false,
                config('app.debug') ? 'enabled' : 'disabled',
                'APP_DEBUG باید خاموش باشد.',
            ),
            $this->check(
                'app_key',
                'کلید رمزنگاری برنامه',
                is_string(config('app.key')) && trim((string) config('app.key')) !== '',
                is_string(config('app.key')) && trim((string) config('app.key')) !== '' ? 'configured' : 'missing',
                'APP_KEY باید خارج از مخزن و در Environment تنظیم شود.',
            ),
            $this->check(
                'https',
                'نشانی امن برنامه',
                str_starts_with((string) config('app.url'), 'https://'),
                config('app.url'),
                'APP_URL باید از HTTPS استفاده کند.',
            ),
            $this->check(
                'database',
                'پایگاه داده مصوب',
                config('database.default') === 'pgsql',
                config('database.default'),
                'DB_CONNECTION باید pgsql باشد.',
            ),
            $this->check(
                'database_runtime',
                'اتصال و Migration پایگاه داده',
                $databaseRuntimeReady,
                $databaseRuntimeStatus,
                'اتصال دیتابیس باید برقرار و همه Migrationها اجرا شده باشند.',
            ),
            $this->check(
                'operational_evidence',
                'بسته شواهد عملیاتی',
                $evidence['valid'],
                $evidence['status'],
                'بسته Evidence تازه، معتبر، مخصوص همین محیط و خارج از Repository برای همه کنترل‌های عملیاتی لازم است.',
            ),
            $this->check(
                'reward_governance',
                'حاکمیت پاداش فعال',
                $rewardGovernanceReady,
                $rewardGovernanceStatus,
                'همه پاداش‌های فعال باید مالک هزینه، موجودی، ظرفیت و محدودیت صدور معتبر داشته باشند.',
            ),
            $this->check(
                'operational_pause',
                'توقف عملیاتی فعال',
                $operationalControlReady,
                $operationalControlStatus,
                'پیش از استقرار یا Go-Live، هیچ Campaign نباید در وضعیت توقف عملیاتی باقی مانده باشد.',
            ),
            $this->check(
                'otp',
                'ارسال‌کننده OTP',
                $otpProviderReady,
                $otpProviderStatus,
                'یک Provider واقعی و غیرمحلی باید برای OtpProvider ثبت شده باشد.',
            ),
            $this->check(
                'mail',
                'ارسال Mail عملیاتی',
                $mailReady && $evidence['checks']['mail'],
                [
                    'configuration' => $mailStatus,
                    'evidence' => $this->evidenceStatus($evidence, 'mail'),
                ],
                'Mail باید از Transport واقعی و غیرمحلی استفاده کند و آزمون E2E بیرونی تازه داشته باشد.',
            ),
            $this->check(
                'storage',
                'ذخیره‌سازی عملیاتی',
                $storageReady && $evidence['checks']['storage'],
                [
                    'runtime' => $storageStatus,
                    'evidence' => $this->evidenceStatus($evidence, 'storage'),
                ],
                'Diskهای مورد استفاده برنامه باید Write/Read/Delete موفق و آزمون E2E بیرونی تازه داشته باشند.',
            ),
            $this->check(
                'monitoring',
                'Monitoring و Alerting',
                $monitoringReady && $evidence['checks']['monitoring'],
                [
                    'configuration' => $monitoringStatus,
                    'evidence' => $this->evidenceStatus($evidence, 'monitoring'),
                ],
                'Log باید به Sink عملیاتی متصل باشد و Central Monitoring، Alerting و On-call با Evidence تازه اثبات شوند.',
            ),
            $this->check(
                'queue',
                'صف پردازش',
                $queueReady && $evidence['checks']['queue'],
                [
                    'runtime' => $queueStatus,
                    'evidence' => $this->evidenceStatus($evidence, 'queue'),
                ],
                'Queue باید Connection معتبر، Backend پایدار، زیرساخت Runtime و Evidence Worker/Retry/Failure داشته باشد.',
            ),
            $this->check(
                'cache',
                'ذخیره‌ساز Cache',
                $cacheReady && $evidence['checks']['cache'],
                [
                    'runtime' => $cacheStatus,
                    'evidence' => $this->evidenceStatus($evidence, 'cache'),
                ],
                'Cache باید Store معتبر، Round-trip موفق و Evidence عملیاتی تازه داشته باشد.',
            ),
            $this->check(
                'session_driver',
                'ذخیره‌ساز Session',
                $sessionReady && $evidence['checks']['session'],
                [
                    'runtime' => $sessionStatus,
                    'evidence' => $this->evidenceStatus($evidence, 'session'),
                ],
                'Session باید Backend معتبر و قابل دسترس و Evidence ماندگاری بین Deployها داشته باشد.',
            ),
            $this->check(
                'secure_cookie',
                'امنیت Session',
                config('session.secure') === true
                    && config('session.http_only') === true
                    && config('session.encrypt') === true
                    && in_array(config('session.same_site'), ['lax', 'strict'], true),
                [
                    'secure' => config('session.secure'),
                    'httpOnly' => config('session.http_only'),
                    'encrypted' => config('session.encrypt'),
                    'sameSite' => config('session.same_site'),
                ],
                'SESSION_SECURE_COOKIE، SESSION_HTTP_ONLY و SESSION_ENCRYPT باید فعال و SESSION_SAME_SITE برابر lax یا strict باشد.',
            ),
            $this->check(
                'scheduler',
                'Scheduler عملیاتی',
                $schedulerReady && $evidence['checks']['scheduler'],
                [
                    'runtime' => $schedulerStatus,
                    'evidence' => $this->evidenceStatus($evidence, 'scheduler'),
                ],
                'Scheduler باید Task واقعی مصوب و Evidence اجرای موفق و Alert شکست داشته باشد.',
            ),
        ];

        $failures = collect($checks)->where('status', 'fail')->values();
        $nextActions = [];

        foreach ($checks as $check) {
            if ($check['status'] === 'fail') {
                $nextActions[] = $check['message'];
            }
        }

        return [
            'summary' => [
                'environment' => $environment,
                'ready' => $failures->isEmpty(),
                'passCount' => collect($checks)->where('status', 'pass')->count(),
                'failCount' => $failures->count(),
            ],
            'checks' => $checks,
            'nextActions' => $nextActions,
        ];
    }

    /**
     * @return array{key: string, label: string, status: string, actual: mixed, message: string}
     */
    private function check(string $key, string $label, bool $passes, mixed $actual, string $message): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $passes ? 'pass' : 'fail',
            'actual' => $actual,
            'message' => $message,
        ];
    }

    /**
     * @return array{bool, string}
     */
    private function databaseRuntimeStatus(bool $shouldCheck): array
    {
        if (! $shouldCheck) {
            return [false, 'not-checked'];
        }

        try {
            $this->database->connection()->select('select 1');

            if (! $this->migrator->repositoryExists()) {
                return [false, 'migration-repository-missing'];
            }

            $files = $this->migrator->getMigrationFiles(database_path('migrations'));
            $ran = $this->migrator->getRepository()->getRan();
            $pendingCount = count(array_diff(array_keys($files), $ran));

            return $pendingCount === 0
                ? [true, 'connected-and-current']
                : [false, "pending-migrations:{$pendingCount}"];
        } catch (Throwable) {
            return [false, 'connection-failed'];
        }
    }

    /**
     * @return array{bool, string|array{activeRewards: int, invalidRewards: int, invalidRewardCodes: list<string>, invalidFields: list<string>}}
     */
    private function rewardGovernanceStatus(bool $shouldCheck, bool $databaseRuntimeReady): array
    {
        if (! $shouldCheck) {
            return [false, 'not-checked'];
        }

        if (! $databaseRuntimeReady) {
            return [false, 'database-runtime-not-ready'];
        }

        try {
            $rewards = RewardDefinition::query()
                ->where('status', RecordStatus::Active->value)
                ->with(['costOwnerFinancialAccount', 'inventoryAllocations.partnerAccount'])
                ->orderBy('code')
                ->get();

            $invalidRewardCodes = [];
            $invalidFields = [];

            foreach ($rewards as $reward) {
                $errors = $this->rewardGovernance->configurationErrors($reward);

                if ($errors === []) {
                    continue;
                }

                $invalidRewardCodes[] = $reward->code;

                foreach (array_keys($errors) as $field) {
                    $invalidFields[$field] = true;
                }
            }

            $invalidFields = array_keys($invalidFields);
            sort($invalidFields);

            return [
                $invalidRewardCodes === [],
                [
                    'activeRewards' => $rewards->count(),
                    'invalidRewards' => count($invalidRewardCodes),
                    'invalidRewardCodes' => $invalidRewardCodes,
                    'invalidFields' => $invalidFields,
                ],
            ];
        } catch (Throwable) {
            return [false, 'reward-governance-check-failed'];
        }
    }

    /**
     * @return array{bool, string|array{pausedCampaigns: int, pausedCampaignCodes: list<string>}}
     */
    private function operationalControlStatus(bool $shouldCheck, bool $databaseRuntimeReady): array
    {
        if (! $shouldCheck) {
            return [false, 'not-checked'];
        }

        if (! $databaseRuntimeReady) {
            return [false, 'database-runtime-not-ready'];
        }

        try {
            $pausedCampaignCodes = [];

            foreach (Campaign::query()->get(['code', 'metadata']) as $campaign) {
                if ($campaign->isOperationallyPaused()) {
                    $pausedCampaignCodes[] = $campaign->code;
                }
            }

            sort($pausedCampaignCodes);

            return [
                $pausedCampaignCodes === [],
                [
                    'pausedCampaigns' => count($pausedCampaignCodes),
                    'pausedCampaignCodes' => $pausedCampaignCodes,
                ],
            ];
        } catch (Throwable) {
            return [false, 'operational-control-check-failed'];
        }
    }

    /**
     * @return array{bool, mixed}
     */
    private function otpProviderStatus(): array
    {
        if ($this->otpProvider instanceof LocalFixedOtpProvider || $this->otpProvider instanceof UnavailableOtpProvider) {
            return [false, class_basename($this->otpProvider)];
        }

        if ($this->otpProvider instanceof HttpOtpProvider) {
            $endpoint = config('otp.http.endpoint');
            $endpointParts = is_string($endpoint) ? parse_url(trim($endpoint)) : false;
            $endpointConfigured = is_array($endpointParts)
                && ($endpointParts['scheme'] ?? null) === 'https'
                && isset($endpointParts['host']);
            $tokenConfigured = is_string(config('otp.http.token')) && trim((string) config('otp.http.token')) !== '';

            return [
                $endpointConfigured && $tokenConfigured,
                [
                    'provider' => class_basename($this->otpProvider),
                    'endpoint' => $endpointConfigured
                        ? 'securely-configured'
                        : (is_string($endpoint) && trim($endpoint) !== '' ? 'insecure-or-invalid' : 'missing'),
                    'token' => $tokenConfigured ? 'configured' : 'missing',
                ],
            ];
        }

        return [true, class_basename($this->otpProvider)];
    }

    /**
     * @param  array{valid: bool, status: string, checks: array<string, bool>}  $evidence
     */
    private function evidenceStatus(array $evidence, string $key): string
    {
        return ($evidence['checks'][$key] ?? false) ? 'verified' : $evidence['status'];
    }

    /**
     * @return array{bool, array{mailer: string, transport: string, from: string}}
     */
    private function mailConfigurationStatus(): array
    {
        $mailer = config('mail.default');
        $from = config('mail.from.address');
        [$mailerReady, $transport] = is_string($mailer) && trim($mailer) !== ''
            ? $this->mailerStatus($mailer)
            : [false, 'missing'];
        $fromReady = is_string($from) && filter_var($from, FILTER_VALIDATE_EMAIL) !== false;

        return [
            $mailerReady && $fromReady,
            [
                'mailer' => is_string($mailer) && trim($mailer) !== '' ? $mailer : 'missing',
                'transport' => $transport,
                'from' => $fromReady ? 'configured' : 'missing-or-invalid',
            ],
        ];
    }

    /**
     * @param  list<string>  $visited
     * @return array{bool, string}
     */
    private function mailerStatus(string $mailer, array $visited = []): array
    {
        if (in_array($mailer, $visited, true)) {
            return [false, 'cyclic'];
        }

        $configuration = config("mail.mailers.{$mailer}");

        if (! is_array($configuration)) {
            return [false, 'unregistered'];
        }

        $transport = $configuration['transport'] ?? null;

        if (! is_string($transport) || in_array($transport, ['', 'array', 'log', 'null'], true)) {
            return [false, is_string($transport) && $transport !== '' ? $transport : 'missing'];
        }

        if (! in_array($transport, ['failover', 'roundrobin'], true)) {
            return [true, $transport];
        }

        $children = $configuration['mailers'] ?? null;

        if (! is_array($children) || $children === []) {
            return [false, "{$transport}-empty"];
        }

        foreach ($children as $child) {
            if (! is_string($child) || ! $this->mailerStatus($child, [...$visited, $mailer])[0]) {
                return [false, "{$transport}-unsafe"];
            }
        }

        return [true, $transport];
    }

    /**
     * @return array{bool, array<string, string>}
     */
    private function storageStatus(bool $shouldCheck): array
    {
        $configuredDisks = config('production_readiness.storage_disks');

        if (! is_array($configuredDisks) || $configuredDisks === []) {
            return [false, ['configuration' => 'missing']];
        }

        $status = [];
        $ready = true;

        foreach ($configuredDisks as $disk) {
            if (! is_string($disk) || trim($disk) === '') {
                $ready = false;
                $status['unknown'] = 'invalid-disk-name';

                continue;
            }

            $configuration = config("filesystems.disks.{$disk}");
            $driver = is_array($configuration) ? ($configuration['driver'] ?? null) : null;

            if (! is_string($driver) || in_array($driver, ['', 'array', 'memory', 'null'], true)) {
                $ready = false;
                $status[$disk] = 'unregistered-or-ephemeral';

                continue;
            }

            if (! $shouldCheck) {
                $ready = false;
                $status[$disk] = 'not-checked';

                continue;
            }

            $probePath = 'readiness-probes/'.Str::uuid().'.txt';
            $probeValue = 'exploria-storage-readiness';

            try {
                $filesystem = $this->filesystems->disk($disk);
                $stored = $filesystem->put($probePath, $probeValue);
                $readBack = $stored ? $filesystem->get($probePath) : null;
                $deleted = $stored ? $filesystem->delete($probePath) : false;
                $passed = $stored && $readBack === $probeValue && $deleted && ! $filesystem->exists($probePath);
                $ready = $ready && $passed;
                $status[$disk] = $passed ? 'write-read-delete-ok' : 'probe-failed';
            } catch (Throwable) {
                $ready = false;
                $status[$disk] = 'probe-failed';

                try {
                    $this->filesystems->disk($disk)->delete($probePath);
                } catch (Throwable) {
                    // The readiness result is already fail-closed.
                }
            }
        }

        return [$ready, $status];
    }

    /**
     * @return array{bool, array{channel: string, sink: string}}
     */
    private function monitoringConfigurationStatus(): array
    {
        $channel = config('logging.default');
        [$ready, $sink] = is_string($channel) && trim($channel) !== ''
            ? $this->loggingChannelStatus($channel)
            : [false, 'missing'];

        return [
            $ready,
            [
                'channel' => is_string($channel) && trim($channel) !== '' ? $channel : 'missing',
                'sink' => $sink,
            ],
        ];
    }

    /**
     * @param  list<string>  $visited
     * @return array{bool, string}
     */
    private function loggingChannelStatus(string $channel, array $visited = []): array
    {
        if (in_array($channel, $visited, true)) {
            return [false, 'cyclic'];
        }

        $configuration = config("logging.channels.{$channel}");

        if (! is_array($configuration)) {
            return [false, 'unregistered'];
        }

        $driver = $configuration['driver'] ?? null;

        if (! is_string($driver) || in_array($driver, ['', 'daily', 'null', 'single'], true)) {
            return [false, is_string($driver) && $driver !== '' ? $driver : 'missing'];
        }

        if ($driver !== 'stack') {
            return [true, $driver];
        }

        $children = $configuration['channels'] ?? null;

        if (! is_array($children) || $children === []) {
            return [false, 'stack-empty'];
        }

        foreach ($children as $child) {
            if (is_string($child) && $this->loggingChannelStatus($child, [...$visited, $channel])[0]) {
                return [true, 'stack-with-operational-sink'];
            }
        }

        return [false, 'stack-local-only'];
    }

    /**
     * @return array{bool, array{connection: string, driver: string, runtime: string}}
     */
    private function queueStatus(bool $shouldCheck): array
    {
        $connection = config('queue.default');
        [$configured, $driver] = is_string($connection) && trim($connection) !== ''
            ? $this->queueConnectionStatus($connection)
            : [false, 'missing'];
        $runtime = 'not-checked';
        $runtimeReady = false;

        if ($configured && $shouldCheck) {
            if ($driver === 'database') {
                try {
                    $databaseConnection = config("queue.connections.{$connection}.connection") ?: config('database.default');
                    $table = config("queue.connections.{$connection}.table");
                    $failedDriver = config('queue.failed.driver');
                    $failedTable = config('queue.failed.table');
                    $schema = $this->database->connection($databaseConnection)->getSchemaBuilder();
                    $runtimeReady = is_string($table)
                        && $schema->hasTable($table)
                        && $failedDriver !== 'null'
                        && is_string($failedTable)
                        && $schema->hasTable($failedTable);
                    $runtime = $runtimeReady ? 'tables-ready' : 'queue-or-failed-table-missing';
                } catch (Throwable) {
                    $runtime = 'backend-unavailable';
                }
            } else {
                $runtimeReady = true;
                $runtime = 'external-runtime-covered-by-evidence';
            }
        }

        return [
            $configured && $runtimeReady,
            [
                'connection' => is_string($connection) && trim($connection) !== '' ? $connection : 'missing',
                'driver' => $driver,
                'runtime' => $runtime,
            ],
        ];
    }

    /**
     * @param  list<string>  $visited
     * @return array{bool, string}
     */
    private function queueConnectionStatus(string $connection, array $visited = []): array
    {
        if (in_array($connection, $visited, true)) {
            return [false, 'cyclic'];
        }

        $configuration = config("queue.connections.{$connection}");

        if (! is_array($configuration)) {
            return [false, 'unregistered'];
        }

        $driver = $configuration['driver'] ?? null;

        if (! is_string($driver) || in_array($driver, ['', 'background', 'deferred', 'null', 'sync'], true)) {
            return [false, is_string($driver) && $driver !== '' ? $driver : 'missing'];
        }

        if ($driver !== 'failover') {
            return [true, $driver];
        }

        $children = $configuration['connections'] ?? null;

        if (! is_array($children) || $children === []) {
            return [false, 'failover-empty'];
        }

        foreach ($children as $child) {
            if (! is_string($child) || ! $this->queueConnectionStatus($child, [...$visited, $connection])[0]) {
                return [false, 'failover-unsafe'];
            }
        }

        return [true, 'failover'];
    }

    /**
     * @return array{bool, array{store: string, driver: string, runtime: string}}
     */
    private function cacheStatus(bool $shouldCheck): array
    {
        $store = config('cache.default');
        [$configured, $driver] = is_string($store) && trim($store) !== ''
            ? $this->cacheStoreStatus($store)
            : [false, 'missing'];
        $runtime = 'not-checked';
        $runtimeReady = false;

        if ($configured && $shouldCheck) {
            $probeKey = 'exploria:readiness:'.Str::uuid();
            $probeValue = 'cache-round-trip';

            try {
                $repository = $this->cache->store($store);
                $repository->put($probeKey, $probeValue, 60);
                $runtimeReady = $repository->get($probeKey) === $probeValue;
                $repository->forget($probeKey);
                $runtimeReady = $runtimeReady && ! $repository->has($probeKey);
                $runtime = $runtimeReady ? 'write-read-delete-ok' : 'probe-failed';
            } catch (Throwable) {
                $runtime = 'backend-unavailable';
            }
        }

        return [
            $configured && $runtimeReady,
            [
                'store' => is_string($store) && trim($store) !== '' ? $store : 'missing',
                'driver' => $driver,
                'runtime' => $runtime,
            ],
        ];
    }

    /**
     * @param  list<string>  $visited
     * @return array{bool, string}
     */
    private function cacheStoreStatus(string $store, array $visited = []): array
    {
        if (in_array($store, $visited, true)) {
            return [false, 'cyclic'];
        }

        $configuration = config("cache.stores.{$store}");

        if (! is_array($configuration)) {
            return [false, 'unregistered'];
        }

        $driver = $configuration['driver'] ?? null;

        if (! is_string($driver) || in_array($driver, ['', 'array', 'null'], true)) {
            return [false, is_string($driver) && $driver !== '' ? $driver : 'missing'];
        }

        if ($driver !== 'failover') {
            return [true, $driver];
        }

        $children = $configuration['stores'] ?? null;

        if (! is_array($children) || $children === []) {
            return [false, 'failover-empty'];
        }

        foreach ($children as $child) {
            if (! is_string($child) || ! $this->cacheStoreStatus($child, [...$visited, $store])[0]) {
                return [false, 'failover-unsafe'];
            }
        }

        return [true, 'failover'];
    }

    /**
     * @return array{bool, array{driver: string, runtime: string}}
     */
    private function sessionStatus(bool $shouldCheck): array
    {
        $driver = config('session.driver');
        $configured = is_string($driver) && in_array($driver, ['database', 'redis'], true);
        $runtimeReady = false;
        $runtime = 'not-checked';

        if ($configured && $shouldCheck) {
            if ($driver === 'database') {
                try {
                    $connection = config('session.connection') ?: config('database.default');
                    $table = config('session.table');
                    $runtimeReady = is_string($table)
                        && $this->database->connection($connection)->getSchemaBuilder()->hasTable($table);
                    $runtime = $runtimeReady ? 'table-ready' : 'session-table-missing';
                } catch (Throwable) {
                    $runtime = 'backend-unavailable';
                }
            } else {
                $runtimeReady = true;
                $runtime = 'external-runtime-covered-by-evidence';
            }
        }

        return [
            $configured && $runtimeReady,
            [
                'driver' => is_string($driver) && trim($driver) !== '' ? $driver : 'missing',
                'runtime' => $runtime,
            ],
        ];
    }

    /**
     * @return array{bool, array{tasks: int, runtime: string}}
     */
    private function schedulerStatus(bool $shouldCheck): array
    {
        if (! $shouldCheck) {
            return [false, ['tasks' => count($this->schedule->events()), 'runtime' => 'not-checked']];
        }

        $tasks = count($this->schedule->events());

        return [
            $tasks > 0,
            [
                'tasks' => $tasks,
                'runtime' => $tasks > 0 ? 'task-registered' : 'no-real-task-registered',
            ],
        ];
    }
}
