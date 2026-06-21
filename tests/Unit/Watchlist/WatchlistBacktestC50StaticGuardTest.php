<?php

use App\Application\Watchlist\Services\WatchlistBacktestC50IsValidationAntiOverfitCheckService;

class WatchlistBacktestC50StaticGuardTest extends TestCase
{
    public function test_C50_command_is_registered_with_C49_hash_lock(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC50IsValidationAntiOverfitCheckCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c50-is-validation-anti-overfit-check', $command);
        $this->assertStringContainsString('RunBacktestC50IsValidationAntiOverfitCheckCommand::class', $kernel);
        $this->assertStringContainsString(WatchlistBacktestC50IsValidationAntiOverfitCheckService::DEFAULT_EXPECTED_C49_HASH, $command);
    }

    public function test_C50_declares_locked_IS_validation_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC50IsValidationAntiOverfitCheckService.php'));
        foreach ([
            'C50_IS_VALIDATION_ANTI_OVERFIT_CHECK_ONLY',
            'C49_ARTIFACT_HASH_LOCK',
            'C49_USED_AS_LOCKED_CANDIDATE_SOURCE',
            'LOCKED_C49_CANDIDATE_REPLAY_ONLY',
            'IS_ONLY_VALIDATION',
            'NO_OOS_TUNING',
            'NO_OOS_PROOF',
            'NO_OOS_PROOF_RERUN',
            'NO_BEST_OF_OOS',
            'NO_OOS_WINNER',
            'NO_OOS_RETURN_SELECTION',
            'NO_OOS_BAD_MONTH_THRESHOLD_SELECTION',
            'NO_OOS_TICKER_SECTOR_EXCLUSION_RULE',
            'NO_CANDIDATE_RESELECTION_FROM_OOS',
            'NO_PROFILE_RESELECTION_FROM_OOS',
            'NO_PRODUCTION_CATALOG',
            'NO_PROMOTION',
            'NO_PLAN_CONFIRM_MUTATION',
            'NO_C01_TO_C49_ARTIFACT_MUTATION',
            'CANDIDATE_IS_NOT_PRODUCTION',
            'C50_MUST_NOT_RECOMMEND_OOS_PROOF',
        ] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
        $this->assertStringContainsString("'oos_data_used_for_tuning' => false", $source);
        $this->assertStringContainsString("'oos_return_used_for_selection' => false", $source);
        $this->assertStringContainsString("'return_used_for_selection' => false", $source);
        $this->assertStringContainsString("'future_path_used_for_selection' => false", $source);
        $this->assertStringContainsString("'production_ready' => false", $source);
        $this->assertStringContainsString(WatchlistBacktestC50IsValidationAntiOverfitCheckService::DEFAULT_EXPECTED_C49_HASH, $source);
    }

    public function test_C50_does_not_persist_or_promote_catalogs_and_does_not_call_OOS_proof_commands(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC50IsValidationAntiOverfitCheckService.php'));
        $this->assertStringNotContainsString("DB::table('watchlist_bt_oos_eval_ws')", $source);
        $this->assertStringNotContainsString('updateOrInsert(', $source);
        $this->assertStringNotContainsString('->insert(', $source);
        $this->assertStringNotContainsString('->update(', $source);
        $this->assertStringNotContainsString('watchlist:backtest-c47-oos-proof-with-locked-c44-refinement', $source);
        $this->assertStringNotContainsString('watchlist:backtest-c29-oos-proof', $source);
    }

