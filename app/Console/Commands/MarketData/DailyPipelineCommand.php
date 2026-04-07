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

        $requestedDate = $this->requestedDate();
        $sourceMode = $this->sourceMode();
        $correctionId = $this->option('correction_id') ?: null;

        try {
            $run = $this->pipeline()->runDaily($requestedDate, $sourceMode, $correctionId);
        } catch (\Throwable $e) {
            $run = $this->latestRunForRequestedDate($requestedDate, $sourceMode);
            $this->renderRecoveredFailureSummary($run, $e);

            return 1;
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
