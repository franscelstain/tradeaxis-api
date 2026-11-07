<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataIncidentResponseService;

class ObserveIncidentCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:incident:observe {--run_id=}';
    protected $description = 'Persist deterministic response to existing observed run conditions; no severity override or closure authority.';

    public function handle()
    {
        if (! ctype_digit((string) $this->option('run_id')) || (int) $this->option('run_id') <= 0) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', 'A positive persisted run_id is required.');
            return 1;
        }
        try {
            $result = app(MarketDataIncidentResponseService::class)->observeRun((int) $this->option('run_id'));
            $this->line(json_encode($result, JSON_UNESCAPED_SLASHES));
            return $result['classes'] || strpos($result['state'], 'EVIDENCE_INCOMPLETE') === 0 ? 2 : 0;
        } catch (\Throwable $error) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $error->getMessage());
            return 1;
        }
    }
}
