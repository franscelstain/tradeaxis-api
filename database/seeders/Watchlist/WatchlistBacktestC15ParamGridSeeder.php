<?php

namespace Database\Seeders\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC15ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Database\Seeder;

class WatchlistBacktestC15ParamGridSeeder extends Seeder
{
    public function run()
    {
        (new WatchlistBacktestParamGridRepository())->seedCatalog(
            WatchlistBacktestC15ParamGridCatalog::rows()
        );
    }
}
