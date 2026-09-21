<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddProducerBoundInputCaptures extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('eod_runs') || ! Schema::hasTable('md_publication_lineage_bindings')) {
            throw new RuntimeException('INPUT_CAPTURE_PARENT_SCHEMA_REQUIRED');
        }
        if (! Schema::hasTable('md_run_input_captures')) {
            Schema::create('md_run_input_captures', function (Blueprint $table) {
                $table->bigIncrements('input_capture_id');
                $table->unsignedBigInteger('run_id');
                $table->string('stage_code', 32);
                $table->string('component_key', 64);
                $table->char('slot_hash', 64);
                $table->string('capture_schema_version', 64);
                $table->longText('selection_context_json');
                $table->longText('semantic_payload_json');
                $table->char('payload_hash', 64);
                $table->unsignedBigInteger('member_count');
                $table->longText('empty_basis_json')->nullable();
                $table->longText('audit_context_json');
                $table->dateTime('captured_at', 6);
                $table->unique(['run_id', 'stage_code', 'component_key', 'slot_hash'], 'uq_md_run_input_slot');
                $table->index(['run_id', 'stage_code'], 'idx_md_run_input_stage');
                $table->foreign('run_id', 'fk_md_run_input_run')->references('run_id')->on('eod_runs')->onDelete('restrict')->onUpdate('restrict');
            });
        }
        Schema::table('md_publication_lineage_bindings', function (Blueprint $table) {
            if (! Schema::hasColumn('md_publication_lineage_bindings', 'bound_input_schema_version')) $table->string('bound_input_schema_version', 64)->nullable();
            if (! Schema::hasColumn('md_publication_lineage_bindings', 'bound_input_context_json')) $table->longText('bound_input_context_json')->nullable();
            if (! Schema::hasColumn('md_publication_lineage_bindings', 'bound_input_context_hash')) $table->char('bound_input_context_hash', 64)->nullable();
            if (! Schema::hasColumn('md_publication_lineage_bindings', 'bound_input_capture_manifest_json')) $table->longText('bound_input_capture_manifest_json')->nullable();
        });
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE md_run_input_captures MODIFY slot_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL, MODIFY payload_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL");
            // Fresh installs already receive these from core SQL; upgrades receive them here.
            // Query the actual schema rather than treating a migration ledger row as DDL proof.
            foreach ([
                ['md_run_input_captures', 'chk_md_input_json', 'JSON_VALID(selection_context_json) AND JSON_VALID(semantic_payload_json) AND JSON_VALID(audit_context_json) AND (empty_basis_json IS NULL OR JSON_VALID(empty_basis_json))'],
                ['md_run_input_captures', 'chk_md_input_hash', "slot_hash REGEXP BINARY '^[a-f0-9]{64}$' AND payload_hash REGEXP BINARY '^[a-f0-9]{64}$'"],
                ['md_run_input_captures', 'chk_md_input_empty', 'member_count > 0 OR empty_basis_json IS NOT NULL'],
                ['md_publication_lineage_bindings', 'chk_md_bound_input_json', '(bound_input_context_json IS NULL OR JSON_VALID(bound_input_context_json)) AND (bound_input_capture_manifest_json IS NULL OR JSON_VALID(bound_input_capture_manifest_json))'],
            ] as [$table, $name, $condition]) {
                $exists = DB::table('information_schema.TABLE_CONSTRAINTS')->where('CONSTRAINT_SCHEMA', DB::connection()->getDatabaseName())
                    ->where('TABLE_NAME', $table)->where('CONSTRAINT_NAME', $name)->exists();
                if (! $exists) DB::statement("ALTER TABLE $table ADD CONSTRAINT $name CHECK ($condition)");
            }
            foreach (['UPDATE', 'DELETE'] as $action) {
                $name = 'trg_md_input_no_'.strtolower($action);
                if (! DB::table('information_schema.TRIGGERS')->where('TRIGGER_SCHEMA', DB::connection()->getDatabaseName())->where('TRIGGER_NAME', $name)->exists()) {
                    DB::unprepared("CREATE TRIGGER $name BEFORE $action ON md_run_input_captures FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'INPUT_CAPTURE_IMMUTABLE'");
                }
            }
        } elseif (DB::getDriverName() === 'sqlite') {
            foreach (['UPDATE', 'DELETE'] as $action) {
                DB::unprepared("CREATE TRIGGER IF NOT EXISTS trg_md_input_no_".strtolower($action)." BEFORE $action ON md_run_input_captures BEGIN SELECT RAISE(ABORT, 'INPUT_CAPTURE_IMMUTABLE'); END");
            }
        } else {
            throw new RuntimeException('INPUT_CAPTURE_UNSUPPORTED_DATABASE');
        }
    }

    public function down()
    {
        if (DB::table('md_run_input_captures')->exists()
            || DB::table('md_publication_lineage_bindings')->whereNotNull('bound_input_schema_version')->exists()) {
            throw new RuntimeException('INPUT_CAPTURE_ROLLBACK_WOULD_ERASE_PROOF');
        }
        Schema::drop('md_run_input_captures');
        if (DB::getDriverName() === 'mysql') DB::statement('ALTER TABLE md_publication_lineage_bindings DROP CONSTRAINT chk_md_bound_input_json');
        Schema::table('md_publication_lineage_bindings', function (Blueprint $table) {
            $table->dropColumn(['bound_input_schema_version', 'bound_input_context_json', 'bound_input_context_hash', 'bound_input_capture_manifest_json']);
        });
    }
}
