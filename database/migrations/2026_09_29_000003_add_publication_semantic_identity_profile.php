<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive V2 publication/correction/seal identity. NULL preserves historical V1 interpretation.
 */
class AddPublicationSemanticIdentityProfile extends Migration
{
    public function up()
    {
        if (Schema::hasTable('eod_runs')) {
            Schema::table('eod_runs', function (Blueprint $table): void {
                if (!Schema::hasColumn('eod_runs', 'publication_semantic_profile')) $table->string('publication_semantic_profile', 64)->nullable();
                if (!Schema::hasColumn('eod_runs', 'seal_fingerprint')) $table->char('seal_fingerprint', 64)->nullable();
            });
        }
        if (Schema::hasTable('eod_publications')) {
            Schema::table('eod_publications', function (Blueprint $table): void {
                if (!Schema::hasColumn('eod_publications', 'publication_semantic_profile')) $table->string('publication_semantic_profile', 64)->nullable();
                if (!Schema::hasColumn('eod_publications', 'correction_semantic_hash')) $table->char('correction_semantic_hash', 64)->nullable();
                if (!Schema::hasColumn('eod_publications', 'seal_fingerprint')) $table->char('seal_fingerprint', 64)->nullable();
            });
        }
        if (Schema::hasTable('eod_dataset_corrections')) {
            Schema::table('eod_dataset_corrections', function (Blueprint $table): void {
                if (!Schema::hasColumn('eod_dataset_corrections', 'semantic_identity_profile')) $table->string('semantic_identity_profile', 64)->nullable();
                if (!Schema::hasColumn('eod_dataset_corrections', 'semantic_identity_hash')) $table->char('semantic_identity_hash', 64)->nullable();
            });
        }
    }

    public function down()
    {
        foreach ([
            'eod_dataset_corrections' => ['semantic_identity_hash', 'semantic_identity_profile'],
            'eod_publications' => ['seal_fingerprint', 'correction_semantic_hash', 'publication_semantic_profile'],
            'eod_runs' => ['seal_fingerprint', 'publication_semantic_profile'],
        ] as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) continue;
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                foreach ($columns as $column) if (Schema::hasColumn($tableName, $column)) $table->dropColumn($column);
            });
        }
    }
}
