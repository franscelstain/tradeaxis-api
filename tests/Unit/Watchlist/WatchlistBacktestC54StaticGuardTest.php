<?php

use App\Application\Watchlist\Services\WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService;

class WatchlistBacktestC54StaticGuardTest extends TestCase
{
    public function test_C54_command_is_registered_with_both_artifact_locks(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyCommand.php')); $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c54-rolling-stability-redesign-or-recalibration-is-only', $command); $this->assertStringContainsString('RunBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyCommand::class', $kernel);
        foreach ([WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService::DEFAULT_EXPECTED_C53_HASH, WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService::DEFAULT_EXPECTED_C53_FILE_SHA1, WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService::DEFAULT_EXPECTED_C52_HASH, WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService::DEFAULT_EXPECTED_C52_FILE_SHA1] as $lock) { $this->assertStringContainsString($lock, $command); }
    }

    public function test_C54_declares_IS_only_no_exclusion_no_gate_relaxation_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService.php'));
        foreach (['C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY', 'C53_ARTIFACT_HASH_LOCK', 'C53_FILE_SHA1_LOCK', 'C52_ARTIFACT_HASH_LOCK', 'C52_FILE_SHA1_LOCK', 'IS_ONLY_VALIDATION', 'C53_ADVERSE_MONTHS_DIAGNOSTIC_ONLY', 'NO_ADVERSE_MONTH_EXCLUSION_RULE', 'NO_TICKER_EXCLUSION_RULE', 'PREDECLARED_SAFE_PRE_TRADE_SELECTION_ONLY', 'RETURN_USED_FOR_SELECTION_FALSE', 'FUTURE_PATH_USED_FOR_SELECTION_FALSE', 'NO_GATE_RELAXATION', 'NO_OOS_TUNING', 'NO_OOS_PROOF', 'NO_OOS_PROOF_RERUN', 'NO_BEST_OF_OOS', 'NO_OOS_WINNER', 'NO_CANDIDATE_RESELECTION_FROM_OOS', 'NO_PROFILE_RESELECTION_FROM_OOS', 'NO_PRODUCTION_CATALOG', 'NO_PROMOTION', 'NO_PLAN_CONFIRM_MUTATION', 'NO_C01_TO_C53_ARTIFACT_MUTATION', 'CANDIDATE_IS_NOT_PRODUCTION', 'C54_MUST_NOT_RECOMMEND_OOS_PROOF', 'PRODUCTION_READY_FALSE'] as $marker) { $this->assertStringContainsString($marker, $source); }
    }

    public function test_C54_has_no_catalog_mutation_or_OOS_command_call(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService.php'));
        foreach (["DB::table('watchlist_bt_oos_eval_ws')", 'updateOrInsert(', '->insert(', '->update(', 'watchlist:backtest-c47-oos-proof', 'watchlist:backtest-c29-oos-proof'] as $forbidden) { $this->assertStringNotContainsString($forbidden, $source); }
    }

    public function test_blocked_artifact_has_unique_and_locked_safety_keys(): void
    {
        $out = storage_path('framework/testing/c54-static.json'); (new WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService())->execute(storage_path('framework/testing/c54-missing.json'), 'h', 's', 'missing', 'h', 's', '2023-01-02', '2025-05-21', $out, ['overwrite' => true]); $artifact = json_decode((string) file_get_contents($out), true);
        foreach (['best_of_oos', 'oos_winner', 'production_candidate', 'production_catalog', 'candidate_promoted', 'candidate_winner', 'new_candidate_selected', 'profile_reselection_from_oos', 'candidate_reselection_from_oos'] as $key) { $this->assertArrayNotHasKey($key, $artifact); }
        $safety = $artifact['safety_boundaries']; foreach (['no_best_of_oos', 'no_oos_winner', 'no_oos_proof', 'no_oos_proof_rerun', 'no_production_catalog', 'no_plan_confirm_mutation', 'no_c01_to_c53_artifact_mutation', 'candidate_is_not_production', 'no_gate_relaxation', 'no_adverse_month_exclusion_rule', 'no_ticker_exclusion_rule'] as $key) { $this->assertTrue($safety[$key]); }
        foreach (['oos_data_used_for_tuning', 'oos_return_used_for_selection', 'return_used_for_selection', 'future_path_used_for_selection', 'future_path_price_used_for_selection', 'profile_ret_net_used_for_selection', 'derived_mfe_mae_used_for_execution', 'direct_oos_proof_recommended', 'oos_proof_unlocked', 'production_ready'] as $key) { $this->assertFalse($safety[$key]); }
        $keys = array_keys($safety); $this->assertSame(count($keys), count(array_unique(array_map('strtolower', $keys)))); @unlink($out);
    }
}
