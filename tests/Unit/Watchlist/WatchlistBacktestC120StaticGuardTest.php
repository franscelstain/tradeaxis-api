<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC120StaticGuardTest extends TestCase
{
    public function test_c120_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC120WeeklySwingWatchlistControlledRuntimeWiringGoDecisionFinalizationReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC120WeeklySwingWatchlistControlledRuntimeWiringGoDecisionFinalizationReviewCommand;', $kernel);
    }

    public function test_c120_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC120WeeklySwingWatchlistControlledRuntimeWiringGoDecisionFinalizationReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC120WeeklySwingWatchlistControlledRuntimeWiringGoDecisionFinalizationReviewCommand.php');
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

    public function test_c120_docs_state_pr08_go_decision_finalization_only_and_not_live_runtime(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C120_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'PR-08 / C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW',
            'C120 validates C119 artifact hash and file SHA1.',
            'C120 validates C119 controlled runtime wiring operator go/no-go review for GO decision finalization review only.',
            'C120 confirms C119 ConvertFrom-Json compatibility.',
            'C120 keeps C111 terminal/final-closed for the non-live handoff audit archive chain.',
            'C120 keeps C112 as a separate post-C111 production phase transition gate.',
            'C120 keeps C113 as production readiness review only.',
            'C120 keeps C114 as runtime wiring readiness review only.',
            'C120 keeps C115 as execution approval review only.',
            'C120 keeps C116 as execution review only.',
            'C120 keeps C117 as observation review only.',
            'C120 keeps C118 as observation result review only.',
            'C120 keeps C119 as operator go/no-go review only.',
            'C120 is controlled runtime wiring GO decision finalization review only.',
            'C120 records go_decision_finalized=1 as artifact-only evidence.',
            'C120 records go_decision_finalization_confirmed=1 as artifact-only evidence.',
            'C120 is not production deployment.',
            'C120 does not mutate PLAN/CONFIRM.',
            'C120 requires --operator-approved.',
            'C120 requires non-empty --approval-reference.',
            'C120 requires --go-decision-finalization-confirmed.',
            'C120 creates controlled runtime wiring GO decision finalization manifest as artifact-only.',
            'C120 creates controlled runtime wiring GO decision finalization checklist as artifact-only.',
            'C120 keeps A01 comparator-only and does not promote A01.',
            'C120 does not activate runtime bridge.',
            'C120 does not create weekly swing live output.',
            'C120 does not generate official weekly swing recommendation.',
            'C120 keeps production_ready=false.',
            'C120 keeps production_catalog_runtime_wired=false.',
            'C120 keeps production_runtime_wiring_allowed=false.',
            'C120 keeps production_runtime_wiring_executed=false.',
            'C120 keeps controlled_runtime_wiring_go_decision_finalization_context_persisted_to_live_runtime=false.',
            'C120 keeps controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime=false.',
            'C120 keeps controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime=false.',
            'C120 GO decision finalization means proceed to C121 controlled runtime wiring completion boundary review only.',
            'C120 GO decision finalization record is not an official weekly swing stock recommendation.',
            'storage/app/watchlist/backtest/c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c120_config_defaults_are_off_and_no_live_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC120WeeklySwingWatchlistControlledRuntimeWiringGoDecisionFinalizationReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC120WeeklySwingWatchlistControlledRuntimeWiringGoDecisionFinalizationReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_controlled_runtime_wiring_go_decision_finalization_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_go_decision_finalization_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('go_decision_finalization_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_allowed', $combined);
        $this->assertStringContainsString('production_runtime_wiring_executed', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('C120 finalizes the operator GO decision from C119 for E02 primary and B01 backup.', $combined);
    }
}
