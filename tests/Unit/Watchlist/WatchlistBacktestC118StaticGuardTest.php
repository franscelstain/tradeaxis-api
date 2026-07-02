<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC118StaticGuardTest extends TestCase
{
    public function test_c118_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC118WeeklySwingWatchlistControlledRuntimeWiringObservationResultReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC118WeeklySwingWatchlistControlledRuntimeWiringObservationResultReviewCommand;', $kernel);
    }

    public function test_c118_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC118WeeklySwingWatchlistControlledRuntimeWiringObservationResultReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC118WeeklySwingWatchlistControlledRuntimeWiringObservationResultReviewCommand.php');
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

    public function test_c118_docs_state_pr06_observation_result_review_only_and_not_live_runtime(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C118_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'PR-06 / C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW',
            'C118 validates C117 artifact hash and file SHA1.',
            'C118 validates C117 controlled runtime wiring observation review for observation result review only.',
            'C118 confirms C117 ConvertFrom-Json compatibility.',
            'C118 keeps C111 terminal/final-closed for the non-live handoff audit archive chain.',
            'C118 keeps C112 as a separate post-C111 production phase transition gate.',
            'C118 keeps C113 as production readiness review only.',
            'C118 keeps C114 as runtime wiring readiness review only.',
            'C118 keeps C115 as execution approval review only.',
            'C118 keeps C116 as execution review only.',
            'C118 keeps C117 as observation review only.',
            'C118 is controlled runtime wiring observation result review only.',
            'C118 is not production deployment.',
            'C118 does not mutate PLAN/CONFIRM.',
            'C118 requires --operator-approved.',
            'C118 requires non-empty --approval-reference.',
            'C118 creates controlled runtime wiring observation result review manifest as artifact-only.',
            'C118 creates controlled runtime wiring observation result review checklist as artifact-only.',
            'C118 keeps A01 comparator-only and does not promote A01.',
            'C118 does not activate runtime bridge.',
            'C118 does not create weekly swing live output.',
            'C118 does not generate official weekly swing recommendation.',
            'C118 keeps production_ready=false.',
            'C118 keeps production_catalog_runtime_wired=false.',
            'C118 keeps production_runtime_wiring_allowed=false.',
            'C118 keeps production_runtime_wiring_executed=false.',
            'C118 keeps controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime=false.',
            'C118 keeps controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime=false.',
            'C118 keeps controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime=false.',
            'C118 observation result review means proceed to C119 controlled runtime wiring operator go/no-go review only.',
            'C118 observation result review record is not an official weekly swing stock recommendation.',
            'storage/app/watchlist/backtest/c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c118_config_defaults_are_off_and_no_live_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC118WeeklySwingWatchlistControlledRuntimeWiringObservationResultReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC118WeeklySwingWatchlistControlledRuntimeWiringObservationResultReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_observation_review_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_execution_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_allowed', $combined);
        $this->assertStringContainsString('production_runtime_wiring_executed', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('C118 reviews the C117 controlled runtime wiring observation result for E02 primary and B01 backup in artifact-only, non-live, non-mutating context.', $combined);
    }
}
