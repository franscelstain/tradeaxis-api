<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC05ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC06ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC07ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC14ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC15ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestC16ParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Application\Watchlist\Services\WatchlistBacktestR2ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Console\Command;

class SeedBacktestC16ParamGridCommand extends Command
{
    protected $signature = 'watchlist:backtest-c16-param-grid-seed';

    protected $description = 'Seed immutable deterministic Weekly Swing C16 controlled-pullback score-window volume-quality catalog without mutating historical catalogs.';

    public function handle()
    {
        $repository = new WatchlistBacktestParamGridRepository();

        try {
            $snapshotsBefore = $this->historicalSnapshots($repository);
            $this->assertHistoricalCatalogs($snapshotsBefore);

            $result = $repository->seedCatalog(WatchlistBacktestC16ParamGridCatalog::rows());

            $snapshotsAfter = $this->historicalSnapshots($repository);
            if ($snapshotsBefore !== $snapshotsAfter) {
                throw new \RuntimeException('WS_BT_R1_MUTATION_REJECTED: historical R1/R2/C01/C02/C03/C04/C05/C06/C07/C14/C15 snapshot changed during C16 seed.');
            }
        } catch (\Throwable $e) {
            $this->line('status=BLOCKED');
            $this->line('reason_code='.$this->reasonCode($e));
            $this->line('message='.$e->getMessage());

            return 1;
        }

        $this->line('status=PASS');
        $this->line('catalog_code='.WatchlistBacktestC16ParamGridCatalog::CATALOG_CODE);
        $this->line('catalog_version='.WatchlistBacktestC16ParamGridCatalog::CATALOG_VERSION);
        $this->line('catalog_count='.WatchlistBacktestC16ParamGridCatalog::CATALOG_COUNT);
        $this->line('catalog_hash='.WatchlistBacktestC16ParamGridCatalog::hash());
        $this->line('inserted_count='.$result['inserted_count']);
        $this->line('updated_count='.$result['updated_count']);
        $this->line('existing_count='.$result['existing_count']);
        foreach ($snapshotsBefore as $prefix => $snapshot) {
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
        $this->line('c07_immutable=1');
        $this->line('c14_immutable=1');
        $this->line('c15_immutable=1');
        $this->line('oos_executed=0');
        $this->line('production_ready=0');

        return 0;
    }

    private function historicalSnapshots(WatchlistBacktestParamGridRepository $repository): array
    {
        return [
            'r1' => $repository->catalogSnapshot(WatchlistBacktestParamGridCatalog::CATALOG_CODE),
            'r2' => $repository->catalogSnapshot(WatchlistBacktestR2ParamGridCatalog::CATALOG_CODE),
            'c01' => $repository->catalogSnapshot(WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE),
            'c02' => $repository->catalogSnapshot(WatchlistBacktestC02ParamGridCatalog::CATALOG_CODE),
            'c03' => $repository->catalogSnapshot(WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE),
            'c04' => $repository->catalogSnapshot(WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE),
            'c05' => $repository->catalogSnapshot(WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE),
            'c06' => $repository->catalogSnapshot(WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE),
            'c07' => $repository->catalogSnapshot(WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE),
            'c14' => $repository->catalogSnapshot(WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE),
            'c15' => $repository->catalogSnapshot(WatchlistBacktestC15ParamGridCatalog::CATALOG_CODE),
        ];
    }

    private function assertHistoricalCatalogs(array $snapshots): void
    {
        $expected = [
            'r1' => [WatchlistBacktestParamGridCatalog::CATALOG_COUNT, WatchlistBacktestParamGridCatalog::hash()],
            'r2' => [WatchlistBacktestR2ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestR2ParamGridCatalog::hash()],
            'c01' => [WatchlistBacktestC01ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC01ParamGridCatalog::hash()],
            'c02' => [WatchlistBacktestC02ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC02ParamGridCatalog::hash()],
            'c03' => [WatchlistBacktestC03ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC03ParamGridCatalog::hash()],
            'c04' => [WatchlistBacktestC04ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC04ParamGridCatalog::hash()],
            'c05' => [WatchlistBacktestC05ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC05ParamGridCatalog::hash()],
            'c06' => [WatchlistBacktestC06ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC06ParamGridCatalog::hash()],
            'c07' => [WatchlistBacktestC07ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC07ParamGridCatalog::hash()],
            'c14' => [WatchlistBacktestC14ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC14ParamGridCatalog::hash()],
            'c15' => [WatchlistBacktestC15ParamGridCatalog::CATALOG_COUNT, WatchlistBacktestC15ParamGridCatalog::hash()],
        ];

        foreach ($expected as $label => [$count, $hash]) {
            $snapshot = $snapshots[$label] ?? [];
            if ((int) ($snapshot['catalog_count'] ?? 0) !== $count || (string) ($snapshot['catalog_hash'] ?? '') !== $hash) {
                throw new \RuntimeException('WS_BT_R2_CATALOG_PERSISTED_SET_MISMATCH: '.strtoupper($label).' must exist with its immutable count/hash before C16 can be seeded.');
            }
        }
    }

    private function reasonCode(\Throwable $e): string
    {
        $message = $e->getMessage();
        if (strpos($message, ':') !== false) {
            return trim(strstr($message, ':', true));
        }

        return 'WS_BT_C16_PARAM_GRID_SEED_FAILED';
    }
}
