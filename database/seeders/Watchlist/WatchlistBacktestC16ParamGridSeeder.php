<?php

namespace Database\Seeders\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC16ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Database\Seeder;

class WatchlistBacktestC16ParamGridSeeder extends Seeder
{
    public function run()
    {
        (new WatchlistBacktestParamGridRepository())->seedCatalog(
            WatchlistBacktestC16ParamGridCatalog::rows()
        );
    }
}
