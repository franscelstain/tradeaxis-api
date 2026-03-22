<?php

namespace App\Console\Commands\MarketData;

class DailyPipelineCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:daily {--requested_date=} {--source_mode=} {--correction_id=} {--latest}';

    protected $description = 'Run the minimum daily market-data sequence: ingest, indicators, eligibility, hash, seal, finalize.';

    public function handle()
    {
        $run = $this->pipeline()->runDaily($this->requestedDate(), $this->sourceMode(), $this->option('correction_id') ?: null);
        $this->renderRunSummary($run);

        return 0;
    }
}
