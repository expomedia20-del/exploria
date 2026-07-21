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
            $this->assertSame(env('EXPLORIA_PG_DATABASE'), config('database.connections.pgsql.database'));

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
        $launchAssuranceScript = file_get_contents(base_path('scripts/run-launch-assurance.ps1'));

        $this->assertIsString($backupScript);
        $this->assertIsString($restoreScript);
        $this->assertIsString($launchAssuranceScript);
        $this->assertStringContainsString('$pgRestore --list', $backupScript);
        $this->assertStringContainsString('EXPLORIA_PG_BIN', $backupScript);
        $this->assertStringContainsString('EXPLORIA_PG_BIN', $restoreScript);
        $this->assertStringContainsString('must end with _restore_test or -restore-test', $restoreScript);
        $this->assertStringContainsString('--clean --if-exists --exit-on-error', $restoreScript);
        $this->assertStringContainsString('exploria:campaign-assurance', $launchAssuranceScript);
        $this->assertStringContainsString('exploria:production-readiness', $launchAssuranceScript);
        $this->assertStringContainsString('scripts\test-postgresql.ps1', $launchAssuranceScript);
        $this->assertStringContainsString('scripts\backup-postgresql.ps1', $launchAssuranceScript);
        $this->assertStringContainsString('scripts\test-postgresql-restore.ps1', $launchAssuranceScript);
        $this->assertStringContainsString('PostgreSQL gate skipped', $launchAssuranceScript);
        $this->assertStringNotContainsString('PGPASSWORD = \'', $backupScript);
        $this->assertStringNotContainsString('PGPASSWORD = \'', $restoreScript);
        $this->assertStringNotContainsString('PGPASSWORD = \'', $launchAssuranceScript);
    }
}
