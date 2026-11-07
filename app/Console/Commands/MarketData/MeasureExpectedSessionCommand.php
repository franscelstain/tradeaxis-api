<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataSessionFreshnessService;

class MeasureExpectedSessionCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:freshness:session {--date=} {--run_id=}';
    protected $description = 'Append expected-session monitoring evidence, including absent runs; no activation or SLO attainment claim.';

    public function handle()
    {
        $run = (string) $this->option('run_id');
        if ($run !== '' && (! ctype_digit($run) || (int) $run <= 0)) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', 'run_id must be an exact positive persisted identity.');
            return 1;
        }
        try {
            $report = app(MarketDataSessionFreshnessService::class)->measure((string) $this->option('date'), $run === '' ? null : (int) $run);
            $this->line(json_encode($report, JSON_UNESCAPED_SLASHES));
            return $report['run_id'] === null || ($report['evaluation']['assessment'] ?? null) === 'EVIDENCE_INCOMPLETE'
                || ! $report['expected_horizon_provenance']['assessment_horizon_verified'] ? 2 : 0;
        } catch (\Throwable $error) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $error->getMessage());
            return 1;
        }
    }
}
