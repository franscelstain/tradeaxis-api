<?php

namespace Database\Seeders;

use Database\Seeders\MarketData\MarketDataReasonCodesSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(MarketDataReasonCodesSeeder::class);
    }
}
