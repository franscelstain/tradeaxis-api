<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\DTOs\MarketDataStageInput;
use App\Application\MarketData\Services\MarketDataPipelineService;
use Carbon\Carbon;
use Illuminate\Console\Command;

abstract class AbstractMarketDataCommand extends Command
{
    protected function requestedDate()
    {
        if ($this->option('requested_date')) {
            return $this->option('requested_date');
        }

        if ($this->option('latest')) {
            return Carbon::now(config('market_data.platform.timezone'))->toDateString();
        }

        return Carbon::now(config('market_data.platform.timezone'))->toDateString();
    }

    protected function sourceMode()
    {
        return $this->option('source_mode') ?: config('market_data.pipeline.default_source_mode');
    }

    protected function makeStageInput($stage)
    {
        return new MarketDataStageInput(
            $this->requestedDate(),
            $this->sourceMode(),
            $this->option('run_id') ?: null,
            $stage,
            $this->option('correction_id') ?: null
        );
    }

    protected function pipeline()
    {
        return app(MarketDataPipelineService::class);
    }

    protected function renderRunSummary($run)
    {
        $this->info('run_id='.(string) $run->run_id);
        $this->line('requested_date='.(string) $run->trade_date_requested);
        $this->line('stage='.(string) $run->stage);
        $this->line('lifecycle_state='.(string) $run->lifecycle_state);
        $this->line('terminal_status='.(string) $run->terminal_status);
        $this->line('publishability_state='.(string) $run->publishability_state);

        if (isset($run->reason_code) && $run->reason_code !== null && $run->reason_code !== '') {
            $this->line('reason_code='.(string) $run->reason_code);
        }

        if (isset($run->notes) && $run->notes !== null && $run->notes !== '') {
            $this->line('notes='.(string) $run->notes);
        }
    }
}
