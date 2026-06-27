<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC92StaticGuardTest extends TestCase
{
    public function test_c92_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewCommand;', $kernel);
    }

    public function test_c92_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewCommand.php');
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

    public function test_c92_docs_state_completion_boundary_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C92_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C92 validates C91 artifact hash and file SHA1.',
            'C92 validates C91 readiness through nested next_readiness_decision.* path.',
            'C92 validates C91 -> C60 lineage.',
            'C92 requires --operator-approved.',
            'C92 requires non-empty --approval-reference.',
            'C92 clears post-activation handoff completion boundary only.',
            'C92 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C92 does not deploy live production.',
            'C92 does not mutate PLAN/CONFIRM.',
            'C92 does not change PLAN/CONFIRM output.',
            'C92 keeps production_catalog_runtime_wired=false.',
            'C92 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C92 keeps controlled_parallel_run_active=false.',
            'C92 keeps controlled_rollout_active=false.',
            'C92 keeps post_activation_handoff_completion_boundary_context_persisted_to_live_runtime=false.',
            'C92 keeps production_deployment_allowed=false.',
            'C92 keeps production_deployment_executed=false.',
            'C92 keeps plan_confirm_mutation_allowed=false.',
            'C92 keeps plan_confirm_mutated=false.',
            'C92 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C92 keeps live_plan_confirm_rollout_allowed=false.',
            'C92 keeps live_plan_confirm_rollout_executed=false.',
            'C92 post-activation handoff completion boundary means continue to C93 post-activation handoff closure seal review only.',
            'C92 post-activation handoff completion boundary record is not production deployment.',
            'C92 post-activation handoff completion boundary record is not PLAN/CONFIRM live rollout.',
            'C92 post-activation handoff completion boundary record is not runtime bridge activation.',
            'storage/app/watchlist/backtest/c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review.json',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c92_config_defaults_are_off_and_no_production_runtime_wiring_is_created(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewCommand.php');
        $combined = $service."\n".$command;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('post_activation_handoff_completion_boundary_context_persisted_to_live_runtime', $combined);
        $this->assertStringContainsString('production_catalog_runtime_wired', $combined);
        $this->assertStringContainsString('controlled_rollout_active', $combined);
        $this->assertStringContainsString('plan_confirm_runtime_reads_activated_catalog', $combined);
        $this->assertStringContainsString('live_plan_confirm_rollout_executed', $combined);
        $this->assertStringContainsString('C92 handoff completion boundary is not production deployment, not live rollout, not runtime bridge activation, and not PLAN/CONFIRM mutation.', $combined);
    }
}
