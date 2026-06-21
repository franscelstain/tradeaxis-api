<?php

use App\Application\Watchlist\Services\WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService;

class WatchlistBacktestC53StaticGuardTest extends TestCase
{
    public function test_C53_command_is_registered_with_C52_hash_and_file_locks(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC53IsEvidenceExpansionForC52RedesignCommand.php')); $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c53-is-evidence-expansion-for-c52-redesign', $command); $this->assertStringContainsString('RunBacktestC53IsEvidenceExpansionForC52RedesignCommand::class', $kernel);
        $this->assertStringContainsString(WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService::DEFAULT_EXPECTED_C52_HASH, $command); $this->assertStringContainsString(WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService::DEFAULT_EXPECTED_C52_FILE_SHA1, $command);
    }

    public function test_C53_declares_IS_only_structural_cohort_and_no_OOS_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService.php'));
        foreach (['C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN_ONLY', 'C52_ARTIFACT_HASH_LOCK', 'C52_FILE_SHA1_LOCK', 'C52_USED_AS_LOCKED_EVIDENCE_SOURCE', 'C51_C50_C49_LINEAGE_CARRIED_FORWARD', 'IS_ONLY_VALIDATION', 'STRUCTURAL_COHORT_NO_RETURN_SELECTION', 'NO_NEW_CANDIDATE_FORMATION', 'NO_CANDIDATE_WINNER', 'NO_OOS_TUNING', 'NO_OOS_PROOF', 'NO_OOS_PROOF_RERUN', 'NO_BEST_OF_OOS', 'NO_OOS_WINNER', 'NO_CANDIDATE_RESELECTION_FROM_OOS', 'NO_PROFILE_RESELECTION_FROM_OOS', 'NO_PRODUCTION_CATALOG', 'NO_PROMOTION', 'NO_PLAN_CONFIRM_MUTATION', 'NO_C01_TO_C52_ARTIFACT_MUTATION', 'CANDIDATE_IS_NOT_PRODUCTION', 'C53_MUST_NOT_RECOMMEND_OOS_PROOF', 'RETURN_USED_FOR_SELECTION_FALSE', 'FUTURE_PATH_USED_FOR_SELECTION_FALSE', 'PRODUCTION_READY_FALSE'] as $marker) { $this->assertStringContainsString($marker, $source); }
    }

    public function test_C53_has_no_catalog_mutation_promotion_or_OOS_command_call(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService.php'));
        $this->assertStringNotContainsString("DB::table('watchlist_bt_oos_eval_ws')", $source); $this->assertStringNotContainsString('updateOrInsert(', $source); $this->assertStringNotContainsString('->insert(', $source); $this->assertStringNotContainsString('->update(', $source); $this->assertStringNotContainsString('watchlist:backtest-c47-oos-proof', $source); $this->assertStringNotContainsString('watchlist:backtest-c29-oos-proof', $source);
    }

    public function test_blocked_C53_artifact_has_structural_forbidden_key_and_powerShell_guards(): void
    {
        $output = storage_path('framework/testing/c53-static-guard.json'); (new WatchlistBacktestC53IsEvidenceExpansionForC52RedesignService())->execute(storage_path('framework/testing/missing-c52-static.json'), 'hash', 'sha1', '2023-01-02', '2025-05-21', $output, ['overwrite' => true]); $artifact = json_decode((string) file_get_contents($output), true);
        foreach (['best_of_oos', 'oos_winner', 'production_candidate', 'production_catalog', 'candidate_promoted', 'candidate_winner', 'new_candidate_selected', 'profile_reselection_from_oos', 'candidate_reselection_from_oos'] as $key) { $this->assertArrayNotHasKey($key, $artifact); }
        $safety = $artifact['safety_boundaries']; foreach (['no_best_of_oos', 'no_oos_winner', 'no_oos_proof', 'no_oos_proof_rerun', 'no_production_catalog', 'no_plan_confirm_mutation', 'no_c01_to_c52_artifact_mutation', 'candidate_is_not_production', 'structural_cohort_no_return_selection', 'no_new_candidate_formation', 'no_candidate_winner'] as $key) { $this->assertTrue($safety[$key]); }
        foreach (['oos_data_used_for_tuning', 'oos_return_used_for_selection', 'return_used_for_selection', 'future_path_used_for_selection', 'future_path_price_used_for_selection', 'profile_ret_net_used_for_selection', 'derived_mfe_mae_used_for_execution', 'direct_oos_proof_recommended', 'oos_proof_unlocked', 'production_ready'] as $key) { $this->assertFalse($safety[$key]); }
        $keys = array_keys($safety); $this->assertSame(count($keys), count(array_unique(array_map('strtolower', $keys)))); @unlink($output);
    }
}
