<?php

namespace App\Console\Commands\MarketData;

class AuditHashCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:audit:hash {--requested_date=} {--source_mode=} {--run_id=} {--correction_id=} {--latest}';

    protected $description = 'Compute content hashes for bars, indicators, and eligibility artifacts.';

    public function handle()
    {
        $run = $this->pipeline()->completeHash($this->makeStageInput('HASH'));
        $this->renderRunSummary($run);

        return 0;
    }
}