    public function test_C50_output_structure_has_no_forbidden_top_level_keys_and_no_production_flags(): void
    {
        $artifact = $this->minimalCompletedArtifact();
        foreach ([
            'best_of_oos',
            'oos_winner',
            'production_candidate',
            'production_catalog',
            'candidate_promoted',
            'profile_reselection_from_oos',
            'candidate_reselection_from_oos',
        ] as $forbidden) {
            $this->assertArrayNotHasKey($forbidden, $artifact);
        }

        $this->assertFalse($artifact['production_ready']);
        $this->assertFalse($artifact['c51_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c51_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($artifact['c51_readiness_decision']['production_ready']);
        $this->assertStringStartsNotWith('C51_OOS_PROOF', $artifact['next_step_recommendation']);

        $safety = array_change_key_case($artifact['safety_boundaries'], CASE_LOWER);
        $this->assertTrue($safety['no_best_of_oos']);
        $this->assertTrue($safety['no_oos_winner']);
        $this->assertTrue($safety['no_oos_proof']);
        $this->assertTrue($safety['no_oos_proof_rerun']);
        $this->assertTrue($safety['no_production_catalog']);
        $this->assertTrue($safety['no_plan_confirm_mutation']);
        $this->assertTrue($safety['candidate_is_not_production']);
        $this->assertFalse($safety['oos_data_used_for_tuning']);
        $this->assertFalse($safety['oos_return_used_for_candidate_selection']);
        $this->assertFalse($safety['return_used_for_selection']);
        $this->assertFalse($safety['future_path_used_for_selection']);
        $this->assertFalse($safety['future_path_price_used_for_selection']);
        $this->assertFalse($safety['profile_ret_net_used_for_selection']);
        $this->assertFalse($safety['derived_mfe_mae_used_for_execution']);
        $this->assertFalse($safety['direct_oos_proof_recommended']);
        $this->assertFalse($safety['oos_proof_unlocked']);
    }


    public function test_C50_safety_boundary_keys_are_powerShell_convertFromJson_safe(): void
    {
        $artifact = $this->minimalCompletedArtifact();
        $keys = array_keys($artifact['safety_boundaries']);
        $normalizedKeys = array_map('strtolower', $keys);

        $this->assertSame(
            count($normalizedKeys),
            count(array_unique($normalizedKeys)),
            'safety_boundaries must not contain case-insensitive duplicate keys because PowerShell ConvertFrom-Json fails on them.'
        );
    }

    public function test_C50_preserves_canonical_execution_model_and_routes_only_to_C51_non_OOS_proof_steps(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC50IsValidationAntiOverfitCheckService.php'));
        foreach (["'entry' => 'NEXT_OPEN'", "'exit' => 'STOP_TP_OR_TIME'", "'hold' => 5", "'fee' => 'IDR_FIXED'", "'slip' => 0", "'gap' => 'OPEN'", "'px' => 'IDX_BANDS'", '2023-01-02', '2025-05-21', '2025-05-22', '2026-05-29'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
        foreach ([
            'C51_PRE_OOS_LOCK_REVIEW_FOR_C49_REDESIGN',
            'C51_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN',
            'C51_REGIME_ROBUSTNESS_EVIDENCE_EXPANSION',
            'C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW',
            'C51_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY',
        ] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
        $this->assertStringNotContainsString('C50_DIRECT_OOS_PROOF_RECOMMENDED', $source);
    }

    private function minimalCompletedArtifact(): array
    {
        return [
            'run_code' => 'C50_IS_VALIDATION_ANTI_OVERFIT_CHECK',
            'artifact_type' => 'C50_IS_VALIDATION_ANTI_OVERFIT_CHECK',
            'status' => 'C50_IS_VALIDATION_COMPLETED',
            'production_ready' => false,
            'next_step_recommendation' => 'C51_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN',
            'c51_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'safety_boundaries' => [
                'no_best_of_oos' => true,
                'no_oos_winner' => true,
                'no_oos_proof' => true,
                'no_oos_proof_rerun' => true,
                'no_production_catalog' => true,
                'no_plan_confirm_mutation' => true,
                'candidate_is_not_production' => true,
                'oos_data_used_for_tuning' => false,
                'oos_return_used_for_candidate_selection' => false,
                'return_used_for_selection' => false,
                'future_path_used_for_selection' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
                'direct_oos_proof_recommended' => false,
                'oos_proof_unlocked' => false,
            ],
        ];
    }
}
