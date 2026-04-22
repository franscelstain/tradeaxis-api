<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPromoteIntentFieldsToEodRuns extends Migration
{
    public function up()
    {
        $columns = [
            "ADD COLUMN promote_mode VARCHAR(32) NULL AFTER correction_id",
            "ADD COLUMN publish_target VARCHAR(64) NULL AFTER promote_mode",
        ];

        foreach ($columns as $statement) {
            $column = preg_replace('/^ADD COLUMN\s+([a-z_]+).*$/i', '$1', $statement);
            if (! Schema::hasColumn('eod_runs', $column)) {
                DB::statement('ALTER TABLE eod_runs '.$statement);
            }
        }

        foreach ([
            'idx_runs_promote_mode' => 'CREATE INDEX idx_runs_promote_mode ON eod_runs (promote_mode)',
            'idx_runs_publish_target' => 'CREATE INDEX idx_runs_publish_target ON eod_runs (publish_target)',
        ] as $index => $sql) {
            try {
                DB::statement($sql);
            } catch (\Throwable $e) {
            }
        }
    }

    public function down()
    {
        foreach (['idx_runs_promote_mode', 'idx_runs_publish_target'] as $index) {
            try {
                DB::statement('DROP INDEX '.$index.' ON eod_runs');
            } catch (\Throwable $e) {
                try {
                    DB::statement('DROP INDEX '.$index);
                } catch (\Throwable $ignored) {
                }
            }
        }

        foreach (['publish_target', 'promote_mode'] as $column) {
            if (Schema::hasColumn('eod_runs', $column)) {
                DB::statement('ALTER TABLE eod_runs DROP COLUMN '.$column);
            }
        }
    }
}
