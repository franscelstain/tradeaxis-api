<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** D011: isolated result authority, never an EOD/publication owner. */
class AddDurableReplayResultAuthority extends Migration
{
    public function up()
    {
        Schema::create('md_replay_result_sequences', function (Blueprint $table) {
            $table->engine='InnoDB';
            $table->string('sequence_key',32)->primary();
            $table->unsignedBigInteger('next_id');
        });
        Schema::create('md_replay_result_cases', function (Blueprint $table) {
            $table->engine='InnoDB';
            $table->unsignedBigInteger('replay_id')->primary();
            $table->date('trade_date');
            $table->char('scope_hash',64);
            $table->longText('scope_json');
            $table->string('state',24);
            $table->unsignedBigInteger('fencing_token');
            $table->char('execution_id',32);
            $table->char('credential_hash',64);
            $table->dateTime('acquired_at');
            $table->dateTime('heartbeat_at');
            $table->dateTime('expires_at');
            $table->dateTime('released_at')->nullable();
            $table->char('result_hash',64)->nullable();
            $table->longText('result_json')->nullable();
            $table->char('result_json_hash',64)->nullable();
        });
        Schema::create('md_replay_result_attempts', function (Blueprint $table) {
            $table->engine='InnoDB';
            $table->char('execution_id',32)->primary();
            $table->unsignedBigInteger('replay_id');
            $table->unsignedBigInteger('fencing_token');
            $table->string('state',24);
            $table->dateTime('started_at');
            $table->dateTime('expires_at');
            $table->dateTime('finished_at')->nullable();
            $table->char('closed_by_execution_id',32)->nullable();
            $table->string('failure_class',191)->nullable();
            $table->unique(['replay_id','fencing_token'],'replay_result_attempt_token_unique');
        });
    }

    public function down()
    {
        if (DB::table('md_replay_result_cases')->exists() || DB::table('md_replay_result_attempts')->exists())
            throw new RuntimeException('REPLAY_RESULT_DOWNGRADE_REQUIRES_GOVERNED_EVIDENCE_DISPOSITION');
        Schema::dropIfExists('md_replay_result_attempts');
        Schema::dropIfExists('md_replay_result_cases');
        Schema::dropIfExists('md_replay_result_sequences');
    }
}
