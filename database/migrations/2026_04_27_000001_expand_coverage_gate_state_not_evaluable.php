<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExpandCoverageGateStateNotEvaluable extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE eod_runs MODIFY coverage_gate_state ENUM('PASS','FAIL','NOT_EVALUABLE','BLOCKED') NULL");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE eod_runs SET coverage_gate_state = 'BLOCKED' WHERE coverage_gate_state = 'NOT_EVALUABLE'");
            DB::statement("ALTER TABLE eod_runs MODIFY coverage_gate_state ENUM('PASS','FAIL','BLOCKED') NULL");
        }
    }
}
