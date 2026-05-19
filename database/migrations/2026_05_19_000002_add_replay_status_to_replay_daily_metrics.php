<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddReplayStatusToReplayDailyMetrics extends Migration
{
    public function up()
    {
        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            if (! Schema::hasColumn('md_replay_daily_metrics', 'replay_status')) {
                $table->string('replay_status', 16)->nullable()->after('comparison_result');
            }
        });

        if (Schema::hasColumn('md_replay_daily_metrics', 'replay_status')) {
            DB::table('md_replay_daily_metrics')
                ->whereNull('replay_status')
                ->update([
                    'replay_status' => DB::raw("CASE WHEN comparison_result IN ('MATCH', 'EXPECTED_DEGRADE') THEN 'PASS' WHEN comparison_result IN ('MISMATCH', 'UNEXPECTED') THEN 'FAIL' ELSE 'BLOCKED' END"),
                ]);
        }

        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('md_replay_daily_metrics', 'replay_status')) {
            DB::statement("ALTER TABLE md_replay_daily_metrics MODIFY replay_status ENUM('PASS','FAIL','BLOCKED') NOT NULL");
        }

        try {
            DB::statement('CREATE INDEX idx_replay_daily_replay_status ON md_replay_daily_metrics (replay_id, replay_status)');
        } catch (\Throwable $e) {
            // Index may already exist on environments created from the locked SQL schema.
        }
    }

    public function down()
    {
        try {
            DB::statement('DROP INDEX idx_replay_daily_replay_status ON md_replay_daily_metrics');
        } catch (\Throwable $e) {
            try {
                DB::statement('DROP INDEX idx_replay_daily_replay_status');
            } catch (\Throwable $ignored) {
            }
        }

        Schema::table('md_replay_daily_metrics', function (Blueprint $table) {
            if (Schema::hasColumn('md_replay_daily_metrics', 'replay_status')) {
                $table->dropColumn('replay_status');
            }
        });
    }
}
