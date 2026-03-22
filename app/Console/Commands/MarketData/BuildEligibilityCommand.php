<?php

namespace App\Console\Commands\MarketData;

class BuildEligibilityCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:eod-eligibility:build {--requested_date=} {--source_mode=} {--run_id=} {--correction_id=} {--latest}';

    protected $description = 'Build eligibility rows for the requested date within a run context.';

    public function handle()
    {
        $run = $this->pipeline()->completeEligibility($this->makeStageInput('BUILD_ELIGIBILITY'));
        $this->renderRunSummary($run);

        return 0;
    }
}
