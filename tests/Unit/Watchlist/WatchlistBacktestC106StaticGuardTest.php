<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC106StaticGuardTest extends TestCase
{
    public function test_c106_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewCommand;', $kernel);
    }

    public function test_c106_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewCommand.php');
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

    public function test_c106_docs_state_handoff_completion_boundary_only_and_not_live_output(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C106_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C106 validates C105 artifact hash and file SHA1.',
            'C106 validates C105 weekly swing watchlist non-live rehearsal handoff finalization state.',
            'C106 requires --operator-approved.',
            'C106 requires non-empty --approval-reference.',
            'C106 confirms no temporary negative test artifact remains.',
            'C106 clears weekly swing watchlist non-live rehearsal handoff completion boundary only.',
            'C106 clears handoff completion boundary for E02 and B01 only.',
            'C106 creates artifact-only non-live rehearsal handoff completion boundary manifest.',
            'C106 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C106 does not deploy live production.',
            'C106 does not mutate PLAN/CONFIRM.',
            'C106 does not change PLAN/CONFIRM output.',
            'C106 does not activate pilot runtime.',
            'C106 does not activate shadow runtime.',
            'C106 does not activate runtime bridge.',
            'C106 does not activate weekly swing watchlist runtime.',
            'C106 does not create weekly swing live output.',
            'C106 does not generate official weekly swing recommendation.',
            'C106 does not publish weekly swing output.',
            'C106 keeps production_ready=false.',
            'C106 keeps production_catalog_runtime_wired=false.',
            'C106 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C106 keeps controlled_parallel_run_active=false.',
            'C106 keeps controlled_rollout_active=false.',
            'C106 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_context_persisted_to_live_runtime=false.',
            'C106 keeps handoff_completion_boundary_context_persisted_to_live_runtime=false.',
            'C106 keeps production_deployment_allowed=false.',
            'C106 keeps production_deployment_executed=false.',
            'C106 keeps plan_confirm_mutation_allowed=false.',
            'C106 keeps plan_confirm_mutated=false.',
            'C106 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C106 keeps live_plan_confirm_rollout_allowed=false.',
            'C106 keeps live_plan_confirm_rollout_executed=false.',
            'C106 keeps pilot_runtime_active=false.',
            'C106 keeps shadow_runtime_active=false.',
            'C106 keeps runtime_bridge_active=false.',
            'C106 keeps weekly_swing_watchlist_runtime_active=false.',
            'C106 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.',
            'C106 keeps weekly_swing_watchlist_live_output_enabled=false.',
            'C106 keeps weekly_swing_watchlist_official_output_generated=false.',
            'C106 keeps weekly_swing_watchlist_official_output_published=false.',
            'C106 keeps weekly_swing_watchlist_live_recommendation_generated=false.',
            'C106 weekly swing watchlist non-live rehearsal handoff completion boundary review means continue to C107 weekly swing watchlist non-live rehearsal handoff closure seal review only.',
            'C106 handoff completion boundary record is not production deployment.',
            'C106 handoff completion boundary record is not PLAN/CONFIRM live rollout.',
            'C106 handoff completion boundary record is not runtime bridge activation.',
            'C106 handoff completion boundary record is not weekly swing live output.',
            'storage/app/watchlist/backtest/c106-weekly-swing-watchlist-non-live-rehearsal-handoff-completion-boundary-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c106_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('handoff_completion_boundary_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_official_output_generated', $combined);
        $this->assertStringContainsString('C106 weekly swing watchlist non-live rehearsal handoff completion boundary review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.', $combined);
    }
}
