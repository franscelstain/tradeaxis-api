<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\UsesMarketDataSqlite;

class MarketDataSqliteSchemaSyncTest extends TestCase
{
    use UsesMarketDataSqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootMarketDataSqlite();
    }

    public function test_sqlite_schema_contains_ticker_calendar_session_and_replay_contract_columns(): void
    {
        foreach ([
            'tickers' => [
                'ticker_id',
                'ticker_code',
                'company_name',
                'company_logo',
                'listed_date',
                'delisted_date',
                'board_code',
                'exchange_code',
                'is_active',
                'created_at',
                'updated_at',
            ],
            'market_calendar' => [
                'cal_date',
                'is_trading_day',
                'holiday_name',
                'session_open_time',
                'session_close_time',
                'breaks_json',
                'source',
                'created_at',
                'updated_at',
            ],
            'market_data_sectors' => [
                'sector_code',
                'sector_name',
                'sector_index_code',
                'classification_system',
                'effective_from',
                'effective_to',
                'is_active',
                'source_name',
                'source_ref',
                'created_at',
                'updated_at',
            ],
            'ticker_sector_memberships' => [
                'membership_id',
                'ticker_id',
                'listing_id',
                'sector_code',
                'classification_system',
                'effective_from',
                'effective_to',
                'source_name',
                'source_ref',
                'source_authority_class',
                'recorded_at',
                'supersedes_membership_id',
                'operator_name',
                'reason_code',
                'created_at',
                'updated_at',
            ],
            'market_data_corporate_actions' => [
                'corporate_action_id',
                'ticker_id',
                'ticker_code',
                'action_date',
                'action_type',
                'source_name',
                'source_ref',
                'notes',
                'created_at',
                'updated_at',
            ],
            'market_data_trading_status_event_types' => [
                'event_type_code',
                'risk_family',
                'transition_type',
                'expected_bar_policy',
                'carries_forward',
                'clears_risk_family',
                'description',
                'created_at',
                'updated_at',
            ],
            'market_data_trading_status_events' => [
                'trading_status_id',
                'ticker_id',
                'ticker_code',
                'trade_date',
                'event_type_code',
                'source_name',
                'source_ref',
                'notes',
                'created_at',
                'updated_at',
            ],
            'market_benchmarks' => [
                'benchmark_id',
                'benchmark_code',
                'benchmark_name',
                'provider',
                'provider_symbol',
                'instrument_type',
                'is_active',
                'created_at',
                'updated_at',
            ],
            'market_benchmark_bars' => [
                'benchmark_bar_id',
                'benchmark_code',
                'trade_date',
                'open_price',
                'high_price',
                'low_price',
                'close_price',
                'adjusted_close',
                'volume',
                'provider',
                'provider_symbol',
                'created_at',
                'updated_at',
            ],
            'market_benchmark_indicators' => [
                'benchmark_indicator_id',
                'benchmark_code',
                'trade_date',
                'roc_20',
                'ma20',
                'ma50',
                'ma20_slope_pct',
                'close_to_ma20_pct',
                'close_to_ma50_pct',
                'is_valid',
                'invalid_reason_code',
                'indicator_set_version',
                'created_at',
                'updated_at',
            ],
            'eod_reason_codes' => [
                'code',
                'category',
                'description',
                'severity',
                'is_active',
                'created_at',
                'updated_at',
            ],
            'eod_indicators' => [
                'ma20',
                'ma50',
                'sector_code',
                'roc5',
                'roc10',
                'll20',
                'close_to_hh20_pct',
                'close_to_ll20_pct',
                'range_20_pct',
                'range_position_20_pct',
                'close_vs_ma20_pct',
                'close_vs_ma50_pct',
                'ma20_slope_pct',
                'rs_20_vs_ihsg',
                'sector_roc20',
                'rs_20_vs_sector',
                'sector_rs_20_vs_ihsg',
                'corporate_action_flag',
                'corporate_action_types',
                'trading_status_code',
                'is_suspended',
                'is_uma',
                'event_risk_flag',
                'event_risk_reasons',
            ],
            'eod_indicators_history' => [
                'ma20',
                'ma50',
                'sector_code',
                'roc5',
                'roc10',
                'll20',
                'close_to_hh20_pct',
                'close_to_ll20_pct',
                'range_20_pct',
                'range_position_20_pct',
                'close_vs_ma20_pct',
                'close_vs_ma50_pct',
                'ma20_slope_pct',
                'rs_20_vs_ihsg',
                'sector_roc20',
                'rs_20_vs_sector',
                'sector_rs_20_vs_ihsg',
                'corporate_action_flag',
                'corporate_action_types',
                'trading_status_code',
                'is_suspended',
                'is_uma',
                'event_risk_flag',
                'event_risk_reasons',
            ],
            'md_session_snapshots' => [
                'snapshot_id',
                'trade_date',
                'snapshot_slot',
                'ticker_id',
                'captured_at',
                'last_price',
                'prev_close',
                'chg_pct',
                'volume',
                'day_high',
                'day_low',
                'source',
                'run_id',
                'reason_code',
                'error_note',
                'created_at',
                'updated_at',
            ],
            'md_replay_daily_metrics' => [
                'publishability_state',
                'replay_status',
                'publication_id',
                'publication_run_id',
                'is_current_publication',
                'expected_terminal_status',
                'expected_publishability_state',
                'expected_publication_id',
                'expected_publication_run_id',
                'expected_is_current_publication',
                'expected_config_identity',
                'expected_publication_version',
                'expected_coverage_universe_count',
                'expected_coverage_available_count',
                'expected_coverage_missing_count',
                'expected_coverage_ratio',
                'expected_coverage_min_threshold',
                'expected_coverage_gate_state',
                'expected_coverage_threshold_mode',
                'expected_coverage_universe_basis',
                'expected_coverage_contract_version',
                'expected_coverage_missing_sample_json',
                'expected_bars_batch_hash',
                'expected_indicators_batch_hash',
                'expected_eligibility_batch_hash',
                'expected_reason_code_counts_json',
            ],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    sprintf('Missing SQLite mirror column %s.%s', $table, $column)
                );
            }
        }
    }

    public function test_sqlite_schema_does_not_contain_runtime_orphan_surrogate_keys_on_publication_bound_artifacts(): void
    {
        foreach ([
            'eod_bars' => ['bar_id'],
            'eod_indicators' => ['indicator_id'],
            'eod_eligibility' => ['eligibility_id'],
            'eod_bars_history' => ['history_id'],
            'eod_indicators_history' => ['history_id'],
            'eod_eligibility_history' => ['history_id'],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertFalse(
                    Schema::hasColumn($table, $column),
                    sprintf('SQLite mirror must not contain runtime-orphan column %s.%s', $table, $column)
                );
            }
        }
    }

    public function test_strategy_v2_schema_is_mirrored_and_contains_no_direct_price_repair_surface(): void
    {
        foreach ([
            'md_config_snapshots' => [
                'config_snapshot_id', 'snapshot_uid', 'snapshot_schema_version',
                'serialization_version', 'resolved_config_json', 'config_hash',
                'registry_revision', 'effective_at', 'recorded_at', 'build_id',
                'environment_profile', 'resolver_version', 'created_at',
            ],
            'md_source_observations' => [
                'source_observation_id', 'observation_uid', 'run_id', 'attempt_uid',
                'requested_trade_date', 'source_name', 'provider', 'provider_symbol',
                'provider_mapping_id', 'sanitized_request_identity', 'response_status',
                'content_type', 'source_timestamp', 'acquired_at', 'schema_fingerprint',
                'adapter_version', 'payload_hash', 'payload_ref', 'bounded_payload_body',
                'outcome_state', 'reason_code', 'supersedes_observation_id', 'created_at',
            ],
            'md_issuers' => ['issuer_id', 'issuer_uid', 'legal_name', 'recorded_at', 'created_at'],
            'md_instruments' => ['instrument_id', 'instrument_uid', 'issuer_id', 'instrument_type', 'currency_code', 'recorded_at', 'created_at'],
            'md_listings' => ['listing_id', 'listing_uid', 'instrument_id', 'exchange_code', 'board_code', 'listed_date', 'delisted_date', 'recorded_at', 'created_at'],
            'md_listing_symbols' => ['listing_symbol_id', 'listing_id', 'symbol', 'symbol_type', 'effective_from', 'effective_to', 'recorded_at', 'retracted_at', 'source_observation_id'],
            'md_provider_symbol_mappings' => ['provider_mapping_id', 'listing_id', 'provider', 'provider_symbol', 'effective_from', 'effective_to', 'recorded_at', 'retracted_at', 'source_observation_id', 'mapping_revision'],
            'md_market_calendar_revisions' => ['calendar_revision_id', 'market_code', 'cal_date', 'revision_uid', 'timezone', 'session_state', 'session_open_at', 'session_close_at', 'completed_at', 'recorded_at', 'source_observation_id', 'supersedes_revision_id'],
            'md_trading_status_revisions' => ['status_revision_id', 'listing_id', 'status_code', 'bar_expectation_state', 'full_session_verified', 'effective_from', 'effective_to', 'recorded_at', 'retracted_at', 'source_observation_id', 'supersedes_revision_id'],
            'md_corporate_action_revisions' => ['corporate_action_revision_id', 'event_uid', 'revision_number', 'listing_id', 'action_type_code', 'lifecycle_state', 'verification_state', 'ex_date', 'cum_date', 'record_date', 'payment_date', 'terms_json', 'source_observation_id', 'effective_at', 'recorded_at', 'supersedes_revision_id'],
            'md_adjustment_factor_sets' => ['factor_set_id', 'factor_set_uid', 'price_product_code', 'factor_formula_version', 'config_snapshot_id', 'state', 'content_hash', 'recorded_at', 'created_at'],
            'md_adjustment_factors' => ['adjustment_factor_id', 'factor_set_id', 'listing_id', 'effective_from', 'effective_to', 'price_factor', 'volume_factor', 'corporate_action_revision_id', 'created_at'],
            'md_publication_lineage_bindings' => ['publication_lineage_id', 'publication_id', 'config_snapshot_id', 'factor_set_id', 'observation_manifest_hash', 'identity_revision_set_hash', 'calendar_revision_set_hash', 'status_revision_set_hash', 'event_revision_set_hash', 'formula_version', 'build_id', 'read_model_version', 'created_at'],
            'eod_runs' => ['config_snapshot_id', 'observation_manifest_hash', 'price_product_code', 'price_product_version', 'factor_set_hash', 'coverage_universe_hash', 'coverage_excluded_sample_json', 'knowledge_cutoff_at', 'coverage_expected_count', 'coverage_expectation_unknown_count', 'coverage_delivered_count', 'coverage_delivered_valid_count', 'operational_start_date', 'freshness_state', 'latest_expected_trade_date', 'latest_acquired_trade_date', 'latest_canonicalized_trade_date', 'latest_readable_trade_date'],
            'eod_publications' => ['config_snapshot_id', 'factor_set_id', 'factor_set_hash', 'observation_manifest_hash', 'publication_manifest_hash', 'price_product_code', 'price_product_version', 'read_model_version', 'readiness_state'],
            'eod_bars_history' => ['listing_id', 'source_observation_id', 'previous_close', 'traded_value_idr_actual', 'trade_count_actual', 'board_code', 'session_code', 'source_timestamp', 'acquired_at', 'canonicalization_version', 'price_product_code', 'quality_state', 'config_snapshot_id'],
            'eod_indicators_history' => ['listing_id', 'formula_version', 'config_snapshot_id', 'factor_set_id', 'factor_set_hash', 'price_product_code', 'price_product_version', 'sector_membership_id', 'adv20_traded_value_idr_actual', 'adv20_close_volume_proxy_idr', 'atr14', 'atr_state_ref', 'null_reasons_json'],
            'eod_eligibility_history' => ['listing_id', 'universe_membership_state', 'bar_expectation_state', 'delivery_state', 'canonical_quality_state', 'liquidity_state', 'temporal_status_state', 'event_risk_state', 'eligibility_reasons_json', 'config_snapshot_id'],
        ] as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table), sprintf('Missing V2 SQLite mirror table %s', $table));

            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    sprintf('Missing V2 SQLite mirror column %s.%s', $table, $column)
                );
            }
        }

        foreach ([
            'repair_factor',
            'repair_range_end_date',
            'repaired_bar_count',
            'repaired_history_row_count',
            'repaired_at',
        ] as $column) {
            $this->assertFalse(
                Schema::hasColumn('market_data_price_scale_breaks', $column),
                sprintf('Direct history-repair field must not exist: market_data_price_scale_breaks.%s', $column)
            );
        }

        $this->assertFalse(
            DB::table('market_data_corporate_action_types')->where('action_type_code', 'PRICE_RESCALE_UNCLASSIFIED')->exists(),
            'Price-derived unclassified rescale must not be seeded as an adjustment-authorizing action type'
        );
    }

    public function test_replay_metrics_does_not_contain_sqlite_only_source_file_columns(): void
    {
        foreach ([
            'source_file_hash',
            'source_file_hash_algorithm',
            'source_file_size_bytes',
            'source_file_row_count',
        ] as $column) {
            $this->assertFalse(
                Schema::hasColumn('md_replay_daily_metrics', $column),
                sprintf('SQLite-only replay metric column must not exist: md_replay_daily_metrics.%s', $column)
            );
        }
    }

    public function test_coverage_decimal_precision_is_synchronized_across_schema_migration_and_sqlite_mirror(): void
    {
        $schema = file_get_contents(dirname(__DIR__, 3).'/docs/market_data/db/Database_Schema_MariaDB.sql');
        $metadata = file_get_contents(dirname(__DIR__, 3).'/docs/market_data/db/DB_FIELDS_AND_METADATA.md');
        $migration = file_get_contents(dirname(__DIR__, 3).'/database/migrations/2026_05_19_000001_widen_market_data_coverage_decimal_precision.php');
        $sqlite = file_get_contents(dirname(__DIR__, 3).'/tests/Support/UsesMarketDataSqlite.php');

        foreach ([
            'coverage_ratio DECIMAL(12,6) NULL',
            'coverage_min_threshold DECIMAL(12,6) NULL',
            'expected_coverage_ratio DECIMAL(12,6) NULL',
            'expected_coverage_min_threshold DECIMAL(12,6) NULL',
        ] as $definition) {
            $this->assertStringContainsString($definition, $schema);
        }

        foreach ([
            'coverage_ratio',
            'coverage_min_threshold',
            'expected_coverage_ratio',
            'expected_coverage_min_threshold',
        ] as $column) {
            $this->assertStringContainsString($column, $migration);
        }

        $this->assertStringContainsString('DECIMAL(12,6)', $metadata);
        $this->assertStringNotContainsString('DECIMAL(8,6)', $schema.$metadata);
        $this->assertStringContainsString("decimal('coverage_ratio', 12, 6)", $sqlite);
        $this->assertStringContainsString("decimal('coverage_min_threshold', 12, 6)", $sqlite);
        $this->assertStringContainsString("decimal('expected_coverage_ratio', 12, 6)", $sqlite);
        $this->assertStringContainsString("decimal('expected_coverage_min_threshold', 12, 6)", $sqlite);
    }

    public function test_sqlite_schema_enforces_db_integrity_keys_and_runtime_indexes(): void
    {
        foreach ([
            'tickers' => ['ticker_id'],
            'market_calendar' => ['cal_date'],
            'eod_reason_codes' => ['code'],
            'market_data_sectors' => ['sector_code'],
            'ticker_sector_memberships' => ['membership_id'],
            'market_data_corporate_actions' => ['corporate_action_id'],
            'market_data_trading_status_event_types' => ['event_type_code'],
            'market_data_trading_status_events' => ['trading_status_id'],
            'market_benchmarks' => ['benchmark_id'],
            'market_benchmark_bars' => ['benchmark_bar_id'],
            'market_benchmark_indicators' => ['benchmark_indicator_id'],
            'eod_runs' => ['run_id'],
            'eod_run_events' => ['event_id'],
            'eod_dataset_corrections' => ['correction_id'],
            'eod_publications' => ['publication_id'],
            'eod_current_publication_pointer' => ['trade_date'],
            'eod_bars' => ['trade_date', 'ticker_id'],
            'eod_invalid_bars' => ['invalid_bar_id'],
            'eod_indicators' => ['trade_date', 'ticker_id'],
            'eod_eligibility' => ['trade_date', 'ticker_id'],
            'md_replay_daily_metrics' => ['replay_id', 'trade_date'],
            'md_replay_reason_code_counts' => ['replay_id', 'trade_date', 'reason_code'],
            'md_session_snapshots' => ['snapshot_id'],
            'eod_bars_history' => ['publication_id', 'trade_date', 'ticker_id'],
            'eod_indicators_history' => ['publication_id', 'trade_date', 'ticker_id'],
            'eod_eligibility_history' => ['publication_id', 'trade_date', 'ticker_id'],
        ] as $table => $columns) {
            $this->assertPrimaryKeyColumns($table, $columns);
        }

        foreach ([
            'tickers' => ['ticker_code'],
            'market_benchmarks' => [
                'uq_market_benchmarks_code',
                'idx_market_benchmarks_provider_symbol',
                'idx_market_benchmarks_active_code',
            ],
            'market_data_sectors' => [
                'idx_market_data_sectors_system_active_code',
                'idx_market_data_sectors_index_code',
            ],
            'ticker_sector_memberships' => [
                'uq_sector_membership_listing_effective_known',
                'idx_ticker_sector_membership_ticker_date',
                'idx_ticker_sector_membership_sector_date',
                'idx_sector_membership_listing_effective',
                'idx_sector_membership_known_authority',
                'idx_sector_membership_supersedes',
            ],
            'market_data_corporate_actions' => [
                'uq_md_corp_action_ticker_date_type_source',
                'idx_md_corp_action_date_ticker',
                'idx_md_corp_action_type_date',
            ],
            'market_data_trading_status_event_types' => [
                'idx_md_status_types_family_transition',
                'idx_md_status_types_expected_bar_policy',
            ],
            'market_data_trading_status_events' => [
                'uq_md_trading_status_ticker_date_type_source',
                'idx_md_trading_status_date_ticker',
                'idx_md_trading_status_event_type_date',
            ],
            'market_benchmark_bars' => [
                'uq_market_benchmark_bars_code_date',
                'idx_market_benchmark_bars_code_date',
                'idx_market_benchmark_bars_provider_symbol',
            ],
            'market_benchmark_indicators' => [
                'uq_market_benchmark_indicators_code_date_version',
                'idx_market_benchmark_indicators_code_date',
            ],
            'eod_publications' => [
                'uq_publication_trade_date_version',
                'idx_publication_readable_lookup',
                'idx_publication_run_trade_date',
            ],
            'eod_current_publication_pointer' => [
                'uq_current_publication_pointer_publication',
                'idx_current_publication_pointer_run',
                'idx_current_publication_pointer_run_version',
            ],
            'eod_runs' => [
                'idx_runs_effective_readable_contract',
                'idx_runs_source_identity',
                'idx_runs_publication_id',
                'idx_runs_correction_id',
            ],
            'eod_bars' => ['idx_eod_bars_publication_date_ticker'],
            'eod_indicators' => ['idx_eod_indicators_publication_date_ticker', 'idx_eod_indicators_sector_date', 'idx_eod_indicators_event_risk_date'],
            'eod_eligibility' => ['idx_eod_eligibility_publication_date_ticker'],
            'eod_dataset_corrections' => [
                'idx_corr_trade_date_status_execution',
                'idx_corr_prior_new_run',
                'idx_corr_baseline_publication',
                'idx_corr_replacement_publication',
                'idx_corr_baseline_replacement_publication',
            ],
            'md_replay_daily_metrics' => [
                'idx_replay_daily_status',
                'idx_replay_daily_publishability',
                'idx_replay_daily_publication_identity',
                'idx_replay_daily_effective',
                'idx_replay_daily_comparison',
                'idx_replay_daily_replay_status',
                'idx_replay_daily_coverage_gate',
            ],
            'md_replay_reason_code_counts' => ['idx_replay_reason_code'],
            'md_session_snapshots' => [
                'md_session_snapshots_trade_date_snapshot_slot_ticker_id_unique',
                'md_session_snapshots_trade_date_snapshot_slot_index',
            ],
        ] as $table => $indexes) {
            $this->assertTableHasIndexes($table, $indexes);
        }
    }

    private function assertPrimaryKeyColumns(string $table, array $expectedColumns): void
    {
        $rows = $this->db()->select("PRAGMA table_info('".$table."')");
        $actual = [];

        foreach ($rows as $row) {
            if ((int) $row->pk > 0) {
                $actual[(int) $row->pk] = $row->name;
            }
        }

        ksort($actual);

        $this->assertSame(
            $expectedColumns,
            array_values($actual),
            sprintf('SQLite mirror primary key mismatch for %s', $table)
        );
    }

    private function assertTableHasIndexes(string $table, array $expectedIndexes): void
    {
        $rows = $this->db()->select("PRAGMA index_list('".$table."')");
        $actual = array_map(static function ($row) {
            return $row->name;
        }, $rows);

        foreach ($expectedIndexes as $index) {
            $this->assertContains(
                $index,
                $actual,
                sprintf('Missing SQLite mirror index %s on %s', $index, $table)
            );
        }
    }

}
