<?php

class WatchlistBacktestOosSchemaContractTest extends TestCase
{
    public function test_fresh_schema_existing_closure_and_r2_migration_share_canonical_identity(): void
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
        $r2Migration = file_get_contents(base_path(
            'database/migrations/2026_06_10_000001_add_watchlist_backtest_catalog_identity_and_r2_entry_quality.php'
        ));

        foreach ([$ddl, $closure, $freshMigration, $gridMigration, $evalMigration, $oosMigration, $r2Migration] as $content) {
            $this->assertIsString($content);
            $this->assertNotSame('', trim($content));
        }

        foreach (['stop_atr_mult', 'min_rr'] as $column) {
            $this->assertStringContainsString($column, $ddl);
            $this->assertStringContainsString($column, $closure);
            $this->assertStringContainsString($column, $freshMigration);
            $this->assertStringContainsString($column, $gridMigration);
            $this->assertStringContainsString($column, $r2Migration);
        }

        foreach (['catalog_code', 'catalog_version', 'catalog_hash'] as $column) {
            $this->assertStringContainsString($column, $ddl);
            $this->assertStringContainsString($column, $r2Migration);
        }
        foreach ([
            'row_code', 'row_hash', 'rationale', 'dv20_strong_idr', 'strong_vol_ratio',
            'min_atr14_pct', 'atr_ideal_low', 'atr_ideal_high', 'roc_lo', 'roc_hi',
            'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
        ] as $column) {
            $this->assertStringContainsString($column, $ddl);
            $this->assertStringContainsString($column, $r2Migration);
        }

        foreach (['eval_model', 'paramset_hash'] as $column) {
            $this->assertStringContainsString($column, $ddl);
            $this->assertStringContainsString($column, $closure);
            $this->assertStringContainsString($column, $freshMigration);
            $this->assertStringContainsString($column, $evalMigration);
            $this->assertStringContainsString($column, $r2Migration);
        }

        $this->assertStringContainsString('LEGACY_UNVERSIONED', $closure);
        $this->assertStringContainsString('LEGACY_UNVERSIONED', $evalMigration);
        $this->assertStringContainsString(
            'policy_code, catalog_code, catalog_version, param_id,',
            $ddl
        );
        $this->assertStringContainsString(
            'policy_code, policy_version, eval_model, param_id_best_is, is_eval_id',
            $ddl
        );
        $this->assertStringContainsString('is_eval_id', $oosMigration);
        $this->assertStringContainsString('UQ_bt_grid_catalog_row', $r2Migration);
        $this->assertStringContainsString('UQ_bt_eval_catalog_param_window', $r2Migration);
    }

    public function test_evaluation_repository_uses_catalog_aware_identity_and_keeps_conflict_fail_closed(): void
    {
        $repository = file_get_contents(base_path(
            'app/Infrastructure/Persistence/Watchlist/WatchlistBacktestEvaluationRepository.php'
        ));

        $this->assertIsString($repository);
        $this->assertStringContainsString(
            "'policy_code', 'catalog_code', 'catalog_version', 'param_id'",
            $repository
        );
        $this->assertStringContainsString(
            "'eval_model', 'eval_model_hash', 'implementation_version', 'implementation_hash'",
            $repository
        );
        $this->assertStringContainsString("'paramset_hash', 'from_date', 'to_date'", $repository);
        $this->assertStringContainsString('WS_BT_EVAL_IDENTITY_CONFLICT', $repository);
        $this->assertStringNotContainsString('updateOrInsert', $repository);
    }
}
