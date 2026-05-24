<?php

use PHPUnit\Framework\TestCase;

class ConfigEnvGovernanceCleanupStaticGuardTest extends TestCase
{
    private function projectPath(string $relativePath): string
    {
        return dirname(__DIR__, 3).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function read(string $relativePath): string
    {
        $path = $this->projectPath($relativePath);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    public function test_inventory_records_schema_config_env_pruning_and_validation_status(): void
    {
        $inventory = $this->read('docs/market_data/audit/CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md');

        foreach ([
            'CONFIG_ENV_GOVERNANCE_CLEANUP_CONTRACT',
            'Config / ENV Governance Cleanup',
            'Schema / Config Alignment Matrix',
            'Config / ENV Inventory Matrix',
            '`tickers.is_active` Decision Matrix',
            'Pruning Matrix',
            'Caller Trace Matrix',
            'Patch Matrix',
            'Validation Matrix',
            'READY_FOR_LOCAL_RUNTIME_VALIDATION',
            'BLOCKED_CONTAINER_RUNTIME_ENV',
            'MARKET_DATA_TICKERS_ACTIVE_VALUE',
            'MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES',
            'MARKET_DATA_TICKERS_ACTIVE_YES_VALUE',
            'REMOVE',
            'RENAME_AND_UPDATE_CALLERS',
            'DEFER_WITH_REASON',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory);
        }

        $this->assertStringNotContainsString('| TBD |', $inventory);
    }

    public function test_schema_truth_for_tickers_is_active_is_numeric_boolean_like(): void
    {
        $migration = $this->read('database/migrations/2026_03_22_000001_create_tickers_table.php');
        $schema = $this->read('docs/market_data/db/Database_Schema_MariaDB.sql');
        $sqliteBootstrap = $this->read('tests/Support/UsesMarketDataSqlite.php');
        $genericTickerDoc = $this->read('docs/db/02_TICKERS_MASTER.md');

        $this->assertStringContainsString("\$table->boolean('is_active')->default(true)", $migration);
        $this->assertStringContainsString('is_active TINYINT(1) NOT NULL DEFAULT 1', $schema);
        $this->assertStringContainsString("\$table->integer('is_active')->default(1)", $sqliteBootstrap);
        $this->assertStringContainsString('BOOLEAN/TINYINT canonical: `1` aktif, `0` tidak aktif', $genericTickerDoc);
        $this->assertStringNotContainsString("ENUM('Yes','No') atau BOOLEAN canonical", $genericTickerDoc);
    }

    public function test_ticker_active_config_uses_numeric_active_value_not_yes_no_alias(): void
    {
        $config = $this->read('config/market_data.php');
        $envExample = $this->read('.env.example');
        $envTesting = $this->read('.env.testing');

        foreach ([$config, $envExample, $envTesting] as $document) {
            $this->assertStringContainsString('MARKET_DATA_TICKERS_ACTIVE_VALUE', $document);
            $this->assertStringNotContainsString('MARKET_DATA_TICKERS_ACTIVE_YES_VALUE', $document);
            $this->assertStringNotContainsString('active_yes_value', $document);
        }

        $this->assertStringContainsString("'active_value' => (int) env('MARKET_DATA_TICKERS_ACTIVE_VALUE', 1)", $config);
        $this->assertMatchesRegularExpression('/^MARKET_DATA_TICKERS_ACTIVE_VALUE=1$/m', $envExample);
        $this->assertMatchesRegularExpression('/^MARKET_DATA_TICKERS_ACTIVE_VALUE=1$/m', $envTesting);
        $this->assertStringNotContainsString('MARKET_DATA_TICKERS_ACTIVE_VALUE=Yes', $envExample.$envTesting);
    }

    public function test_ticker_universe_repository_does_not_accept_ambiguous_yes_no_fallbacks(): void
    {
        $repository = $this->read('app/Infrastructure/Persistence/MarketData/TickerMasterRepository.php');

        $this->assertStringContainsString("\$activeValue = (int) config('market_data.tickers.active_value', 1);", $repository);
        $this->assertStringContainsString('$query->where($activeColumn, $activeValue);', $repository);
        $this->assertStringNotContainsString('activeYesValue', $repository);
        $this->assertStringNotContainsString('active_yes_value', $repository);
        $this->assertStringNotContainsString('orWhere($activeColumn, 1)', $repository);
        $this->assertStringNotContainsString('orWhere($activeColumn, true)', $repository);
    }

    public function test_runtime_fixtures_do_not_seed_tickers_is_active_as_yes_string(): void
    {
        foreach ([
            'tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php',
            'tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php',
        ] as $file) {
            $source = $this->read($file);
            $this->assertStringNotContainsString("'is_active' => 'Yes'", $source, $file);
            $this->assertStringNotContainsString('"is_active" => "Yes"', $source, $file);
        }
    }

    public function test_active_env_keys_are_synchronized_between_env_templates_and_config(): void
    {
        $config = $this->read('config/market_data.php');
        preg_match_all("/env\('([^']+)'/", $config, $configMatches);
        $configEnvKeys = array_values(array_unique($configMatches[1]));
        sort($configEnvKeys);

        foreach (['.env.example', '.env.testing'] as $envFile) {
            $env = $this->read($envFile);
            preg_match_all('/^(MARKET_DATA_[A-Z0-9_]+)=/m', $env, $envMatches);
            $templateEnvKeys = array_values(array_unique($envMatches[1]));
            sort($templateEnvKeys);

            $this->assertSame($configEnvKeys, $templateEnvKeys, $envFile.' must contain exactly the active MARKET_DATA_* keys declared in config/market_data.php.');
        }
    }

    public function test_unused_multi_source_keys_are_pruned_not_left_as_active_config(): void
    {
        $config = $this->read('config/market_data.php');
        $envExample = $this->read('.env.example');
        $envTesting = $this->read('.env.testing');
        $coverageContract = $this->read('docs/market_data/book/Coverage_Edge_Cases_Contract_LOCKED.md');

        foreach ([$config, $envExample, $envTesting] as $document) {
            $this->assertStringNotContainsString('MARKET_DATA_MULTI_SOURCE_MODE', $document);
            $this->assertStringNotContainsString('MARKET_DATA_ALLOW_MIXED_SOURCES', $document);
            $this->assertStringNotContainsString('multi_source_mode', $document);
            $this->assertStringNotContainsString('allow_mixed_sources', $document);
        }

        $this->assertStringContainsString('there is no active env/config key that permits multi-source row mixing', $coverageContract);
        $this->assertStringContainsString('are pruned as unused/stale config surfaces', $coverageContract);
    }

    public function test_delay_window_config_is_active_and_documented_in_env_templates(): void
    {
        $config = $this->read('config/market_data.php');
        $envExample = $this->read('.env.example');
        $envTesting = $this->read('.env.testing');
        $pipeline = $this->read('app/Application/MarketData/Services/MarketDataPipelineService.php');

        $this->assertStringContainsString("'delay_window_minutes' => (int) env('MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES', 60)", $config);
        $this->assertMatchesRegularExpression('/^MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES=60$/m', $envExample);
        $this->assertMatchesRegularExpression('/^MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES=60$/m', $envTesting);
        $this->assertStringContainsString("config('market_data.coverage_edge_cases.delay_window_minutes'", $pipeline);
    }

    public function test_cleanup_does_not_regress_source_mode_read_side_or_db_integrity_contracts(): void
    {
        $inventory = $this->read('docs/market_data/audit/CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md');
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        foreach ([
            'source-mode, coverage, read-side pointer, publication, replay, evidence, or DB integrity policy',
            'Source mode non-regression',
            'Read-side non-regression',
            'DB integrity FK/implicit policy non-regression',
        ] as $needle) {
            $this->assertStringContainsString($needle, $inventory.$status.$tracker);
        }

        foreach ([
            'IMPORT_PROMOTE_SEPARATION_CONTRACT',
            'READ_SIDE_POINTER_ENFORCEMENT_CONTRACT',
            'DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_CONTRACT',
        ] as $contract) {
            $this->assertStringContainsString($contract, $tracker);
        }
    }

    public function test_audit_docs_preserve_config_env_cleanup_history_without_requiring_it_as_active_session(): void
    {
        $status = $this->read('docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md');
        $tracker = $this->read('docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md');

        foreach ([$status, $tracker] as $document) {
            $this->assertStringContainsString('CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md', $document);
            $this->assertStringContainsString('ConfigEnvGovernanceCleanupStaticGuardTest.php', $document);
            $this->assertStringContainsString('TickerMasterRepositoryTest.php', $document);
            $this->assertStringContainsString('BLOCKED_CONTAINER_RUNTIME_ENV', $document);
            $this->assertStringContainsString('LOCKED_LOCAL_PHPUNIT_PASS', $document);
            $this->assertStringContainsString('DB Integrity FK / Implicit Integrity Decision', $document, 'Previous audit history must remain present.');
        }

        $this->assertStringContainsString("ACTIVE SESSION:
- Market Benchmark + Indicator Extension / Final Production Ready Re-Lock", $status);
        $this->assertStringContainsString("ACTIVE SESSION:
- Market Benchmark + Indicator Extension / Final Production Ready Re-Lock", $tracker);
        $this->assertStringContainsString('REPLAY_DETERMINISM_RUNTIME_PROOF_CONTRACT', $status.$tracker);
        $this->assertStringContainsString('EVIDENCE_EXPORT_RUNTIME_PROOF_CONTRACT', $status.$tracker);
        $this->assertStringContainsString('DB Schema & Migration Sync / Runtime Schema Four-Way Synchronization', $status.$tracker);
        $this->assertStringContainsString('Ops Environment Baseline', $status.$tracker, 'Latest Ops Environment history must remain present.');
        $this->assertStringContainsString('- Config / ENV Governance Cleanup -> DONE', $status);
        $this->assertStringContainsString('[RELATED_CONTRACT] CONFIG_ENV_GOVERNANCE_CLEANUP_CONTRACT', $status);
        $this->assertStringContainsString('- CONFIG_ENV_GOVERNANCE_CLEANUP_CONTRACT -> LOCKED', $tracker);
        $this->assertStringContainsString('[RELATED_IMPLEMENTATION] Config / ENV Governance Cleanup', $tracker);
        $this->assertStringContainsString('Operator-local full suite: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (427 tests, 6198 assertions).', $status.$tracker);
    }
}
