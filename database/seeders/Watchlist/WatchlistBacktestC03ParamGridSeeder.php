<?php

namespace Database\Seeders\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC03ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Database\Seeder;

class WatchlistBacktestC03ParamGridSeeder extends Seeder
{
    public function run(): void
    {
        (new WatchlistBacktestParamGridRepository())->seedCatalog(
            WatchlistBacktestC03ParamGridCatalog::rows()
        );
    }
}
