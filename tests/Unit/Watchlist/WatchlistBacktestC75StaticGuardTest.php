<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC75StaticGuardTest extends TestCase
{
    public function test_c75_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewCommand;', $kernel);
    }

    public function test_c75_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewCommand.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContract.php');
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

    public function test_c75_docs_state_controlled_wiring_only_and_not_live_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C75_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C75 is controlled operator-approved rollout execution review / controlled wiring execution review',
            'C75 starts from locked C74 final evidence',
            'C74 controlled operator-reviewed rollout gate passed primary + backup',
            'E02 is primary controlled execution review candidate',
            'B01 is backup controlled execution review candidate',
            'A01 is comparator-only and cannot be promoted',
            'C75 validates C74 artifact hash and file SHA1',
            'C75 validates C74 readiness through nested `c75_readiness_decision.*` path',
            'C75 validates C74 → C60 lineage',
            'C75 requires --operator-approved',
            'C75 requires non-empty --approval-reference',
            'C75 does not redesign',
            'C75 does not retune',
            'C75 does not run parameter search',
            'C75 does not use OOS to rerank',
            'C75 does not use parallel-run delta to rerank',
            'C75 does not use controlled wiring result to rerank',
            'C75 does not change candidate scope',
            'C75 may create controlled operator-approved execution review proof',
            'C75 may create explicit controlled wiring context proof',
            'C75 may create rollback/emergency disable proof',
            'C75 may create next-session readiness decision',
            'C75 does not wire activated catalog to PLAN/CONFIRM live default runtime',
            'C75 does not deploy live production',
            'C75 does not mutate PLAN/CONFIRM',
            'C75 does not change PLAN/CONFIRM output',
            'C75 keeps `production_catalog_runtime_wired=false`',
            'C75 keeps `controlled_opt_in_runtime_bridge_active=false`',
            'C75 keeps `controlled_parallel_run_active=false`',
            'C75 keeps `controlled_rollout_active=false`',
            'C75 keeps `controlled_wiring_context_persisted_to_live_runtime=false`',
            'C75 keeps `production_deployment_allowed=false`',
            'C75 keeps `production_deployment_executed=false`',
            'C75 keeps `plan_confirm_mutation_allowed=false`',
            'C75 keeps `plan_confirm_mutated=false`',
            'C75 keeps `plan_confirm_runtime_reads_activated_catalog=false`',
            'C75 keeps `live_plan_confirm_rollout_allowed=false`',
            'C75 keeps `live_plan_confirm_rollout_executed=false`',
            'C75 carries bad-month risk as documented risk',
            'C75 carries weak-regime risk as documented risk',
            'C75 carries source-bias/shared-core risk as documented risk',
            'C65 cleanup note remains non-blocking',
            'C75 may only recommend C76 controlled runtime opt-in pilot / shadow rollout preparation review if all execution/wiring gates pass',
            'C75 pass is not full production deployment',
            'C75 pass is not PLAN/CONFIRM live rollout',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c75_config_defaults_are_off_and_contract_is_non_live(): void
    {
        $config = (string) file_get_contents('config/watchlist.php');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContract.php');
        $context = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContext.php');

        $this->assertStringContainsString("'production_catalog_runtime_bridge_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_runtime_bridge_kill_switch' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_parallel_run_enabled' => false", $config);
        $this->assertStringContainsString("'production_catalog_controlled_rollout_enabled' => false", $config);
        $this->assertStringContainsString('DEFAULT_ENABLED = false', $contract);
        $this->assertStringContainsString('DEFAULT_CONTROLLED_ROLLOUT_ENABLED = false', $contract);
        $this->assertStringContainsString('DEFAULT_KILL_SWITCH = false', $contract);
        $this->assertStringContainsString('intentionally not consumed by PLAN/CONFIRM runtime services', $contract);
        $this->assertStringContainsString('REQUIRED_OPERATOR_OPTION = \'--operator-approved\'', $context);
        $this->assertStringContainsString('REQUIRED_APPROVAL_REFERENCE_OPTION = \'--approval-reference\'', $context);
    }

    public function test_c75_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW.md');
        $contract = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistProductionCatalogControlledOperatorApprovedRolloutExecutionReviewContract.php');

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

    public function test_c75_docs_do_not_contain_stale_hashes(): void
    {
        $combined = '';
        foreach ([
            'docs/watchlist/audit/WS_C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW.md',
            'docs/watchlist/audit/WS_C75_OPERATOR_VALIDATION_COMMANDS.md',
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
