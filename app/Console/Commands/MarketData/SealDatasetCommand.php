<?php

namespace App\Console\Commands\MarketData;

class SealDatasetCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:dataset:seal {--requested_date=} {--source_mode=} {--run_id=} {--correction_id=} {--latest}';

    protected $description = 'Seal a coherent consumer-readable dataset for the requested date.';

    public function handle()
    {
        $run = $this->pipeline()->completeSeal($this->makeStageInput('SEAL'));
        $this->renderRunSummary($run);

        return 0;
    }
}
