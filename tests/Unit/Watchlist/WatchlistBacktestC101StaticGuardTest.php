<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC101StaticGuardTest extends TestCase
{
    public function test_c101_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC101WeeklySwingWatchlistNonLiveRehearsalOperatorGoNoGoReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC101WeeklySwingWatchlistNonLiveRehearsalOperatorGoNoGoReviewCommand;', $kernel);
    }

    public function test_c101_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC101WeeklySwingWatchlistNonLiveRehearsalOperatorGoNoGoReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC101WeeklySwingWatchlistNonLiveRehearsalOperatorGoNoGoReviewCommand.php');
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

    public function test_c101_docs_state_operator_go_no_go_only_and_not_live_output(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C101_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C101 validates C100 artifact hash and file SHA1.',
            'C101 validates C100 weekly swing watchlist non-live rehearsal result review state.',
            'C101 requires --operator-approved.',
            'C101 requires non-empty --approval-reference.',
            'C101 confirms no temporary negative test artifact remains.',
            'C101 records weekly swing watchlist non-live rehearsal operator go/no-go review only.',
            'C101 records operator GO for E02 and B01 only.',
            'C101 creates artifact-only non-live rehearsal operator go/no-go manifest.',
            'C101 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C101 does not deploy live production.',
            'C101 does not mutate PLAN/CONFIRM.',
            'C101 does not change PLAN/CONFIRM output.',
            'C101 does not activate pilot runtime.',
            'C101 does not activate shadow runtime.',
            'C101 does not activate runtime bridge.',
            'C101 does not activate weekly swing watchlist runtime.',
            'C101 does not create weekly swing live output.',
            'C101 does not generate official weekly swing recommendation.',
            'C101 does not publish weekly swing output.',
            'C101 keeps production_ready=false.',
            'C101 keeps production_catalog_runtime_wired=false.',
            'C101 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C101 keeps controlled_parallel_run_active=false.',
            'C101 keeps controlled_rollout_active=false.',
            'C101 keeps weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime=false.',
            'C101 keeps weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime=false.',
            'C101 keeps weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime=false.',
            'C101 keeps weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime=false.',
            'C101 keeps operator_go_no_go_context_persisted_to_live_runtime=false.',
            'C101 keeps production_deployment_allowed=false.',
            'C101 keeps production_deployment_executed=false.',
            'C101 keeps plan_confirm_mutation_allowed=false.',
            'C101 keeps plan_confirm_mutated=false.',
            'C101 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C101 keeps live_plan_confirm_rollout_allowed=false.',
            'C101 keeps live_plan_confirm_rollout_executed=false.',
            'C101 keeps pilot_runtime_active=false.',
            'C101 keeps shadow_runtime_active=false.',
            'C101 keeps runtime_bridge_active=false.',
            'C101 keeps weekly_swing_watchlist_runtime_active=false.',
            'C101 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.',
            'C101 keeps weekly_swing_watchlist_live_output_enabled=false.',
            'C101 keeps weekly_swing_watchlist_official_output_generated=false.',
            'C101 keeps weekly_swing_watchlist_official_output_published=false.',
            'C101 keeps weekly_swing_watchlist_live_recommendation_generated=false.',
            'C101 weekly swing watchlist non-live rehearsal operator go/no-go review means continue to C102 weekly swing watchlist non-live rehearsal go decision finalization review only.',
            'C101 GO is not production deployment.',
            'C101 GO is not PLAN/CONFIRM live rollout.',
            'C101 GO is not runtime bridge activation.',
            'C101 GO is not weekly swing live output.',
            'storage/app/watchlist/backtest/c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c101_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC101WeeklySwingWatchlistNonLiveRehearsalOperatorGoNoGoReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC101WeeklySwingWatchlistNonLiveRehearsalOperatorGoNoGoReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('operator_go_no_go_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_official_output_generated', $combined);
        $this->assertStringContainsString('C101 weekly swing watchlist non-live rehearsal operator go/no-go review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.', $combined);
    }
}
