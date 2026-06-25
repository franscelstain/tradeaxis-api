<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC79StaticGuardTest extends TestCase
{
    public function test_c79_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewCommand;', $kernel);
    }

    public function test_c79_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC79ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationResultReviewCommand.php');
        $pilotContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedRuntimeOptInPilotObservationResultReviewContract.php');
        $shadowContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedShadowRolloutObservationResultReviewContract.php');
        $combined = $service."\n".$command."\n".$pilotContract."\n".$shadowContract;

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

    public function test_c79_docs_state_observation_result_review_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C79_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C79 is controlled limited runtime opt-in pilot / shadow rollout observation result review.',
            'C79 starts from locked C78 final evidence.',
            'C78 controlled limited pilot/shadow observation review passed primary + backup.',
            'E02 is primary controlled limited observation result review candidate.',
            'B01 is backup controlled limited observation result review candidate.',
            'A01 is comparator-only and cannot be promoted.',
            'C79 validates C78 artifact hash and file SHA1.',
            'C79 validates C78 readiness through nested next_readiness_decision.* path.',
            'C79 validates C78 -> C60 lineage.',
            'C79 requires --operator-approved.',
            'C79 requires non-empty --approval-reference.',
            'C79 does not redesign.',
            'C79 does not retune.',
            'C79 does not run parameter search.',
            'C79 does not use OOS to rerank.',
            'C79 does not use parallel-run delta to rerank.',
            'C79 does not use pilot/shadow observation result to rerank.',
            'C79 does not change candidate scope.',
            'C79 may create controlled limited runtime opt-in pilot observation result review proof.',
            'C79 may create controlled limited shadow rollout observation result review proof.',
            'C79 may create explicit controlled limited pilot/shadow observation result context proof.',
            'C79 may create progress summary.',
            'C79 may create planned next summary.',
            'C79 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C79 does not deploy live production.',
            'C79 does not mutate PLAN/CONFIRM.',
            'C79 does not change PLAN/CONFIRM output.',
            'C79 keeps production_catalog_runtime_wired=false.',
            'C79 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C79 keeps controlled_parallel_run_active=false.',
            'C79 keeps controlled_rollout_active=false.',
            'C79 keeps controlled_limited_pilot_observation_result_context_persisted_to_live_runtime=false.',
            'C79 keeps controlled_limited_shadow_observation_result_context_persisted_to_live_runtime=false.',
            'C79 keeps production_deployment_allowed=false.',
            'C79 keeps production_deployment_executed=false.',
            'C79 keeps plan_confirm_mutation_allowed=false.',
            'C79 keeps plan_confirm_mutated=false.',
            'C79 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C79 keeps live_plan_confirm_rollout_allowed=false.',
            'C79 keeps live_plan_confirm_rollout_executed=false.',
            'C79 may only recommend C80 controlled limited runtime opt-in pilot / shadow rollout operator go/no-go review if all observation result review gates pass.',
            'C79 pass is not full production deployment.',
            'C79 pass is not PLAN/CONFIRM live rollout.',
            'C79 pass is not runtime bridge activation.',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c79_config_defaults_are_off_and_contracts_are_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $pilotContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedRuntimeOptInPilotObservationResultReviewContract.php');
        $pilotContext = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedRuntimeOptInPilotObservationResultReviewContext.php');
        $shadowContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedShadowRolloutObservationResultReviewContract.php');
        $shadowContext = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedShadowRolloutObservationResultReviewContext.php');
        $combined = $pilotContract."\n".$pilotContext."\n".$shadowContract."\n".$shadowContext;

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
