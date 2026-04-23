<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE eod_dataset_corrections MODIFY status ENUM('REQUESTED','APPROVED','EXECUTING','RESEALED','REPAIR_CANDIDATE','PUBLISHED','REJECTED','CANCELLED') NOT NULL");
    }

    public function down()
    {
        DB::statement("UPDATE eod_dataset_corrections SET status = 'RESEALED' WHERE status = 'REPAIR_CANDIDATE'");
        DB::statement("ALTER TABLE eod_dataset_corrections MODIFY status ENUM('REQUESTED','APPROVED','EXECUTING','RESEALED','PUBLISHED','REJECTED','CANCELLED') NOT NULL");
    }
};
