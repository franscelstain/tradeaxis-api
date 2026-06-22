<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC62StaticGuardTest extends TestCase
{
    private string $servicePath = 'app/Application/Watchlist/Services/WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService.php';
    private string $commandPath = 'app/Console/Commands/Watchlist/RunBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyCommand.php';

    public function test_c62_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');
        $command = (string) file_get_contents($this->commandPath);

        $this->assertStringContainsString('RunBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyCommand::class', $kernel);
        $this->assertStringContainsString('watchlist:backtest-c62-pre-lock-review-for-c61-signal-quality-candidates-is-only', $command);
    }

    public function test_c62_database_dictionary_paths_exist(): void
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

    public function test_c62_service_enforces_dictionary_and_asof_safety_tokens(): void
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
            'NO_FUTURE_LOOKUP',
            'RETURN_USED_FOR_SELECTION_FALSE',
            'FUTURE_PATH_USED_FOR_SELECTION_FALSE',
            'OOS_RETURN_USED_FOR_SELECTION_FALSE',
            'NO_GATE_RELAXATION',
            'MONTH_WIN_RATE_MIN_ZERO_MUST_BE_AUDITED',
            'BAD_MONTH_EXPOSURE_AUDIT_REQUIRED',
            'CANDIDATE_HIERARCHY_REQUIRED',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }
    }

    public function test_c62_service_has_no_forbidden_latest_shortcuts(): void
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

    public function test_c62_static_guards_prevent_oos_pre_oos_and_production_unlock(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'NO_OOS_PROOF',
            'NO_PRE_OOS_UNLOCK',
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

    public function test_c62_static_guards_do_not_allow_bad_month_or_weak_regime_removal(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'NO_BAD_MONTH_REMOVAL',
            'NO_WEAK_REGIME_REMOVAL',
            'NO_MARKET_DOWN_OR_SIDEWAYS_HIGH_VOL_SKIP',
            'NO_TICKER_EXCLUSION_RULE',
            'NO_SECTOR_EXCLUSION_RULE',
            'NO_REPLAY_COMPARATOR_PROMOTION',
            'WEAK_REGIME_SURVIVAL_REVALIDATION_REQUIRED',
            'SOURCE_BIAS_VALIDATION_REQUIRED',
            'ANTI_SHARED_CORE_RECHECK_REQUIRED',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }
    }
}
