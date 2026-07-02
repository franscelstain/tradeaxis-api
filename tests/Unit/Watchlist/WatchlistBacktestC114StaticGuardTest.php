<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC114StaticGuardTest extends TestCase
{
    public function test_c114_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC114WeeklySwingWatchlistProductionRuntimeWiringReadinessReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC114WeeklySwingWatchlistProductionRuntimeWiringReadinessReviewCommand;', $kernel);
    }

    public function test_c114_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC114WeeklySwingWatchlistProductionRuntimeWiringReadinessReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC114WeeklySwingWatchlistProductionRuntimeWiringReadinessReviewCommand.php');
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

    public function test_c114_docs_state_pr02_runtime_wiring_readiness_only_and_not_live_output(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C114_WEEKLY_SWING_WATCHLIST_PRODUCTION_RUNTIME_WIRING_READINESS_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C114_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'PR-02 / C114_WEEKLY_SWING_WATCHLIST_PRODUCTION_RUNTIME_WIRING_READINESS_REVIEW',
            'C114 validates C113 artifact hash and file SHA1.',
            'C114 validates C113 production readiness review for runtime wiring readiness review only.',
            'C114 confirms C113 ConvertFrom-Json compatibility.',
            'C114 keeps C111 terminal/final-closed for the non-live handoff audit archive chain.',
            'C114 keeps C112 as a separate post-C111 production phase transition gate.',
            'C114 keeps C113 as production readiness review only.',
            'C114 is not audit archive continuation.',
            'C114 does not reopen C111 final closure.',
            'C114 requires --operator-approved.',
            'C114 requires non-empty --approval-reference.',
            'C114 confirms no temporary negative test artifact remains.',
            'C114 creates production runtime wiring readiness review manifest as artifact-only.',
            'C114 creates production runtime wiring readiness checklist as artifact-only.',
            'C114 keeps A01 comparator-only and does not promote A01.',
            'C114 does not deploy live production.',
            'C114 does not execute production runtime wiring.',
            'C114 does not wire production runtime.',
            'C114 does not mutate PLAN/CONFIRM.',
            'C114 does not activate controlled rollout.',
            'C114 does not activate pilot runtime.',
            'C114 does not activate shadow runtime.',
            'C114 does not activate runtime bridge.',
            'C114 does not activate weekly swing watchlist runtime.',
            'C114 does not create weekly swing live output.',
            'C114 does not generate official weekly swing recommendation.',
            'C114 does not publish weekly swing output.',
            'C114 keeps production_ready=false.',
            'C114 keeps production_catalog_runtime_wired=false.',
            'C114 keeps production_runtime_wiring_allowed=false.',
            'C114 keeps production_runtime_wiring_executed=false.',
            'C114 keeps production_deployment_allowed=false.',
            'C114 keeps production_deployment_executed=false.',
            'C114 keeps plan_confirm_mutation_allowed=false.',
            'C114 keeps plan_confirm_mutated=false.',
            'C114 keeps production_runtime_wiring_readiness_context_persisted_to_live_runtime=false.',
            'C114 keeps production_runtime_wiring_context_persisted_to_live_runtime=false.',
            'C114 runtime wiring readiness review means proceed to C115 controlled runtime wiring execution approval review only.',
            'C114 runtime wiring readiness record is not an official weekly swing stock recommendation.',
            'storage/app/watchlist/backtest/c114-weekly-swing-watchlist-production-runtime-wiring-readiness-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c114_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC114WeeklySwingWatchlistProductionRuntimeWiringReadinessReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC114WeeklySwingWatchlistProductionRuntimeWiringReadinessReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_production_runtime_wiring_readiness_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_readiness_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_allowed', $combined);
        $this->assertStringContainsString('production_runtime_wiring_executed', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('C114 completes PR-02 weekly swing watchlist production runtime wiring readiness review for E02 primary and B01 backup in review-only, non-live, non-mutating context.', $combined);
    }
}
