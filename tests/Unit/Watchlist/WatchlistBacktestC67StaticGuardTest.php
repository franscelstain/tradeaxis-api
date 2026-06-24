<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC67StaticGuardTest extends TestCase
{
    public function test_c67_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC67ProductionCatalogActivationReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC67ProductionCatalogActivationReviewCommand;', $kernel);
    }

    public function test_c67_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC67ProductionCatalogActivationReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC67ProductionCatalogActivationReviewCommand.php');
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

    public function test_c67_docs_state_activation_review_only_and_not_live_activation_or_deployment(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C67_OPERATOR_VALIDATION_COMMANDS.md');

        foreach ([
            'C67 is production catalog activation review',
            'C67 pass is not live activation',
            'C67 pass is not live deployment',
            'does not execute live production catalog activation',
            'does not deploy production',
            'does not mutate PLAN/CONFIRM',
            'A01 remains comparator-only',
            'bad-month risk remains documented',
            'weak-regime risk remains documented',
            'activation execution is deferred to C68',
        ] as $required) {
            $this->assertStringContainsString($required, $doc."\n".$commands, $required);
        }
    }

    public function test_c67_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC67ProductionCatalogActivationReviewService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW.md');

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
