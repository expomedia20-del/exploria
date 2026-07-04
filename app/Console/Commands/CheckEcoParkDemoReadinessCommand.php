<?php

namespace App\Console\Commands;

use App\Services\EcoParkDemoReadinessService;
use Illuminate\Console\Command;

class CheckEcoParkDemoReadinessCommand extends Command
{
    protected $signature = 'exploria:demo-readiness
        {--venue=ecopark-abbasabad : Venue code to evaluate}
        {--json : Output the readiness report as JSON}';

    protected $description = 'Check whether the EcoPark end-to-end demo has the required campaign, QR, mission, reward, treasure, sponsor, partner, and panel data.';

    public function handle(EcoParkDemoReadinessService $readiness): int
    {
        $report = $readiness->report((string) $this->option('venue'));

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $report['summary']['ready'] ? self::SUCCESS : self::FAILURE;
        }

        $summary = $report['summary'];
        $this->info('EcoPark demo readiness');
        $this->line('Venue: '.$summary['venueCode']);
        $this->line("Pass: {$summary['passCount']} | Warning: {$summary['warningCount']} | Fail: {$summary['failCount']}");

        $this->table(
            ['Key', 'Label', 'Status', 'Count', 'Minimum', 'Message'],
            collect($report['checks'])->map(fn (array $check): array => [
                $check['key'],
                $check['label'],
                $check['status'],
                $check['count'],
                $check['minimum'] ?? '-',
                $check['message'],
            ])->all(),
        );

        if (! empty($report['nextActions'])) {
            $this->warn('Next actions:');
            foreach ($report['nextActions'] as $action) {
                $this->line('- '.$action);
            }
        }

        return $summary['ready'] ? self::SUCCESS : self::FAILURE;
    }
}
