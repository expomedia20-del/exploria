<?php

namespace Tests\Feature\Infrastructure;

use Tests\TestCase;

class EnvironmentBaselineTest extends TestCase
{
    public function test_example_environment_uses_the_approved_database_and_locale(): void
    {
        $exampleEnvironment = file_get_contents(base_path('.env.example'));

        $this->assertIsString($exampleEnvironment);
        $this->assertStringContainsString('APP_NAME=EXPLORIA', $exampleEnvironment);
        $this->assertStringContainsString('APP_LOCALE=fa', $exampleEnvironment);
        $this->assertStringContainsString('DB_CONNECTION=pgsql', $exampleEnvironment);
        $this->assertStringContainsString('DB_PORT=5432', $exampleEnvironment);
    }

    public function test_automated_tests_use_an_isolated_in_memory_database(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertSame('sqlite', config('database.default'));
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
        $this->assertStringContainsString('<env name="DB_CONNECTION" value="pgsql"/>', $configuration);
        $this->assertStringNotContainsString('DB_PASSWORD" value=', $configuration);
    }
}
