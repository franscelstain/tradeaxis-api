<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC78StaticGuardTest extends TestCase
{
    public function test_c78_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewCommand;', $kernel);
    }

    public function test_c78_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewCommand.php');
        $pilotContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedRuntimeOptInPilotObservationReviewContract.php');
        $shadowContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedShadowRolloutObservationReviewContract.php');
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

    public function test_c78_docs_state_observation_review_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C78_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C78 is controlled limited runtime opt-in pilot / shadow rollout observation review.',
            'C78 starts from locked C77 final evidence.',
            'C77 controlled pilot/shadow execution review passed primary + backup.',
            'E02 is primary controlled limited observation review candidate.',
            'B01 is backup controlled limited observation review candidate.',
            'A01 is comparator-only and cannot be promoted.',
            'C78 validates C77 artifact hash and file SHA1.',
            'C78 validates C77 readiness through nested next_readiness_decision.* path.',
            'C78 validates C77 -> C60 lineage.',
            'C78 requires --operator-approved.',
            'C78 requires non-empty --approval-reference.',
            'C78 does not redesign.',
            'C78 does not retune.',
            'C78 does not run parameter search.',
            'C78 does not use OOS to rerank.',
            'C78 does not use parallel-run delta to rerank.',
            'C78 does not use controlled wiring result to rerank.',
            'C78 does not use pilot/shadow execution result to rerank.',
            'C78 does not use pilot/shadow observation result to rerank.',
            'C78 does not change candidate scope.',
            'C78 may create controlled limited runtime opt-in pilot observation review proof.',
            'C78 may create controlled limited shadow rollout observation review proof.',
            'C78 may create explicit controlled limited pilot/shadow observation context proof.',
            'C78 may create rollback/emergency disable proof.',
            'C78 may create next-session readiness decision.',
            'C78 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C78 does not deploy live production.',
            'C78 does not mutate PLAN/CONFIRM.',
            'C78 does not change PLAN/CONFIRM output.',
            'C78 keeps production_catalog_runtime_wired=false.',
            'C78 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C78 keeps controlled_parallel_run_active=false.',
            'C78 keeps controlled_rollout_active=false.',
            'C78 keeps controlled_limited_pilot_observation_context_persisted_to_live_runtime=false.',
            'C78 keeps controlled_limited_shadow_observation_context_persisted_to_live_runtime=false.',
            'C78 keeps production_deployment_allowed=false.',
            'C78 keeps production_deployment_executed=false.',
            'C78 keeps plan_confirm_mutation_allowed=false.',
            'C78 keeps plan_confirm_mutated=false.',
            'C78 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C78 keeps live_plan_confirm_rollout_allowed=false.',
            'C78 keeps live_plan_confirm_rollout_executed=false.',
            'C78 carries bad-month risk as documented risk.',
            'C78 carries weak-regime risk as documented risk.',
            'C78 carries source-bias/shared-core risk as documented risk.',
            'C65 cleanup note remains non-blocking.',
            'C78 may only recommend C79 controlled limited runtime opt-in pilot / shadow rollout observation result review if all observation review gates pass.',
            'C78 pass is not full production deployment.',
            'C78 pass is not PLAN/CONFIRM live rollout.',
            'C78 pass is not runtime bridge activation.',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c78_config_defaults_are_off_and_contracts_are_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $pilotContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedRuntimeOptInPilotObservationReviewContract.php');
        $pilotContext = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedRuntimeOptInPilotObservationReviewContext.php');
        $shadowContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedShadowRolloutObservationReviewContract.php');
        $shadowContext = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedShadowRolloutObservationReviewContext.php');
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

    public function test_c78_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC78ControlledLimitedRuntimeOptInPilotOrShadowRolloutObservationReviewService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW.md');
        $pilotContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedRuntimeOptInPilotObservationReviewContract.php');
        $shadowContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledLimitedShadowRolloutObservationReviewContract.php');
        $combined = $service."\n".$doc."\n".$pilotContract."\n".$shadowContract;

        foreach ([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
            'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST',
        ] as $candidate) {
            $this->assertStringContainsString($candidate, $combined);
        }
    }
}
