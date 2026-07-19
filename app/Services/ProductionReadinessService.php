<?php

namespace App\Services;

use App\Contracts\OtpProvider;
use App\Infrastructure\Otp\LocalFixedOtpProvider;
use App\Infrastructure\Otp\UnavailableOtpProvider;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migrator;
use Throwable;

class ProductionReadinessService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly Migrator $migrator,
        private readonly OtpProvider $otpProvider,
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
                is_string(config('app.key')) && config('app.key') !== '',
                config('app.key') ? 'configured' : 'missing',
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
                'otp',
                'ارسال‌کننده OTP',
                ! $this->otpProvider instanceof LocalFixedOtpProvider
                    && ! $this->otpProvider instanceof UnavailableOtpProvider,
                class_basename($this->otpProvider),
                'یک Provider واقعی و غیرمحلی باید برای OtpProvider ثبت شده باشد.',
            ),
            $this->check(
                'queue',
                'صف پردازش',
                ! in_array(config('queue.default'), ['sync', 'null'], true),
                config('queue.default'),
                'QUEUE_CONNECTION باید یک صف پایدار مانند database یا redis باشد.',
            ),
            $this->check(
                'cache',
                'ذخیره‌ساز Cache',
                ! in_array(config('cache.default'), ['array', 'null'], true),
                config('cache.default'),
                'CACHE_STORE باید در استقرار پایدار باشد.',
            ),
            $this->check(
                'session_driver',
                'ذخیره‌ساز Session',
                in_array(config('session.driver'), ['database', 'redis'], true),
                config('session.driver'),
                'SESSION_DRIVER باید database یا redis باشد.',
            ),
            $this->check(
                'secure_cookie',
                'Cookie امن Session',
                config('session.secure') === true && config('session.http_only') === true,
                [
                    'secure' => config('session.secure'),
                    'httpOnly' => config('session.http_only'),
                ],
                'SESSION_SECURE_COOKIE و SESSION_HTTP_ONLY باید فعال باشند.',
            ),
            $this->check(
                'logging',
                'ثبت رویداد عملیاتی',
                config('logging.default') !== 'null',
                config('logging.default'),
                'LOG_CHANNEL نباید null باشد.',
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
            return [true, 'skipped-for-isolated-test'];
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
}
