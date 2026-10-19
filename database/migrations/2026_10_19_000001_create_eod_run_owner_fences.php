<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEodRunOwnerFences extends Migration
{
    public function up()
    {
        if (Schema::hasTable('eod_run_owner_fences')) {
            return;
        }
        Schema::create('eod_run_owner_fences', function (Blueprint $table) {
            $table->date('trade_date_requested')->primary();
            $table->unsignedBigInteger('owner_run_id')->nullable();
            $table->dateTime('updated_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('eod_run_owner_fences');
    }
}
