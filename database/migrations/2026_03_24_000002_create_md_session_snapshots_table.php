<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMdSessionSnapshotsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('md_session_snapshots')) {
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

            return;
        }

        Schema::table('md_session_snapshots', function (Blueprint $table) {
            if (! Schema::hasColumn('md_session_snapshots', 'snapshot_id')) {
                $table->bigIncrements('snapshot_id');
            }
            if (! Schema::hasColumn('md_session_snapshots', 'trade_date')) {
                $table->date('trade_date');
            }
            if (! Schema::hasColumn('md_session_snapshots', 'snapshot_slot')) {
                $table->string('snapshot_slot', 32);
            }
            if (! Schema::hasColumn('md_session_snapshots', 'ticker_id')) {
                $table->unsignedBigInteger('ticker_id');
            }
            if (! Schema::hasColumn('md_session_snapshots', 'captured_at')) {
                $table->dateTime('captured_at');
            }
            if (! Schema::hasColumn('md_session_snapshots', 'last_price')) {
                $table->decimal('last_price', 18, 4)->nullable();
            }
            if (! Schema::hasColumn('md_session_snapshots', 'prev_close')) {
                $table->decimal('prev_close', 18, 4)->nullable();
            }
            if (! Schema::hasColumn('md_session_snapshots', 'chg_pct')) {
                $table->decimal('chg_pct', 18, 10)->nullable();
            }
            if (! Schema::hasColumn('md_session_snapshots', 'volume')) {
                $table->unsignedBigInteger('volume')->nullable();
            }
            if (! Schema::hasColumn('md_session_snapshots', 'day_high')) {
                $table->decimal('day_high', 18, 4)->nullable();
            }
            if (! Schema::hasColumn('md_session_snapshots', 'day_low')) {
                $table->decimal('day_low', 18, 4)->nullable();
            }
            if (! Schema::hasColumn('md_session_snapshots', 'source')) {
                $table->string('source', 32);
            }
            if (! Schema::hasColumn('md_session_snapshots', 'run_id')) {
                $table->unsignedBigInteger('run_id')->nullable();
            }
            if (! Schema::hasColumn('md_session_snapshots', 'reason_code')) {
                $table->string('reason_code', 64)->nullable();
            }
            if (! Schema::hasColumn('md_session_snapshots', 'error_note')) {
                $table->string('error_note', 255)->nullable();
            }
            if (! Schema::hasColumn('md_session_snapshots', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('md_session_snapshots', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('md_session_snapshots');
    }
}
