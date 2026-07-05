<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC121StaticGuardTest extends TestCase
{
    public function test_c121_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC121WeeklySwingWatchlistControlledRuntimeWiringCompletionBoundaryReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC121WeeklySwingWatchlistControlledRuntimeWiringCompletionBoundaryReviewCommand;', $kernel);
    }

    public function test_c121_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC121WeeklySwingWatchlistControlledRuntimeWiringCompletionBoundaryReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC121WeeklySwingWatchlistControlledRuntimeWiringCompletionBoundaryReviewCommand.php');
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

    public function test_c121_docs_state_pr09_completion_boundary_only_and_not_live_runtime(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C121_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C121_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'PR-09 / C121_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW',
            'C121 validates C120 artifact hash and file SHA1.',
            'C121 validates C120 controlled runtime wiring GO decision finalization for completion boundary review only.',
            'C121 confirms C120 ConvertFrom-Json compatibility.',
            'C121 keeps C111 terminal/final-closed for the non-live handoff audit archive chain.',
            'C121 keeps C112 as a separate post-C111 production phase transition gate.',
            'C121 keeps C113 as production readiness review only.',
            'C121 keeps C114 as runtime wiring readiness review only.',
            'C121 keeps C115 as execution approval review only.',
            'C121 keeps C116 as execution review only.',
            'C121 keeps C117 as observation review only.',
            'C121 keeps C118 as observation result review only.',
            'C121 keeps C119 as operator go/no-go review only.',
            'C121 keeps C120 as GO decision finalization review only.',
            'C121 is controlled runtime wiring completion boundary review only.',
            'C121 records completion_boundary_cleared=1 as artifact-only evidence.',
            'C121 records completion_boundary_confirmed=1 as artifact-only evidence.',
            'C121 is not production deployment.',
            'C121 does not mutate PLAN/CONFIRM.',
            'C121 requires --operator-approved.',
            'C121 requires non-empty --approval-reference.',
            'C121 requires --completion-boundary-confirmed.',
            'C121 creates controlled runtime wiring completion boundary manifest as artifact-only.',
            'C121 creates controlled runtime wiring completion boundary checklist as artifact-only.',
            'C121 keeps A01 comparator-only and does not promote A01.',
            'C121 does not activate runtime bridge.',
            'C121 does not create weekly swing live output.',
            'C121 does not generate official weekly swing recommendation.',
            'C121 keeps production_ready=false.',
            'C121 keeps production_catalog_runtime_wired=false.',
            'C121 keeps production_runtime_wiring_allowed=false.',
            'C121 keeps production_runtime_wiring_executed=false.',
            'C121 keeps controlled_runtime_wiring_completion_boundary_context_persisted_to_live_runtime=false.',
            'C121 completion boundary review means proceed to C122 controlled runtime wiring handoff readiness review only.',
            'C121 completion boundary record is not an official weekly swing stock recommendation.',
            'storage/app/watchlist/backtest/c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c121_config_defaults_are_off_and_no_live_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC121WeeklySwingWatchlistControlledRuntimeWiringCompletionBoundaryReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC121WeeklySwingWatchlistControlledRuntimeWiringCompletionBoundaryReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_completion_boundary_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('completion_boundary_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_go_decision_finalization_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_allowed', $combined);
        $this->assertStringContainsString('production_runtime_wiring_executed', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('C121 cleared the controlled runtime wiring completion boundary for E02 primary and B01 backup.', $combined);
    }
}
