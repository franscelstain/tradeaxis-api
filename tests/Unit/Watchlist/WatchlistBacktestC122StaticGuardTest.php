<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC122StaticGuardTest extends TestCase
{
    public function test_c122_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewCommand;', $kernel);
    }

    public function test_c122_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewCommand.php');
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

    public function test_c122_docs_state_pr10_handoff_readiness_only_and_not_live_runtime(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C122_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'PR-10 / C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW',
            'C122 validates C121 artifact hash and file SHA1.',
            'C122 validates C121 controlled runtime wiring completion boundary for handoff readiness review only.',
            'C122 confirms C121 ConvertFrom-Json compatibility.',
            'C122 keeps C121 as completion boundary review only.',
            'C122 is controlled runtime wiring handoff readiness review only.',
            'C122 records handoff_ready=1 as artifact-only evidence.',
            'C122 records handoff_readiness_confirmed=1 as artifact-only evidence.',
            'C122 is not production deployment.',
            'C122 does not mutate PLAN/CONFIRM.',
            'C122 requires --operator-approved.',
            'C122 requires non-empty --approval-reference.',
            'C122 requires --handoff-readiness-confirmed.',
            'C122 creates controlled runtime wiring handoff readiness manifest as artifact-only.',
            'C122 creates controlled runtime wiring handoff readiness checklist as artifact-only.',
            'C122 keeps A01 comparator-only and does not promote A01.',
            'C122 does not activate runtime bridge.',
            'C122 does not create weekly swing live output.',
            'C122 does not generate official weekly swing recommendation.',
            'C122 keeps production_ready=false.',
            'C122 keeps production_catalog_runtime_wired=false.',
            'C122 keeps production_runtime_wiring_allowed=false.',
            'C122 keeps production_runtime_wiring_executed=false.',
            'C122 keeps controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime=false.',
            'C122 handoff readiness review means continue to C123 controlled runtime wiring handoff finalization review only.',
            'C122 handoff readiness record is not an official weekly swing stock recommendation.',
            'storage/app/watchlist/backtest/c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c122_config_defaults_are_off_and_no_live_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('controlled_runtime_wiring_completion_boundary_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_runtime_wiring_allowed', $combined);
        $this->assertStringContainsString('production_runtime_wiring_executed', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('weekly_swing_watchlist_live_output_enabled', $combined);
        $this->assertStringContainsString('C122 marks the controlled runtime wiring handoff package ready for E02 primary and B01 backup.', $combined);
    }
}
