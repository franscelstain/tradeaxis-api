<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC107StaticGuardTest extends TestCase
{
    public function test_c107_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC107WeeklySwingWatchlistNonLiveRehearsalHandoffClosureSealReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC107WeeklySwingWatchlistNonLiveRehearsalHandoffClosureSealReviewCommand;', $kernel);
    }

    public function test_c107_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC107WeeklySwingWatchlistNonLiveRehearsalHandoffClosureSealReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC107WeeklySwingWatchlistNonLiveRehearsalHandoffClosureSealReviewCommand.php');
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

    public function test_c107_docs_state_handoff_closure_seal_only_and_not_live_output(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C107_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C107 validates C106 artifact hash and file SHA1.',
            'C107 validates C106 weekly swing watchlist non-live rehearsal handoff completion boundary state.',
            'C107 requires --operator-approved.',
            'C107 requires non-empty --approval-reference.',
            'C107 confirms no temporary negative test artifact remains.',
            'C107 seals weekly swing watchlist non-live rehearsal handoff closure only.',
            'C107 seals handoff closure for E02 and B01 only.',
            'C107 creates artifact-only non-live rehearsal handoff closure seal manifest.',
            'C107 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C107 does not deploy live production.',
            'C107 does not mutate PLAN/CONFIRM.',
            'C107 does not change PLAN/CONFIRM output.',
            'C107 does not activate pilot runtime.',
            'C107 does not activate shadow runtime.',
            'C107 does not activate runtime bridge.',
            'C107 does not activate weekly swing watchlist runtime.',
            'C107 does not create weekly swing live output.',
            'C107 does not generate official weekly swing recommendation.',
            'C107 does not publish weekly swing output.',
            'C107 keeps production_ready=false.',
            'C107 keeps production_catalog_runtime_wired=false.',
            'C107 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C107 keeps controlled_parallel_run_active=false.',
            'C107 keeps controlled_rollout_active=false.',
            'C107 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_persisted_to_live_runtime=false.',
            'C107 keeps handoff_closure_seal_context_persisted_to_live_runtime=false.',
            'C107 keeps production_deployment_allowed=false.',
            'C107 keeps production_deployment_executed=false.',
            'C107 keeps plan_confirm_mutation_allowed=false.',
            'C107 keeps plan_confirm_mutated=false.',
            'C107 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C107 keeps live_plan_confirm_rollout_allowed=false.',
            'C107 keeps live_plan_confirm_rollout_executed=false.',
            'C107 keeps pilot_runtime_active=false.',
            'C107 keeps shadow_runtime_active=false.',
            'C107 keeps runtime_bridge_active=false.',
            'C107 keeps weekly_swing_watchlist_runtime_active=false.',
            'C107 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.',
            'C107 keeps weekly_swing_watchlist_live_output_enabled=false.',
            'C107 keeps weekly_swing_watchlist_official_output_generated=false.',
            'C107 keeps weekly_swing_watchlist_official_output_published=false.',
            'C107 keeps weekly_swing_watchlist_live_recommendation_generated=false.',
            'C107 weekly swing watchlist non-live rehearsal handoff closure seal review means continue to C108 weekly swing watchlist non-live rehearsal handoff audit archive review only.',
            'C107 handoff closure seal record is not production deployment.',
            'C107 handoff closure seal record is not PLAN/CONFIRM live rollout.',
            'C107 handoff closure seal record is not runtime bridge activation.',
            'C107 handoff closure seal record is not weekly swing live output.',
            'storage/app/watchlist/backtest/c107-weekly-swing-watchlist-non-live-rehearsal-handoff-closure-seal-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c107_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC107WeeklySwingWatchlistNonLiveRehearsalHandoffClosureSealReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC107WeeklySwingWatchlistNonLiveRehearsalHandoffClosureSealReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('handoff_closure_seal_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_official_output_generated', $combined);
        $this->assertStringContainsString('C107 weekly swing watchlist non-live rehearsal handoff closure seal review is not production deployment, not live rollout, not runtime bridge activation, not pilot/shadow runtime, not weekly swing live output, and not PLAN/CONFIRM mutation.', $combined);
    }
}
