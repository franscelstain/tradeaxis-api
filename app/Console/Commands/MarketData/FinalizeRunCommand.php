<?php

namespace App\Console\Commands\MarketData;

class FinalizeRunCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:run:finalize {--requested_date=} {--source_mode=} {--run_id=} {--correction_id=} {--latest}';

    protected $description = 'Resolve terminal run status and effective readable date.';

    public function handle()
    {
        if (! $this->validateDateString($this->option('requested_date'), 'requested_date') || ! $this->validateSourceModeString($this->option('source_mode'))) {
            return 1;
        }

        $run = $this->pipeline()->completeFinalize($this->makeStageInput('FINALIZE'));
        $this->renderRunSummary($run);

        return 0;
    }
}
