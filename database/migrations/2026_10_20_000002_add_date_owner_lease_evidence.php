<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateOwnerLeaseEvidence extends Migration
{
    public function up()
    {
        Schema::table('eod_run_owner_fences', function (Blueprint $table) {
            $table->dateTime('acquired_at')->nullable();
            $table->dateTime('heartbeat_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('released_at')->nullable();
        });
    }
    public function down()
    {
        Schema::table('eod_run_owner_fences', function (Blueprint $table) {
            $table->dropColumn(['acquired_at','heartbeat_at','expires_at','released_at']);
        });
    }
}
