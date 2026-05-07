<?php

namespace App\Console\Commands\MarketData;

class ComputeIndicatorsCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:eod-indicators:compute {--requested_date=} {--source_mode=} {--run_id=} {--correction_id=} {--latest}';

    protected $description = 'Compute deterministic indicators for the requested date within a run context.';

    public function handle()
    {
        if (! $this->validateDateString($this->option('requested_date'), 'requested_date') || ! $this->validateSourceModeString($this->option('source_mode'))) {
            return 1;
        }

        $run = $this->pipeline()->completeIndicators($this->makeStageInput('COMPUTE_INDICATORS'));
        $this->renderRunSummary($run);

        return 0;
    }
}
