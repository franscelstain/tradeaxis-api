<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC112StaticGuardTest extends TestCase
{
    public function test_c112_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewCommand;', $kernel);
    }

    public function test_c112_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewCommand.php');
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
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $combined, $forbidden);
        }
    }

    public function test_c112_docs_state_new_production_approval_only_and_not_live_output(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C112_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C112 validates C111 artifact hash and file SHA1.',
            'C112 validates C111 weekly swing watchlist non-live rehearsal handoff audit archive final closure state.',
            'C112 requires new --operator-approved.',
            'C112 requires non-empty new --approval-reference.',
            'C112 opens weekly swing watchlist production phase for readiness review only.',
            'C112 grants production phase approval for E02 and B01 only.',
            'C112 keeps A01 comparator-only and does not promote A01.',
            'C112 does not deploy live production.',
            'C112 does not wire production runtime.',
            'C112 does not mutate PLAN/CONFIRM.',
            'C112 does not change PLAN/CONFIRM output.',
            'C112 does not activate controlled rollout.',
            'C112 does not activate pilot runtime.',
            'C112 does not activate shadow runtime.',
            'C112 does not activate runtime bridge.',
            'C112 does not activate weekly swing watchlist runtime.',
            'C112 does not create weekly swing live output.',
            'C112 does not generate official weekly swing recommendation.',
            'C112 does not publish weekly swing output.',
            'C112 keeps production_ready=false.',
            'C112 keeps production_catalog_runtime_wired=false.',
            'C112 keeps production_runtime_wiring_allowed=false.',
            'C112 keeps production_runtime_wiring_executed=false.',
            'C112 keeps production_deployment_allowed=false.',
            'C112 keeps production_deployment_executed=false.',
            'C112 keeps plan_confirm_mutation_allowed=false.',
            'C112 keeps plan_confirm_mutated=false.',
            'C112 keeps weekly_swing_watchlist_production_phase_approval_context_persisted_to_live_runtime=false.',
            'C112 keeps production_phase_approval_context_persisted_to_live_runtime=false.',
            'C112 production phase approval review means proceed to C113 production readiness review only; it is not production deployment or live rollout.',
            'C112 production phase approval record is not an official weekly swing stock recommendation.',
            'storage/app/watchlist/backtest/c112-weekly-swing-watchlist-production-phase-approval-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c112_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_production_phase_approval_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_phase_approval_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_allowed', $combined);
        $this->assertStringContainsString('production_runtime_wiring_executed', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('C112 records a new operator-approved production phase entry for weekly swing watchlist readiness review only. It does not deploy production, wire live runtime, activate rollout, generate official weekly swing output, or mutate PLAN/CONFIRM.', $combined);
    }
}
