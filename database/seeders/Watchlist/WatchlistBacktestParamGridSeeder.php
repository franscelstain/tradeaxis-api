<?php

namespace Database\Seeders\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestParamGridCatalog;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Database\Seeder;

class WatchlistBacktestParamGridSeeder extends Seeder
{
    public function run(): void
    {
        (new WatchlistBacktestParamGridRepository())->seedCanonical(
            WatchlistBacktestParamGridCatalog::rows()
        );
    }
}
