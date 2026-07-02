<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC116StaticGuardTest extends TestCase
{
    public function test_c116_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC116WeeklySwingWatchlistControlledRuntimeWiringExecutionReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC116WeeklySwingWatchlistControlledRuntimeWiringExecutionReviewCommand;', $kernel);
    }

    public function test_c116_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC116WeeklySwingWatchlistControlledRuntimeWiringExecutionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC116WeeklySwingWatchlistControlledRuntimeWiringExecutionReviewCommand.php');
        $combined = $service."\n".$command;

        foreach ([
            "latest('trade_date')",
            'latest("trade_date")',
            "orderByDesc('trade_date')",
            'orderByDesc("trade_date")',
            'MAX(trade_date)',
            "max('trade_date')",
            'returnFieldsUsedForSelection',
            'futurePathUsedForSelection',
            'generateStockRecommendation()',
            'publishWeeklySwingOutput()',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $combined, $forbidden);
        }
    }

    public function test_c116_docs_state_pr04_execution_review_only_and_not_live_runtime(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C116_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C116_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'PR-04 / C116_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW',
            'C116 validates C115 artifact hash and file SHA1.',
            'C116 validates C115 controlled runtime wiring execution approval review for execution review only.',
            'C116 confirms C115 ConvertFrom-Json compatibility.',
            'C116 keeps C111 terminal/final-closed for the non-live handoff audit archive chain.',
            'C116 keeps C112 as a separate post-C111 production phase transition gate.',
            'C116 keeps C113 as production readiness review only.',
            'C116 keeps C114 as runtime wiring readiness review only.',
            'C116 keeps C115 as execution approval review only.',
            'C116 is controlled runtime wiring execution review only.',
            'C116 is not production deployment.',
            'C116 does not mutate PLAN/CONFIRM.',
            'C116 requires --operator-approved.',
            'C116 requires non-empty --approval-reference.',
            'C116 creates controlled runtime wiring execution review manifest as artifact-only.',
            'C116 creates controlled runtime wiring execution review checklist as artifact-only.',
            'C116 keeps A01 comparator-only and does not promote A01.',
            'C116 does not activate runtime bridge.',
            'C116 does not create weekly swing live output.',
            'C116 does not generate official weekly swing recommendation.',
            'C116 keeps production_ready=false.',
            'C116 keeps production_catalog_runtime_wired=false.',
            'C116 keeps production_runtime_wiring_allowed=false.',
            'C116 keeps production_runtime_wiring_executed=false.',
            'C116 keeps controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime=false.',
            'C116 keeps controlled_runtime_wiring_execution_context_persisted_to_live_runtime=false.',
            'C116 execution review means proceed to C117 controlled runtime wiring observation review only.',
            'C116 execution review record is not an official weekly swing stock recommendation.',
            'storage/app/watchlist/backtest/c116-weekly-swing-watchlist-controlled-runtime-wiring-execution-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c116_config_defaults_are_off_and_no_live_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC116WeeklySwingWatchlistControlledRuntimeWiringExecutionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC116WeeklySwingWatchlistControlledRuntimeWiringExecutionReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_execution_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_allowed', $combined);
        $this->assertStringContainsString('production_runtime_wiring_executed', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('C116 completes controlled runtime wiring execution review for E02 primary and B01 backup in artifact-only, non-live, non-mutating context.', $combined);
    }
}
