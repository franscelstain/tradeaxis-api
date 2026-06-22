<?php

namespace Tests\Unit\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC64PreOosOrOosProofExecutionService;
use PHPUnit\Framework\TestCase;

class WatchlistBacktestC64StaticGuardTest extends TestCase
{
    private string $servicePath = 'app/Application/Watchlist/Services/WatchlistBacktestC64PreOosOrOosProofExecutionService.php';
    private string $commandPath = 'app/Console/Commands/Watchlist/RunBacktestC64PreOosOrOosProofExecutionCommand.php';

    public function test_c64_command_is_registered(): void
    {
        $kernel = (string) file_get_contents('app/Console/Kernel.php');
        $command = (string) file_get_contents($this->commandPath);

        $this->assertStringContainsString('RunBacktestC64PreOosOrOosProofExecutionCommand::class', $kernel);
        $this->assertStringContainsString('watchlist:backtest-c64-pre-oos-or-oos-proof-execution', $command);
    }

    public function test_c64_default_locked_c63_evidence_matches_current_c63_artifact(): void
    {
        $this->assertSame('e98f1386928b36ee367728ceeec4de4344e1f3be', WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C63_HASH);
        $this->assertSame('24C7EE585A165DA41E8FC22538A68145247C68B4', WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C63_FILE_SHA1);
    }

    public function test_c64_default_lineage_locks_match_current_artifacts(): void
    {
        $this->assertSame('d3a089b9b986838764d517682035d76e0bb4112d', WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C62_HASH);
        $this->assertSame('8DF1649BC72233D119581A802F9E41BA9BEBF12E', WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C62_FILE_SHA1);
        $this->assertSame('40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8', WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C61_HASH);
        $this->assertSame('DEA3C807813DE81DB6776AB2C441C945D4E98EC6', WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C61_FILE_SHA1);
        $this->assertSame('25a32ee9c4cb77ecc29103c86a1abf0826aea705', WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C60_HASH);
        $this->assertSame('1FA933157B61ECB4554CE6C76B0F2B314F19DB0F', WatchlistBacktestC64PreOosOrOosProofExecutionService::DEFAULT_EXPECTED_C60_FILE_SHA1);
    }

    public function test_c64_database_dictionary_paths_exist(): void
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

    public function test_c64_service_enforces_dictionary_and_asof_safety_tokens(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'DATABASE_DICTIONARY_READ_RULE_ENFORCED',
            'MARKET_INDEX_ROC20_SOURCE_MARKET_BENCHMARK_INDICATORS_ROC_20',
            'MARKET_INDEX_MA20_SLOPE_SOURCE_MARKET_BENCHMARK_INDICATORS_MA20_SLOPE_PCT',
            'MARKET_INDEX_IDENTIFIER_IHSG',
            'MARKET_CALENDAR_DATE_KEY_CAL_DATE',
            'ASOF_SAFE_LOOKUP_REQUIRED',
            'NO_LATEST_DATE_SHORTCUT',
            'NO_MAX_TRADE_DATE_SHORTCUT',
            'NO_ORDER_DESC_TRADE_DATE_SHORTCUT',
            'RETURN_USED_FOR_SELECTION_FALSE',
            'FUTURE_PATH_USED_FOR_SELECTION_FALSE',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }
    }

    public function test_c64_service_has_no_forbidden_latest_shortcuts(): void
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

    public function test_c64_static_guards_require_selection_freeze_before_oos(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'SELECTION_FROZEN_BEFORE_OOS_REQUIRED',
            'NO_OOS_READ_BEFORE_SELECTION_FREEZE',
            'NO_SELECTION_RULE_CHANGED_AFTER_OOS',
            'NO_PARAMETER_CHANGED_AFTER_OOS',
            'NO_OOS_BASED_RETUNING',
            'NO_OOS_TIE_BREAK',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }
    }

    public function test_c64_static_guards_prevent_comparator_promotion_and_best_of_failed(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'NO_BEST_OF_FAILED_PROMOTION',
            'NO_REPLAY_COMPARATOR_PROMOTION',
            'A01_COMPARATOR_ONLY_NOT_PROMOTABLE',
            'C64_A01_REMAINS_COMPARATOR_ONLY',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }
    }

    public function test_c64_static_guards_require_oos_review_tracks(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'OOS_BAD_MONTH_PROOF_REQUIRED',
            'OOS_WEAK_REGIME_PROOF_REQUIRED',
            'OOS_ROLLING_PROOF_REQUIRED',
            'OOS_MONTH_DEPENDENCY_PROOF_REQUIRED',
            'OOS_CONCENTRATION_PROOF_REQUIRED',
            'OOS_LOSS_CLUSTER_PROOF_REQUIRED',
            'OOS_SHARED_CORE_PROOF_REQUIRED',
            'OOS_SOURCE_BIAS_PROOF_REQUIRED',
            'OOS_SAFETY_LEAKAGE_AUDIT_REQUIRED',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }
    }

    public function test_c64_static_guards_prevent_production_and_plan_confirm_mutation(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'NO_PRODUCTION_CATALOG',
            'NO_PLAN_CONFIRM_MUTATION',
            'C64_RESULT_IS_NOT_PRODUCTION_READY',
            'C64_CAN_ONLY_RECOMMEND_C65_PRODUCTION_PRE_LOCK_REVIEW',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }

        $this->assertStringContainsString("'production_ready' => false", $service);
        $this->assertStringContainsString("'direct_oos_proof_recommended' => false", $service);
        $this->assertStringContainsString("'oos_proof_unlocked' => false", $service);
        $this->assertStringContainsString("'pre_oos_unlocked' => false", $service);
    }

    public function test_c64_static_guards_do_not_allow_bad_month_or_weak_regime_deletion(): void
    {
        $service = (string) file_get_contents($this->servicePath);

        foreach ([
            'NO_BAD_MONTH_REMOVAL',
            'NO_WEAK_REGIME_REMOVAL',
            'NO_MARKET_DOWN_OR_SIDEWAYS_HIGH_VOL_SKIP',
            'NO_TICKER_EXCLUSION_RULE',
            'NO_SECTOR_EXCLUSION_RULE',
        ] as $token) {
            $this->assertStringContainsString($token, $service, $token);
        }
    }
}
