<?php

use App\Application\Watchlist\Services\WatchlistBacktestC49BroaderStrategyRedesignService;

class WatchlistBacktestC49StaticGuardTest extends TestCase
{
    public function test_C49_command_is_registered_with_C48_hash_lock(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC49BroaderStrategyRedesignCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c49-broader-strategy-redesign', $command);
        $this->assertStringContainsString('RunBacktestC49BroaderStrategyRedesignCommand::class', $kernel);
        $this->assertStringContainsString(WatchlistBacktestC49BroaderStrategyRedesignService::DEFAULT_EXPECTED_C48_HASH, $command);
    }

    public function test_C49_declares_IS_broader_redesign_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC49BroaderStrategyRedesignService.php'));
        foreach ([
            'C49_IS_BROADER_STRATEGY_REDESIGN_ONLY',
            'C48_ARTIFACT_HASH_LOCK',
            'C48_USED_FOR_HYPOTHESIS_ONLY',
            'IS_ONLY_SELECTION',
            'NO_OOS_TUNING',
            'NO_OOS_PROOF',
            'NO_OOS_PROOF_RERUN',
            'NO_BEST_OF_OOS',
            'NO_OOS_WINNER',
            'NO_OOS_RETURN_SELECTION',
            'NO_OOS_BAD_MONTH_THRESHOLD_SELECTION',
            'NO_OOS_TICKER_SECTOR_EXCLUSION_RULE',
            'NO_PROFILE_RESELECTION_FROM_OOS',
            'NO_CANDIDATE_RESELECTION_FROM_OOS',
            'NO_PRODUCTION_CATALOG',
            'NO_PROMOTION',
            'NO_PLAN_CONFIRM_MUTATION',
            'NO_C01_TO_C48_ARTIFACT_MUTATION',
            'CANDIDATE_IS_NOT_PRODUCTION',
        ] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
        $this->assertStringContainsString("'oos_data_used_for_tuning' => false", $source);
        $this->assertStringContainsString("'oos_return_used_for_selection' => false", $source);
        $this->assertStringContainsString("'return_used_for_selection' => false", $source);
        $this->assertStringContainsString("'future_path_used_for_selection' => false", $source);
        $this->assertStringContainsString("'production_ready' => false", $source);
    }

    public function test_C49_does_not_persist_or_promote_catalogs_and_does_not_call_OOS_proof_commands(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC49BroaderStrategyRedesignService.php'));
        $this->assertStringNotContainsString("DB::table('watchlist_bt_oos_eval_ws')", $source);
        $this->assertStringNotContainsString('updateOrInsert(', $source);
        $this->assertStringNotContainsString('->insert(', $source);
        $this->assertStringNotContainsString('->update(', $source);
        $this->assertStringNotContainsString('watchlist:backtest-c47-oos-proof-with-locked-c44-refinement', $source);
        $this->assertStringNotContainsString('watchlist:backtest-c29-oos-proof', $source);
    }

    public function test_C49_output_structure_has_no_forbidden_top_level_keys_and_no_production_flags(): void
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
        $this->assertFalse($artifact['c50_readiness_decision']['direct_oos_proof_recommended']);
        $this->assertFalse($artifact['c50_readiness_decision']['oos_proof_unlocked']);
        $this->assertFalse($artifact['c50_readiness_decision']['production_ready']);

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

    public function test_C49_preserves_canonical_execution_model_and_routes_only_to_C50(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC49BroaderStrategyRedesignService.php'));
        foreach (["'entry' => 'NEXT_OPEN'", "'exit' => 'STOP_TP_OR_TIME'", "'hold' => 5", "'fee' => 'IDR_FIXED'", "'slip' => 0", "'gap' => 'OPEN'", "'px' => 'IDX_BANDS'", '2023-01-02', '2025-05-21', '2025-05-22', '2026-05-29'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
        $this->assertStringContainsString('C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN', $source);
        $this->assertStringContainsString('C50_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN', $source);
        $this->assertStringNotContainsString('C49_OOS_PROOF_RECOMMENDED', $source);
    }

    private function minimalCompletedArtifact(): array
    {
        return [
            'run_code' => 'C49_BROADER_STRATEGY_REDESIGN',
            'artifact_type' => 'C49_BROADER_STRATEGY_REDESIGN',
            'status' => 'C49_BROADER_STRATEGY_REDESIGN_COMPLETED',
            'production_ready' => false,
            'c50_readiness_decision' => ['direct_oos_proof_recommended' => false, 'oos_proof_unlocked' => false, 'production_ready' => false],
            'safety_boundaries' => [
                'NO_BEST_OF_OOS' => true,
                'NO_OOS_WINNER' => true,
                'NO_OOS_PROOF' => true,
                'NO_OOS_PROOF_RERUN' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'CANDIDATE_IS_NOT_PRODUCTION' => true,
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
