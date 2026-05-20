<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFailedCorrectionStatus extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE eod_dataset_corrections MODIFY status ENUM('REQUESTED','APPROVED','EXECUTING','RESEALED','REPAIR_ACTIVE','REPAIR_EXECUTED','REPAIR_CANDIDATE','CONSUMED_CURRENT','PUBLISHED','FAILED','REJECTED','CANCELLED','CLOSED') NOT NULL");
    }

    public function down()
    {
        DB::statement("UPDATE eod_dataset_corrections SET status = 'REJECTED' WHERE status = 'FAILED'");
        DB::statement("ALTER TABLE eod_dataset_corrections MODIFY status ENUM('REQUESTED','APPROVED','EXECUTING','RESEALED','REPAIR_ACTIVE','REPAIR_EXECUTED','REPAIR_CANDIDATE','CONSUMED_CURRENT','PUBLISHED','REJECTED','CANCELLED','CLOSED') NOT NULL");
    }
}
