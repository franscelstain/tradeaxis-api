<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ProtectMarketDataRunEventTrail extends Migration
{
    public function up()
    {
        foreach (['UPDATE' => 'update', 'DELETE' => 'delete'] as $operation => $suffix) {
            DB::unprepared("CREATE TRIGGER trg_eod_run_events_no_{$suffix} BEFORE {$operation} ON eod_run_events FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RUN_EVENT_APPEND_ONLY'");
        }
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_eod_run_events_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_eod_run_events_no_delete');
    }
}
