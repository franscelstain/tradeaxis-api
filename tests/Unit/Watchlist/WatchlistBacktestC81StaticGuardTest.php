<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC81StaticGuardTest extends TestCase
{
    public function test_c81_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewCommand;', $kernel);
    }

    public function test_c81_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC81ControlledLimitedRuntimeOptInPilotOrShadowRolloutGoDecisionFinalizationReviewCommand.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedGoDecisionFinalizationReviewContract.php');
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

    public function test_c81_docs_state_finalization_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C81_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C81 is controlled limited runtime opt-in pilot / shadow rollout GO decision finalization review.',
            'C81 starts from locked C80 final evidence.',
            'C80 operator go/no-go review passed GO for primary + backup.',
            'E02 is primary finalized GO candidate.',
            'B01 is backup finalized GO candidate.',
            'A01 is comparator-only and cannot be promoted.',
            'C81 validates C80 artifact hash and file SHA1.',
            'C81 validates C80 readiness through nested next_readiness_decision.* path.',
            'C81 validates C80 -> C60 lineage.',
            'C81 requires --operator-approved.',
            'C81 requires non-empty --approval-reference.',
            'C81 finalizes GO decision only.',
            'C81 does not redesign.',
            'C81 does not retune.',
            'C81 does not run parameter search.',
            'C81 does not use OOS to rerank.',
            'C81 does not use finalized GO to rerank.',
            'C81 does not use finalized GO to deploy.',
            'C81 does not change candidate scope.',
            'C81 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C81 does not deploy live production.',
            'C81 does not mutate PLAN/CONFIRM.',
            'C81 does not change PLAN/CONFIRM output.',
            'C81 keeps production_catalog_runtime_wired=false.',
            'C81 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C81 keeps controlled_parallel_run_active=false.',
            'C81 keeps controlled_rollout_active=false.',
            'C81 keeps go_decision_finalization_context_persisted_to_live_runtime=false.',
            'C81 keeps production_deployment_allowed=false.',
            'C81 keeps production_deployment_executed=false.',
            'C81 keeps plan_confirm_mutation_allowed=false.',
            'C81 keeps plan_confirm_mutated=false.',
            'C81 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C81 keeps live_plan_confirm_rollout_allowed=false.',
            'C81 keeps live_plan_confirm_rollout_executed=false.',
            'C81 finalized GO means continue to C82 pre-activation boundary review only.',
            'C81 finalized GO is not production deployment.',
            'C81 finalized GO is not PLAN/CONFIRM live rollout.',
            'C81 finalized GO is not runtime bridge activation.',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c81_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedGoDecisionFinalizationReviewContract.php');
        $context = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedGoDecisionFinalizationReviewContext.php');
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
        $this->assertStringContainsString('PERSISTED_TO_LIVE_RUNTIME = false', $combined);
        $this->assertStringContainsString('MUTATES_PLAN_CONFIRM = false', $combined);
        $this->assertStringContainsString('ACTIVATES_RUNTIME_BRIDGE = false', $combined);
        $this->assertStringContainsString('ACTIVATES_CONTROLLED_ROLLOUT = false', $combined);
    }
}
