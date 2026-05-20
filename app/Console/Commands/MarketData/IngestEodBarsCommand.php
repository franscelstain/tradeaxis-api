<?php

namespace App\Console\Commands\MarketData;

class IngestEodBarsCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:eod-bars:ingest {--requested_date=} {--source_mode=} {--run_id=} {--correction_id=} {--request_mode=} {--latest}';

    protected $description = 'Acquire and canonicalize EOD bars for the requested date within a run context.';

    public function handle()
    {
        if (! $this->validateDateString($this->option('requested_date'), 'requested_date')
            || ! $this->validateSourceModeString($this->option('source_mode'))
            || ! $this->validateStageRequestModeString($this->option('request_mode'))) {
            return 1;
        }

        $requestMode = $this->option('request_mode') ?: null;
        $run = $this->pipeline()->completeIngest($this->makeStageInput('INGEST_BARS', $requestMode));
        $this->renderRunSummary($run);

        return 0;
    }
}
