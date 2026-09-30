<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Distinguish legacy sealed artifact hashes from the allocation-independent semantic profile.
 *
 * Existing rows remain NULL and therefore retain their historical V1 interpretation.  Application
 * run creation explicitly assigns V2 to new governed work; direct historical fixtures with NULL
 * continue to exercise the legacy verifier.
 */
class AddArtifactSemanticHashProfile extends Migration
{
    public function up()
    {
        foreach (['eod_runs', 'eod_publications'] as $tableName) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'artifact_hash_profile')) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('artifact_hash_profile', 64)->nullable();
            });
        }
    }

    public function down()
    {
        foreach (['eod_publications', 'eod_runs'] as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'artifact_hash_profile')) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('artifact_hash_profile');
            });
        }
    }
}
