<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC115StaticGuardTest extends TestCase
{
    public function test_c115_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewCommand;', $kernel);
    }

    public function test_c115_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewCommand.php');
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

    public function test_c115_docs_state_pr03_execution_approval_only_and_not_runtime_execution(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C115_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'PR-03 / C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW',
            'C115 validates C114 artifact hash and file SHA1.',
            'C115 validates C114 production runtime wiring readiness review for execution approval review only.',
            'C115 confirms C114 ConvertFrom-Json compatibility.',
            'C115 keeps C111 terminal/final-closed for the non-live handoff audit archive chain.',
            'C115 keeps C112 as a separate post-C111 production phase transition gate.',
            'C115 keeps C113 as production readiness review only.',
            'C115 keeps C114 as runtime wiring readiness review only.',
            'C115 is not runtime wiring execution.',
            'C115 is not production deployment.',
            'C115 does not mutate PLAN/CONFIRM.',
            'C115 requires --operator-approved.',
            'C115 requires non-empty --approval-reference.',
            'C115 creates controlled runtime wiring execution approval review manifest as artifact-only.',
            'C115 creates controlled runtime wiring execution approval checklist as artifact-only.',
            'C115 keeps A01 comparator-only and does not promote A01.',
            'C115 does not execute production runtime wiring.',
            'C115 does not wire production runtime.',
            'C115 does not activate runtime bridge.',
            'C115 does not create weekly swing live output.',
            'C115 does not generate official weekly swing recommendation.',
            'C115 keeps production_ready=false.',
            'C115 keeps production_catalog_runtime_wired=false.',
            'C115 keeps production_runtime_wiring_allowed=false.',
            'C115 keeps production_runtime_wiring_executed=false.',
            'C115 keeps controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime=false.',
            'C115 keeps controlled_runtime_wiring_execution_context_persisted_to_live_runtime=false.',
            'C115 execution approval review means proceed to C116 controlled runtime wiring execution review only.',
            'C115 execution approval record is not an official weekly swing stock recommendation.',
            'storage/app/watchlist/backtest/c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c115_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_execution_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_allowed', $combined);
        $this->assertStringContainsString('production_runtime_wiring_executed', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('C115 completes controlled runtime wiring execution approval review for E02 primary and B01 backup in review-only, non-live, non-mutating context.', $combined);
    }
}
