<?php

use App\Application\Watchlist\Services\WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService;

class WatchlistBacktestC56StaticGuardTest extends TestCase
{
    public function test_C56_command_is_registered_and_declares_all_locked_hashes(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC56RollingStabilityRedesignContinuationIsOnlyCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c56-rolling-stability-redesign-continuation-is-only', $command);
        $this->assertStringContainsString('RunBacktestC56RollingStabilityRedesignContinuationIsOnlyCommand::class', $kernel);
        foreach ([
            WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C55_HASH,
            WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C55_FILE_SHA1,
            WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C54_HASH,
            WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C54_FILE_SHA1,
            WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C53_HASH,
            WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C53_FILE_SHA1,
            WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C52_HASH,
            WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService::DEFAULT_EXPECTED_C52_FILE_SHA1,
        ] as $lock) { $this->assertStringContainsString($lock, $command); }
    }

    public function test_C56_declares_IS_only_locked_lineage_and_no_OOS_or_exclusion_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService.php'));
        foreach ([
            'C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY',
            'C55_ARTIFACT_HASH_LOCK', 'C55_FILE_SHA1_LOCK', 'C54_ARTIFACT_HASH_LOCK', 'C54_FILE_SHA1_LOCK', 'C53_ARTIFACT_HASH_LOCK', 'C53_FILE_SHA1_LOCK', 'C52_ARTIFACT_HASH_LOCK', 'C52_FILE_SHA1_LOCK',
            'C55_C54_C53_C52_LOCKED_LINEAGE', 'C55_FAILED_WINDOWS_DIAGNOSTIC_ONLY', 'C54_FAILED_WINDOWS_DIAGNOSTIC_ONLY', 'C53_ADVERSE_MONTHS_DIAGNOSTIC_ONLY',
            'NO_ADVERSE_MONTH_EXCLUSION_RULE', 'NO_FAILED_WINDOW_EXCLUSION_RULE', 'NO_TICKER_EXCLUSION_RULE', 'NO_SECTOR_EXCLUSION_RULE',
            'PREDECLARED_SAFE_PRE_TRADE_SELECTION_ONLY', 'RETURN_USED_FOR_SELECTION_FALSE', 'FUTURE_PATH_USED_FOR_SELECTION_FALSE', 'NO_GATE_RELAXATION',
            'NO_OOS_TUNING', 'NO_OOS_PROOF', 'NO_OOS_PROOF_RERUN', 'NO_BEST_OF_OOS', 'NO_OOS_WINNER', 'NO_OOS_RETURN_SELECTION',
            'NO_CANDIDATE_RESELECTION_FROM_OOS', 'NO_PROFILE_RESELECTION_FROM_OOS', 'NO_PRODUCTION_CATALOG', 'NO_PROMOTION', 'NO_PLAN_CONFIRM_MUTATION',
            'NO_C01_TO_C55_ARTIFACT_MUTATION', 'CANDIDATE_IS_NOT_PRODUCTION', 'C56_MUST_NOT_RECOMMEND_OOS_PROOF', 'SOURCE_RECONSTRUCTION_NO_MAX_TRADE_DATE', 'REGIME_RECONSTRUCTION_ASOF_SAFE',
        ] as $marker) { $this->assertStringContainsString($marker, $source, $marker); }
    }

    public function test_C56_has_no_catalog_mutation_promotion_or_OOS_command_call(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService.php'));
        foreach (["DB::table('watchlist_bt_oos_eval_ws')", 'updateOrInsert(', '->insert(', '->update(', 'watchlist:backtest-c47-oos-proof', 'watchlist:backtest-c29-oos-proof', 'watchlist:backtest-c56-oos-proof', 'MAX(trade_date)'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_blocked_artifact_has_structure_based_no_OOS_no_production_safety_keys(): void
    {
        $out = storage_path('framework/testing/c56-static.json');
        (new WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService())->execute(storage_path('framework/testing/c56-missing.json'), 'h', 's', 'missing', 'h', 's', 'missing', 'h', 's', 'missing', 'h', 's', '2023-01-02', '2025-05-21', $out, ['overwrite' => true]);
        $artifact = json_decode((string) file_get_contents($out), true);

        foreach (['best_of_oos', 'oos_winner', 'production_candidate', 'production_catalog', 'candidate_promoted', 'profile_reselection_from_oos', 'candidate_reselection_from_oos'] as $key) {
            $this->assertArrayNotHasKey($key, $artifact);
        }
        $this->assertFalse($artifact['production_ready']);
        $this->assertArrayHasKey('safety_boundaries', $artifact);
        $safety = $artifact['safety_boundaries'];
        foreach (['no_best_of_oos', 'no_oos_winner', 'no_oos_proof', 'no_oos_proof_rerun', 'no_production_catalog', 'no_plan_confirm_mutation', 'no_c01_to_c55_artifact_mutation', 'candidate_is_not_production', 'c56_must_not_recommend_oos_proof', 'no_gate_relaxation', 'no_adverse_month_exclusion_rule', 'no_failed_window_exclusion_rule', 'no_ticker_exclusion_rule', 'no_sector_exclusion_rule', 'c53_adverse_months_diagnostic_only', 'c54_failed_windows_diagnostic_only', 'c55_failed_windows_diagnostic_only', 'source_reconstruction_no_max_trade_date', 'regime_reconstruction_asof_safe'] as $key) {
            $this->assertTrue($safety[$key], $key);
        }
        foreach (['oos_data_used_for_tuning', 'oos_return_used_for_selection', 'return_used_for_selection', 'future_path_used_for_selection', 'future_path_price_used_for_selection', 'profile_ret_net_used_for_selection', 'derived_mfe_mae_used_for_execution', 'adverse_month_exclusion_used', 'failed_window_exclusion_used', 'direct_oos_proof_recommended', 'oos_proof_unlocked', 'production_ready'] as $key) {
            $this->assertFalse($safety[$key], $key);
        }
        $keys = array_keys($safety);
        $this->assertSame(count($keys), count(array_unique(array_map('strtolower', $keys))), 'safety_boundaries must not contain case-insensitive duplicate keys because PowerShell ConvertFrom-Json fails on them.');
        @unlink($out);
    }
}
