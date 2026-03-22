<?php

namespace App\Console\Commands\MarketData;

class FinalizeRunCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:run:finalize {--requested_date=} {--source_mode=} {--run_id=} {--correction_id=} {--latest}';

    protected $description = 'Resolve terminal run status and effective readable date.';

    public function handle()
    {
        $run = $this->pipeline()->completeFinalize($this->makeStageInput('FINALIZE'));
        $this->renderRunSummary($run);

        return 0;
    }
}
