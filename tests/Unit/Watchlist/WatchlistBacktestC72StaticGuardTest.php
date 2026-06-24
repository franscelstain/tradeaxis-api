<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC72StaticGuardTest extends TestCase
{
    public function test_c72_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC72ControlledOptInRuntimeBridgeValidationCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC72ControlledOptInRuntimeBridgeValidationCommand;', $kernel);
    }

    public function test_c72_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC72ControlledOptInRuntimeBridgeValidationCommand.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContract.php');
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

    public function test_c72_docs_state_controlled_opt_in_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C72_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C72 is controlled opt-in runtime bridge validation',
            'C72 starts from locked C71 final evidence',
            'C71 shadow-read/dry-run runtime validation passed primary + backup',
            'E02 is primary controlled opt-in runtime bridge candidate',
            'B01 is backup controlled opt-in runtime bridge candidate',
            'A01 is comparator-only and cannot be promoted',
            'C72 validates C71 artifact hash and file SHA1',
            'C72 validates C71 readiness through nested `c72_readiness_decision.*` path',
            'C72 validates C71 → C60 lineage',
            'C72 does not redesign',
            'C72 does not retune',
            'C72 does not run parameter search',
            'C72 does not use OOS to rerank',
            'C72 does not change candidate scope',
            'C72 may create isolated controlled opt-in runtime bridge proof',
            'C72 may create controlled bridge read proof',
            'C72 may create baseline PLAN/CONFIRM non-mutation proof',
            'C72 may create fallback behavior proof',
            'C72 does not wire activated catalog to PLAN/CONFIRM live',
            'C72 does not deploy live production',
            'C72 does not mutate PLAN/CONFIRM',
            'C72 does not change PLAN/CONFIRM output',
            'C72 keeps `production_catalog_runtime_wired=false`',
            'C72 keeps `controlled_opt_in_runtime_bridge_active=false`',
            'C72 keeps `production_deployment_allowed=false`',
            'C72 keeps `production_deployment_executed=false`',
            'C72 keeps `plan_confirm_mutation_allowed=false`',
            'C72 keeps `plan_confirm_mutated=false`',
            'C72 keeps `plan_confirm_runtime_reads_activated_catalog=false`',
            'C72 keeps `live_plan_confirm_rollout_allowed=false`',
            'C72 keeps `live_plan_confirm_rollout_executed=false`',
            'C72 carries bad-month risk as documented risk',
            'C72 carries weak-regime risk as documented risk',
            'C72 carries source-bias/shared-core risk as documented risk',
            'C65 cleanup note remains non-blocking',
            'C72 may only recommend C73 controlled parallel-run non-mutating PLAN/CONFIRM bridge validation if all controlled opt-in gates pass',
            'C72 pass is not full production deployment',
            'C72 pass is not PLAN/CONFIRM rollout',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c72_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContract.php');
        $context = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContext.php');

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_opt_in_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString('DEFAULT_ENABLED = false', $contract);
        $this->assertStringContainsString('DEFAULT_CONTROLLED_OPT_IN_ENABLED = false', $contract);
        $this->assertStringContainsString('DEFAULT_KILL_SWITCH = false', $contract);
        $this->assertStringContainsString('intentionally not consumed by PLAN/CONFIRM runtime services', $contract);
        $this->assertStringContainsString('REQUIRED_OPERATOR_OPTION = \'--controlled-opt-in\'', $context);
    }

    public function test_c72_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION.md');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOptInRuntimeBridgeContract.php');

        foreach ([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
            'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST',
        ] as $candidate) {
            $this->assertStringContainsString($candidate, $service);
            $this->assertStringContainsString($candidate, $doc);
            $this->assertStringContainsString($candidate, $contract);
        }
    }
}
