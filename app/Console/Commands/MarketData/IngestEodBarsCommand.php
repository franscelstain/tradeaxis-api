<?php

namespace App\Console\Commands\MarketData;

class IngestEodBarsCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:eod-bars:ingest {--requested_date=} {--source_mode=} {--run_id=} {--correction_id=} {--latest}';

    protected $description = 'Acquire and canonicalize EOD bars for the requested date within a run context.';

    public function handle()
    {
        $run = $this->pipeline()->completeIngest($this->makeStageInput('INGEST_BARS'));
        $this->renderRunSummary($run);

        return 0;
    }
}
