<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC119StaticGuardTest extends TestCase
{
    public function test_c119_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewCommand;', $kernel);
    }

    public function test_c119_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewCommand.php');
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

    public function test_c119_docs_state_pr07_operator_go_no_go_only_and_not_live_runtime(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C119_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'PR-07 / C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW',
            'C119 validates C118 artifact hash and file SHA1.',
            'C119 validates C118 controlled runtime wiring observation result review for operator go/no-go review only.',
            'C119 confirms C118 ConvertFrom-Json compatibility.',
            'C119 keeps C111 terminal/final-closed for the non-live handoff audit archive chain.',
            'C119 keeps C112 as a separate post-C111 production phase transition gate.',
            'C119 keeps C113 as production readiness review only.',
            'C119 keeps C114 as runtime wiring readiness review only.',
            'C119 keeps C115 as execution approval review only.',
            'C119 keeps C116 as execution review only.',
            'C119 keeps C117 as observation review only.',
            'C119 keeps C118 as observation result review only.',
            'C119 is controlled runtime wiring operator go/no-go review only.',
            'C119 records operator_go_decision=GO as artifact-only evidence.',
            'C119 is not production deployment.',
            'C119 does not mutate PLAN/CONFIRM.',
            'C119 requires --operator-approved.',
            'C119 requires non-empty --approval-reference.',
            'C119 requires --operator-go-decision-confirmed.',
            'C119 creates controlled runtime wiring operator go/no-go manifest as artifact-only.',
            'C119 creates controlled runtime wiring operator go/no-go checklist as artifact-only.',
            'C119 keeps A01 comparator-only and does not promote A01.',
            'C119 does not activate runtime bridge.',
            'C119 does not create weekly swing live output.',
            'C119 does not generate official weekly swing recommendation.',
            'C119 keeps production_ready=false.',
            'C119 keeps production_catalog_runtime_wired=false.',
            'C119 keeps production_runtime_wiring_allowed=false.',
            'C119 keeps production_runtime_wiring_executed=false.',
            'C119 keeps controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime=false.',
            'C119 keeps controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime=false.',
            'C119 operator go/no-go review means proceed to C120 controlled runtime wiring GO decision finalization review only.',
            'C119 operator go/no-go record is not an official weekly swing stock recommendation.',
            'storage/app/watchlist/backtest/c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c119_config_defaults_are_off_and_no_live_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('operator_go_no_go_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_allowed', $combined);
        $this->assertStringContainsString('production_runtime_wiring_executed', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('C119 records an operator GO decision for E02 primary and B01 backup using the locked C118 observation result review.', $combined);
    }
}
