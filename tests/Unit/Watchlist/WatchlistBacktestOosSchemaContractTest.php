<?php

class WatchlistBacktestOosSchemaContractTest extends TestCase
{
    public function test_fresh_schema_and_existing_database_closure_share_canonical_grid_and_eval_identity(): void
    {
        $ddl = file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/db/BACKTEST_SCHEMA_DDL.sql'
        ));
        $closure = file_get_contents(base_path(
            'docs/watchlist/system/policies/weekly_swing/db/BACKTEST_OOS_RUNTIME_GAP_CLOSURE.sql'
        ));
        $freshMigration = file_get_contents(base_path(
            'database/migrations/2026_06_09_000001_create_watchlist_backtest_oos_schema.php'
        ));
        $gridMigration = file_get_contents(base_path(
            'database/migrations/2026_06_09_000002_add_stop_rr_to_watchlist_bt_param_grid.php'
        ));
        $evalMigration = file_get_contents(base_path(
            'database/migrations/2026_06_09_000003_version_watchlist_bt_eval_identity.php'
        ));
        $oosMigration = file_get_contents(base_path(
            'database/migrations/2026_06_09_000004_version_watchlist_bt_oos_identity.php'
        ));

        foreach ([$ddl, $closure, $freshMigration, $gridMigration, $evalMigration, $oosMigration] as $content) {
            $this->assertIsString($content);
            $this->assertNotSame('', trim($content));
        }

        foreach (['stop_atr_mult', 'min_rr'] as $column) {
            $this->assertStringContainsString($column, $ddl);
            $this->assertStringContainsString($column, $closure);
            $this->assertStringContainsString($column, $freshMigration);
            $this->assertStringContainsString($column, $gridMigration);
        }

        foreach (['eval_model', 'paramset_hash'] as $column) {
            $this->assertStringContainsString($column, $ddl);
            $this->assertStringContainsString($column, $closure);
            $this->assertStringContainsString($column, $freshMigration);
            $this->assertStringContainsString($column, $evalMigration);
        }

        $this->assertStringContainsString('LEGACY_UNVERSIONED', $closure);
        $this->assertStringContainsString('LEGACY_UNVERSIONED', $evalMigration);
        $this->assertStringContainsString(
            'policy_code, param_id, eval_model, paramset_hash, from_date, to_date',
            $ddl
        );
        $this->assertStringContainsString(
            'policy_code, policy_version, eval_model, param_id_best_is, is_eval_id',
            $ddl
        );
        $this->assertStringContainsString('is_eval_id', $oosMigration);
    }

    public function test_evaluation_repository_uses_versioned_identity_and_keeps_conflict_fail_closed(): void
    {
        $repository = file_get_contents(base_path(
            'app/Infrastructure/Persistence/Watchlist/WatchlistBacktestEvaluationRepository.php'
        ));

        $this->assertIsString($repository);
        $this->assertStringContainsString(
            "['policy_code', 'param_id', 'eval_model', 'paramset_hash', 'from_date', 'to_date']",
            $repository
        );
        $this->assertStringContainsString('Duplicate persistence conflict', $repository);
        $this->assertStringNotContainsString('updateOrInsert', $repository);
    }
}
