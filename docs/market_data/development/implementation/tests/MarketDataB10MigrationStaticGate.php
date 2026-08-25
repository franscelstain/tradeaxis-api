<?php

/**
 * MD-B10 static migration/ops consistency gate.
 *
 * This gate is intentionally stronger than trigger-count scanning: the first local deployment
 * exposed MariaDB error 1059 while the previous static gate still passed. The gate therefore
 * requires short explicit reconciliation index names and a repair-safe path for a table left
 * behind by a failed non-transactional DDL attempt.
 */
final class MarketDataB10MigrationStaticGate
{
    private const TRIGGERS = [
        'trg_eod_bars_history_bi_sealed_immutable' => ['eod_bars_history', 'INSERT'],
        'trg_eod_bars_history_bu_sealed_immutable' => ['eod_bars_history', 'UPDATE'],
        'trg_eod_bars_history_bd_sealed_immutable' => ['eod_bars_history', 'DELETE'],
        'trg_eod_indicators_history_bi_sealed_immutable' => ['eod_indicators_history', 'INSERT'],
        'trg_eod_indicators_history_bu_sealed_immutable' => ['eod_indicators_history', 'UPDATE'],
        'trg_eod_indicators_history_bd_sealed_immutable' => ['eod_indicators_history', 'DELETE'],
        'trg_eod_eligibility_history_bi_sealed_immutable' => ['eod_eligibility_history', 'INSERT'],
        'trg_eod_eligibility_history_bu_sealed_immutable' => ['eod_eligibility_history', 'UPDATE'],
        'trg_eod_eligibility_history_bd_sealed_immutable' => ['eod_eligibility_history', 'DELETE'],
    ];

    public static function run($root = null)
    {
        $root = $root ?: dirname(__DIR__, 5);
        $migrationPath = $root.'/database/migrations/2026_08_24_000001_enforce_sealed_history_and_projection_reconciliation.php';
        $opsPath = $root.'/docs/market_data/development/implementation/ops/History_Table_Immutability_Guards_LOCKED.sql';
        $schemaPath = $root.'/docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql';

        $errors = [];
        foreach ([$migrationPath, $opsPath, $schemaPath] as $path) {
            if (! is_file($path)) {
                $errors[] = 'MISSING_FILE: '.str_replace($root.'/', '', $path);
            }
        }
        if ($errors !== []) {
            return self::result($errors, 0, false);
        }

        $migration = file_get_contents($migrationPath);
        $ops = file_get_contents($opsPath);
        $schema = file_get_contents($schemaPath);

        foreach (self::TRIGGERS as $trigger => $definition) {
            [$table, $event] = $definition;
            $migrationPattern = "/'".preg_quote($trigger, '/')."'\s*=>\s*\[\s*'"
                .preg_quote($table, '/')."'\s*,\s*'".preg_quote($event, '/')."'\s*\]/";
            if (! preg_match($migrationPattern, $migration)) {
                $errors[] = 'MIGRATION_TRIGGER_DEFINITION_MISSING_OR_DRIFTED: '.$trigger.'='.$table.':'.$event;
            }

            $opsPattern = '/CREATE\s+TRIGGER\s+`?'.preg_quote($trigger, '/').'`?\s+BEFORE\s+'
                .preg_quote($event, '/').'\s+ON\s+`?'.preg_quote($table, '/').'`?/i';
            if (! preg_match($opsPattern, $ops)) {
                $errors[] = 'OPS_TRIGGER_DEFINITION_MISSING_OR_DRIFTED: '.$trigger.'='.$table.':'.$event;
            }
        }

        if (substr_count($ops, 'CREATE TRIGGER ') !== 9) {
            $errors[] = 'OPS_TRIGGER_COUNT_MISMATCH: '.substr_count($ops, 'CREATE TRIGGER ');
        }
        if (strpos($migration, 'SEALED_PUBLICATION_IMMUTABLE') === false || strpos($ops, 'SEALED_PUBLICATION_IMMUTABLE') === false) {
            $errors[] = 'CANONICAL_IMMUTABILITY_REASON_MISSING';
        }

        $requiredIndexes = [
            'uq_md_pub_proj_recon_uid',
            'idx_md_pub_proj_recon_date_state',
            'idx_md_pub_proj_recon_pub_checked',
            'idx_md_pub_proj_recon_checked',
        ];
        foreach ($requiredIndexes as $index) {
            if (strpos($migration, $index) === false || strpos($schema, $index) === false) {
                $errors[] = 'SHORT_INDEX_NAME_MISSING: '.$index;
            }
        }
        if (strpos($migration, 'md_publication_projection_reconciliations_reconciliation_uid_unique') !== false) {
            $errors[] = 'MARIADB_IDENTIFIER_OVERFLOW_RISK_REINTRODUCED';
        }

        foreach ([
            'Schema::hasTable(self::RECON_TABLE)',
            'ensureReconciliationIndexes',
            'foreach (self::TRIGGERS as $name => $definition)',
            'createTriggerSql($name, $table, $event)',
            'DROP TRIGGER IF EXISTS',
        ] as $needle) {
            if (strpos($migration, $needle) === false) {
                $errors[] = 'PARTIAL_DDL_RECOVERY_GUARD_MISSING: '.$needle;
            }
        }

        $reconciliationTable = strpos($migration, 'md_publication_projection_reconciliations') !== false
            && strpos($schema, 'CREATE TABLE IF NOT EXISTS md_publication_projection_reconciliations') !== false;
        if (! $reconciliationTable) {
            $errors[] = 'RECONCILIATION_TABLE_CONTRACT_MISSING';
        }

        return self::result($errors, substr_count($ops, 'CREATE TRIGGER '), $reconciliationTable);
    }

    private static function result(array $errors, $opsTriggerCount, $reconciliationTable)
    {
        return [
            'status' => $errors === [] ? 'PASS' : 'FAIL',
            'history_tables' => 3,
            'expected_triggers' => 9,
            'ops_trigger_count' => $opsTriggerCount,
            'reconciliation_table' => (bool) $reconciliationTable,
            'short_explicit_indexes' => 4,
            'partial_ddl_recovery_guard' => $errors === [] || ! array_filter($errors, function ($error) {
                return strpos($error, 'PARTIAL_DDL_RECOVERY_GUARD_MISSING') === 0;
            }),
            'errors' => array_values($errors),
        ];
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $result = MarketDataB10MigrationStaticGate::run();
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
    exit($result['status'] === 'PASS' ? 0 : 1);
}
