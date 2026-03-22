<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMarketDataCoreSchema extends Migration
{
    public function up()
    {
        $schemaPath = base_path('docs/market_data/db/Database_Schema_MariaDB.sql');

        if (! file_exists($schemaPath)) {
            throw new RuntimeException('Official market-data schema document not found: '.$schemaPath);
        }

        DB::unprepared(file_get_contents($schemaPath));
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'md_replay_reason_code_counts',
            'md_replay_daily_metrics',
            'eod_eligibility_history',
            'eod_indicators_history',
            'eod_bars_history',
            'eod_dataset_corrections',
            'eod_current_publication_pointer',
            'eod_publications',
            'eod_run_events',
            'eod_runs',
            'eod_eligibility',
            'eod_indicators',
            'eod_invalid_bars',
            'eod_bars',
            'eod_reason_codes',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }
}
