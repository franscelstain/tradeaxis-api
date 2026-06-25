<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC76StaticGuardTest extends TestCase
{
    public function test_c76_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewCommand;', $kernel);
    }

    public function test_c76_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewCommand.php');
        $pilotContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotPreparationContract.php');
        $shadowContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutPreparationContract.php');
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

    public function test_c76_docs_state_preparation_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C76_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C76 is controlled runtime opt-in pilot / shadow rollout preparation review',
            'C76 starts from locked C75 final evidence',
            'C75 controlled operator-approved execution/wiring review passed primary + backup',
            'E02 is primary controlled pilot/shadow preparation candidate',
            'B01 is backup controlled pilot/shadow preparation candidate',
            'A01 is comparator-only and cannot be promoted',
            'C76 validates C75 artifact hash and file SHA1',
            'C76 validates C75 readiness through nested `next_readiness_decision.*` path',
            'C76 validates C75 -> C60 lineage',
            'C76 requires --operator-approved',
            'C76 requires non-empty --approval-reference',
            'C76 does not redesign',
            'C76 does not retune',
            'C76 does not run parameter search',
            'C76 does not use OOS to rerank',
            'C76 does not use parallel-run delta to rerank',
            'C76 does not use controlled wiring result to rerank',
            'C76 does not use pilot/shadow preparation result to rerank',
            'C76 does not change candidate scope',
            'C76 may create controlled runtime opt-in pilot preparation proof',
            'C76 may create controlled shadow rollout preparation proof',
            'C76 may create explicit controlled pilot/shadow context proof',
            'C76 may create rollback/emergency disable proof',
            'C76 may create next-session readiness decision',
            'C76 does not wire activated catalog to PLAN/CONFIRM live default runtime',
            'C76 does not deploy live production',
            'C76 does not mutate PLAN/CONFIRM',
            'C76 does not change PLAN/CONFIRM output',
            'C76 keeps `production_catalog_runtime_wired=false`',
            'C76 keeps `controlled_opt_in_runtime_bridge_active=false`',
            'C76 keeps `controlled_parallel_run_active=false`',
            'C76 keeps `controlled_rollout_active=false`',
            'C76 keeps `controlled_pilot_context_persisted_to_live_runtime=false`',
            'C76 keeps `controlled_shadow_context_persisted_to_live_runtime=false`',
            'C76 keeps `production_deployment_allowed=false`',
            'C76 keeps `production_deployment_executed=false`',
            'C76 keeps `plan_confirm_mutation_allowed=false`',
            'C76 keeps `plan_confirm_mutated=false`',
            'C76 keeps `plan_confirm_runtime_reads_activated_catalog=false`',
            'C76 keeps `live_plan_confirm_rollout_allowed=false`',
            'C76 keeps `live_plan_confirm_rollout_executed=false`',
            'C76 carries bad-month risk as documented risk',
            'C76 carries weak-regime risk as documented risk',
            'C76 carries source-bias/shared-core risk as documented risk',
            'C65 cleanup note remains non-blocking',
            'C76 may only recommend C77 controlled runtime opt-in pilot / shadow rollout execution review if all preparation gates pass',
            'C76 pass is not full production deployment',
            'C76 pass is not PLAN/CONFIRM live rollout',
            'C76 pass is not runtime bridge activation',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c76_config_defaults_are_off_and_contracts_are_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $pilotContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotPreparationContract.php');
        $pilotContext = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotPreparationContext.php');
        $shadowContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutPreparationContract.php');
        $shadowContext = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutPreparationContext.php');
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
    }

    public function test_c76_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW.md');
        $pilotContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledRuntimeOptInPilotPreparationContract.php');
        $shadowContract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledShadowRolloutPreparationContract.php');
        $combined = $service."\n".$doc."\n".$pilotContract."\n".$shadowContract;

        foreach ([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
            'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST',
        ] as $candidate) {
            $this->assertStringContainsString($candidate, $combined);
        }
    }

    public function test_c76_docs_do_not_contain_stale_hashes_as_active_locks(): void
    {
        $combined = '';
        foreach ([
            'docs/watchlist/audit/WS_C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW.md',
            'docs/watchlist/audit/WS_C76_OPERATOR_VALIDATION_COMMANDS.md',
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
