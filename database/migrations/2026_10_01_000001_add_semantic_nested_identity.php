<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive V2 nested semantic identities (D-MD-B10-A002-003).
 *
 * The V1 nested columns keep their allocation-bearing meaning for every existing consumer: replay,
 * input binding, as-known replay, evidence export. V2 identities live beside them and are written
 * only for V2-profile runs. NULL means "not a V2 publication", never "empty set".
 */
class AddSemanticNestedIdentity extends Migration
{
    private const LINEAGE_HASH_COLUMNS = [
        'semantic_observation_manifest_hash',
        'semantic_identity_revision_set_hash',
        'semantic_calendar_revision_set_hash',
        'semantic_status_revision_set_hash',
        'semantic_event_revision_set_hash',
        'semantic_source_scale_assessment_set_hash',
        'semantic_market_structure_revision_set_hash',
        'semantic_factor_decision_set_hash',
        'semantic_factor_set_hash',
    ];

    public function up()
    {
        foreach (['eod_runs', 'eod_publications'] as $tableName) {
            if (!Schema::hasTable($tableName)) continue;
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (!Schema::hasColumn($tableName, 'semantic_observation_manifest_hash')) {
                    $table->char('semantic_observation_manifest_hash', 64)->nullable();
                }
            });
        }
        if (Schema::hasTable('md_publication_lineage_bindings')) {
            Schema::table('md_publication_lineage_bindings', function (Blueprint $table): void {
                if (!Schema::hasColumn('md_publication_lineage_bindings', 'semantic_nested_identity_version')) {
                    $table->string('semantic_nested_identity_version', 64)->nullable();
                }
                foreach (self::LINEAGE_HASH_COLUMNS as $column) {
                    if (!Schema::hasColumn('md_publication_lineage_bindings', $column)) {
                        $table->char($column, 64)->nullable();
                    }
                }
            });
        }
    }

    public function down()
    {
        foreach ([
            'md_publication_lineage_bindings' => array_merge(['semantic_nested_identity_version'], self::LINEAGE_HASH_COLUMNS),
            'eod_publications' => ['semantic_observation_manifest_hash'],
            'eod_runs' => ['semantic_observation_manifest_hash'],
        ] as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) continue;
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                foreach ($columns as $column) if (Schema::hasColumn($tableName, $column)) $table->dropColumn($column);
            });
        }
    }
}
