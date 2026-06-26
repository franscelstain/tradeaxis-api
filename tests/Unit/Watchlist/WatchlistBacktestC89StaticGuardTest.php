<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC89StaticGuardTest extends TestCase
{
    public function test_c89_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC89ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationCompletionBoundaryReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC89ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationCompletionBoundaryReviewCommand;', $kernel);
    }

    public function test_c89_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC89ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationCompletionBoundaryReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC89ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationCompletionBoundaryReviewCommand.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationCompletionBoundaryReviewContract.php');
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

    public function test_c89_docs_state_completion_boundary_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C89_OPERATOR_VALIDATION_COMMANDS.md');
        $status = (string) file_get_contents('docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = (string) file_get_contents('docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md');
        $governance = (string) file_get_contents('docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md');
        $combined = $doc."\n".$commands."\n".$status."\n".$tracker."\n".$governance;

        foreach ([
            'C89 validates C88 artifact hash and file SHA1.',
            'C89 validates C88 readiness through nested next_readiness_decision.* path.',
            'C89 validates C88 -> C60 lineage.',
            'C89 requires --operator-approved.',
            'C89 requires non-empty --approval-reference.',
            'C89 clears post-activation completion boundary only.',
            'C89 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C89 does not deploy live production.',
            'C89 does not mutate PLAN/CONFIRM.',
            'C89 does not change PLAN/CONFIRM output.',
            'C89 keeps production_catalog_runtime_wired=false.',
            'C89 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C89 keeps controlled_parallel_run_active=false.',
            'C89 keeps controlled_rollout_active=false.',
            'C89 keeps post_activation_completion_boundary_context_persisted_to_live_runtime=false.',
            'C89 keeps production_deployment_allowed=false.',
            'C89 keeps production_deployment_executed=false.',
            'C89 keeps plan_confirm_mutation_allowed=false.',
            'C89 keeps plan_confirm_mutated=false.',
            'C89 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C89 keeps live_plan_confirm_rollout_allowed=false.',
            'C89 keeps live_plan_confirm_rollout_executed=false.',
            'C89 post-activation completion boundary means continue to C90 post-activation handoff readiness review only.',
            'C89 post-activation completion boundary record is not production deployment.',
            'C89 post-activation completion boundary record is not PLAN/CONFIRM live rollout.',
            'C89 post-activation completion boundary record is not runtime bridge activation.',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c89_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationCompletionBoundaryReviewContract.php');
        $context = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationCompletionBoundaryReviewContext.php');
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
        $this->assertStringContainsString('POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_ONLY = true', $combined);
        $this->assertStringContainsString('CLEARS_POST_ACTIVATION_COMPLETION_BOUNDARY = true', $combined);
        $this->assertStringContainsString('ARTIFACT_ONLY = true', $combined);
        $this->assertStringContainsString('PERSISTED_TO_LIVE_RUNTIME = false', $combined);
        $this->assertStringContainsString('MUTATES_PLAN_CONFIRM = false', $combined);
        $this->assertStringContainsString('WIRES_DEFAULT_RUNTIME = false', $combined);
        $this->assertStringContainsString('ACTIVATES_RUNTIME_BRIDGE = false', $combined);
        $this->assertStringContainsString('ACTIVATES_CONTROLLED_ROLLOUT = false', $combined);
        $this->assertStringContainsString('DEPLOYS_PRODUCTION = false', $combined);
    }
}
