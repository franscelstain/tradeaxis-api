<?php

namespace App\Console\Commands\MarketData;

class DailyPipelineCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:daily {--requested_date=} {--source_mode=} {--input_file=} {--correction_id=} {--latest}';

    protected $description = 'Run the minimum daily market-data sequence: ingest, indicators, eligibility, hash, seal, finalize.';

    public function handle()
    {
        $previousInputFile = config('market_data.source.local_input_file');
        $configuredOverride = false;

        if ($this->sourceMode() === 'manual_file' && $this->option('input_file')) {
            config()->set('market_data.source.local_input_file', $this->option('input_file'));
            $configuredOverride = true;
        }

        try {
            $run = $this->pipeline()->runDaily($this->requestedDate(), $this->sourceMode(), $this->option('correction_id') ?: null);
        } finally {
            if ($configuredOverride) {
                config()->set('market_data.source.local_input_file', $previousInputFile);
            }
        }

        $this->renderRunSummary($run);
        if ($configuredOverride) {
            $this->line('input_file='.(string) $this->option('input_file'));
        }

        return 0;
    }
}
