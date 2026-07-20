<?php

namespace App\Console\Commands;

use App\Services\MultiCampaignAssuranceService;
use Illuminate\Console\Command;

class CheckMultiCampaignAssuranceCommand extends Command
{
    protected $signature = 'exploria:campaign-assurance
        {--venue= : Restrict assurance to one venue code}
        {--minimum-campaigns=2 : Minimum active campaigns expected for multi-campaign assurance}
        {--require-execution : Fail when participant mission/reward/redemption evidence is missing}
        {--json : Output the assurance report as JSON}';

    protected $description = 'Check whether EXPLORIA has enough campaign variety, scope integrity, and executed evidence for launch confidence.';

    public function handle(MultiCampaignAssuranceService $assurance): int
    {
        $minimumCampaigns = max(1, (int) $this->option('minimum-campaigns'));
        $venueCode = $this->option('venue');
        $report = $assurance->report(
            is_string($venueCode) && $venueCode !== '' ? $venueCode : null,
            $minimumCampaigns,
            (bool) $this->option('require-execution'),
        );

        if ($this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $report['summary']['ready'] ? self::SUCCESS : self::FAILURE;
        }

        $summary = $report['summary'];
        $this->info('EXPLORIA multi-campaign assurance');
        $this->line("Scope: {$summary['scope']}");
        $this->line("Active campaigns: {$summary['activeCampaigns']}");
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
