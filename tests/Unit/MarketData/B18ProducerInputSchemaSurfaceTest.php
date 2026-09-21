<?php

use PHPUnit\Framework\TestCase;

class B18ProducerInputSchemaSurfaceTest extends TestCase
{
    public function test_capture_schema_protections_cover_base_sql_and_every_migration_definition(): void
    {
        $root = dirname(__DIR__, 3);
        $paths = array_merge([$root.'/docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql'], glob($root.'/database/migrations/*.php'));
        $this->assertGreaterThan(73, count($paths), 'An empty or migration-only scan must fail.');
        $definitions = [];
        foreach ($paths as $path) {
            $source = file_get_contents($path);
            if (! preg_match('/(?:CREATE TABLE IF NOT EXISTS |Schema::create\(\x27)md_run_input_captures\b/', $source)) continue;
            $definitions[] = $path;
            foreach (['input_capture_id', 'run_id', 'stage_code', 'component_key', 'slot_hash', 'capture_schema_version',
                'selection_context_json', 'semantic_payload_json', 'payload_hash', 'member_count', 'empty_basis_json',
                'audit_context_json', 'captured_at', 'uq_md_run_input_slot', 'fk_md_run_input_run', 'chk_md_input_json',
                'chk_md_input_hash', 'chk_md_input_empty', 'INPUT_CAPTURE_IMMUTABLE'] as $member) {
                $this->assertStringContainsString($member, $source, $path.' lacks '.$member);
            }
            $this->assertStringContainsString('member_count > 0 OR empty_basis_json IS NOT NULL', $source, $path);
            $this->assertStringContainsString("slot_hash REGEXP BINARY '^[a-f0-9]{64}$'", $source, $path);
            $this->assertStringContainsString("payload_hash REGEXP BINARY '^[a-f0-9]{64}$'", $source, $path);
            foreach (['bound_input_schema_version', 'bound_input_context_json', 'bound_input_context_hash', 'bound_input_capture_manifest_json'] as $column) {
                $this->assertStringContainsString($column, $source, $path);
            }
        }
        $this->assertCount(2, $definitions, 'Exactly base SQL and the new forward migration declare the capture table.');
        $this->assertStringEndsWith('Database_Schema_MariaDB.sql', $definitions[0]);
    }
}
