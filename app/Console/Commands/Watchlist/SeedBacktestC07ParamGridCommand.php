<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC05ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC06ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC07ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Console\Command;

class SeedBacktestC07ParamGridCommand extends Command
{
    protected $signature = 'watchlist:backtest-c07-param-grid-seed';

    protected $description = 'Seed immutable deterministic Weekly Swing downside/stability C07 catalog without mutating R1/R2/C01/C02/C03/C04/C05/C06.';

    public function handle()
    {
        $repository = new WatchlistBacktestParamGridRepository();

        try {
            $r1Before = $repository->catalogSnapshot(WatchlistBacktestParamGridCatalog::CATALOG_CODE);
            $r2Before = $repository->catalogSnapshot(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE);
            $c01Before = $repository->catalogSnapshot(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
            $c02Before = $repository->catalogSnapshot(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE);
            $c03Before = $repository->catalogSnapshot(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
            $c04Before = $repository->catalogSnapshot(WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
            $c05Before = $repository->catalogSnapshot(WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE);
            $c06Before = $repository->catalogSnapshot(WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
            $this->assertHistoricalCatalogs($r1Before, $r2Before, $c01Before, $c02Before, $c03Before, $c04Before, $c05Before, $c06Before);

            $result = $repository->seedCatalog(WatchlistBacktestC07ParamGridCatalog::rows());

            $r1After = $repository->catalogSnapshot(WatchlistBacktestParamGridCatalog::CATALOG_CODE);
            $r2After = $repository->catalogSnapshot(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE);
            $c01After = $repository->catalogSnapshot(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE);
            $c02After = $repository->catalogSnapshot(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE);
            $c03After = $repository->catalogSnapshot(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE);
            $c04After = $repository->catalogSnapshot(WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE);
            $c05After = $repository->catalogSnapshot(WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE);
            $c06After = $repository->catalogSnapshot(WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE);
            if ($r1Before !== $r1After || $r2Before !== $r2After || $c01Before !== $c01After
                || $c02Before !== $c02After || $c03Before !== $c03After || $c04Before !== $c04After
                || $c05Before !== $c05After || $c06Before !== $c06After) {
                throw new \RuntimeException('WS_BT_R1_MUTATION_REJECTED: historical R1/R2/C01/C02/C03/C04/C05/C06 snapshot changed during C07 seed.');
            }
        } catch (\Throwable $e) {
            $this->line('status=BLOCKED');
            $this->line('reason_code='.$this->reasonCode($e));
            $this->line('message='.$e->getMessage());

            return 1;
        }

        $this->line('status=PASS');
        $this->line('catalog_code='.WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE);
        $this->line('catalog_version='.WatchlistBacktestC07ParamGridCatalog::CATALOG_VERSION);
        $this->line('catalog_count='.WatchlistBacktestC07ParamGridCatalog::CATALOG_COUNT);
        $this->line('catalog_hash='.WatchlistBacktestC07ParamGridCatalog::hash());
        $this->line('inserted_count='.$result['inserted_count']);
        $this->line('updated_count='.$result['updated_count']);
        $this->line('existing_count='.$result['existing_count']);
        foreach ([
            'r1' => $r1Before,
            'r2' => $r2Before,
            'c01' => $c01Before,
            'c02' => $c02Before,
            'c03' => $c03Before,
            'c04' => $c04Before,
            'c05' => $c05Before,
            'c06' => $c06Before,
        ] as $prefix => $snapshot) {
            $this->line($prefix.'_catalog_count='.$snapshot['catalog_count']);
            $this->line($prefix.'_catalog_hash='.$snapshot['catalog_hash']);
        }
        $this->line('r1_immutable=1');
        $this->line('r2_immutable=1');
        $this->line('c01_immutable=1');
        $this->line('c02_immutable=1');
        $this->line('c03_immutable=1');
        $this->line('c04_immutable=1');
        $this->line('c05_immutable=1');
        $this->line('c06_immutable=1');
        $this->line('oos_executed=0');
        $this->line('production_ready=0');

        return 0;
    }

    private function assertHistoricalCatalogs(array $r1, array $r2, array $c01, array $c02, array $c03, array $c04, array $c05, array $c06): void
    {
        $expected = [
            'R1' => [$r1, WatchlistBacktestParamGridCatalog::CATALOG_COUNT, WatchlistBacktestParamGridCatalog::hash()],
            'R2' => [$r2, WatchlistBacktestR2ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestR2ParamGridCatalog::hash()],
            'C01' => [$c01, WatchlistBacktestC01ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC01ParamGridCatalog::hash()],
            'C02' => [$c02, WatchlistBacktestC02ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC02ParamGridCatalog::hash()],
            'C03' => [$c03, WatchlistBacktestC03ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC03ParamGridCatalog::hash()],
            'C04' => [$c04, WatchlistBacktestC04ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC04ParamGridCatalog::hash()],
            'C05' => [$c05, WatchlistBacktestC05ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC05ParamGridCatalog::hash()],
            'C06' => [$c06, WatchlistBacktestC06ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC06ParamGridCatalog::hash()],
        ];

        foreach ($expected as $label => [$snapshot, $count, $hash]) {
            if ((int) ($snapshot['catalog_count'] ?? 0) !== $count || (string) ($snapshot['catalog_hash'] ?? '') !== $hash) {
                throw new \RuntimeException('WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH: '.$label.' must exist with its immutable count/hash before C07 can be seeded.');
            }
        }
    }

    private function reasonCode(\Throwable $e): string
    {
        $message = $e->getMessage();
        if (strpos($message, ':') !== false) {
            return trim(strstr($message, ':', true));
        }

        return 'WS_BT_C07_PARAM_GRID_SEED_FAILED';
    }
}
