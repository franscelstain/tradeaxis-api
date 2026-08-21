<?php

class DbIntegrityConstraintEnforcementStaticGuardTest extends TestCase
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

    public function test_db_integrity_inventory_is_present_and_contract_mapped(): void
    {
        $inventory = $this->read('docs/market_data/tests/Db_Integrity_Constraint_Inventory.md');

        foreach ([
            'DB_INTEGRITY_CONSTRAINT_ENFORCEMENT_CONTRACT',
            'Primary Key',
            'Unique / Business Key',
            'FK / Implicit Integrity',
            'Runtime Index Contract',
            'eod_current_publication_pointer',
            'md_replay_reason_code_counts',
            'vendor/bin/phpunit tests/Unit/MarketData --filter "DbIntegrity"',
        ] as $expected) {
            $this->assertStringContainsString($expected, $inventory);
        }
    }

    public function test_locked_sql_schema_declares_primary_keys_for_all_critical_market_data_tables(): void
    {
        $schema = $this->read('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');

        foreach ([
            'tickers' => 'PRIMARY KEY (ticker_id)',
            'market_calendar' => 'PRIMARY KEY (cal_date)',
            'market_benchmarks' => 'PRIMARY KEY (benchmark_id)',
            'market_benchmark_bars' => 'PRIMARY KEY (benchmark_bar_id)',
            'market_benchmark_indicators' => 'PRIMARY KEY (benchmark_indicator_id)',
            'eod_reason_codes' => 'PRIMARY KEY (code)',
            'eod_bars' => 'PRIMARY KEY (trade_date, ticker_id)',
            'eod_invalid_bars' => 'PRIMARY KEY (invalid_bar_id)',
            'eod_indicators' => 'PRIMARY KEY (trade_date, ticker_id)',
            'eod_eligibility' => 'PRIMARY KEY (trade_date, ticker_id)',
            'eod_runs' => 'PRIMARY KEY (run_id)',
            'eod_run_events' => 'PRIMARY KEY (event_id)',
            'eod_publications' => 'PRIMARY KEY (publication_id)',
            'eod_current_publication_pointer' => 'PRIMARY KEY (trade_date)',
            'eod_dataset_corrections' => 'PRIMARY KEY (correction_id)',
            'eod_bars_history' => 'PRIMARY KEY (publication_id, trade_date, ticker_id)',
            'eod_indicators_history' => 'PRIMARY KEY (publication_id, trade_date, ticker_id)',
            'eod_eligibility_history' => 'PRIMARY KEY (publication_id, trade_date, ticker_id)',
            'md_session_snapshots' => 'PRIMARY KEY (snapshot_id)',
            'md_replay_daily_metrics' => 'PRIMARY KEY (replay_id, trade_date)',
            'md_replay_reason_code_counts' => 'PRIMARY KEY (replay_id, trade_date, reason_code)',
        ] as $table => $primaryKey) {
            $this->assertStringContainsString($primaryKey, $schema, $table.' must declare '.$primaryKey);
        }
    }

    public function test_business_keys_and_runtime_indexes_are_explicit_in_sql_schema_and_sqlite_mirror(): void
    {
        $schema = $this->read('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');
        $sqlite = $this->read('tests/Support/UsesMarketDataSqlite.php');
        $migration = $this->read('database/migrations/2026_05_07_000002_enforce_market_data_db_integrity_indexes.php')
            .$this->read('database/migrations/2026_05_24_000001_add_market_benchmark_indicator_extension.php');

        foreach ([
            'UNIQUE KEY ticker_code (ticker_code)',
            'UNIQUE KEY uq_market_benchmarks_code (benchmark_code)',
            'UNIQUE KEY uq_market_benchmark_bars_code_date (benchmark_code, trade_date)',
            'UNIQUE KEY uq_market_benchmark_indicators_code_date_version (benchmark_code, trade_date, indicator_set_version)',
            'UNIQUE KEY uq_publication_trade_date_version (trade_date, publication_version)',
            'UNIQUE KEY uq_current_publication_pointer_publication (publication_id)',
            'UNIQUE KEY md_session_snapshots_trade_date_snapshot_slot_ticker_id_unique (trade_date, snapshot_slot, ticker_id)',
            'KEY idx_runs_effective_readable_contract (trade_date_effective, terminal_status, publishability_state, coverage_gate_state, is_current_publication)',
            'KEY idx_runs_source_identity (source, source_name, source_provider, source_file_hash)',
            'KEY idx_publication_readable_lookup (trade_date, is_current, seal_state, publication_version, run_id)',
            'KEY idx_publication_run_trade_date (run_id, trade_date, publication_id)',
            'KEY idx_current_publication_pointer_run_version (run_id, publication_version)',
            'KEY idx_market_benchmark_bars_code_date (benchmark_code, trade_date)',
            'KEY idx_market_benchmark_indicators_code_date (benchmark_code, trade_date)',
            'KEY idx_eod_bars_publication_date_ticker (publication_id, trade_date, ticker_id)',
            'KEY idx_eod_indicators_publication_date_ticker (publication_id, trade_date, ticker_id)',
            'KEY idx_eod_eligibility_publication_date_ticker (publication_id, trade_date, ticker_id)',
            'KEY idx_corr_trade_date_status_execution (trade_date, status, execution_count)',
            'KEY idx_corr_prior_new_run (prior_run_id, new_run_id)',
            'KEY idx_corr_baseline_publication (baseline_publication_id)',
            'KEY idx_corr_replacement_publication (replacement_publication_id)',
            'KEY idx_corr_baseline_replacement_publication (baseline_publication_id, replacement_publication_id)',
            'KEY idx_replay_daily_publication_identity (replay_id, publication_id, publication_version)',
            'KEY idx_replay_reason_code (replay_id, reason_code)',
        ] as $definition) {
            $this->assertStringContainsString($definition, $schema, 'SQL schema missing integrity definition: '.$definition);
        }

        foreach ([
            "unique(['trade_date', 'publication_version'], 'uq_publication_trade_date_version')",
            "unique('benchmark_code', 'uq_market_benchmarks_code')",
            "unique(['benchmark_code', 'trade_date'], 'uq_market_benchmark_bars_code_date')",
            "unique(['benchmark_code', 'trade_date', 'indicator_set_version'], 'uq_market_benchmark_indicators_code_date_version')",
            "unique(['publication_id'], 'uq_current_publication_pointer_publication')",
            "index(['trade_date_effective', 'terminal_status', 'publishability_state', 'coverage_gate_state', 'is_current_publication'], 'idx_runs_effective_readable_contract')",
            "index(['source', 'source_name', 'source_provider', 'source_file_hash'], 'idx_runs_source_identity')",
            "index(['trade_date', 'is_current', 'seal_state', 'publication_version', 'run_id'], 'idx_publication_readable_lookup')",
            "index(['run_id', 'trade_date', 'publication_id'], 'idx_publication_run_trade_date')",
            "index(['benchmark_code', 'trade_date'], 'idx_market_benchmark_bars_code_date')",
            "index(['benchmark_code', 'trade_date'], 'idx_market_benchmark_indicators_code_date')",
            "index(['publication_id', 'trade_date', 'ticker_id'], 'idx_eod_bars_publication_date_ticker')",
            "index(['publication_id', 'trade_date', 'ticker_id'], 'idx_eod_indicators_publication_date_ticker')",
            "index(['publication_id', 'trade_date', 'ticker_id'], 'idx_eod_eligibility_publication_date_ticker')",
            "index(['baseline_publication_id'], 'idx_corr_baseline_publication')",
            "index(['replacement_publication_id'], 'idx_corr_replacement_publication')",
            "index(['baseline_publication_id', 'replacement_publication_id'], 'idx_corr_baseline_replacement_publication')",
            "primary(['replay_id', 'trade_date', 'reason_code'])",
        ] as $definition) {
            $this->assertStringContainsString($definition, $sqlite, 'SQLite mirror missing integrity definition: '.$definition);
        }

        foreach ([
            'idx_runs_effective_readable_contract',
            'idx_runs_source_identity',
            'idx_publication_readable_lookup',
            'idx_publication_run_trade_date',
            'idx_current_publication_pointer_run_version',
            'idx_market_benchmark_bars_code_date',
            'idx_market_benchmark_indicators_code_date',
            'idx_eod_bars_publication_date_ticker',
            'idx_eod_indicators_publication_date_ticker',
            'idx_eod_eligibility_publication_date_ticker',
            'idx_corr_trade_date_status_execution',
            'idx_corr_prior_new_run',
            'idx_corr_baseline_publication',
            'idx_corr_replacement_publication',
            'idx_corr_baseline_replacement_publication',
        ] as $index) {
            $this->assertStringContainsString($index, $migration, 'Integrity migration missing index '.$index);
        }
    }

    public function test_implicit_integrity_policy_and_repository_guards_cover_non_fk_lifecycle_relations(): void
    {
        $schema = $this->read('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');
        $publicationRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php');
        $evidenceRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php');
        $correctionRepository = $this->read('app/Infrastructure/Persistence/MarketData/EodCorrectionRepository.php');

        $this->assertStringContainsString('any non-FK relation must have an implicit guard test', $schema);

        foreach ([
            "whereColumn('ptr.run_id', 'pub.run_id')",
            "whereColumn('ptr.publication_version', 'pub.publication_version')",
            "whereColumn('run.publication_id', 'ptr.publication_id')",
            "whereColumn('run.publication_version', 'ptr.publication_version')",
            'determineCurrentIntegrityViolationReasons',
            'PUBLICATION_ROW_MISSING',
            'RUN_PUBLICATION_ID_MISMATCH',
            'RUN_COVERAGE_GATE_NOT_PASS',
            'RUN_COVERAGE_TELEMETRY_INVALID',
        ] as $guard) {
            $this->assertStringContainsString($guard, $publicationRepository, 'Publication pointer implicit guard missing: '.$guard);
        }

        foreach (['eod_runs', 'eod_publications', 'eod_current_publication_pointer', 'eod_dataset_corrections'] as $table) {
            $this->assertStringContainsString($table, $evidenceRepository, 'Evidence repository must preserve lineage context through '.$table);
        }

        foreach (['prior_run_id', 'new_run_id', 'correction_reason_code', 'status'] as $column) {
            $this->assertStringContainsString($column, $correctionRepository, 'Correction repository must persist guarded correction linkage column '.$column);
        }
    }

    public function test_enum_like_values_are_not_schema_or_registry_orphaned(): void
    {
        $schema = $this->read('docs/market_data/development/implementation/db/Database_Schema_MariaDB.sql');
        $registry = $this->read('docs/market_data/authority/strategy/registry/Reason_Codes_Registry.md');
        $seed = $this->read('docs/market_data/development/implementation/db/registry/Reason_Codes_Seed.sql');

        foreach ([
            "ENUM('SUCCESS','HELD','FAILED')",
            "ENUM('NOT_READABLE','READABLE')",
            "ENUM('PASS','FAIL','NOT_EVALUABLE')",
            "ENUM('SEALED','UNSEALED')",
            "ENUM('REQUESTED','APPROVED','EXECUTING','RESEALED','REPAIR_ACTIVE','REPAIR_EXECUTED','REPAIR_CANDIDATE','CONSUMED_CURRENT','PUBLISHED','FAILED','REJECTED','CANCELLED','CLOSED')",
        ] as $enum) {
            $this->assertStringContainsString($enum, $schema, 'SQL schema missing enum-like value set: '.$enum);
        }

        foreach ([
            'COVERAGE_BELOW_THRESHOLD',
            'RUN_LOCK_CONFLICT',
            'RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID',
            'RUN_SOURCE_PARTIAL_RESPONSE',
            'REPLAY_NON_DETERMINISTIC_OUTPUT',
        ] as $reasonCode) {
            $this->assertStringContainsString($reasonCode, $registry, 'Reason code registry missing '.$reasonCode);
            $this->assertStringContainsString($reasonCode, $seed, 'Reason code seed missing '.$reasonCode);
        }
    }

    public function test_runtime_market_data_paths_do_not_use_forbidden_latest_date_shortcuts(): void
    {
        foreach ($this->runtimePhpFiles() as $file) {
            $source = $this->read($file);

            $this->assertDoesNotMatchRegularExpression('/\bMAX\s*\(\s*trade_date\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/->\s*max\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/latest\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/orderByDesc\s*\(\s*[\'\"]trade_date[\'\"]\s*\)/i', $source, $file);
            $this->assertDoesNotMatchRegularExpression('/ORDER\s+BY\s+trade_date\s+DESC/i', $source, $file);
        }
    }

    private function runtimePhpFiles(): array
    {
        $roots = [
            $this->projectPath('app/Application/MarketData'),
            $this->projectPath('app/Infrastructure/Persistence/MarketData'),
            $this->projectPath('app/Infrastructure/MarketData'),
            $this->projectPath('app/Console/Commands/MarketData'),
        ];

        $files = [];
        foreach ($roots as $root) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = str_replace(dirname(__DIR__, 3).DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        sort($files);

        return $files;
    }
}
