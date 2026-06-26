<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC83StaticGuardTest extends TestCase
{
    public function test_c83_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewCommand;', $kernel);
    }

    public function test_c83_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC83ControlledLimitedRuntimeOptInPilotOrShadowRolloutActivationAuthorizationReviewCommand.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedActivationAuthorizationReviewContract.php');
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

    public function test_c83_docs_state_authorization_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C83_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C83 is controlled limited runtime opt-in pilot / shadow rollout activation authorization review.',
            'C83 starts from locked C82 final evidence.',
            'C82 pre-activation boundary review passed boundary clearance for primary + backup.',
            'E02 is primary activation-authorized candidate.',
            'B01 is backup activation-authorized candidate.',
            'A01 is comparator-only and cannot be promoted.',
            'C83 validates C82 artifact hash and file SHA1.',
            'C83 validates C82 readiness through nested next_readiness_decision.* path.',
            'C83 validates C82 -> C60 lineage.',
            'C83 requires --operator-approved.',
            'C83 requires non-empty --approval-reference.',
            'C83 records activation authorization only.',
            'C83 does not execute activation.',
            'C83 does not redesign.',
            'C83 does not retune.',
            'C83 does not run parameter search.',
            'C83 does not use OOS to rerank.',
            'C83 does not use activation authorization to rerank.',
            'C83 does not use activation authorization to deploy.',
            'C83 does not change candidate scope.',
            'C83 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C83 does not deploy live production.',
            'C83 does not mutate PLAN/CONFIRM.',
            'C83 does not change PLAN/CONFIRM output.',
            'C83 keeps activation_executed=false.',
            'C83 keeps production_catalog_runtime_wired=false.',
            'C83 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C83 keeps controlled_parallel_run_active=false.',
            'C83 keeps controlled_rollout_active=false.',
            'C83 keeps activation_authorization_context_persisted_to_live_runtime=false.',
            'C83 keeps production_deployment_allowed=false.',
            'C83 keeps production_deployment_executed=false.',
            'C83 keeps plan_confirm_mutation_allowed=false.',
            'C83 keeps plan_confirm_mutated=false.',
            'C83 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C83 keeps live_plan_confirm_rollout_allowed=false.',
            'C83 keeps live_plan_confirm_rollout_executed=false.',
            'C83 activation authorization means continue to C84 activation execution review only.',
            'C83 activation authorization is not activation execution.',
            'C83 activation authorization is not production deployment.',
            'C83 activation authorization is not PLAN/CONFIRM live rollout.',
            'C83 activation authorization is not runtime bridge activation.',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c83_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedActivationAuthorizationReviewContract.php');
        $context = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedActivationAuthorizationReviewContext.php');
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
        $this->assertStringContainsString('ACTIVATION_AUTHORIZED_ARTIFACT_ONLY = true', $combined);
        $this->assertStringContainsString('ACTIVATION_EXECUTED = false', $combined);
        $this->assertStringContainsString('PERSISTED_TO_LIVE_RUNTIME = false', $combined);
        $this->assertStringContainsString('MUTATES_PLAN_CONFIRM = false', $combined);
        $this->assertStringContainsString('EXECUTES_ACTIVATION = false', $combined);
        $this->assertStringContainsString('ACTIVATES_RUNTIME_BRIDGE = false', $combined);
        $this->assertStringContainsString('ACTIVATES_CONTROLLED_ROLLOUT = false', $combined);
    }
}
