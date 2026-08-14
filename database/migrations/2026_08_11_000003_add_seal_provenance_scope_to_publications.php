<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F-033: let a seal say what it actually covers.
 *
 * Binding config identity woke an integrity gate that had never run — it was wrapped in
 * `if (! empty($run->config_snapshot_id))`, and every run carried NULL, so 64,939 publications
 * sealed without it. Awake, the gate immediately refused every new promote run, because it demands
 * an observation manifest and none has ever existed.
 *
 * The demand was misdirected rather than unmet. `observation_manifest_hash` is produced by the
 * ingest path, which records which acquired observations produced a candidate. A recompute run
 * acquires nothing: it recomputes analytics over bars that already exist. Requiring it to present
 * an acquisition manifest asks it to attest to work it did not do.
 *
 * So the seal records its own scope. `FULL` means config identity and acquisition provenance are
 * both covered. `ANALYTICAL_ONLY` means the run recomputed analytics over existing bars and no
 * acquisition provenance was available to carry forward — the seal is real but narrower, and says
 * so on the publication rather than in a check that was quietly skipped.
 *
 * This is deliberately not a re-gating into dormancy: a run that did acquire and cannot produce a
 * manifest still fails, exactly as it does today.
 */
class AddSealProvenanceScopeToPublications extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('eod_publications')) {
            return;
        }

        if (! Schema::hasColumn('eod_publications', 'seal_provenance_scope')) {
            Schema::table('eod_publications', function (Blueprint $table) {
                $table->string('seal_provenance_scope', 32)->nullable()->after('observation_manifest_hash');
            });
        }
    }

    public function down()
    {
        if (! Schema::hasTable('eod_publications')) {
            return;
        }

        if (Schema::hasColumn('eod_publications', 'seal_provenance_scope')) {
            Schema::table('eod_publications', function (Blueprint $table) {
                $table->dropColumn('seal_provenance_scope');
            });
        }
    }
}
