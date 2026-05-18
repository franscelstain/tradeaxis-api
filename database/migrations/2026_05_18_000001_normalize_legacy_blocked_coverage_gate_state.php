<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeLegacyBlockedCoverageGateState extends Migration
{
    public function up()
    {
        $this->normalizeCoverageGateStateColumn('eod_runs', 'coverage_gate_state');
        $this->normalizeCoverageGateStateColumn('md_replay_daily_metrics', 'coverage_gate_state');
        $this->normalizeCoverageGateStateColumn('md_replay_daily_metrics', 'expected_coverage_gate_state');

        if (DB::getDriverName() === 'mysql' && Schema::hasTable('eod_runs') && Schema::hasColumn('eod_runs', 'coverage_gate_state')) {
            DB::statement("ALTER TABLE eod_runs MODIFY coverage_gate_state ENUM('PASS','FAIL','NOT_EVALUABLE') NULL");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasTable('eod_runs') && Schema::hasColumn('eod_runs', 'coverage_gate_state')) {
            DB::statement("ALTER TABLE eod_runs MODIFY coverage_gate_state ENUM('PASS','FAIL','NOT_EVALUABLE','BLOCKED') NULL");
        }
    }

    private function normalizeCoverageGateStateColumn($table, $column)
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)
            ->where($column, 'BLOCKED')
            ->update([$column => 'NOT_EVALUABLE']);
    }
}
