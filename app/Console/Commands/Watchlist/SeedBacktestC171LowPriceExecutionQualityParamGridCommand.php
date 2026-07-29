<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Console\Command;
use Throwable;

class SeedBacktestC171LowPriceExecutionQualityParamGridCommand extends Command
{
    protected $signature = 'watchlist:backtest-c171-seed-low-price-execution-quality-c01-param-grid';

    protected $description = 'Idempotently seed the immutable five-row C171 low-price execution-quality C01 catalog; never runs IS/OOS or promotes a paramset.';

    public function handle(): int
    {
        try {
            $result = (new WatchlistBacktestParamGridRepository())->seedCatalog(
                WatchlistBacktestC171LowPriceExecutionQualityParamGridCatalog::rows()
            );
        } catch (Throwable $exception) {
            $this->error('status=BLOCKED');
            $this->line('reason_code=C171_LOW_PRICE_EXECUTION_QUALITY_C01_CATALOG_SEED_FAILED');
            $this->line('error='.$exception->getMessage());
            $this->line('production_ready=0');

            return 1;
        }

        foreach (['status','catalog_code','catalog_version','catalog_hash','inserted_count','existing_count','param_grid_count','param_grid_hash'] as $key) {
            $this->line($key.'='.(string) ($result[$key] ?? ''));
        }
        $this->line('official_is_runtime_invoked=0');
        $this->line('oos_runtime_invoked=0');
        $this->line('oos_table_read=0');
        $this->line('paramset_promoted=0');
        $this->line('production_ready=0');

        return 0;
    }
}
