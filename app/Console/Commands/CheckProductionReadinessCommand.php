<?php

namespace App\Console\Commands;

use App\Services\ProductionReadinessService;
use Illuminate\Console\Command;

class CheckProductionReadinessCommand extends Command
{
    protected $signature = 'exploria:production-readiness
        {--json : Output the readiness report as JSON}';

    protected $description = 'Fail closed when the current environment is not ready for staging or production deployment.';

    public function handle(ProductionReadinessService $readiness): int
    {
        $report = $readiness->report();

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $report['summary']['ready'] ? self::SUCCESS : self::FAILURE;
        }

        $summary = $report['summary'];
        $this->info('EXPLORIA production readiness');
        $this->line("Environment: {$summary['environment']}");
        $this->line("Pass: {$summary['passCount']} | Fail: {$summary['failCount']}");

        $this->table(
            ['Key', 'Label', 'Status', 'Actual', 'Requirement'],
            collect($report['checks'])->map(fn (array $check): array => [
                $check['key'],
                $check['label'],
                $check['status'],
                is_array($check['actual'])
                    ? json_encode($check['actual'], JSON_UNESCAPED_UNICODE)
                    : (string) $check['actual'],
                $check['message'],
            ])->all(),
        );

        return $summary['ready'] ? self::SUCCESS : self::FAILURE;
    }
}
