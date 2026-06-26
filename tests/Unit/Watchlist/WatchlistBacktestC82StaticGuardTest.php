<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC82StaticGuardTest extends TestCase
{
    public function test_c82_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewCommand;', $kernel);
    }

    public function test_c82_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC82ControlledLimitedRuntimeOptInPilotOrShadowRolloutPreActivationBoundaryReviewCommand.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPreActivationBoundaryReviewContract.php');
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

    public function test_c82_docs_state_boundary_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C82_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C82 is controlled limited runtime opt-in pilot / shadow rollout pre-activation boundary review.',
            'C82 starts from locked C81 final evidence.',
            'C81 GO decision finalization review passed finalized GO for primary + backup.',
            'E02 is primary pre-activation boundary-cleared candidate.',
            'B01 is backup pre-activation boundary-cleared candidate.',
            'A01 is comparator-only and cannot be promoted.',
            'C82 validates C81 artifact hash and file SHA1.',
            'C82 validates C81 readiness through nested next_readiness_decision.* path.',
            'C82 validates C81 -> C60 lineage.',
            'C82 requires --operator-approved.',
            'C82 requires non-empty --approval-reference.',
            'C82 clears pre-activation boundary only.',
            'C82 does not authorize activation.',
            'C82 does not redesign.',
            'C82 does not retune.',
            'C82 does not run parameter search.',
            'C82 does not use OOS to rerank.',
            'C82 does not use boundary clearance to rerank.',
            'C82 does not use boundary clearance to deploy.',
            'C82 does not change candidate scope.',
            'C82 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C82 does not deploy live production.',
            'C82 does not mutate PLAN/CONFIRM.',
            'C82 does not change PLAN/CONFIRM output.',
            'C82 keeps activation_authorized=false.',
            'C82 keeps production_catalog_runtime_wired=false.',
            'C82 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C82 keeps controlled_parallel_run_active=false.',
            'C82 keeps controlled_rollout_active=false.',
            'C82 keeps pre_activation_boundary_context_persisted_to_live_runtime=false.',
            'C82 keeps production_deployment_allowed=false.',
            'C82 keeps production_deployment_executed=false.',
            'C82 keeps plan_confirm_mutation_allowed=false.',
            'C82 keeps plan_confirm_mutated=false.',
            'C82 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C82 keeps live_plan_confirm_rollout_allowed=false.',
            'C82 keeps live_plan_confirm_rollout_executed=false.',
            'C82 boundary clearance means continue to C83 activation authorization review only.',
            'C82 boundary clearance is not activation authorization.',
            'C82 boundary clearance is not production deployment.',
            'C82 boundary clearance is not PLAN/CONFIRM live rollout.',
            'C82 boundary clearance is not runtime bridge activation.',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c82_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPreActivationBoundaryReviewContract.php');
        $context = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPreActivationBoundaryReviewContext.php');
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
        $this->assertStringContainsString('ACTIVATION_AUTHORIZED = false', $combined);
        $this->assertStringContainsString('PERSISTED_TO_LIVE_RUNTIME = false', $combined);
        $this->assertStringContainsString('MUTATES_PLAN_CONFIRM = false', $combined);
        $this->assertStringContainsString('AUTHORIZES_ACTIVATION = false', $combined);
        $this->assertStringContainsString('ACTIVATES_RUNTIME_BRIDGE = false', $combined);
        $this->assertStringContainsString('ACTIVATES_CONTROLLED_ROLLOUT = false', $combined);
    }
}
