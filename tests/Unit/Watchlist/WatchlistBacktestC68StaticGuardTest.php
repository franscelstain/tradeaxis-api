<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC68StaticGuardTest extends TestCase
{
    public function test_c68_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC68ProductionCatalogActivationExecutionReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC68ProductionCatalogActivationExecutionReviewCommand;', $kernel);
    }

    public function test_c68_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC68ProductionCatalogActivationExecutionReviewCommand.php');
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

    public function test_c68_docs_state_activation_execution_only_and_not_deployment_or_plan_confirm_rollout(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C68_OPERATOR_VALIDATION_COMMANDS.md');

        foreach ([
            'C68 is production catalog activation execution review',
            'C68 starts from locked C67 final evidence',
            'C67 activation review passed primary + backup',
            'C68 validates C67 artifact hash and file SHA1',
            'E02 is primary activation execution candidate',
            'B01 is backup activation execution candidate',
            'A01 is comparator-only and cannot be promoted',
            'C68 does not redesign',
            'C68 does not retune',
            'C68 does not use OOS to rerank',
            'C68 may create controlled activation execution artifact/record',
            'C68 does not wire activated catalog to PLAN/CONFIRM',
            'C68 does not deploy production',
            'C68 does not mutate PLAN/CONFIRM',
            'bad-month risk remains documented',
            'weak-regime risk remains documented',
            'C68 pass is not production deployment',
            'C68 pass is not PLAN/CONFIRM rollout',
        ] as $required) {
            $this->assertStringContainsString($required, $doc."\n".$commands, $required);
        }
    }

    public function test_c68_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC68ProductionCatalogActivationExecutionReviewService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW.md');

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
