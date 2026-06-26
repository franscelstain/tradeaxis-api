<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC84StaticGuardTest extends TestCase
{
    public function test_c84_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewCommand;', $kernel);
    }

    public function test_c84_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC84ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationExecutionReviewCommand.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedActivationExecutionReviewContract.php');
        $combined = $service."\n".$command."\n".$contract;

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

    public function test_c84_docs_state_execution_record_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C84_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C84 is controlled limited runtime opt-in pilot / shadow rollout activation execution review.',
            'C84 starts from locked C83 final evidence.',
            'C83 activation authorization review passed authorization for primary + backup.',
            'E02 is primary activation execution candidate.',
            'B01 is backup activation execution candidate.',
            'A01 is comparator-only and cannot be promoted.',
            'C84 validates C83 artifact hash and file SHA1.',
            'C84 validates C83 readiness through nested next_readiness_decision.* path.',
            'C84 validates C83 -> C60 lineage.',
            'C84 requires --operator-approved.',
            'C84 requires non-empty --approval-reference.',
            'C84 creates controlled activation execution record only.',
            'C84 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C84 does not deploy live production.',
            'C84 does not mutate PLAN/CONFIRM.',
            'C84 does not change PLAN/CONFIRM output.',
            'C84 keeps production_catalog_runtime_wired=false.',
            'C84 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C84 keeps controlled_parallel_run_active=false.',
            'C84 keeps controlled_rollout_active=false.',
            'C84 keeps activation_execution_context_persisted_to_live_runtime=false.',
            'C84 keeps production_deployment_allowed=false.',
            'C84 keeps production_deployment_executed=false.',
            'C84 keeps plan_confirm_mutation_allowed=false.',
            'C84 keeps plan_confirm_mutated=false.',
            'C84 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C84 keeps live_plan_confirm_rollout_allowed=false.',
            'C84 keeps live_plan_confirm_rollout_executed=false.',
            'C84 activation execution means continue to C85 post-activation observation review only.',
            'C84 activation execution record is not production deployment.',
            'C84 activation execution record is not PLAN/CONFIRM live rollout.',
            'C84 activation execution record is not runtime bridge activation.',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c84_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedActivationExecutionReviewContract.php');
        $context = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedActivationExecutionReviewContext.php');
        $combined = $contract."\n".$context;

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_runtime_opt_in_pilot_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_shadow_rollout_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('DEFAULT_ENABLED = false', $combined);
        $this->assertStringContainsString('DEFAULT_CONTROLLED_RUNTIME_OPT_IN_PILOT_ENABLED = false', $combined);
        $this->assertStringContainsString('DEFAULT_CONTROLLED_SHADOW_ROLLOUT_ENABLED = false', $combined);
        $this->assertStringContainsString('DEFAULT_KILL_SWITCH = false', $combined);
        $this->assertStringContainsString('intentionally not consumed by PLAN/CONFIRM runtime services', $combined);
        $this->assertStringContainsString('REQUIRED_OPERATOR_OPTION = \'--operator-approved\'', $combined);
        $this->assertStringContainsString('REQUIRED_APPROVAL_REFERENCE_OPTION = \'--approval-reference\'', $combined);
        $this->assertStringContainsString('ACTIVATION_EXECUTION_REVIEW_ONLY = true', $combined);
        $this->assertStringContainsString('CONTROLLED_ACTIVATION_RECORD_ONLY = true', $combined);
        $this->assertStringContainsString('ARTIFACT_ONLY = true', $combined);
        $this->assertStringContainsString('PERSISTED_TO_LIVE_RUNTIME = false', $combined);
        $this->assertStringContainsString('MUTATES_PLAN_CONFIRM = false', $combined);
        $this->assertStringContainsString('WIRES_DEFAULT_RUNTIME = false', $combined);
        $this->assertStringContainsString('ACTIVATES_RUNTIME_BRIDGE = false', $combined);
        $this->assertStringContainsString('ACTIVATES_CONTROLLED_ROLLOUT = false', $combined);
        $this->assertStringContainsString('DEPLOYS_PRODUCTION = false', $combined);
    }
}
