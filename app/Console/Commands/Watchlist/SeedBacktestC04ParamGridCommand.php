<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Console\Command;

class SeedBacktestC04ParamGridCommand extends Command
{
    protected $signature = 'watchlist:backtest-c04-param-grid-seed';

    protected $description = 'Seed immutable deterministic Weekly Swing downside/stability C04 catalog without mutating R1/R2/C01/C02/C03.';

    public function handle(): int
    {
        $repository = new WatchlistBacktestParamGridRepository();

        try {
            $r1Before = $repository->catalogSnapshot(WatchlistBacktestParamGridCatalog::CATALOG_CODE);
            $r2Before = $repository->catalogSnapshot(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE);
            $c01Before = $repository->catalogSnapshot(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
            $c02Before = $repository->catalogSnapshot(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE);
            $c03Before = $repository->catalogSnapshot(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
            $this->assertHistoricalCatalogsIntact($r1Before, $r2Before, $c01Before, $c02Before, $c03Before);

            $result = $repository->seedCatalog(WatchlistBacktestC04ParamGridCatalog::rows());

            $r1After = $repository->catalogSnapshot(WatchlistBacktestParamGridCatalog::CATALOG_CODE);
            $r2After = $repository->catalogSnapshot(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE);
            $c01After = $repository->catalogSnapshot(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
            $c02After = $repository->catalogSnapshot(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE);
            $c03After = $repository->catalogSnapshot(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
            if ($r1Before !== $r1After || $r2Before !== $r2After || $c01Before !== $c01After
                || $c02Before !== $c02After || $c03Before !== $c03After) {
                throw new \RuntimeException('WS_BT_R1_MUTATION_REJECTED: historical R1/R2/C01/C02/C03 snapshot changed during C04 seed.');
            }
        } catch (\Throwable $e) {
            $this->error('status=BLOCKED');
            $this->line('reason_code='.$this->reasonCode($e));
            $this->line('message='.$e->getMessage());
            $this->line('production_ready=0');

            return 1;
        }

        $this->line('status=PASS');
        $this->line('catalog_code='.WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
        $this->line('catalog_version='.WatchlistBacktestC04ParamGridCatalog::CATALOG_VERSION);
        $this->line('catalog_count='.WatchlistBacktestC04ParamGridCatalog::CATALOG_COUNT);
        $this->line('catalog_hash='.WatchlistBacktestC04ParamGridCatalog::hash());
        $this->line('inserted_count='.$result['inserted_count']);
        $this->line('updated_count=0');
        $this->line('existing_count='.$result['existing_count']);
        $this->line('r1_catalog_count='.$r1After['catalog_count']);
        $this->line('r1_catalog_hash='.$r1After['catalog_hash']);
        $this->line('r2_catalog_count='.$r2After['catalog_count']);
        $this->line('r2_catalog_hash='.$r2After['catalog_hash']);
        $this->line('c01_catalog_count='.$c01After['catalog_count']);
        $this->line('c01_catalog_hash='.$c01After['catalog_hash']);
        $this->line('c02_catalog_count='.$c02After['catalog_count']);
        $this->line('c02_catalog_hash='.$c02After['catalog_hash']);
        $this->line('c03_catalog_count='.$c03After['catalog_count']);
        $this->line('c03_catalog_hash='.$c03After['catalog_hash']);
        $this->line('r1_immutable=1');
        $this->line('r2_immutable=1');
        $this->line('c01_immutable=1');
        $this->line('c02_immutable=1');
        $this->line('c03_immutable=1');
        $this->line('oos_executed=0');
        $this->line('production_ready=0');

        return 0;
    }

    private function assertHistoricalCatalogsIntact(array $r1, array $r2, array $c01, array $c02, array $c03): void
    {
        if ((int) $r1['catalog_count'] !== WatchlistBacktestParamGridCatalog::CATALOG_COUNT
            || (string) $r1['catalog_hash'] !== WatchlistBacktestParamGridCatalog::hash()) {
            throw new \RuntimeException(
                'WS_BT_R1_MUTATION_REJECTED: R1 must exist with its immutable count/hash before C04 can be seeded.'
            );
        }
        if ((int) $r2['catalog_count'] !== WatchlistBacktestR2ParamGridCatalog::CATALOG_COUNT
            || (string) $r2['catalog_hash'] !== WatchlistBacktestR2ParamGridCatalog::hash()) {
            throw new \RuntimeException(
                'WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH: R2 must exist with its immutable count/hash before C04 can be seeded.'
            );
        }
        if ((int) $c01['catalog_count'] !== WatchlistBacktestC01ParamGridCatalog::CATALOG_COUNT
            || (string) $c01['catalog_hash'] !== WatchlistBacktestC01ParamGridCatalog::hash()) {
            throw new \RuntimeException(
                'WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH: C01 must exist with its immutable count/hash before C04 can be seeded.'
            );
        }
        if ((int) $c02['catalog_count'] !== WatchlistBacktestC02ParamGridCatalog::CATALOG_COUNT
            || (string) $c02['catalog_hash'] !== WatchlistBacktestC02ParamGridCatalog::hash()) {
            throw new \RuntimeException(
                'WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH: C02 must exist with its immutable count/hash before C04 can be seeded.'
            );
        }
        if ((int) $c03['catalog_count'] !== WatchlistBacktestC03ParamGridCatalog::CATALOG_COUNT
            || (string) $c03['catalog_hash'] !== WatchlistBacktestC03ParamGridCatalog::hash()) {
            throw new \RuntimeException(
                'WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH: C03 must exist with its immutable count/hash before C04 can be seeded.'
            );
        }
    }

    private function reasonCode(\Throwable $e): string
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return 'WS_BT_R2_CATALOG_INVALID';
    }
}
