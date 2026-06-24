<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC69StaticGuardTest extends TestCase
{
    public function test_c69_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC69ProductionDeploymentPrepOrBridgeReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC69ProductionDeploymentPrepOrBridgeReviewCommand;', $kernel);
    }

    public function test_c69_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC69ProductionDeploymentPrepOrBridgeReviewCommand.php');
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

    public function test_c69_docs_state_bridge_review_only_and_not_deployment_or_plan_confirm_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C69_OPERATOR_VALIDATION_COMMANDS.md');
        $combined = $doc."\n".$commands;

        foreach ([
            'C69 is production deployment prep / bridge review',
            'C69 starts from locked C68 final evidence',
            'C68 activation execution passed primary + backup',
            'C69 validates C68 artifact hash and file SHA1',
            'C69 validates C68 readiness through nested `c69_readiness_decision.*` path',
            'C69 validates C68 controlled activation record through nested `production_catalog_activation_record.*` path',
            'C69 validates C60 → C69 lineage',
            'E02 is primary deployment bridge candidate',
            'B01 is backup deployment bridge candidate',
            'A01 is comparator-only and cannot be promoted',
            'C69 does not redesign',
            'C69 does not retune',
            'C69 does not run parameter search',
            'C69 does not use OOS to rerank',
            'C69 does not change candidate scope',
            'C69 may create deployment prep / bridge artifact',
            'C69 may create bridge contract proposal',
            'C69 may create feature flag / kill switch plan',
            'C69 may create rollback plan',
            'C69 may create smoke test plan',
            'C69 may create shadow-read / dry-run plan',
            'C69 does not wire activated catalog to PLAN/CONFIRM',
            'C69 does not deploy production',
            'C69 does not mutate PLAN/CONFIRM',
            'C69 keeps `production_catalog_runtime_wired=false`',
            'C69 keeps `production_deployment_allowed=false`',
            'C69 keeps `production_deployment_executed=false`',
            'C69 keeps `plan_confirm_mutation_allowed=false`',
            'C69 keeps `plan_confirm_mutated=false`',
            'C69 keeps `plan_confirm_runtime_reads_activated_catalog=false`',
            'C69 carries bad-month risk as documented risk',
            'C69 carries weak-regime risk as documented risk',
            'C69 carries source-bias/shared-core risk as documented risk',
            'C65 cleanup note remains non-blocking',
            'C69 may only recommend C70 production deployment execution review if all bridge/prep gates pass',
            'C69 pass is not production deployment',
            'C69 pass is not PLAN/CONFIRM rollout',
        ] as $required) {
            $this->assertStringContainsString($required, $combined, $required);
        }
    }

    public function test_c69_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW.md');

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
