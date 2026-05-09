<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpandReplayMismatchSummaryToLongtext extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('md_replay_daily_metrics') || ! Schema::hasColumn('md_replay_daily_metrics', 'mismatch_summary')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE md_replay_daily_metrics MODIFY mismatch_summary LONGTEXT NULL');
        }
    }

    public function down()
    {
        if (! Schema::hasTable('md_replay_daily_metrics') || ! Schema::hasColumn('md_replay_daily_metrics', 'mismatch_summary')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE md_replay_daily_metrics MODIFY mismatch_summary VARCHAR(255) NULL');
        }
    }
}
