<?php

class PublicationHistoryDatabaseImmutabilityStaticGuardTest extends TestCase
{
    private function migration(): string
    {
        return file_get_contents(dirname(__DIR__, 3).'/database/migrations/2026_08_24_000001_enforce_sealed_history_and_projection_reconciliation.php');
    }

    public function test_forward_migration_deploys_insert_update_delete_guards_for_all_three_history_tables(): void
    {
        $source = $this->migration();

        foreach (['eod_bars_history', 'eod_indicators_history', 'eod_eligibility_history'] as $table) {
            foreach (['bi', 'bu', 'bd'] as $event) {
                $this->assertStringContainsString('trg_'.$table.'_'.$event.'_sealed_immutable', $source);
            }
        }

        foreach ([
            "['eod_bars_history', 'INSERT']",
            "['eod_bars_history', 'UPDATE']",
            "['eod_bars_history', 'DELETE']",
            "['eod_indicators_history', 'INSERT']",
            "['eod_indicators_history', 'UPDATE']",
            "['eod_indicators_history', 'DELETE']",
            "['eod_eligibility_history', 'INSERT']",
            "['eod_eligibility_history', 'UPDATE']",
            "['eod_eligibility_history', 'DELETE']",
        ] as $triggerMapping) {
            $this->assertStringContainsString($triggerMapping, $source);
        }
        $this->assertStringContainsString('DB::unprepared($this->createTriggerSql($name, $table, $event))', $source);
        $this->assertStringContainsString('CREATE TRIGGER `{$name}` BEFORE {$event} ON `{$table}` FOR EACH ROW', $source);
        $this->assertStringContainsString("MESSAGE_TEXT = 'SEALED_PUBLICATION_IMMUTABLE'", $source);
    }

    public function test_guard_is_bound_to_sealed_publication_identity_and_update_checks_old_and_new_binding(): void
    {
        $source = $this->migration();

        $this->assertStringContainsString("publication_id = NEW.publication_id AND seal_state = 'SEALED'", $source);
        $this->assertStringContainsString("publication_id = OLD.publication_id AND seal_state = 'SEALED'", $source);
        $this->assertStringContainsString("OR EXISTS", $source);
    }

    public function test_down_path_removes_all_guards_and_reconciliation_table(): void
    {
        $source = $this->migration();

        $this->assertStringContainsString('foreach (array_keys(self::TRIGGERS) as $name)', $source);
        $this->assertStringContainsString('DROP TRIGGER IF EXISTS', $source);
        $this->assertStringContainsString('Schema::dropIfExists(self::RECON_TABLE)', $source);
    }

    public function test_reconciliation_persistence_is_created_by_the_same_forward_migration(): void
    {
        $source = $this->migration();

        foreach ([
            'reconciliation_uid', 'pointer_state', 'reconciliation_state',
            'bars_missing_history_count', 'bars_missing_projection_count', 'bars_value_mismatch_count',
            'indicators_missing_history_count', 'indicators_missing_projection_count', 'indicators_value_mismatch_count',
            'eligibility_missing_history_count', 'eligibility_missing_projection_count', 'eligibility_value_mismatch_count',
            'orphan_projection_row_count', 'mismatch_count', 'mismatch_sample_json', 'reconciliation_hash', 'checked_at',
        ] as $field) {
            $this->assertStringContainsString("'{$field}'", $source);
        }
    }
}
