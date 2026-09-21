<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * C1 contract Sec4.2: sealed V2 bound_input_* writes must be rejected through both the repository
 * path (PublicationInputBindingService, E034) and tested database protections -- application-level
 * alone is not sufficient. Scope is intentionally narrow: only the four bound_input_* columns this
 * migration's predecessor (2026_09_16_000001) added, gated on the owning publication's seal_state.
 * Every other column on md_publication_lineage_bindings (the pre-existing V1 hash columns) is
 * untouched -- that is a separate, not-yet-authorized concern, not something to fold in here.
 */
class AddPublicationLineageBindingSealImmutability extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('md_publication_lineage_bindings') || ! Schema::hasTable('eod_publications')) {
            throw new RuntimeException('INPUT_CAPTURE_BINDING_PARENT_SCHEMA_REQUIRED');
        }
        if (! Schema::hasColumn('md_publication_lineage_bindings', 'bound_input_context_hash')) {
            throw new RuntimeException('INPUT_CAPTURE_BINDING_V2_COLUMNS_REQUIRED');
        }

        if (DB::getDriverName() === 'mysql') {
            $schema = DB::connection()->getDatabaseName();
            $triggers = [
                'trg_md_lineage_bound_input_no_update_sealed' => "
                    CREATE TRIGGER trg_md_lineage_bound_input_no_update_sealed BEFORE UPDATE ON md_publication_lineage_bindings
                    FOR EACH ROW
                    BEGIN
                        IF (NOT (NEW.bound_input_schema_version <=> OLD.bound_input_schema_version)
                            OR NOT (NEW.bound_input_context_json <=> OLD.bound_input_context_json)
                            OR NOT (NEW.bound_input_context_hash <=> OLD.bound_input_context_hash)
                            OR NOT (NEW.bound_input_capture_manifest_json <=> OLD.bound_input_capture_manifest_json))
                           AND (SELECT seal_state FROM eod_publications WHERE publication_id = OLD.publication_id) = 'SEALED'
                        THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INPUT_CAPTURE_BINDING_SEALED_IMMUTABLE';
                        END IF;
                    END",
                'trg_md_lineage_no_delete_sealed_bound' => "
                    CREATE TRIGGER trg_md_lineage_no_delete_sealed_bound BEFORE DELETE ON md_publication_lineage_bindings
                    FOR EACH ROW
                    BEGIN
                        IF OLD.bound_input_context_hash IS NOT NULL
                           AND (SELECT seal_state FROM eod_publications WHERE publication_id = OLD.publication_id) = 'SEALED'
                        THEN
                            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INPUT_CAPTURE_BINDING_SEALED_IMMUTABLE';
                        END IF;
                    END",
            ];
            foreach ($triggers as $name => $sql) {
                $exists = DB::table('information_schema.TRIGGERS')->where('TRIGGER_SCHEMA', $schema)->where('TRIGGER_NAME', $name)->exists();
                if (! $exists) DB::unprepared($sql);
            }
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trg_md_lineage_bound_input_no_update_sealed
                BEFORE UPDATE ON md_publication_lineage_bindings
                WHEN (NEW.bound_input_schema_version IS NOT OLD.bound_input_schema_version
                   OR NEW.bound_input_context_json IS NOT OLD.bound_input_context_json
                   OR NEW.bound_input_context_hash IS NOT OLD.bound_input_context_hash
                   OR NEW.bound_input_capture_manifest_json IS NOT OLD.bound_input_capture_manifest_json)
                  AND (SELECT seal_state FROM eod_publications WHERE publication_id = OLD.publication_id) = 'SEALED'
                BEGIN
                    SELECT RAISE(ABORT, 'INPUT_CAPTURE_BINDING_SEALED_IMMUTABLE');
                END
            ");
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trg_md_lineage_no_delete_sealed_bound
                BEFORE DELETE ON md_publication_lineage_bindings
                WHEN OLD.bound_input_context_hash IS NOT NULL
                  AND (SELECT seal_state FROM eod_publications WHERE publication_id = OLD.publication_id) = 'SEALED'
                BEGIN
                    SELECT RAISE(ABORT, 'INPUT_CAPTURE_BINDING_SEALED_IMMUTABLE');
                END
            ");
        } else {
            throw new RuntimeException('INPUT_CAPTURE_BINDING_UNSUPPORTED_DATABASE');
        }
    }

    public function down()
    {
        if (DB::table('md_publication_lineage_bindings')->join('eod_publications', 'eod_publications.publication_id', '=', 'md_publication_lineage_bindings.publication_id')
            ->where('eod_publications.seal_state', 'SEALED')->whereNotNull('md_publication_lineage_bindings.bound_input_context_hash')->exists()) {
            throw new RuntimeException('INPUT_CAPTURE_BINDING_ROLLBACK_WOULD_REMOVE_PROOF_PROTECTION');
        }
        foreach (['trg_md_lineage_bound_input_no_update_sealed', 'trg_md_lineage_no_delete_sealed_bound'] as $trigger) {
            DB::unprepared('DROP TRIGGER IF EXISTS '.$trigger);
        }
    }
}
