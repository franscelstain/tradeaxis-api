<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC171RemediationParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Console\Command;
use Throwable;

class SeedBacktestC171RemediationParamGridCommand extends Command
{
    protected $signature = 'watchlist:backtest-c171-seed-remediation-param-grid';

    protected $description = 'Idempotently seed the immutable five-row C171 real-IS remediation catalog; never runs IS/OOS or promotes a paramset.';

    public function handle(): int
    {
        try {
            $result = (new WatchlistBacktestParamGridRepository())->seedCatalog(
                WatchlistBacktestC171RemediationParamGridCatalog::rows()
            );
        } catch (Throwable $exception) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=C171_REMEDIATION_CATALOG_SEED_FAILED');
            $this->line('error='.$exception->getMessage());
            $this->line('production_ready=0');

            return 1;
        }

        foreach (['status','catalog_code','catalog_version','catalog_hash','inserted_count','existing_count','param_grid_count','param_grid_hash'] as $key) {
            $this->line($key.'='.(string) ($result[$key] ?? ''));
        }
        $this->line('oos_runtime_invoked=0');
        $this->line('paramset_promoted=0');
        $this->line('production_ready=0');

        return 0;
    }
}
