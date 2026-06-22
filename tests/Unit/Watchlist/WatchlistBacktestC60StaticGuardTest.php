<?php

namespace Tests\Unit\Watchlist;

use PHPUnit\Framework\TestCase;

class WatchlistBacktestC60StaticGuardTest extends TestCase
{
    private string $servicePath = 'app/Application/Watchlist/Services/WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService.php';
    private string $commandPath = 'app/Console/Commands/Watchlist/RunBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyCommand.php';

    public function test_c60_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');
        $command = (string) file_get_contents($this->commandPath);

        $this->assertStringContainsString('RunBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyCommand::class', $kernel);
        $this->assertStringContainsString('watchlist:backtest-c60-regime-stress-and-loo-dependency-redesign-is-only', $command);
    }

    public function test_c60_database_dictionary_paths_exist(): void
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

    public function test_c60_service_enforces_dictionary_and_asof_safety_tokens(): void
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
            'NO_GATE_RELAXATION',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }
    }

    public function test_c60_service_has_no_forbidden_latest_shortcuts(): void
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

    public function test_c60_static_guards_prevent_oos_and_production_unlock(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'no_oos_proof',
            'no_oos_return_selection',
            'no_production_catalog',
            'production_ready' => 'production_ready',
            'direct_oos_proof_recommended',
            'oos_proof_unlocked',
        ] as $token) {
            $this->assertStringContainsString((string) $token, $service);
        }

        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringContainsString("'direct_oos_proof_recommended' => false", $service);
        $this->assertStringContainsString("'oos_proof_unlocked' => false", $service);
    }

    public function test_c60_static_guards_do_not_allow_weak_regime_skip_or_replay_promotion(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'NO_MARKET_DOWN_OR_SIDEWAYS_HIGH_VOL_SKIP',
            'NO_WEAK_REGIME_REMOVAL',
            'NO_BAD_MONTH_REMOVAL',
            'NO_TICKER_EXCLUSION_RULE',
            'NO_SECTOR_EXCLUSION_RULE',
            'NO_REPLAY_COMPARATOR_PROMOTION',
            'C60_REPLAY_COMPARATOR_ONLY_NOT_SELECTABLE',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }
    }
}
