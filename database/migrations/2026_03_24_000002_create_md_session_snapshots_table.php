<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMdSessionSnapshotsTable extends Migration
{
    public function up()
    {
        Schema::create('md_session_snapshots', function (Blueprint $table) {
            $table->bigIncrements('snapshot_id');
            $table->date('trade_date');
            $table->string('snapshot_slot', 32);
            $table->unsignedBigInteger('ticker_id');
            $table->dateTime('captured_at');
            $table->decimal('last_price', 18, 4)->nullable();
            $table->decimal('prev_close', 18, 4)->nullable();
            $table->decimal('chg_pct', 18, 10)->nullable();
            $table->unsignedBigInteger('volume')->nullable();
            $table->decimal('day_high', 18, 4)->nullable();
            $table->decimal('day_low', 18, 4)->nullable();
            $table->string('source', 32);
            $table->unsignedBigInteger('run_id')->nullable();
            $table->string('reason_code', 64)->nullable();
            $table->string('error_note', 255)->nullable();
            $table->timestamps();

            $table->index(['trade_date', 'snapshot_slot']);
            $table->index(['captured_at']);
            $table->unique(['trade_date', 'snapshot_slot', 'ticker_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('md_session_snapshots');
    }
}
