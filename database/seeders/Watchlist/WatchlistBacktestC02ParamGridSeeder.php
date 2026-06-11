<?php

namespace Database\Seeders\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC02ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Database\Seeder;

class WatchlistBacktestC02ParamGridSeeder extends Seeder
{
    public function run(): void
    {
        (new WatchlistBacktestParamGridRepository())->seedCatalog(
            WatchlistBacktestC02ParamGridCatalog::rows()
        );
    }
}
