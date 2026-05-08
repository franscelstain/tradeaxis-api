<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddRequestModeToEodRuns extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('eod_runs', 'request_mode')) {
            Schema::table('eod_runs', function (Blueprint $table) {
                $table->string('request_mode', 32)->nullable();
            });
        }

        try {
            DB::statement('CREATE INDEX idx_runs_request_mode ON eod_runs (request_mode)');
        } catch (\Throwable $e) {
        }
    }

    public function down()
    {
        try {
            DB::statement('DROP INDEX idx_runs_request_mode ON eod_runs');
        } catch (\Throwable $e) {
            try {
                DB::statement('DROP INDEX idx_runs_request_mode');
            } catch (\Throwable $ignored) {
            }
        }

        if (Schema::hasColumn('eod_runs', 'request_mode')) {
            Schema::table('eod_runs', function (Blueprint $table) {
                $table->dropColumn('request_mode');
            });
        }
    }
}
