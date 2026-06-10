<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Console\Command;

class SeedBacktestParamGridCommand extends Command
{
    protected $signature = 'watchlist:backtest-param-grid-seed';

    protected $description = 'Seed the canonical deterministic Weekly Swing backtest parameter grid.';

    public function handle(): int
    {
        try {
            $result = (new WatchlistBacktestParamGridRepository())->seedCanonical(
                WatchlistBacktestParamGridCatalog::rows()
            );
        } catch (\Throwable $e) {
            $this->error('status=BLOCKED');
            $this->error('reason_code='.$this->reasonCode($e));
            $this->error('message='.$e->getMessage());

            return 1;
        }

        $this->line('status=PASS');
        $this->line('catalog_code='.WatchlistBacktestParamGridCatalog::CATALOG_CODE);
        $this->line('catalog_count='.WatchlistBacktestParamGridCatalog::CATALOG_COUNT);
        $this->line('catalog_hash='.WatchlistBacktestParamGridCatalog::hash());
        $this->line('inserted_count='.$result['inserted_count']);
        $this->line('updated_count='.$result['updated_count']);
        $this->line('existing_count='.$result['existing_count']);
        $this->line('param_grid_count='.$result['param_grid_count']);
        $this->line('param_grid_hash='.$result['param_grid_hash']);
        $this->line('production_ready=0');

        return 0;
    }

    private function reasonCode(\Throwable $e): string
    {
        if (preg_match('/^(WS_BT_[A-Z0-9_]+):/', $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'WS_BT_PARAM_GRID_SEED_FAILED';
    }
}
