<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MD-B07-A001 — retain row-level rejection evidence when one provider response is partially valid.
 */
class AddSourceObservationRejectedRows extends Migration
{
    public function up()
    {
        if (Schema::hasTable('md_source_observation_rejected_rows')) {
            return;
        }

        Schema::create('md_source_observation_rejected_rows', function (Blueprint $table) {
            $table->bigIncrements('source_observation_rejected_row_id');
            $table->unsignedBigInteger('source_observation_id');
            $table->unsignedBigInteger('capture_observation_id');
            $table->string('source_row_ref', 255);
            $table->string('instrument_code', 32);
            $table->string('provider_symbol', 128)->nullable();
            $table->date('trade_date');
            $table->string('open_value', 64)->nullable();
            $table->string('high_value', 64)->nullable();
            $table->string('low_value', 64)->nullable();
            $table->string('close_value', 64)->nullable();
            $table->string('volume_value', 64)->nullable();
            $table->string('adj_close_value', 64)->nullable();
            $table->string('reason_code', 64);
            $table->string('reason_note', 255);
            $table->dateTime('created_at');
            $table->unique(['source_observation_id', 'source_row_ref'], 'uq_md_obs_rejected_row_ref');
            $table->index(['instrument_code', 'trade_date', 'reason_code'], 'idx_md_obs_rejected_identity');
        });
    }

    public function down()
    {
        Schema::dropIfExists('md_source_observation_rejected_rows');
    }
}
