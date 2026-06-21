<?php

use App\Application\Watchlist\Services\WatchlistBacktestC47OosProofWithLockedC44RefinementService;

class WatchlistBacktestC47StaticGuardTest extends TestCase
{
    public function test_C47_command_is_registered_with_all_source_hash_locks(): void
    {
        $command = file_get_contents(base_path('app/Console/Commands/Watchlist/RunBacktestC47OosProofWithLockedC44RefinementCommand.php'));
        $kernel = file_get_contents(base_path('app/Console/Kernel.php'));
        $this->assertStringContainsString('watchlist:backtest-c47-oos-proof-with-locked-c44-refinement', $command);
        $this->assertStringContainsString('RunBacktestC47OosProofWithLockedC44RefinementCommand::class', $kernel);
        $this->assertStringContainsString(WatchlistBacktestC47OosProofWithLockedC44RefinementService::DEFAULT_EXPECTED_C46_HASH, $command);
        $this->assertStringContainsString(WatchlistBacktestC47OosProofWithLockedC44RefinementService::DEFAULT_EXPECTED_C44_HASH, $command);
        $this->assertStringContainsString(WatchlistBacktestC47OosProofWithLockedC44RefinementService::DEFAULT_EXPECTED_OOS_SOURCE_HASH, $command);
    }

    public function test_C47_declares_one_shot_no_tuning_no_reselection_boundaries(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC47OosProofWithLockedC44RefinementService.php'));
        foreach (['ONE_SHOT_LOCKED_OOS_PROOF', 'NO_OOS_TUNING', 'NO_BEST_OF_OOS', 'NO_OOS_WINNER_SELECTION', 'NO_PROFILE_RESELECTION_FROM_OOS', 'NO_CANDIDATE_RESELECTION_FROM_OOS', 'NO_PRODUCTION_CATALOG', 'NO_PROMOTION', 'NO_PLAN_CONFIRM_MUTATION', 'NO_C01_TO_C46_ARTIFACT_MUTATION', 'CANDIDATE_IS_NOT_PRODUCTION'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
        $this->assertStringContainsString("'oos_result_used_for_retuning' => false", $source);
        $this->assertStringContainsString("'oos_result_used_for_candidate_reselection' => false", $source);
        $this->assertStringContainsString("'production_ready' => false", $source);
    }

    public function test_C47_does_not_persist_OOS_catalog_or_change_acceptance_thresholds_dynamically(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC47OosProofWithLockedC44RefinementService.php'));
        $this->assertStringNotContainsString("DB::table('watchlist_bt_oos_eval_ws')", $source);
        $this->assertStringNotContainsString('updateOrInsert(', $source);
        $this->assertStringNotContainsString('->insert(', $source);
        $this->assertStringNotContainsString('->update(', $source);
        $this->assertStringContainsString('LOCKED_C29_OOS_ACCEPTANCE_GATE_REUSED_WITHOUT_RETUNING', $source);
        $this->assertStringContainsString('private const MIN_MONTH_WIN_RATE = 0.45', $source);
    }

    public function test_C47_preserves_canonical_execution_locked_rule_and_reserved_window(): void
    {
        $source = file_get_contents(base_path('app/Application/Watchlist/Services/WatchlistBacktestC47OosProofWithLockedC44RefinementService.php'));
        foreach (["'entry' => 'NEXT_OPEN'", "'exit' => 'STOP_TP_OR_TIME'", "'hold' => 5", "'fee' => 'IDR_FIXED'", "'slip' => 0", "'gap' => 'OPEN'", "'px' => 'IDX_BANDS'", '2025-05-22', '2026-05-29', 'MONTHLY_G21_QUOTA = 13', 'prefer non-extended IHSG ROC20 dates, then signal metadata, inside fixed quota'] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
    }
}
