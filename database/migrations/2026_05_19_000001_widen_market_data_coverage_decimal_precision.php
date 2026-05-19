<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WidenMarketDataCoverageDecimalPrecision extends Migration
{
    public function up()
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->coverageDecimalColumns() as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::statement('ALTER TABLE '.$table.' MODIFY '.$column.' DECIMAL(12,6) NULL');
            }
        }
    }

    public function down()
    {
        // Do not narrow coverage precision on rollback; it can truncate audit evidence.
    }

    private function coverageDecimalColumns(): array
    {
        return [
            'eod_runs' => [
                'coverage_ratio',
                'coverage_min_threshold',
            ],
            'md_replay_daily_metrics' => [
                'coverage_ratio',
                'coverage_min_threshold',
                'expected_coverage_ratio',
                'expected_coverage_min_threshold',
            ],
        ];
    }
}
