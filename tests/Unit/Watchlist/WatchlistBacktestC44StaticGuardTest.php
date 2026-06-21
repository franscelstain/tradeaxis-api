<?php

use App\Application\Watchlist\Services\WatchlistBacktestC44IsGuardRefinementCandidateFormationService;

class WatchlistBacktestC44StaticGuardTest extends TestCase
{
    public function test_C44_command_is_registered_with_locked_C43_hash(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC44IsGuardRefinementCandidateFormationCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c44-is-guard-refinement-candidate-formation', $command);
        $this->assertStringContainsString('RunBacktestC44IsGuardRefinementCandidateFormationCommand::class', $kernel);
        $this->assertStringContainsString(WatchlistBacktestC44IsGuardRefinementCandidateFormationService::DEFAULT_EXPECTED_C43_HASH, $command);
    }

    public function test_C44_declares_all_no_OOS_and_no_production_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC44IsGuardRefinementCandidateFormationService.php'));
        foreach (['NO_OOS_TUNING','NO_OOS_PROOF','NO_BEST_OF_OOS','NO_OOS_WINNER','NO_PRODUCTION_CATALOG','NO_PROMOTION','NO_PLAN_CONFIRM_MUTATION','NO_C01_TO_C43_ARTIFACT_MUTATION','CANDIDATE_IS_NOT_PRODUCTION','CANDIDATE_REQUIRES_C45_VALIDATION'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
        $this->assertStringContainsString("'return_used_for_selection' => false", $source);
        $this->assertStringContainsString("'future_path_used_for_selection' => false", $source);
        $this->assertStringContainsString("'production_ready' => false", $source);
    }

    public function test_C44_does_not_call_OOS_services_or_write_catalogs(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC44IsGuardRefinementCandidateFormationService.php'));
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $source);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $source);
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $source);
        $this->assertStringNotContainsString('updateOrInsert(', $source);
        $this->assertStringNotContainsString('->insert(', $source);
        $this->assertStringNotContainsString('->update(', $source);
    }

    public function test_C44_preserves_canonical_execution_and_C39_guards(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC44IsGuardRefinementCandidateFormationService.php'));
        foreach (["'entry' => 'NEXT_OPEN'","'exit' => 'STOP_TP_OR_TIME'","'hold' => 5","'fee' => 'IDR_FIXED'","'slip' => 0","'gap' => 'OPEN'","'px' => 'IDX_BANDS'",'C39_COVERAGE_GUARD_PRESERVED','C39_BRANCH_DIVERSIFICATION_GUARD_PRESERVED','quota_count_is_fixed_across_candidates'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
    }
}
