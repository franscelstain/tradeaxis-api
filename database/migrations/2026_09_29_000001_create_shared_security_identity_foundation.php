<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSharedSecurityIdentityFoundation extends Migration
{
    public function up(): void
    {
        Schema::create('si_packages', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->char('package_hash', 64)->primary();
            $t->string('package_version', 120);
            $t->dateTime('recorded_at');
            $t->longText('document_json');
        });
        Schema::create('si_entities', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->bigIncrements('row_id'); // Navigation only; never exported as identity.
            $t->char('identity_id', 36)->unique();
            $t->enum('entity_type', ['ISSUER', 'INSTRUMENT', 'LISTING']);
            $t->char('parent_identity_id', 36)->nullable();
            $t->char('package_hash', 64);
            $t->longText('document_json');
        });
        Schema::create('si_revisions', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->bigIncrements('row_id');
            $t->char('revision_id', 36)->unique();
            $t->char('identity_id', 36);
            $t->enum('revision_type', ['PROFILE', 'LISTING', 'SYMBOL', 'BOARD', 'PROVIDER_MAPPING', 'CONTINUITY']);
            $t->enum('state', ['ADMITTED', 'HELD', 'AMBIGUOUS', 'RETRACTED']);
            $t->dateTime('valid_from')->nullable();
            $t->dateTime('valid_to')->nullable(); // Half-open [from,to).
            $t->dateTime('known_at')->nullable();
            $t->dateTime('recorded_at');
            $t->char('package_hash', 64);
            $t->char('supersedes_revision_id', 36)->nullable();
            $t->longText('document_json');
            $t->index(['identity_id', 'revision_type', 'known_at'], 'si_revision_lookup');
        });
        Schema::create('si_holds', function (Blueprint $t) {
            $t->engine = 'InnoDB';
            $t->char('hold_hash', 64)->primary();
            $t->char('package_hash', 64);
            $t->string('scope', 200);
            $t->longText('document_json');
        });
        // Referenced unique indexes must exist before MariaDB adds self-referencing FKs.
        Schema::table('si_entities', function (Blueprint $t) {
            $t->foreign('parent_identity_id')->references('identity_id')->on('si_entities');
            $t->foreign('package_hash')->references('package_hash')->on('si_packages');
        });
        Schema::table('si_revisions', function (Blueprint $t) {
            $t->foreign('identity_id')->references('identity_id')->on('si_entities');
            $t->foreign('package_hash')->references('package_hash')->on('si_packages');
            $t->foreign('supersedes_revision_id')->references('revision_id')->on('si_revisions');
        });
        Schema::table('si_holds', function (Blueprint $t) {
            $t->foreign('package_hash')->references('package_hash')->on('si_packages');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('si_packages') && DB::table('si_packages')->exists()) {
            throw new \RuntimeException('FOUNDATION_HISTORY_PRESENT: use a governed retained-data migration, not destructive rollback');
        }
        Schema::dropIfExists('si_holds');
        Schema::dropIfExists('si_revisions');
        Schema::dropIfExists('si_entities');
        Schema::dropIfExists('si_packages');
    }
}
