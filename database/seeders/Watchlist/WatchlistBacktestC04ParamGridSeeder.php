<?php

namespace Database\Seeders\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC04ParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Database\Seeder;

class WatchlistBacktestC04ParamGridSeeder extends Seeder
{
    public function run(): void
    {
        (new WatchlistBacktestParamGridRepository())->seedCatalog(
            WatchlistBacktestC04ParamGridCatalog::rows()
        );
    }
}
