<?php

use App\Application\Watchlist\Services\WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService;

class WatchlistBacktestC46StaticGuardTest extends TestCase
{
    public function test_C46_command_is_registered_with_locked_C45_hash(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC46IsReviewEvidenceExpansionBeforeOosCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c46-is-review-evidence-expansion-before-oos', $command);
        $this->assertStringContainsString('RunBacktestC46IsReviewEvidenceExpansionBeforeOosCommand::class', $kernel);
        $this->assertStringContainsString(WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService::DEFAULT_EXPECTED_C45_HASH, $command);
    }

    public function test_C46_declares_review_only_and_non_production_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService.php'));
        foreach (['NO_OOS_TUNING', 'OOS_PROOF_NOT_EXECUTED', 'NO_BEST_OF_OOS', 'NO_OOS_WINNER', 'NO_PRODUCTION_CATALOG', 'NO_PROMOTION', 'NO_PLAN_CONFIRM_MUTATION', 'NO_C01_TO_C45_ARTIFACT_MUTATION', 'NO_C46_CANDIDATE_RESELECTION', 'CANDIDATE_IS_NOT_PRODUCTION'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
        $this->assertStringContainsString("'oos_proof_executed' => false", $source);
        $this->assertStringContainsString("'candidate_reselected' => false", $source);
        $this->assertStringContainsString("'new_candidate_selected' => false", $source);
        $this->assertStringContainsString("'production_ready' => false", $source);
    }

    public function test_C46_does_not_call_OOS_services_or_mutate_catalogs(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService.php'));
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $source);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $source);
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $source);
        $this->assertStringNotContainsString('updateOrInsert(', $source);
        $this->assertStringNotContainsString('->insert(', $source);
        $this->assertStringNotContainsString('->update(', $source);
    }

    public function test_C46_preserves_canonical_execution_and_reviews_all_warning_layers(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService.php'));
        foreach (["'entry' => 'NEXT_OPEN'", "'exit' => 'STOP_TP_OR_TIME'", "'hold' => 5", "'fee' => 'IDR_FIXED'", "'slip' => 0", "'gap' => 'OPEN'", "'px' => 'IDX_BANDS'", 'yearly_warning_review', 'rolling_warning_review', 'non_bad_month_warning_review', 'corroborating_pass_review', 'guard_and_safety_recheck', 'prior_warning_gap_resolution'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
    }
}
