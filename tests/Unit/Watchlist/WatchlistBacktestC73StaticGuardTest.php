<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC73StaticGuardTest extends TestCase
{
    public function test_c73_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationCommand;', $kernel);
    }

    public function test_c73_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationCommand.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContract.php');
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

    public function test_c73_docs_state_parallel_run_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C73_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C73 is controlled parallel-run non-mutating PLAN/CONFIRM bridge validation',
            'C73 starts from locked C72 final evidence',
            'C72 controlled opt-in runtime bridge validation passed primary + backup',
            'E02 is primary controlled parallel-run candidate',
            'B01 is backup controlled parallel-run candidate',
            'A01 is comparator-only and cannot be promoted',
            'C73 validates C72 artifact hash and file SHA1',
            'C73 validates C72 readiness through nested `c73_readiness_decision.*` path',
            'C73 validates C72 → C60 lineage',
            'C73 does not redesign',
            'C73 does not retune',
            'C73 does not run parameter search',
            'C73 does not use OOS to rerank',
            'C73 does not change candidate scope',
            'C73 may create isolated controlled parallel-run proof',
            'C73 may create PLAN/CONFIRM baseline-vs-bridge comparison proof',
            'C73 may create parallel-run delta report',
            'C73 may create baseline PLAN/CONFIRM non-mutation proof',
            'C73 may create fallback behavior proof',
            'C73 does not wire activated catalog to PLAN/CONFIRM live',
            'C73 does not deploy live production',
            'C73 does not mutate PLAN/CONFIRM',
            'C73 does not change PLAN/CONFIRM output',
            'C73 keeps `production_catalog_runtime_wired=false`',
            'C73 keeps `controlled_opt_in_runtime_bridge_active=false`',
            'C73 keeps `controlled_parallel_run_active=false`',
            'C73 keeps `production_deployment_allowed=false`',
            'C73 keeps `production_deployment_executed=false`',
            'C73 keeps `plan_confirm_mutation_allowed=false`',
            'C73 keeps `plan_confirm_mutated=false`',
            'C73 keeps `plan_confirm_runtime_reads_activated_catalog=false`',
            'C73 keeps `live_plan_confirm_rollout_allowed=false`',
            'C73 keeps `live_plan_confirm_rollout_executed=false`',
            'C73 carries bad-month risk as documented risk',
            'C73 carries weak-regime risk as documented risk',
            'C73 carries source-bias/shared-core risk as documented risk',
            'C65 cleanup note remains non-blocking',
            'C73 may only recommend C74 controlled operator-reviewed rollout gate / deployment readiness review if all controlled parallel-run gates pass',
            'C73 pass is not full production deployment',
            'C73 pass is not PLAN/CONFIRM rollout',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c73_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContract.php');
        $context = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContext.php');

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString('DEFAULT_ENABLED = false', $contract);
        $this->assertStringContainsString('DEFAULT_CONTROLLED_PARALLEL_RUN_ENABLED = false', $contract);
        $this->assertStringContainsString('DEFAULT_KILL_SWITCH = false', $contract);
        $this->assertStringContainsString('intentionally not consumed by PLAN/CONFIRM runtime services', $contract);
        $this->assertStringContainsString('REQUIRED_OPERATOR_OPTION = \'--controlled-parallel-run\'', $context);
    }

    public function test_c73_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION.md');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContract.php');

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
