<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC70StaticGuardTest extends TestCase
{
    public function test_c70_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC70ProductionDeploymentExecutionReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC70ProductionDeploymentExecutionReviewCommand;', $kernel);
    }

    public function test_c70_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC70ProductionDeploymentExecutionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC70ProductionDeploymentExecutionReviewCommand.php');
        $combined = $service."\n".$command;

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

    public function test_c70_docs_state_controlled_review_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C70_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C70 is controlled production deployment execution review',
            'C70 starts from locked C69 final evidence',
            'E02 is primary controlled deployment execution candidate',
            'B01 is backup controlled deployment execution candidate',
            'A01 is comparator-only and cannot be promoted',
            'C70 validates C69 artifact hash and file SHA1',
            'C70 validates C69 readiness through nested `c70_readiness_decision.*` path',
            'C70 validates C69 → C60 lineage',
            'C70 does not redesign',
            'C70 does not retune',
            'C70 does not run parameter search',
            'C70 does not use OOS to rerank',
            'C70 does not change candidate scope',
            'C70 does not wire activated catalog to PLAN/CONFIRM live',
            'C70 does not deploy live production',
            'C70 does not mutate PLAN/CONFIRM',
            'C70 does not change PLAN/CONFIRM output',
            'C70 keeps `production_catalog_runtime_wired=false`',
            'C70 keeps `production_deployment_allowed=false`',
            'C70 keeps `production_deployment_executed=false`',
            'C70 keeps `plan_confirm_mutation_allowed=false`',
            'C70 keeps `plan_confirm_mutated=false`',
            'C70 keeps `plan_confirm_runtime_reads_activated_catalog=false`',
            'C70 keeps `live_plan_confirm_rollout_allowed=false`',
            'C70 keeps `live_plan_confirm_rollout_executed=false`',
            'C70 carries bad-month risk as documented risk',
            'C70 carries weak-regime risk as documented risk',
            'C70 carries source-bias/shared-core risk as documented risk',
            'C65 cleanup note remains non-blocking',
            'C70 pass is not full production deployment',
            'C70 pass is not PLAN/CONFIRM rollout',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c70_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogRuntimeBridgeContract.php');

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString('DEFAULT_ENABLED = false', $contract);
        $this->assertStringContainsString('DEFAULT_KILL_SWITCH = false', $contract);
        $this->assertStringContainsString('intentionally not consumed by PLAN/CONFIRM runtime services', $contract);
    }

    public function test_c70_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC70ProductionDeploymentExecutionReviewService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW.md');

        foreach ([
            'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE',
            'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION',
            'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST',
        ] as $candidate) {
            $this->assertStringContainsString($candidate, $service);
            $this->assertStringContainsString($candidate, $doc);
        }
    }
}
