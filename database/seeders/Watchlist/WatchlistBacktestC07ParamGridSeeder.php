<?php

namespace Database\Seeders\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC07ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Database\Seeder;

class WatchlistBacktestC07ParamGridSeeder extends Seeder
{
    public function run()
    {
        (new WatchlistBacktestParamGridRepository())->seedCatalog(
            WatchlistBacktestC07ParamGridCatalog::rows()
        );
    }
}
