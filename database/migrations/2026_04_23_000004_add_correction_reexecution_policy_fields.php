<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddCorrectionReexecutionPolicyFields extends Migration
{
    public function up()
    {
        Schema::table('eod_dataset_corrections', function (Blueprint $table) {
            $table->unsignedInteger('execution_count')->default(0)->after('published_at');
            $table->dateTime('last_executed_at')->nullable()->after('execution_count');
            $table->dateTime('current_consumed_at')->nullable()->after('last_executed_at');
        });

        DB::statement("ALTER TABLE eod_dataset_corrections MODIFY status ENUM('REQUESTED','APPROVED','EXECUTING','RESEALED','REPAIR_ACTIVE','REPAIR_EXECUTED','REPAIR_CANDIDATE','CONSUMED_CURRENT','PUBLISHED','REJECTED','CANCELLED','CLOSED') NOT NULL");
        DB::statement("UPDATE eod_dataset_corrections SET execution_count = 0 WHERE execution_count IS NULL");
    }

    public function down()
    {
        DB::statement("UPDATE eod_dataset_corrections SET status = 'RESEALED' WHERE status IN ('REPAIR_ACTIVE','REPAIR_EXECUTED','REPAIR_CANDIDATE')");
        DB::statement("UPDATE eod_dataset_corrections SET status = 'PUBLISHED' WHERE status IN ('CONSUMED_CURRENT','CLOSED')");
        DB::statement("ALTER TABLE eod_dataset_corrections MODIFY status ENUM('REQUESTED','APPROVED','EXECUTING','RESEALED','REPAIR_CANDIDATE','PUBLISHED','REJECTED','CANCELLED') NOT NULL");

        Schema::table('eod_dataset_corrections', function (Blueprint $table) {
            $table->dropColumn(['execution_count', 'last_executed_at', 'current_consumed_at']);
        });
    }
}
