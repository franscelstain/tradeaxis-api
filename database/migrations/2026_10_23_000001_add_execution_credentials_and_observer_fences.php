<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExecutionCredentialsAndObserverFences extends Migration
{
    public function up()
    {
        Schema::table('eod_run_owner_fences', function (Blueprint $table) {
            $table->string('execution_id', 32)->nullable();
            $table->string('credential_hash', 64)->nullable();
        });
        Schema::create('eod_observer_fences', function (Blueprint $table) {
            $table->string('scope_key', 64)->primary();
            $table->unsignedBigInteger('token');
            $table->string('actor_id', 32)->nullable();
            $table->string('credential_hash', 64)->nullable();
            $table->unsignedBigInteger('target_run_id')->nullable();
            $table->dateTime('acquired_at')->nullable();
            $table->dateTime('released_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('eod_observer_fences');
        Schema::table('eod_run_owner_fences', function (Blueprint $table) {
            $table->dropColumn(['execution_id', 'credential_hash']);
        });
    }
}
