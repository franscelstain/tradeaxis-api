<?php

use App\Application\Watchlist\Services\WatchlistBacktestC48OosFailureAttributionService;

class WatchlistBacktestC48StaticGuardTest extends TestCase
{
    public function test_C48_command_is_registered_with_C47_hash_lock(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC48OosFailureAttributionCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c48-oos-failure-attribution', $command);
        $this->assertStringContainsString('RunBacktestC48OosFailureAttributionCommand::class', $kernel);
        $this->assertStringContainsString(WatchlistBacktestC48OosFailureAttributionService::DEFAULT_EXPECTED_C47_HASH, $command);
    }

    public function test_C48_declares_failure_attribution_only_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC48OosFailureAttributionService.php'));
        foreach (['C48_OOS_FAILURE_ATTRIBUTION_ONLY', 'C47_ARTIFACT_HASH_LOCK', 'NO_OOS_TUNING', 'NO_OOS_PROOF_RERUN', 'NO_BEST_OF_OOS', 'NO_OOS_WINNER', 'NO_PROFILE_RESELECTION_FROM_OOS', 'NO_CANDIDATE_RESELECTION_FROM_OOS', 'NO_PRODUCTION_CATALOG', 'NO_PROMOTION', 'NO_PLAN_CONFIRM_MUTATION', 'NO_C01_TO_C47_ARTIFACT_MUTATION', 'CANDIDATE_IS_NOT_PRODUCTION'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
        $this->assertStringContainsString("'oos_data_used_for_tuning' => false", $source);
        $this->assertStringContainsString("'return_used_for_selection' => false", $source);
        $this->assertStringContainsString("'future_path_used_for_selection' => false", $source);
        $this->assertStringContainsString("'production_ready' => false", $source);
    }

    public function test_C48_does_not_persist_or_promote_catalogs_and_does_not_call_OOS_proof_command(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC48OosFailureAttributionService.php'));
        $this->assertStringNotContainsString("DB::table('watchlist_bt_oos_eval_ws')", $source);
        $this->assertStringNotContainsString('updateOrInsert(', $source);
        $this->assertStringNotContainsString('->insert(', $source);
        $this->assertStringNotContainsString('->update(', $source);
        $this->assertStringNotContainsString('watchlist:backtest-c47-oos-proof-with-locked-c44-refinement', $source);
        $this->assertStringNotContainsString('C48_OOS_PROOF_RECOMMENDED', $source);
    }

    public function test_C48_output_structure_has_no_forbidden_top_level_keys_and_no_production_flags(): void
    {
        $artifact = $this->minimalCompletedArtifact();
        foreach (['best_of_oos', 'oos_winner', 'production_candidate', 'production_catalog', 'candidate_promoted', 'profile_reselection_from_oos', 'candidate_reselection_from_oos'] as $forbidden) {
            $this->assertArrayNotHasKey($forbidden, $artifact);
        }
        $this->assertFalse($artifact['production_ready']);
        $safety = array_change_key_case($artifact['safety_boundaries'], CASE_LOWER);
        $this->assertTrue($safety['no_best_of_oos']);
        $this->assertTrue($safety['no_oos_winner']);
        $this->assertTrue($safety['no_oos_proof_rerun']);
        $this->assertTrue($safety['no_production_catalog']);
        $this->assertTrue($safety['no_plan_confirm_mutation']);
        $this->assertTrue($safety['candidate_is_not_production']);
        $this->assertFalse($safety['oos_data_used_for_tuning']);
        $this->assertFalse($safety['return_used_for_selection']);
        $this->assertFalse($safety['future_path_used_for_selection']);
        $this->assertFalse($safety['future_path_price_used_for_selection']);
        $this->assertFalse($safety['profile_ret_net_used_for_selection']);
        $this->assertFalse($safety['derived_mfe_mae_used_for_execution']);
        $this->assertFalse($artifact['c49_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c49_readiness_decision']['oos_proof_unlocked']);
    }

    public function test_C48_preserves_canonical_execution_model_and_reserved_window(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC48OosFailureAttributionService.php'));
        foreach (["'entry' => 'NEXT_OPEN'", "'exit' => 'STOP_TP_OR_TIME'", "'hold' => 5", "'fee' => 'IDR_FIXED'", "'slip' => 0", "'gap' => 'OPEN'", "'px' => 'IDX_BANDS'", '2025-05-22', '2026-05-29', 'MONTHLY_G21_QUOTA = 13'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
    }

    private function minimalCompletedArtifact(): array
    {
        $artifact = [
            'run_code' => 'C48_OOS_FAILURE_ATTRIBUTION',
            'artifact_type' => 'C48_OOS_FAILURE_ATTRIBUTION',
            'status' => 'C48_OOS_FAILURE_ATTRIBUTION_COMPLETED',
            'production_ready' => false,
            'c49_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'safety_boundaries' => [
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_OOS_PROOF_RERUN' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'CANDIDATE_IS_NOT_PRODUCTION' => true,
                'oos_data_used_for_tuning' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
            ],
        ];
        return $artifact;
    }
}
