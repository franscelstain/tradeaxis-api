<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExpandEodRunTerminalStatusForFencedOutcomes extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE eod_runs MODIFY terminal_status ENUM('SUCCESS','HELD','FAILED','SKIPPED','BLOCKED') NULL");
    }

    public function down()
    {
        if (DB::table('eod_runs')->whereIn('terminal_status', ['SKIPPED', 'BLOCKED'])->exists()) {
            throw new \RuntimeException('RUN_STATUS_ROLLBACK_BLOCKED: governed SKIPPED/BLOCKED outcomes are persisted.');
        }
        DB::statement("ALTER TABLE eod_runs MODIFY terminal_status ENUM('SUCCESS','HELD','FAILED') NULL");
    }
}
