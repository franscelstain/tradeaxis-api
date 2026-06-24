<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC74StaticGuardTest extends TestCase
{
    public function test_c74_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewCommand;', $kernel);
    }

    public function test_c74_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewCommand.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContract.php');
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

    public function test_c74_docs_state_rollout_gate_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C74_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C74 is controlled operator-reviewed rollout gate / deployment readiness review',
            'C74 starts from locked C73 final evidence',
            'C73 controlled parallel-run non-mutating PLAN/CONFIRM bridge validation passed primary + backup',
            'E02 is primary rollout gate candidate',
            'B01 is backup rollout gate candidate',
            'A01 is comparator-only and cannot be promoted',
            'C74 validates C73 artifact hash and file SHA1',
            'C74 validates C73 readiness through nested `c74_readiness_decision.*` path',
            'C74 validates C73 → C60 lineage',
            'C74 does not redesign',
            'C74 does not retune',
            'C74 does not run parameter search',
            'C74 does not use OOS to rerank',
            'C74 does not use parallel-run delta to rerank',
            'C74 does not change candidate scope',
            'C74 may create operator review checklist',
            'C74 may create rollback readiness proof',
            'C74 may create emergency disable proof',
            'C74 may create C75 readiness decision',
            'C74 does not wire activated catalog to PLAN/CONFIRM live',
            'C74 does not deploy live production',
            'C74 does not mutate PLAN/CONFIRM',
            'C74 does not change PLAN/CONFIRM output',
            'C74 keeps `production_catalog_runtime_wired=false`',
            'C74 keeps `controlled_opt_in_runtime_bridge_active=false`',
            'C74 keeps `controlled_parallel_run_active=false`',
            'C74 keeps `controlled_rollout_active=false`',
            'C74 keeps `production_deployment_allowed=false`',
            'C74 keeps `production_deployment_executed=false`',
            'C74 keeps `plan_confirm_mutation_allowed=false`',
            'C74 keeps `plan_confirm_mutated=false`',
            'C74 keeps `plan_confirm_runtime_reads_activated_catalog=false`',
            'C74 keeps `live_plan_confirm_rollout_allowed=false`',
            'C74 keeps `live_plan_confirm_rollout_executed=false`',
            'C74 carries bad-month risk as documented risk',
            'C74 carries weak-regime risk as documented risk',
            'C74 carries source-bias/shared-core risk as documented risk',
            'C65 cleanup note remains non-blocking',
            'C74 may only recommend C75 controlled operator-approved rollout execution review if all rollout gate/readiness gates pass',
            'C74 pass is not full production deployment',
            'C74 pass is not PLAN/CONFIRM live rollout',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c74_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContract.php');
        $context = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContext.php');

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('DEFAULT_ENABLED = false', $contract);
        $this->assertStringContainsString('DEFAULT_CONTROLLED_ROLLOUT_ENABLED = false', $contract);
        $this->assertStringContainsString('DEFAULT_KILL_SWITCH = false', $contract);
        $this->assertStringContainsString('intentionally not consumed by PLAN/CONFIRM runtime services', $contract);
        $this->assertStringContainsString('REQUIRED_OPERATOR_OPTION = \'--operator-reviewed\'', $context);
    }

    public function test_c74_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW.md');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorReviewedRolloutGateContract.php');

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

    public function test_c74_docs_do_not_contain_stale_hashes(): void
    {
        $combined = '';
        foreach ([
            'docs/watchlist/audit/WS_C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW.md',
            'docs/watchlist/audit/WS_C74_OPERATOR_VALIDATION_COMMANDS.md',
            'docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md',
            'docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md',
            'docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md',
        ] as $path) {
            $combined .= (string) file_get_contents($path)."\n";
        }

        foreach ([
            'ed9ec016df7c317ddf22e94cf74b36fb6fb274a5',
            'AAB2D38C8579557B6045DE1DEF5F3C960415B313',
            'fcc59995234dd883524b5e6a23b572c3117faf2d',
            'DFD1976F5004F0A2C00B333F281141E8A3F6E85A',
        ] as $stale) {
            $this->assertStringNotContainsString($stale, $combined, $stale);
        }
    }
}
