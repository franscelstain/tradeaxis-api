<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC113StaticGuardTest extends TestCase
{
    public function test_c113_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC113WeeklySwingWatchlistProductionReadinessReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC113WeeklySwingWatchlistProductionReadinessReviewCommand;', $kernel);
    }

    public function test_c113_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC113WeeklySwingWatchlistProductionReadinessReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC113WeeklySwingWatchlistProductionReadinessReviewCommand.php');
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

    public function test_c113_docs_state_pr01_production_readiness_only_and_not_live_output(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C113_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'PR-01 / C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW',
            'C113 validates C112 artifact hash and file SHA1.',
            'C113 validates C112 production phase approval for readiness review only.',
            'C113 keeps C111 terminal/final-closed for the non-live handoff audit archive chain.',
            'C113 keeps C112 as a separate post-C111 production phase transition gate.',
            'C113 is not audit archive continuation.',
            'C113 does not reopen C111 final closure.',
            'C113 requires --operator-approved.',
            'C113 requires non-empty --approval-reference.',
            'C113 confirms no temporary negative test artifact remains.',
            'C113 creates production readiness review manifest as artifact-only.',
            'C113 creates production readiness checklist as artifact-only.',
            'C113 keeps A01 comparator-only and does not promote A01.',
            'C113 does not deploy live production.',
            'C113 does not wire production runtime.',
            'C113 does not mutate PLAN/CONFIRM.',
            'C113 does not activate controlled rollout.',
            'C113 does not activate pilot runtime.',
            'C113 does not activate shadow runtime.',
            'C113 does not activate runtime bridge.',
            'C113 does not activate weekly swing watchlist runtime.',
            'C113 does not create weekly swing live output.',
            'C113 does not generate official weekly swing recommendation.',
            'C113 does not publish weekly swing output.',
            'C113 keeps production_ready=false.',
            'C113 keeps production_catalog_runtime_wired=false.',
            'C113 keeps production_runtime_wiring_allowed=false.',
            'C113 keeps production_runtime_wiring_executed=false.',
            'C113 keeps production_deployment_allowed=false.',
            'C113 keeps production_deployment_executed=false.',
            'C113 keeps plan_confirm_mutation_allowed=false.',
            'C113 keeps plan_confirm_mutated=false.',
            'C113 keeps production_readiness_context_persisted_to_live_runtime=false.',
            'C113 production readiness review means proceed to C114 controlled production runtime wiring readiness review only.',
            'C113 production readiness record is not an official weekly swing stock recommendation.',
            'storage/app/watchlist/backtest/c113-weekly-swing-watchlist-production-readiness-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c113_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC113WeeklySwingWatchlistProductionReadinessReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC113WeeklySwingWatchlistProductionReadinessReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('weekly_swing_watchlist_production_readiness_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_readiness_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_allowed', $combined);
        $this->assertStringContainsString('production_runtime_wiring_executed', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('C113 completes PR-01 weekly swing watchlist production readiness review for E02 primary and B01 backup in review-only, non-live, non-mutating context.', $combined);
    }
}
