<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class AddProjectionObserverIdentity extends Migration
{
    public function up()
    {
        Schema::table('md_publication_projection_reconciliations', function (Blueprint $table) {
            // Historical evidence remains unattributed, never assigned invented writer facts.
            $table->string('writer_kind', 32)->nullable();
            $table->string('writer_actor_id', 32)->nullable();
            $table->string('writer_scope_key', 64)->nullable();
            $table->unsignedBigInteger('writer_fencing_token')->nullable();
        });
    }
    public function down()
    {
        throw new \RuntimeException('PROJECTION_OBSERVER_DOWNGRADE_REQUIRES_GOVERNED_EVIDENCE_DISPOSITION');
    }
}
