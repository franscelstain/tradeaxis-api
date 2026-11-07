<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionObservationWriterIdentity extends Migration
{
    public function up()
    {
        Schema::table('md_freshness_measurements', function (Blueprint $table) {
            $table->string('writer_actor_id', 32)->nullable();
            $table->string('writer_scope_key', 64)->nullable();
            $table->unsignedBigInteger('writer_fencing_token')->nullable();
        });
    }

    public function down()
    {
        Schema::table('md_freshness_measurements', function (Blueprint $table) {
            $table->dropColumn(['writer_actor_id', 'writer_scope_key', 'writer_fencing_token']);
        });
    }
}
