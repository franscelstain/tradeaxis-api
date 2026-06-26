<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC86StaticGuardTest extends TestCase
{
    public function test_c86_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewCommand;', $kernel);
    }

    public function test_c86_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC86ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationObservationResultReviewCommand.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationObservationResultReviewContract.php');
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

    public function test_c86_docs_state_result_review_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C86_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C86 is controlled limited runtime opt-in pilot / shadow rollout post-activation observation result review.',
            'C86 starts from locked C85 final evidence.',
            'C85 post-activation observation review passed observation for primary + backup.',
            'E02 is primary post-activation observation result candidate.',
            'B01 is backup post-activation observation result candidate.',
            'A01 is comparator-only and cannot be promoted.',
            'C86 validates C85 artifact hash and file SHA1.',
            'C86 validates C85 readiness through nested next_readiness_decision.* path.',
            'C86 validates C85 -> C60 lineage.',
            'C86 requires --operator-approved.',
            'C86 requires non-empty --approval-reference.',
            'C86 reviews post-activation observation result only.',
            'C86 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C86 does not deploy live production.',
            'C86 does not mutate PLAN/CONFIRM.',
            'C86 does not change PLAN/CONFIRM output.',
            'C86 keeps production_catalog_runtime_wired=false.',
            'C86 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C86 keeps controlled_parallel_run_active=false.',
            'C86 keeps controlled_rollout_active=false.',
            'C86 keeps post_activation_observation_result_context_persisted_to_live_runtime=false.',
            'C86 keeps production_deployment_allowed=false.',
            'C86 keeps production_deployment_executed=false.',
            'C86 keeps plan_confirm_mutation_allowed=false.',
            'C86 keeps plan_confirm_mutated=false.',
            'C86 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C86 keeps live_plan_confirm_rollout_allowed=false.',
            'C86 keeps live_plan_confirm_rollout_executed=false.',
            'C86 post-activation observation result means continue to C87 post-activation operator go/no-go review only.',
            'C86 post-activation observation result record is not production deployment.',
            'C86 post-activation observation result record is not PLAN/CONFIRM live rollout.',
            'C86 post-activation observation result record is not runtime bridge activation.',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c86_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationObservationResultReviewContract.php');
        $context = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedPostActivationObservationResultReviewContext.php');
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
        $this->assertStringContainsString('POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_ONLY = true', $combined);
        $this->assertStringContainsString('CONTROLLED_POST_ACTIVATION_RESULT_RECORD_ONLY = true', $combined);
        $this->assertStringContainsString('ARTIFACT_ONLY = true', $combined);
        $this->assertStringContainsString('PERSISTED_TO_LIVE_RUNTIME = false', $combined);
        $this->assertStringContainsString('MUTATES_PLAN_CONFIRM = false', $combined);
        $this->assertStringContainsString('WIRES_DEFAULT_RUNTIME = false', $combined);
        $this->assertStringContainsString('ACTIVATES_RUNTIME_BRIDGE = false', $combined);
        $this->assertStringContainsString('ACTIVATES_CONTROLLED_ROLLOUT = false', $combined);
        $this->assertStringContainsString('DEPLOYS_PRODUCTION = false', $combined);
    }
}
