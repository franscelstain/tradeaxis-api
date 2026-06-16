<?php

namespace Database\Seeders\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC17ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Database\Seeder;

class WatchlistBacktestC17ParamGridSeeder extends Seeder
{
    public function run()
    {
        (new WatchlistBacktestParamGridRepository())->seedCatalog(
            WatchlistBacktestC17ParamGridCatalog::rows()
        );
    }
}
