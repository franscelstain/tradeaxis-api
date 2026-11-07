<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataFreshnessEvidenceService;

class MeasureFreshnessCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:freshness:measure {--run_id=}';
    protected $description = 'Append observed run freshness evidence; never approve activation or claim operational SLO compliance.';

    public function handle()
    {
        if (! ctype_digit((string) $this->option('run_id')) || (int) $this->option('run_id') <= 0) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', 'A positive persisted run_id is required.');
            return 1;
        }
        try {
            $result = app(MarketDataFreshnessEvidenceService::class)->assessRun((int) $this->option('run_id'));
            $this->line(json_encode($result, JSON_UNESCAPED_SLASHES));
            return $result['evaluation']['assessment'] === 'EVIDENCE_INCOMPLETE'
                || ! $result['expected_horizon_provenance']['assessment_horizon_verified'] ? 2 : 0;
        } catch (\Throwable $error) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $error->getMessage());
            return 1;
        }
    }
}
