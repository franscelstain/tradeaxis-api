<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFreshnessMeasurementLedger extends Migration
{
    public function up()
    {
        Schema::create('md_freshness_measurements', function (Blueprint $table) {
            $table->bigIncrements('measurement_id');
            $table->date('trade_date');
            $table->unsignedBigInteger('run_id')->nullable();
            $table->string('record_type', 32);
            $table->string('content_sha256', 64)->unique();
            $table->longText('measurement_json');
            $table->dateTime('recorded_at');
            $table->index(['trade_date', 'measurement_id'], 'freshness_date_evidence');
        });
        foreach (['UPDATE' => 'update', 'DELETE' => 'delete'] as $operation => $suffix) {
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::unprepared("CREATE TRIGGER trg_md_freshness_no_{$suffix} BEFORE {$operation} ON md_freshness_measurements FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'FRESHNESS_EVIDENCE_APPEND_ONLY'");
            } elseif (DB::connection()->getDriverName() === 'sqlite') {
                DB::unprepared("CREATE TRIGGER trg_md_freshness_no_{$suffix} BEFORE {$operation} ON md_freshness_measurements BEGIN SELECT RAISE(ABORT, 'FRESHNESS_EVIDENCE_APPEND_ONLY'); END");
            }
        }
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_md_freshness_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_md_freshness_no_delete');
        Schema::dropIfExists('md_freshness_measurements');
    }
}
