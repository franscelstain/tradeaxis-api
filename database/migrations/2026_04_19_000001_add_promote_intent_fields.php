<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPromoteIntentFields extends Migration
{
    public function up()
    {
        $runColumns = [
            "ADD COLUMN promote_mode VARCHAR(32) NULL AFTER correction_id",
            "ADD COLUMN publish_target VARCHAR(64) NULL AFTER promote_mode",
        ];

        foreach ($runColumns as $statement) {
            $column = preg_replace('/^ADD COLUMN\s+([a-z_]+).*$/i', '$1', $statement);
            if (! Schema::hasColumn('eod_runs', $column)) {
                DB::statement('ALTER TABLE eod_runs '.$statement);
            }
        }

        $publicationColumns = [
            "ADD COLUMN promote_mode VARCHAR(32) NULL AFTER supersedes_publication_id",
            "ADD COLUMN publish_target VARCHAR(64) NULL AFTER promote_mode",
        ];

        foreach ($publicationColumns as $statement) {
            $column = preg_replace('/^ADD COLUMN\s+([a-z_]+).*$/i', '$1', $statement);
            if (! Schema::hasColumn('eod_publications', $column)) {
                DB::statement('ALTER TABLE eod_publications '.$statement);
            }
        }
    }

    public function down()
    {
        foreach (['publish_target', 'promote_mode'] as $column) {
            if (Schema::hasColumn('eod_publications', $column)) {
                DB::statement('ALTER TABLE eod_publications DROP COLUMN '.$column);
            }
        }

        foreach (['publish_target', 'promote_mode'] as $column) {
            if (Schema::hasColumn('eod_runs', $column)) {
                DB::statement('ALTER TABLE eod_runs DROP COLUMN '.$column);
            }
        }
    }
}
