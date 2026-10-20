<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataOperationalStatusService;

class OperationalStatusCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:status {--requested_date=} {--run_id=} {--latest}';
    protected $description = 'Read-only operational status from persisted run, calendar, event and verified publication evidence.';

    public function handle()
    {
        if (! $this->validateDateString($this->option('requested_date'), 'requested_date')) return 1;
        if ($this->option('requested_date') && $this->option('latest')) {
            $this->renderCommandBlocked('COMMAND_CONFLICTING_OPTIONS', 'Select requested_date or latest, not both.');
            return 1;
        }
        try {
            $status = app(MarketDataOperationalStatusService::class)->forDate($this->requestedDate(), $this->option('run_id') ? (int) $this->option('run_id') : null);
            $this->line(json_encode($status, JSON_UNESCAPED_SLASHES));
            return $status['operationally_healthy'] ? 0 : 2;
        } catch (\Throwable $e) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $e->getMessage());
            return 1;
        }
    }
}
