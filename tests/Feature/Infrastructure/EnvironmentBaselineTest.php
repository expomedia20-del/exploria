<?php

namespace Tests\Feature\Infrastructure;

use Tests\TestCase;

class EnvironmentBaselineTest extends TestCase
{
    public function test_example_environment_uses_the_approved_database_and_locale(): void
    {
        $exampleEnvironment = file_get_contents(base_path('.env.example'));
        $stagingEnvironment = file_get_contents(base_path('.env.staging.example'));

        $this->assertIsString($exampleEnvironment);
        $this->assertIsString($stagingEnvironment);
        $this->assertStringContainsString('APP_NAME=EXPLORIA', $exampleEnvironment);
        $this->assertStringContainsString('APP_LOCALE=fa', $exampleEnvironment);
        $this->assertStringContainsString('DB_CONNECTION=pgsql', $exampleEnvironment);
        $this->assertStringContainsString('DB_PORT=5432', $exampleEnvironment);
        $this->assertStringContainsString('OTP_HTTP_ENDPOINT=', $exampleEnvironment);
        $this->assertStringContainsString('OTP_HTTP_TOKEN=', $exampleEnvironment);
        $this->assertStringContainsString('APP_ENV=staging', $stagingEnvironment);
        $this->assertStringContainsString('APP_DEBUG=false', $stagingEnvironment);
        $this->assertStringContainsString('APP_URL=https://', $stagingEnvironment);
        $this->assertStringContainsString('DB_CONNECTION=pgsql', $stagingEnvironment);
        $this->assertStringContainsString('QUEUE_CONNECTION=database', $stagingEnvironment);
        $this->assertStringContainsString('SESSION_DRIVER=database', $stagingEnvironment);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $stagingEnvironment);
        $this->assertStringContainsString('OTP_DRIVER=http', $stagingEnvironment);
        $this->assertStringContainsString('OTP_HTTP_TOKEN=', $stagingEnvironment);
        $this->assertStringNotContainsString('OTP_HTTP_TOKEN=sk_', $stagingEnvironment);
    }

    public function test_automated_tests_use_an_approved_isolated_database(): void
    {
        $this->assertSame('testing', app()->environment());

        if (config('database.default') === 'pgsql') {
            $database = config('database.connections.pgsql.database');

            $this->assertIsString($database);
            $this->assertMatchesRegularExpression('/(^|[_-])test(ing)?$/', $database);

            return;
        }

        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    public function test_postgresql_gate_is_fail_closed_and_contains_no_credentials(): void
    {
        $script = file_get_contents(base_path('scripts/test-postgresql.ps1'));
        $configuration = file_get_contents(base_path('phpunit.pgsql.xml'));

        $this->assertIsString($script);
        $this->assertIsString($configuration);
        $this->assertStringContainsString('must end with _test, -test, _testing, or -testing', $script);
        $this->assertStringContainsString('select current_database()', $script);
        $this->assertStringContainsString('EXPLORIA_PG_BIN', $script);
        $this->assertStringContainsString('Required PostgreSQL tool', $script);
        $this->assertStringContainsString('$env:APP_ENV = \'testing\'', $script);
        $this->assertStringContainsString('$env:SESSION_DRIVER = \'array\'', $script);
        $this->assertStringContainsString('$env:SESSION_SECURE_COOKIE = \'false\'', $script);
        $this->assertStringContainsString('$env:OTP_DRIVER = \'local\'', $script);
        $this->assertStringContainsString('$env:OTP_HTTP_ENDPOINT = \'\'', $script);
        $this->assertStringContainsString('$env:OTP_HTTP_TOKEN = \'\'', $script);
        $this->assertStringContainsString('<env name="DB_CONNECTION" value="pgsql"/>', $configuration);
        $this->assertStringNotContainsString('DB_PASSWORD" value=', $configuration);
    }

    public function test_backup_and_restore_scripts_are_fail_closed(): void
    {
        $backupScript = file_get_contents(base_path('scripts/backup-postgresql.ps1'));
        $restoreScript = file_get_contents(base_path('scripts/test-postgresql-restore.ps1'));
        $linuxBackupScript = file_get_contents(base_path('scripts/backup-postgresql.sh'));
        $linuxRestoreScript = file_get_contents(base_path('scripts/test-postgresql-restore.sh'));
        $launchAssuranceScript = file_get_contents(base_path('scripts/run-launch-assurance.ps1'));

        $this->assertIsString($backupScript);
        $this->assertIsString($restoreScript);
        $this->assertIsString($linuxBackupScript);
        $this->assertIsString($linuxRestoreScript);
        $this->assertIsString($launchAssuranceScript);
        $this->assertStringContainsString('$pgRestore --list', $backupScript);
        $this->assertStringContainsString('EXPLORIA_PG_BIN', $backupScript);
        $this->assertStringContainsString('EXPLORIA_PG_BIN', $restoreScript);
        $this->assertStringContainsString('must end with _restore_test or -restore-test', $restoreScript);
        $this->assertStringContainsString('--clean --if-exists --exit-on-error', $restoreScript);
        $this->assertStringContainsString('set -Eeuo pipefail', $linuxBackupScript);
        $this->assertStringContainsString('umask 077', $linuxBackupScript);
        $this->assertStringContainsString('pg_restore --list', $linuxBackupScript);
        $this->assertStringContainsString('must end with _restore_test or -restore-test', $linuxRestoreScript);
        $this->assertStringContainsString('--clean', $linuxRestoreScript);
        $this->assertStringContainsString('--if-exists', $linuxRestoreScript);
        $this->assertStringContainsString('--exit-on-error', $linuxRestoreScript);
        $this->assertStringContainsString('exploria:campaign-assurance', $launchAssuranceScript);
        $this->assertStringContainsString('exploria:production-readiness', $launchAssuranceScript);
        $this->assertStringContainsString('scripts\test-postgresql.ps1', $launchAssuranceScript);
        $this->assertStringContainsString('scripts\backup-postgresql.ps1', $launchAssuranceScript);
        $this->assertStringContainsString('scripts\test-postgresql-restore.ps1', $launchAssuranceScript);
        $this->assertStringContainsString('PostgreSQL gate skipped', $launchAssuranceScript);
        $this->assertStringNotContainsString('PGPASSWORD = \'', $backupScript);
        $this->assertStringNotContainsString('PGPASSWORD = \'', $restoreScript);
        $this->assertStringNotContainsString('PGPASSWORD = \'', $launchAssuranceScript);
        $this->assertStringContainsString('export PGPASSWORD="$password"', $linuxBackupScript);
        $this->assertStringContainsString('export PGPASSWORD="$password"', $linuxRestoreScript);
        $this->assertStringNotContainsString("PGPASSWORD='", $linuxBackupScript);
        $this->assertStringNotContainsString("PGPASSWORD='", $linuxRestoreScript);
    }

    public function test_linux_staging_deployment_is_atomic_and_fail_closed(): void
    {
        $deployScript = file_get_contents(base_path('scripts/deploy-staging.sh'));
        $nginx = file_get_contents(base_path('deploy/nginx/exploria-staging.conf.example'));
        $queue = file_get_contents(base_path('deploy/systemd/exploria-staging-queue.service.example'));
        $scheduler = file_get_contents(base_path('deploy/systemd/exploria-staging-scheduler.service.example'));
        $timer = file_get_contents(base_path('deploy/systemd/exploria-staging-scheduler.timer.example'));

        $this->assertIsString($deployScript);
        $this->assertIsString($nginx);
        $this->assertIsString($queue);
        $this->assertIsString($scheduler);
        $this->assertIsString($timer);
        $this->assertStringContainsString('EXPLORIA_DEPLOY_ROOT', $deployScript);
        $this->assertStringContainsString('must end with /exploria-staging', $deployScript);
        $this->assertStringContainsString('EXPLORIA_DEPLOY_REF', $deployScript);
        $this->assertStringContainsString('EXPLORIA_HEALTH_URL', $deployScript);
        $this->assertStringContainsString('EXPLORIA_VERIFIED_BACKUP_PATH', $deployScript);
        $this->assertStringContainsString("app_environment\" == 'staging'", $deployScript);
        $this->assertStringContainsString("app_debug\" == 'false'", $deployScript);
        $this->assertStringContainsString("database_connection\" == 'pgsql'", $deployScript);
        $this->assertStringContainsString('EXPLORIA_HEALTH_URL must match APP_URL plus /up', $deployScript);
        $this->assertStringContainsString('git status --porcelain', $deployScript);
        $this->assertStringContainsString('git archive', $deployScript);
        $this->assertStringContainsString('storage.scaffold', $deployScript);
        $this->assertStringContainsString('php artisan migrate --force --no-interaction', $deployScript);
        $this->assertStringContainsString('exploria:production-readiness --json', $deployScript);
        $this->assertStringContainsString('exploria:demo-readiness --json', $deployScript);
        $this->assertStringContainsString('recover_previous_release', $deployScript);
        $this->assertStringContainsString('curl', $deployScript);
        $this->assertStringContainsString('https://', $deployScript);
        $this->assertStringNotContainsString('APP_KEY=', $deployScript);
        $this->assertStringNotContainsString('DB_PASSWORD=', $deployScript);
        $this->assertStringNotContainsString('rm -rf', $deployScript);
        $this->assertStringContainsString('root /var/www/exploria-staging/current/public;', $nginx);
        $this->assertStringContainsString('X-Robots-Tag "noindex, nofollow, noarchive"', $nginx);
        $this->assertStringContainsString('php8.4-fpm.sock', $nginx);
        $this->assertStringContainsString('User=exploria', $queue);
        $this->assertStringContainsString('queue:work database', $queue);
        $this->assertStringContainsString('schedule:run --no-interaction', $scheduler);
        $this->assertStringContainsString('OnCalendar=*-*-* *:*:00', $timer);
    }
}
