<?php

namespace Database\Seeders\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC01ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Database\Seeder;

class WatchlistBacktestC01ParamGridSeeder extends Seeder
{
    public function run(): void
    {
        (new WatchlistBacktestParamGridRepository())->seedCatalog(
            WatchlistBacktestC01ParamGridCatalog::rows()
        );
    }
}
