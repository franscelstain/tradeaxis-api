<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC66StaticGuardTest extends TestCase
{
    public function test_c66_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');

        $this->assertStringContainsString('RunBacktestC66ProductionLockReviewCommand::class', $kernel);
        $this->assertStringContainsString('use App\\Console\\Commands\\Watchlist\\RunBacktestC66ProductionLockReviewCommand;', $kernel);
    }

    public function test_c66_runtime_has_no_forbidden_date_or_future_selection_shortcuts(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC66ProductionLockReviewService.php');
        $command = (string) file_get_contents('app/Console/Commands/Watchlist/RunBacktestC66ProductionLockReviewCommand.php');
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

    public function test_c66_docs_state_lock_review_only_and_not_live_deployment(): void
    {
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C66_PRODUCTION_LOCK_REVIEW.md');
        $commands = (string) file_get_contents('docs/watchlist/audit/WS_C66_OPERATOR_VALIDATION_COMMANDS.md');

        foreach ([
            'C66 is production lock review',
            'C66 pass is not live deployment',
            'does not activate production catalog',
            'does not deploy production',
            'does not mutate PLAN/CONFIRM',
            'A01 remains comparator-only',
            'bad-month risk remains documented',
            'weak-regime risk remains documented',
            'activation is deferred to C67',
        ] as $required) {
            $this->assertStringContainsString($required, $doc."\n".$commands, $required);
        }
    }

    public function test_c66_docs_and_runtime_preserve_candidate_hierarchy(): void
    {
        $service = (string) file_get_contents('app/Application/Watchlist/Services/WatchlistBacktestC66ProductionLockReviewService.php');
        $doc = (string) file_get_contents('docs/watchlist/audit/WS_C66_PRODUCTION_LOCK_REVIEW.md');

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
