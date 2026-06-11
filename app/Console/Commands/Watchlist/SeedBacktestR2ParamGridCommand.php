<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Console\Command;

class SeedBacktestR2ParamGridCommand extends Command
{
    protected $signature = 'watchlist:backtest-r2-param-grid-seed';

    protected $description = 'Seed immutable deterministic Weekly Swing R2 entry-quality catalog without mutating R1.';

    public function handle(): int
    {
        $repository = new WatchlistBacktestParamGridRepository();

        try {
            $r1Before = $repository->catalogSnapshot(WatchlistBacktestParamGridCatalog::CATALOG_CODE);
            if ((int) $r1Before['catalog_count'] !== WatchlistBacktestParamGridCatalog::CATALOG_COUNT
                || (string) $r1Before['catalog_hash'] !== WatchlistBacktestParamGridCatalog::hash()) {
                throw new \RuntimeException(
                    'WS_BT_R1_MUTATION_REJECTED: R1 must exist with its immutable count/hash before R2 can be seeded.'
                );
            }

            $result = $repository->seedCatalog(WatchlistBacktestR2ParamGridCatalog::rows());
            $r1After = $repository->catalogSnapshot(WatchlistBacktestParamGridCatalog::CATALOG_CODE);
            if ($r1Before !== $r1After) {
                throw new \RuntimeException('WS_BT_R1_MUTATION_REJECTED: R1 snapshot changed during R2 seed.');
            }
        } catch (\Throwable $e) {
            $this->error('status=BLOCKED');
            $this->line('reason_code='.$this->reasonCode($e));
            $this->line('message='.$e->getMessage());
            $this->line('production_ready=0');

            return 1;
        }

        $this->line('status=PASS');
        $this->line('catalog_code='.WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE);
        $this->line('catalog_version='.WatchlistBacktestR2ParamGridCatalog::CATALOG_VERSION);
        $this->line('catalog_count='.WatchlistBacktestR2ParamGridCatalog::CATALOG_COUNT);
        $this->line('catalog_hash='.WatchlistBacktestR2ParamGridCatalog::hash());
        $this->line('inserted_count='.$result['inserted_count']);
        $this->line('updated_count=0');
        $this->line('existing_count='.$result['existing_count']);
        $this->line('r1_catalog_count='.$r1After['catalog_count']);
        $this->line('r1_catalog_hash='.$r1After['catalog_hash']);
        $this->line('r1_immutable=1');
        $this->line('production_ready=0');

        return 0;
    }

    private function reasonCode(\Throwable $e): string
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'WS_BT_R2_CATALOG_INVALID';
    }
}
