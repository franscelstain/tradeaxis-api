<?php

namespace Database\Seeders\MarketData;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketDataReasonCodesSeeder extends Seeder
{
    public function run()
    {
        $seedSqlPath = base_path('docs/market_data/registry/Reason_Codes_Seed.sql');

        if (! file_exists($seedSqlPath)) {
            throw new \RuntimeException('Reason code seed document not found: '.$seedSqlPath);
        }

        DB::unprepared(file_get_contents($seedSqlPath));
    }
}
