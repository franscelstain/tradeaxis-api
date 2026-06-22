<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC63PreOosUnlockReviewIsOnlyService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC63StaticGuardTest extends TestCase
{
    private string $servicePath = 'app/Application/Watchlist/Services/WatchlistBacktestC63PreOosUnlockReviewIsOnlyService.php';
    private string $commandPath = 'app/Console/Commands/Watchlist/RunBacktestC63PreOosUnlockReviewIsOnlyCommand.php';

    public function test_c63_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');
        $command = (string) file_get_contents($this->commandPath);

        $this->assertStringContainsString('RunBacktestC63PreOosUnlockReviewIsOnlyCommand::class', $kernel);
        $this->assertStringContainsString('watchlist:backtest-c63-pre-oos-unlock-review-is-only', $command);
    }

    public function test_c63_default_locked_c62_evidence_matches_current_c62_artifact(): void
    {
        $this->assertSame('d3a089b9b986838764d517682035d76e0bb4112d', WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C62_HASH);
        $this->assertSame('8DF1649BC72233D119581A802F9E41BA9BEBF12E', WatchlistBacktestC63PreOosUnlockReviewIsOnlyService::DEFAULT_EXPECTED_C62_FILE_SHA1);
    }

    public function test_c63_database_dictionary_paths_exist(): void
    {
        foreach ([
            'docs/market_data/db/MARKET_DATA_DICTIONARY.md',
            'docs/db/DATABASE_DICTIONARY_USAGE_RULE.md',
            'docs/market_data/db/Database_Schema_MariaDB.sql',
            'docs/market_data/db/Database_Schema_Contracts_MariaDB.md',
            'docs/market_data/db/DB_FIELDS_AND_METADATA.md',
            'docs/watchlist/system/db/WATCHLIST_DB_DICTIONARY.md',
        ] as $path) {
            $this->assertFileExists($path, $path);
        }
    }

    public function test_c63_service_enforces_dictionary_and_asof_safety_tokens(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'DATABASE_DICTIONARY_READ_RULE_ENFORCED',
            'MARKET_INDEX_ROC20_SOURCE_MARKET_BENCHMARK_INDICATORS_ROC_20',
            'MARKET_INDEX_MA20_SLOPE_SOURCE_MARKET_BENCHMARK_INDICATORS_MA20_SLOPE_PCT',
            'MARKET_INDEX_IDENTIFIER_IHSG',
            'MARKET_CALENDAR_DATE_KEY_CAL_DATE',
            'ASOF_SAFE_LOOKUP_REQUIRED',
            'NO_RESERVED_OOS_ROWS',
            'NO_OOS_DATE_QUERY',
            'RETURN_USED_FOR_SELECTION_FALSE',
            'FUTURE_PATH_USED_FOR_SELECTION_FALSE',
            'OOS_RETURN_USED_FOR_SELECTION_FALSE',
            'NO_GATE_RELAXATION',
            'MONTH_WIN_RATE_MIN_ZERO_MUST_BE_AUDITED',
            'BAD_MONTH_UNLOCK_RISK_REQUIRED',
            'WEAK_REGIME_UNLOCK_READINESS_REQUIRED',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }
    }

    public function test_c63_service_has_no_forbidden_latest_shortcuts(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'latest(\'trade_date\')',
            'latest("trade_date")',
            'orderByDesc(\'trade_date\')',
            'orderByDesc("trade_date")',
            'MAX(trade_date)',
        ] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $service, $forbidden);
        }
    }

    public function test_c63_static_guards_prevent_oos_pre_oos_and_production_unlock(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'NO_OOS_PROOF',
            'NO_PRE_OOS_EXECUTION',
            'NO_PRODUCTION_CATALOG',
            'NO_PLAN_CONFIRM_MUTATION',
            'production_ready',
            'direct_oos_proof_recommended',
            'oos_proof_unlocked',
            'pre_oos_unlocked',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }

        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringContainsString("'direct_oos_proof_recommended' => false", $service);
        $this->assertStringContainsString("'oos_proof_unlocked' => false", $service);
        $this->assertStringContainsString("'pre_oos_unlocked' => false", $service);
    }

    public function test_c63_static_guards_do_not_allow_bad_month_weak_regime_or_comparator_promotion_abuse(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'NO_BAD_MONTH_REMOVAL',
            'NO_WEAK_REGIME_REMOVAL',
            'NO_MARKET_DOWN_OR_SIDEWAYS_HIGH_VOL_SKIP',
            'NO_TICKER_EXCLUSION_RULE',
            'NO_SECTOR_EXCLUSION_RULE',
            'NO_REPLAY_COMPARATOR_PROMOTION',
            'E02_WORST_MONTH_2024_08_AUDIT_REQUIRED',
            'B01_WORST_MONTH_2024_11_AUDIT_REQUIRED',
            'SHARED_CORE_UNLOCK_RECHECK_REQUIRED',
            'SOURCE_BIAS_UNLOCK_RECHECK_REQUIRED',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }
    }
}
