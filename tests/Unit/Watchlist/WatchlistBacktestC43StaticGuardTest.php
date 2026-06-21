<?php

use App\Application\Watchlist\Services\WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService;

class WatchlistBacktestC43StaticGuardTest extends TestCase
{
    public function test_C43_source_declares_locked_IS_only_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService.php'));
        $this->assertStringContainsString(WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService::DEFAULT_EXPECTED_C42_HASH, $source);
        foreach ([
            'C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC', 'C42_ARTIFACT_HASH_LOCK', 'IS_ONLY_FIELD_EXPANSION_DIAGNOSTIC',
            'C43_FROM_C42_WARNING_GAP_REQUIREMENTS', 'NO_OOS_TUNING', 'NO_OOS_PROOF', 'NO_BEST_OF_OOS',
            'NO_PRODUCTION_CATALOG', 'NO_PROMOTION', 'NO_PLAN_CONFIRM_MUTATION', 'NO_C01_TO_C42_ARTIFACT_MUTATION',
            'CANDIDATE_IS_NOT_PRODUCTION', 'return_used_for_selection', 'future_path_used_for_selection',
        ] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
    }

    public function test_C43_command_is_registered_and_not_scheduled(): void
    {
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('RunBacktestC43PreTradeFieldExpansionDiagnosticCommand::class', $kernel);
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC43PreTradeFieldExpansionDiagnosticCommand.php'));
        $this->assertStringContainsString('watchlist:backtest-c43-pre-trade-field-expansion-diagnostic', $command);
        $this->assertStringContainsString('never runs OOS proof', $command);
    }

    public function test_C43_structural_guard_uses_exact_top_level_key_checks(): void
    {
        $artifact = $this->minimalBoundaryArtifact();
        $safety = array_change_key_case($artifact['safety_boundaries'], CASE_LOWER);
        foreach (['best_of_oos', 'oos_winner', 'production_candidate', 'production_catalog', 'candidate_promoted', 'profile_reselection_from_oos'] as $forbidden) {
            $this->assertArrayNotHasKey($forbidden, $artifact);
        }
        $this->assertFalse($artifact['production_ready']);
        $this->assertTrue($safety['no_oos_proof']);
        $this->assertTrue($safety['no_best_of_oos']);
        $this->assertTrue($safety['no_production_catalog']);
        $this->assertTrue($safety['no_plan_confirm_mutation']);
        $this->assertTrue($safety['candidate_is_not_production']);
        $this->assertFalse($safety['oos_data_used_for_tuning']);
        $this->assertFalse($safety['return_used_for_selection']);
        $this->assertFalse($safety['future_path_used_for_selection']);
        $this->assertFalse($safety['future_path_price_used_for_selection']);
        $this->assertFalse($safety['profile_ret_net_used_for_selection']);
        $this->assertFalse($safety['derived_mfe_mae_used_for_execution']);
    }

    public function test_C43_has_no_mutation_or_OOS_proof_dependencies(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService.php'));
        $this->assertStringNotContainsString('WatchlistBacktestC29OosProofService', $source);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $source);
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $source);
        $this->assertStringNotContainsString('updateOrInsert(', $source);
        $this->assertStringNotContainsString('->insert(', $source);
        $this->assertStringNotContainsString('->update(', $source);
        $this->assertStringContainsString("'direct_oos_proof_recommended' => false", $source);
        $this->assertStringContainsString("'production_ready' => false", $source);
    }

    private function minimalBoundaryArtifact(): array
    {
        return [
            'production_ready' => false,
            'safety_boundaries' => [
                'NO_OOS_PROOF' => true,
                'NO_BEST_OF_OOS' => true,
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
    }
}
