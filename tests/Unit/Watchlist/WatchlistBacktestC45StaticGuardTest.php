<?php

use App\Application\Watchlist\Services\WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService;

class WatchlistBacktestC45StaticGuardTest extends TestCase
{
    public function test_C45_command_is_registered_with_locked_C44_hash(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC45IsValidationAntiOverfitCheckForC44RefinementCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c45-is-validation-anti-overfit-check-for-c44-refinement', $command);
        $this->assertStringContainsString('RunBacktestC45IsValidationAntiOverfitCheckForC44RefinementCommand::class', $kernel);
        $this->assertStringContainsString(WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService::DEFAULT_EXPECTED_C44_HASH, $command);
    }

    public function test_C45_declares_all_no_OOS_and_no_production_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService.php'));
        foreach (['NO_OOS_TUNING', 'NO_OOS_PROOF', 'NO_BEST_OF_OOS', 'NO_OOS_WINNER', 'NO_PRODUCTION_CATALOG', 'NO_PROMOTION', 'NO_PLAN_CONFIRM_MUTATION', 'NO_C01_TO_C44_ARTIFACT_MUTATION', 'CANDIDATE_IS_NOT_PRODUCTION', 'HUMAN_REVIEW_REQUIRED_BEFORE_OOS'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
        $this->assertStringContainsString("'return_used_for_selection' => false", $source);
        $this->assertStringContainsString("'future_path_used_for_selection' => false", $source);
        $this->assertStringContainsString("'oos_proof_unlocked' => false", $source);
        $this->assertStringContainsString("'production_ready' => false", $source);
    }

    public function test_C45_does_not_call_OOS_services_or_mutate_catalogs(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService.php'));
        $this->assertStringNotContainsString('WatchlistBacktestOosProofService', $source);
        $this->assertStringNotContainsString('WatchlistBacktestOosEvaluationRepository', $source);
        $this->assertStringNotContainsString('watchlist_bt_oos_eval_ws', $source);
        $this->assertStringNotContainsString('updateOrInsert(', $source);
        $this->assertStringNotContainsString('->insert(', $source);
        $this->assertStringNotContainsString('->update(', $source);
    }

    public function test_C45_contains_required_anti_overfit_layers_and_canonical_execution(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService.php'));
        foreach (["'entry' => 'NEXT_OPEN'", "'exit' => 'STOP_TP_OR_TIME'", "'hold' => 5", "'fee' => 'IDR_FIXED'", "'slip' => 0", "'gap' => 'OPEN'", "'px' => 'IDX_BANDS'", 'full_is_validation', 'yearly_validation', 'rolling_window_validation', 'bad_month_like_stress_validation', 'non_bad_month_validation', 'ticker_concentration_validation', 'branch_concentration_validation', 'month_coverage_validation', 'downside_stability_validation'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
    }
}
