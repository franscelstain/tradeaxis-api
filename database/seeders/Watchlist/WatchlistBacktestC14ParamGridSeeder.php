<?php

namespace Database\Seeders\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC14ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Database\Seeder;

class WatchlistBacktestC14ParamGridSeeder extends Seeder
{
    public function run()
    {
        (new WatchlistBacktestParamGridRepository())->seedCatalog(
            WatchlistBacktestC14ParamGridCatalog::rows()
        );
    }
}
