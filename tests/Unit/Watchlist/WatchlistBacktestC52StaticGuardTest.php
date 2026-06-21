<?php

use App\Application\Watchlist\Services\WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService;

class WatchlistBacktestC52StaticGuardTest extends TestCase
{
    public function test_C52_command_is_registered_with_three_explicit_hash_locks(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC52ConcentrationDependencyRedesignContinuationCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c52-concentration-dependency-redesign-continuation', $command);
        $this->assertStringContainsString('RunBacktestC52ConcentrationDependencyRedesignContinuationCommand::class', $kernel);
        foreach ([WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService::DEFAULT_EXPECTED_C51_HASH, WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService::DEFAULT_EXPECTED_C50_HASH, WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService::DEFAULT_EXPECTED_C49_HASH] as $hash) { $this->assertStringContainsString($hash, $command); }
    }

    public function test_C52_declares_locked_IS_sector_reconstruction_and_safety_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService.php'));
        foreach (['C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_ONLY', 'C51_ARTIFACT_HASH_LOCK', 'C50_ARTIFACT_HASH_LOCK', 'C49_ARTIFACT_HASH_LOCK', 'C51_USED_AS_LOCKED_CONTINUATION_SOURCE', 'C50_USED_AS_LOCKED_VALIDATION_SOURCE', 'C49_USED_AS_LOCKED_CANDIDATE_SOURCE', 'IS_ONLY_VALIDATION', 'SECTOR_METADATA_ASOF_SAFE_REQUIRED', 'NO_DUMMY_SECTOR', 'SECTOR_NOT_EVALUABLE_DISTINCT_FROM_TRUE_FAIL', 'NO_OOS_TUNING', 'NO_OOS_PROOF', 'NO_OOS_PROOF_RERUN', 'NO_BEST_OF_OOS', 'NO_OOS_WINNER', 'NO_OOS_RETURN_SELECTION', 'NO_CANDIDATE_RESELECTION_FROM_OOS', 'NO_PROFILE_RESELECTION_FROM_OOS', 'NO_PRODUCTION_CATALOG', 'NO_PROMOTION', 'NO_PLAN_CONFIRM_MUTATION', 'NO_C01_TO_C51_ARTIFACT_MUTATION', 'CANDIDATE_IS_NOT_PRODUCTION', 'C52_MUST_NOT_RECOMMEND_OOS_PROOF', 'RETURN_USED_FOR_SELECTION_FALSE', 'FUTURE_PATH_USED_FOR_SELECTION_FALSE'] as $marker) { $this->assertStringContainsString($marker, $source); }
        foreach (['C53_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C52_REDESIGN', 'C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN', 'C53_SECTOR_METADATA_EVIDENCE_EXPANSION_REQUIRED', 'C53_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION', 'C53_SHARED_CORE_REVERSION_REDESIGN_REQUIRED', 'C53_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY'] as $next) { $this->assertStringContainsString($next, $source); }
    }

    public function test_C52_does_not_write_catalogs_promote_candidates_or_call_OOS_proof(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService.php'));
        $this->assertStringNotContainsString("DB::table('watchlist_bt_oos_eval_ws')", $source);
        $this->assertStringNotContainsString('updateOrInsert(', $source);
        $this->assertStringNotContainsString('->insert(', $source);
        $this->assertStringNotContainsString('->update(', $source);
        $this->assertStringNotContainsString('watchlist:backtest-c47-oos-proof', $source);
        $this->assertStringNotContainsString('watchlist:backtest-c29-oos-proof', $source);
        $this->assertStringNotContainsString("max('trade_date')", strtolower($source));
    }

    public function test_blocked_artifact_has_structural_forbidden_key_and_powerShell_guards(): void
    {
        $output = storage_path('framework/testing/c52-static-guard.json');
        (new WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService())->execute(storage_path('framework/testing/missing-c51-static.json'), 'hash', 'missing-c50', 'hash', 'missing-c49', 'hash', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]);
        $artifact = json_decode((string) file_get_contents($output), true);
        foreach (['best_of_oos', 'oos_winner', 'production_candidate', 'production_catalog', 'candidate_promoted', 'profile_reselection_from_oos', 'candidate_reselection_from_oos'] as $forbidden) { $this->assertArrayNotHasKey($forbidden, $artifact); }
        $this->assertFalse($artifact['production_ready']);
        $safety = $artifact['safety_boundaries'];
        foreach (['no_best_of_oos', 'no_oos_winner', 'no_oos_proof', 'no_oos_proof_rerun', 'no_production_catalog', 'no_plan_confirm_mutation', 'no_c01_to_c51_artifact_mutation', 'candidate_is_not_production', 'sector_metadata_asof_safe_required', 'no_dummy_sector', 'sector_not_evaluable_distinct_from_true_fail'] as $key) { $this->assertTrue($safety[$key]); }
        foreach (['oos_data_used_for_tuning', 'oos_return_used_for_selection', 'return_used_for_selection', 'future_path_used_for_selection', 'future_path_price_used_for_selection', 'profile_ret_net_used_for_selection', 'derived_mfe_mae_used_for_execution', 'direct_oos_proof_recommended', 'oos_proof_unlocked', 'production_ready'] as $key) { $this->assertFalse($safety[$key]); }
        $keys = array_keys($safety); $this->assertSame(count($keys), count(array_unique(array_map('strtolower', $keys))), 'safety_boundaries must not contain case-insensitive duplicate keys because PowerShell ConvertFrom-Json fails on them.');
        @unlink($output);
    }
}
