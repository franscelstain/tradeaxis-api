<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC77StaticGuardTest extends TestCase
{
    public function test_c77_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewCommand;', $kernel);
    }

    public function test_c77_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewCommand.php');
        $pilotContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotExecutionReviewContract.php');
        $shadowContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutExecutionReviewContract.php');
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

    public function test_c77_docs_state_execution_review_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C77_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C77 is controlled runtime opt-in pilot / shadow rollout execution review.',
            'C77 starts from locked C76 final evidence.',
            'C76 controlled pilot/shadow preparation review passed primary + backup.',
            'E02 is primary controlled pilot/shadow execution review candidate.',
            'B01 is backup controlled pilot/shadow execution review candidate.',
            'A01 is comparator-only and cannot be promoted.',
            'C77 validates C76 artifact hash and file SHA1.',
            'C77 validates C76 readiness through nested next_readiness_decision.* path.',
            'C77 validates C76 -> C60 lineage.',
            'C77 requires --operator-approved.',
            'C77 requires non-empty --approval-reference.',
            'C77 does not redesign.',
            'C77 does not retune.',
            'C77 does not run parameter search.',
            'C77 does not use OOS to rerank.',
            'C77 does not use parallel-run delta to rerank.',
            'C77 does not use controlled wiring result to rerank.',
            'C77 does not use pilot/shadow preparation result to rerank.',
            'C77 does not use pilot/shadow execution result to rerank.',
            'C77 does not change candidate scope.',
            'C77 may create controlled runtime opt-in pilot execution review proof.',
            'C77 may create controlled shadow rollout execution review proof.',
            'C77 may create explicit controlled pilot/shadow execution context proof.',
            'C77 may create rollback/emergency disable proof.',
            'C77 may create next-session readiness decision.',
            'C77 does not wire activated catalog to PLAN/CONFIRM live default runtime.',
            'C77 does not deploy live production.',
            'C77 does not mutate PLAN/CONFIRM.',
            'C77 does not change PLAN/CONFIRM output.',
            'C77 keeps production_catalog_runtime_wired=false.',
            'C77 keeps controlled_opt_in_runtime_bridge_active=false.',
            'C77 keeps controlled_parallel_run_active=false.',
            'C77 keeps controlled_rollout_active=false.',
            'C77 keeps controlled_pilot_execution_context_persisted_to_live_runtime=false.',
            'C77 keeps controlled_shadow_execution_context_persisted_to_live_runtime=false.',
            'C77 keeps production_deployment_allowed=false.',
            'C77 keeps production_deployment_executed=false.',
            'C77 keeps plan_confirm_mutation_allowed=false.',
            'C77 keeps plan_confirm_mutated=false.',
            'C77 keeps plan_confirm_runtime_reads_activated_catalog=false.',
            'C77 keeps live_plan_confirm_rollout_allowed=false.',
            'C77 keeps live_plan_confirm_rollout_executed=false.',
            'C77 carries bad-month risk as documented risk.',
            'C77 carries weak-regime risk as documented risk.',
            'C77 carries source-bias/shared-core risk as documented risk.',
            'C65 cleanup note remains non-blocking.',
            'C77 may only recommend C78 controlled limited runtime opt-in pilot / shadow rollout observation review if all execution review gates pass.',
            'C77 pass is not full production deployment.',
            'C77 pass is not PLAN/CONFIRM live rollout.',
            'C77 pass is not runtime bridge activation.',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c77_config_defaults_are_off_and_contracts_are_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $pilotContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotExecutionReviewContract.php');
        $pilotContext = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotExecutionReviewContext.php');
        $shadowContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutExecutionReviewContract.php');
        $shadowContext = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutExecutionReviewContext.php');
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

    public function test_c77_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC77ControlledRuntimeOptInPilotOrShadowRolloutExecutionReviewService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW.md');
        $pilotContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotExecutionReviewContract.php');
        $shadowContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutExecutionReviewContract.php');
        $combined = $service."\n".$doc."\n".$pilotContract."\n".$shadowContract;

        foreach ([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
            'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST',
        ] as $candidate) {
            $this->assertStringContainsString($candidate, $combined);
        }
    }

    public function test_c77_docs_do_not_contain_stale_hashes_as_active_locks(): void
    {
        $combined = '';
        foreach ([
            'docs/watchlist/audit/WS_C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW.md',
            'docs/watchlist/audit/WS_C76_OPERATOR_VALIDATION_COMMANDS.md',
            'docs/watchlist/audit/WS_C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW.md',
            'docs/watchlist/audit/WS_C77_OPERATOR_VALIDATION_COMMANDS.md',
            'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
            'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
            'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
        ] as $path) {
            $combined .= (string) file_get_contents($path)."\n";
        }

        foreach ([
            '2e02737a212cf9043d5937f5354a3c31541dc22f',
            'C7FCA9797AFF0B2B3CD4B37E587DC646F01C2187',
            '4896a0479675d969a142d1880545459c391dbc11',
            'CDD9F75CF96CC8842DC22F8A29A7959682550D84',
            '886019fba9143820e3d135a0586d63244c31e35a',
            '83A065CCDAD13A328F286D38BDED61117BE28BF6',
        ] as $stale) {
            $this->assertStaleHashOnlyAppearsAsHistoricalIfPresent($combined, $stale);
        }
    }

    private function assertStaleHashOnlyAppearsAsHistoricalIfPresent(string $content, string $hash): void
    {
        $offset = 0;
        while (($pos = strpos($content, $hash, $offset)) !== false) {
            $window = strtolower(substr($content, max(0, $pos - 160), 360));
            $this->assertTrue(
                strpos($window, 'superseded') !== false || strpos($window, 'historical') !== false || strpos($window, 'pre-alignment') !== false || strpos($window, 'not active') !== false,
                $hash.' appears without historical/superseded context'
            );
            $offset = $pos + strlen($hash);
        }
    }
}
