<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventActingWriterIdentity extends Migration
{
    public function up()
    {
        Schema::table('eod_run_events', function (Blueprint $table) {
            $table->string('writer_kind', 24)->nullable();
            $table->string('writer_actor_id', 32)->nullable();
            $table->string('writer_scope_key', 64)->nullable();
            $table->unsignedBigInteger('writer_fencing_token')->nullable();
        });
    }

    public function down()
    {
        Schema::table('eod_run_events', function (Blueprint $table) {
            $table->dropColumn(['writer_kind', 'writer_actor_id', 'writer_scope_key', 'writer_fencing_token']);
        });
    }
}
