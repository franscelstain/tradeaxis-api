<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC80StaticGuardTest extends TestCase
{
    public function test_c80_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewCommand;', $kernel);
    }

    public function test_c80_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewCommand.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedOperatorGoNoGoReviewContract.php');
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

    public function test_c80_docs_state_go_no_go_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C80_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C80 is controlled limited runtime opt-in pilot / shadow rollout operator go/no-go review.',
            'C80 starts from locked C79 final evidence.',
            'C79 controlled limited pilot/shadow observation result review passed primary + backup.',
            'E02 is primary operator GO candidate.',
            'B01 is backup operator GO candidate.',
            'A01 is comparator-only and cannot be promoted.',
            'C80 validates C79 artifact hash and file SHA1.',
            'C80 validates C79 readiness through nested next_readiness_decision.* path.',
            'C80 validates C79 -> C60 lineage.',
            'C80 requires --operator-approved.',
            'C80 requires non-empty --approval-reference.',
            'C80 records operator GO/NO-GO only.',
            'C80 does not redesign.',
            'C80 does not retune.',
            'C80 does not run parameter search.',
            'C80 does not use OOS to rerank.',
            'C80 does not use operator GO to rerank.',
            'C80 does not use operator GO to deploy.',
            'C80 does not change candidate scope.',
            'C80 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C80 does not deploy live production.',
            'C80 does not mutate PLAN/CONFIRM.',
            'C80 does not change PLAN/CONFIRM output.',
            'C80 keeps production_catalog_runtime_wired=false.',
            'C80 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C80 keeps controlled_parallel_run_active=false.',
            'C80 keeps controlled_rollout_active=false.',
            'C80 keeps operator_go_no_go_context_persisted_to_live_runtime=false.',
            'C80 keeps production_deployment_allowed=false.',
            'C80 keeps production_deployment_executed=false.',
            'C80 keeps plan_confirm_mutation_allowed=false.',
            'C80 keeps plan_confirm_mutated=false.',
            'C80 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C80 keeps live_plan_confirm_rollout_allowed=false.',
            'C80 keeps live_plan_confirm_rollout_executed=false.',
            'C80 GO means continue to C81 finalization review only.',
            'C80 GO is not production deployment.',
            'C80 GO is not PLAN/CONFIRM live rollout.',
            'C80 GO is not runtime bridge activation.',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c80_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedOperatorGoNoGoReviewContract.php');
        $context = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedOperatorGoNoGoReviewContext.php');
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
