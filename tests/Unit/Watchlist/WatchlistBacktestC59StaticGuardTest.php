<?php

use App\Application\Watchlist\Services\WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService;

class WatchlistBacktestC59StaticGuardTest extends TestCase
{
    public function test_C59_command_is_registered_and_declares_C58_locks(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only', $command);
        $this->assertStringContainsString('RunBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyCommand::class', $kernel);
        $this->assertStringContainsString(WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_HASH, $command);
        $this->assertStringContainsString(WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C58_FILE_SHA1, $command);
    }

    public function test_C59_declares_dictionary_IS_only_and_no_OOS_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService.php'));
        foreach ([
            'C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY',
            'C58_ARTIFACT_HASH_LOCK', 'C58_FILE_SHA1_LOCK', 'C58_LOCKED_LINEAGE', 'C57_REGIME_RECONSTRUCTION_RETAINED_THROUGH_C58_LOCK',
            'DATABASE_DICTIONARY_READ_RULE_ENFORCED', 'MARKET_DATA_DICTIONARY_REQUIRED', 'WATCHLIST_DB_DICTIONARY_REQUIRED',
            'MARKET_INDEX_MAPPING_DICTIONARY_LOCKED', 'MARKET_INDEX_ROC20_SOURCE_MARKET_BENCHMARK_INDICATORS_ROC_20', 'MARKET_INDEX_MA20_SLOPE_SOURCE_MARKET_BENCHMARK_INDICATORS_MA20_SLOPE_PCT',
            'MARKET_INDEX_IDENTIFIER_IHSG', 'MARKET_CALENDAR_DATE_KEY_CAL_DATE', 'ASOF_SAFE_LOOKUP_REQUIRED',
            'NO_LATEST_DATE_SHORTCUT', 'NO_MAX_TRADE_DATE_SHORTCUT', 'NO_OOS_ROWS', 'NO_FUTURE_LOOKUP',
            'PREDECLARED_SAFE_PRE_TRADE_SELECTION_ONLY', 'RETURN_USED_FOR_SELECTION_FALSE', 'FUTURE_PATH_USED_FOR_SELECTION_FALSE', 'NO_GATE_RELAXATION', 'STRICT_GATE_RETENTION_REQUIRED',
            'NO_OOS_TUNING', 'NO_OOS_PROOF', 'NO_OOS_PROOF_RERUN', 'NO_BEST_OF_OOS', 'NO_OOS_WINNER', 'NO_OOS_RETURN_SELECTION',
            'NO_CANDIDATE_RESELECTION_FROM_OOS', 'NO_PROFILE_RESELECTION_FROM_OOS', 'NO_PRODUCTION_CATALOG', 'NO_PROMOTION', 'NO_PLAN_CONFIRM_MUTATION',
            'NO_C01_TO_C58_ARTIFACT_MUTATION', 'NO_ADVERSE_MONTH_EXCLUSION_RULE', 'NO_FAILED_WINDOW_EXCLUSION_RULE', 'NO_TICKER_EXCLUSION_RULE', 'NO_SECTOR_EXCLUSION_RULE',
            'NO_BAD_MONTH_REMOVAL', 'NO_REPLAY_COMPARATOR_PROMOTION', 'LOSS_CLUSTER_PRE_TRADE_PROXY_ONLY', 'BRANCH_BUCKET_CONCENTRATION_REDESIGN_ONLY',
            'MARKET_DOWN_OR_SIDEWAYS_HIGH_VOL_REGIME_REQUIRED', 'CANDIDATE_IS_NOT_PRODUCTION', 'C59_MUST_NOT_RECOMMEND_DIRECT_OOS_PROOF',
        ] as $marker) { $this->assertStringContainsString($marker, $source, $marker); }
    }

    public function test_C59_market_data_dictionary_paths_exist(): void
    {
        foreach ([
            'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
            'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
            'docs/market_data/db/Database_Schema_MariaDB.sql',
            'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
            'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
            'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
        ] as $path) {
            $this->assertFileExists(base_path($path), $path);
        }
    }

    public function test_C59_runtime_path_has_no_catalog_mutation_OOS_call_or_date_shortcut(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService.php'));
        foreach (["DB::table('watchlist_bt_oos_eval_ws')", 'updateOrInsert(', '->insert(', '->update(', 'watchlist:backtest-c47-oos-proof', 'watchlist:backtest-c29-oos-proof', 'watchlist:backtest-c59-oos-proof', 'MAX(trade_date)', 'latest(\'trade_date\')', 'latest("trade_date")', 'orderByDesc(\'trade_date\')', 'orderByDesc("trade_date")'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source, $forbidden);
        }
    }

    public function test_C59_safety_boundaries_have_no_production_or_OOS_unlock_flags(): void
    {
        $out = storage_path('framework/testing/c59-static.json');
        (new WatchlistBacktestC59LossClusterOrBranchBucketRedesignContinuationIsOnlyService())->execute(storage_path('framework/testing/c59-missing.json'), 'h', 's', '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $artifact = json_decode((string) file_get_contents($out), true);
        foreach (['best_of_oos', 'oos_winner', 'production_candidate', 'production_catalog', 'candidate_promoted', 'profile_reselection_from_oos', 'candidate_reselection_from_oos'] as $key) {
            $this->assertArrayNotHasKey($key, $artifact);
        }
        $this->assertFalse($artifact['production_ready']);
        $safety = $artifact['safety_boundaries'];
        foreach (['c59_loss_cluster_or_branch_bucket_redesign_continuation_is_only', 'c58_artifact_hash_lock', 'c58_file_sha1_lock', 'c58_locked_lineage', 'c57_regime_reconstruction_retained_through_c58_lock', 'database_dictionary_read_rule_enforced', 'market_data_dictionary_required', 'watchlist_db_dictionary_required', 'market_index_mapping_dictionary_locked', 'is_only_validation', 'asof_safe_lookup_required', 'no_latest_date_shortcut', 'no_max_trade_date_shortcut', 'no_oos_rows', 'no_future_lookup', 'no_gate_relaxation', 'strict_gate_retention_required', 'no_oos_tuning', 'no_oos_proof', 'no_oos_proof_rerun', 'no_best_of_oos', 'no_oos_winner', 'no_oos_return_selection', 'no_candidate_reselection_from_oos', 'no_profile_reselection_from_oos', 'no_production_catalog', 'no_promotion', 'no_plan_confirm_mutation', 'no_c01_to_c58_artifact_mutation', 'no_adverse_month_exclusion_rule', 'no_failed_window_exclusion_rule', 'no_ticker_exclusion_rule', 'no_sector_exclusion_rule', 'no_bad_month_removal', 'no_replay_comparator_promotion', 'loss_cluster_pre_trade_proxy_only', 'branch_bucket_concentration_redesign_only', 'market_down_or_sideways_high_vol_regime_required', 'candidate_is_not_production', 'c59_must_not_recommend_direct_oos_proof'] as $key) {
            $this->assertTrue($safety[$key], $key);
        }
        foreach (['oos_data_used_for_tuning', 'oos_return_used_for_selection', 'return_used_for_selection', 'future_path_used_for_selection', 'future_path_price_used_for_selection', 'profile_ret_net_used_for_selection', 'derived_mfe_mae_used_for_execution', 'adverse_month_exclusion_used', 'failed_window_exclusion_used', 'bad_month_removal_used', 'ticker_hard_exclusion_used', 'sector_hard_exclusion_used', 'direct_oos_proof_recommended', 'oos_proof_unlocked', 'production_ready'] as $key) {
            $this->assertFalse($safety[$key], $key);
        }
        $keys = array_keys($safety);
        $this->assertSame(count($keys), count(array_unique(array_map('strtolower', $keys))), 'safety_boundaries must not contain case-insensitive duplicate keys.');
        @unlink($out);
    }
}
