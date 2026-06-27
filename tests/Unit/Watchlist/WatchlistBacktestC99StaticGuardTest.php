<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC99StaticGuardTest extends TestCase
{
    public function test_c99_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewCommand;', $kernel);
    }

    public function test_c99_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewCommand.php');
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

    public function test_c99_docs_state_non_live_rehearsal_execution_only_and_not_live_output(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C99_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C99 validates C98 artifact hash and file SHA1.',
            'C99 validates C98 weekly swing watchlist non-live rehearsal ready state.',
            'C99 requires --operator-approved.',
            'C99 requires non-empty --approval-reference.',
            'C99 confirms no temporary negative test artifact remains.',
            'C99 records weekly swing watchlist non-live rehearsal execution review only.',
            'C99 creates artifact-only non-live rehearsal execution manifest.',
            'C99 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C99 does not deploy live production.',
            'C99 does not mutate PLAN/CONFIRM.',
            'C99 does not change PLAN/CONFIRM output.',
            'C99 does not activate pilot runtime.',
            'C99 does not activate shadow runtime.',
            'C99 does not activate runtime bridge.',
            'C99 does not activate weekly swing watchlist runtime.',
            'C99 does not create weekly swing live output.',
            'C99 does not generate official weekly swing recommendation.',
            'C99 does not publish weekly swing output.',
            'C99 keeps production_ready=false.',
            'C99 keeps production_catalog_runtime_wired=false.',
            'C99 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C99 keeps controlled_parallel_run_active=false.',
            'C99 keeps controlled_rollout_active=false.',
            'C99 keeps weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime=false.',
            'C99 keeps weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime=false.',
            'C99 keeps production_deployment_allowed=false.',
            'C99 keeps production_deployment_executed=false.',
            'C99 keeps plan_confirm_mutation_allowed=false.',
            'C99 keeps plan_confirm_mutated=false.',
            'C99 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C99 keeps live_plan_confirm_rollout_allowed=false.',
            'C99 keeps live_plan_confirm_rollout_executed=false.',
            'C99 keeps pilot_runtime_active=false.',
            'C99 keeps shadow_runtime_active=false.',
            'C99 keeps runtime_bridge_active=false.',
            'C99 keeps weekly_swing_watchlist_runtime_active=false.',
            'C99 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.',
            'C99 keeps weekly_swing_watchlist_live_output_enabled=false.',
            'C99 keeps weekly_swing_watchlist_official_output_generated=false.',
            'C99 keeps weekly_swing_watchlist_official_output_published=false.',
            'C99 keeps weekly_swing_watchlist_live_recommendation_generated=false.',
            'C99 weekly swing watchlist non-live rehearsal execution review means continue to C100 weekly swing watchlist non-live rehearsal result review only.',
            'C99 weekly swing watchlist non-live rehearsal execution review is not production deployment.',
            'C99 weekly swing watchlist non-live rehearsal execution review is not PLAN/CONFIRM live rollout.',
            'C99 weekly swing watchlist non-live rehearsal execution review is not runtime bridge activation.',
            'C99 weekly swing watchlist non-live rehearsal execution review is not weekly swing live output.',
            'storage/app/watchlist/backtest/c99-weekly-swing-watchlist-non-live-rehearsal-execution-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c99_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_official_output_generated', $combined);
        $this->assertStringContainsString('C99 weekly swing watchlist non-live rehearsal execution review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.', $combined);
    }
}
