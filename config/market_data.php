<?php

return [
    'scope' => [
        'market_code' => 'IDX',
        'market_segment' => 'REGULAR',
        'frequency' => 'EOD',
        'timezone' => 'Asia/Jakarta',
        'dataset_start' => env('MARKET_DATA_DATASET_START', '2023-01-02'),
        'operational_start_date' => env('MARKET_DATA_OPERATIONAL_START_DATE', null),
        'canonical_product_code' => 'IDX_REGULAR_EOD_RAW_V1',
        'raw_product_code' => 'RAW',
        'structural_adjusted_product_code' => 'STRUCTURAL_ADJUSTED',
        'total_return_product_code' => 'TOTAL_RETURN',
        'data_usability_field' => 'data_usable',
        'compatibility_eligibility_field' => 'eligible',
        'contract_version' => 'market_data_scope_v2',
    ],
    'platform' => [
        'timezone' => env('MARKET_DATA_PLATFORM_TIMEZONE', 'Asia/Jakarta'),
        'seal_required_for_consumers' => (bool) env('MARKET_DATA_SEAL_REQUIRED_FOR_CONSUMERS', true),
        'cutoff_time' => env('MARKET_DATA_PLATFORM_EOD_CUTOFF_TIME', '17:15:00'),
        'cutoff_grace_minutes' => (int) env('MARKET_DATA_CUT_OFF_GRACE_MINUTES', 15),
        'coverage_min' => (float) env('MARKET_DATA_COVERAGE_MIN', 0.98), // legacy alias; owner block lives under coverage_gate.min_ratio
        // price_basis_default was pruned on 2026-08-11 (F-024). Its registry entry permitted it only
        // "while compatibility code exists"; no reader remained, so the key described a selection the
        // platform no longer makes. The analytical product is AnalyticalProductIdentityService.
    ],
    'pipeline' => [
        'daily_enabled' => (bool) env('MARKET_DATA_DAILY_ENABLED', false),
        'default_source_mode' => env('MARKET_DATA_DEFAULT_SOURCE_MODE', 'api'),
        'active_run_stale_minutes' => (int) env('MARKET_DATA_ACTIVE_RUN_STALE_MINUTES', 1440),
    ],
    'scheduler' => [
        'output_path' => env('MARKET_DATA_SCHEDULER_OUTPUT_PATH', 'storage/logs/market-data-scheduler.log'),
        'without_overlapping_minutes' => (int) env('MARKET_DATA_SCHEDULER_WITHOUT_OVERLAPPING_MINUTES', 120),
    ],
    'coverage_edge_cases' => [
        'delay_window_minutes' => (int) env('MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES', 60),
    ],
    'coverage_gate' => [
        'enabled' => (bool) env('MARKET_DATA_COVERAGE_GATE_ENABLED', true),
        'min_ratio' => (float) env('MARKET_DATA_COVERAGE_MIN', 0.98),
        'threshold_mode' => env('MARKET_DATA_COVERAGE_THRESHOLD_MODE', 'MIN_RATIO'),
        'blocked_on_zero_universe' => (bool) env('MARKET_DATA_COVERAGE_BLOCK_ZERO_UNIVERSE', true),
        'require_canonical_bar_evidence' => (bool) env('MARKET_DATA_COVERAGE_REQUIRE_CANONICAL_BAR_EVIDENCE', true),
        'universe_basis' => env('MARKET_DATA_COVERAGE_UNIVERSE_BASIS', 'ACTIVE_LISTED_EQUITY_AS_OF_DATE'),
        'contract_version' => env('MARKET_DATA_COVERAGE_CONTRACT_VERSION', 'coverage_gate_v1'),
        'missing_sample_limit' => (int) env('MARKET_DATA_COVERAGE_MISSING_SAMPLE_LIMIT', 25),
        // A ticker silent for this many trading days is no longer expected to produce a bar.
        // Deliberately far beyond normal illiquidity; see Coverage_Universe_Definition_LOCKED.md.
        'dormant_absence_trading_days' => (int) env('MARKET_DATA_COVERAGE_DORMANT_ABSENCE_TRADING_DAYS', 60),
    ],
    'indicators' => [
        'set_version' => env('MARKET_DATA_INDICATOR_SET_VERSION', 'v1'),
        'price_product_default' => env('MARKET_DATA_INDICATOR_PRICE_PRODUCT_DEFAULT', 'STRUCTURAL_ADJUSTED'),
        'price_product_version' => env('MARKET_DATA_INDICATOR_PRICE_PRODUCT_VERSION', 'structural_adjusted_v1'),
        'factor_formula_version' => env('MARKET_DATA_FACTOR_FORMULA_VERSION', 'structural_factor_product_v1'),
        'dv_window_days' => (int) env('MARKET_DATA_DV_WINDOW_DAYS', 20),
        'atr_window_days' => (int) env('MARKET_DATA_ATR_WINDOW_DAYS', 14),
        'vol_ratio_lookback_days' => (int) env('MARKET_DATA_VOL_RATIO_LOOKBACK_DAYS', 20),
        'roc_lookback_days' => (int) env('MARKET_DATA_ROC_LOOKBACK_DAYS', 20),
        'hh_window_days' => (int) env('MARKET_DATA_HH_WINDOW_DAYS', 20),
    ],
    'price_scale_break' => [
        'contract_version' => env('MARKET_DATA_PRICE_SCALE_BREAK_CONTRACT_VERSION', 'price_scale_break_v1'),
        'min_ratio' => (float) env('MARKET_DATA_PRICE_SCALE_BREAK_MIN_RATIO', 1.7),
        'min_price_idr' => (float) env('MARKET_DATA_PRICE_SCALE_BREAK_MIN_PRICE_IDR', 50),
        'action_match_trading_days' => (int) env('MARKET_DATA_PRICE_SCALE_BREAK_ACTION_MATCH_TRADING_DAYS', 5),
        'ratio_tolerance' => (float) env('MARKET_DATA_PRICE_SCALE_BREAK_RATIO_TOLERANCE', 0.08),
    ],
    'hash' => [
        'algorithm' => env('MARKET_DATA_HASH_ALGORITHM', 'SHA-256'),
        'delimiter' => env('MARKET_DATA_HASH_DELIMITER', '|'),
        'line_separator' => env('MARKET_DATA_HASH_LINE_SEPARATOR', "\n"),
        'null_token' => env('MARKET_DATA_HASH_NULL_TOKEN', '[empty]'),
    ],
    'source' => [
        'adapter_contract_version' => 'provider_neutral_eod_source_v2',
        'observation_schema_version' => 'source_observation_v2',
        'observation_retention_version' => 'bounded_payload_v1',
        'canonicalization_version' => 'idx_regular_raw_v2',
        'mapping_revision' => 'temporal_provider_mapping_v1',
        'bounded_payload_bytes' => (int) env('MARKET_DATA_SOURCE_BOUNDED_PAYLOAD_BYTES', 65536),
        'local_directory' => env('MARKET_DATA_SOURCE_LOCAL_DIRECTORY', 'storage/app/market_data/eod_bars'),
        'file_template_json' => env('MARKET_DATA_SOURCE_FILE_TEMPLATE_JSON', '{date}.json'),
        'file_template_csv' => env('MARKET_DATA_SOURCE_FILE_TEMPLATE_CSV', '{date}.csv'),
        'default_source_name' => env('MARKET_DATA_SOURCE_DEFAULT_NAME', 'YAHOO_FINANCE'),
        'api' => [
            'provider' => env('MARKET_DATA_SOURCE_API_PROVIDER', 'yahoo_finance'),
            'adapter_version' => env('MARKET_DATA_SOURCE_API_ADAPTER_VERSION', 'yahoo_chart_v2'),
            'schema_version' => env('MARKET_DATA_SOURCE_API_SCHEMA_VERSION', 'yahoo_chart_schema_v1'),
            'endpoint_template' => env('MARKET_DATA_SOURCE_API_ENDPOINT_TEMPLATE', 'https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?period1={period1}&period2={period2}&interval={interval}&includePrePost=false&events=div%2Csplits&corsDomain=finance.yahoo.com'),
            'response_format' => env('MARKET_DATA_SOURCE_API_RESPONSE_FORMAT', 'json'),
            'response_rows_path' => env('MARKET_DATA_SOURCE_API_ROWS_PATH', ''),
            'timeout_seconds' => (int) env('MARKET_DATA_SOURCE_API_TIMEOUT_SECONDS', 20),
            'user_agent' => env('MARKET_DATA_SOURCE_API_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36'),
            'auth_header_name' => env('MARKET_DATA_SOURCE_API_AUTH_HEADER_NAME', ''),
            'auth_token' => env('MARKET_DATA_SOURCE_API_AUTH_TOKEN', ''),
            'source_name' => env('MARKET_DATA_SOURCE_API_NAME', 'YAHOO_FINANCE'),
            'yahoo' => [
                'symbol_suffix' => env('MARKET_DATA_SOURCE_YAHOO_SYMBOL_SUFFIX', '.JK'),
                'range' => env('MARKET_DATA_SOURCE_YAHOO_RANGE', '10d'),
                'interval' => env('MARKET_DATA_SOURCE_YAHOO_INTERVAL', '1d'),
            ],
            'field_map' => [
                'ticker_code' => env('MARKET_DATA_SOURCE_API_FIELD_TICKER_CODE', 'ticker_code'),
                'trade_date' => env('MARKET_DATA_SOURCE_API_FIELD_TRADE_DATE', 'trade_date'),
                'open' => env('MARKET_DATA_SOURCE_API_FIELD_OPEN', 'open'),
                'high' => env('MARKET_DATA_SOURCE_API_FIELD_HIGH', 'high'),
                'low' => env('MARKET_DATA_SOURCE_API_FIELD_LOW', 'low'),
                'close' => env('MARKET_DATA_SOURCE_API_FIELD_CLOSE', 'close'),
                'volume' => env('MARKET_DATA_SOURCE_API_FIELD_VOLUME', 'volume'),
                'adj_close' => env('MARKET_DATA_SOURCE_API_FIELD_ADJ_CLOSE', 'adj_close'),
                'source_row_ref' => env('MARKET_DATA_SOURCE_API_FIELD_SOURCE_ROW_REF', 'source_row_ref'),
                'captured_at' => env('MARKET_DATA_SOURCE_API_FIELD_CAPTURED_AT', 'captured_at'),
            ],
        ],
        'api_backfill' => [
            'window_days' => (int) env('MARKET_DATA_API_BACKFILL_WINDOW_DAYS', 90),
            'warmup_days' => (int) env('MARKET_DATA_API_BACKFILL_WARMUP_DAYS', 120),
            'warmup_trading_days' => (int) env('MARKET_DATA_API_BACKFILL_WARMUP_TRADING_DAYS', env('MARKET_DATA_API_BACKFILL_WARMUP_DAYS', 120)),
            'concurrency' => (int) env('MARKET_DATA_API_BACKFILL_CONCURRENCY', 5),
            'max_dates_per_run' => (int) env('MARKET_DATA_API_BACKFILL_MAX_DATES_PER_RUN', 20),
            'collect_all_errors' => (bool) env('MARKET_DATA_API_BACKFILL_COLLECT_ALL_ERRORS', false),
            'default_error_policy' => env('MARKET_DATA_API_BACKFILL_DEFAULT_ERROR_POLICY', 'stop_on_error'),
        ],
    ],
    'tickers' => [
        'table' => env('MARKET_DATA_TICKERS_TABLE', 'tickers'),
        'id_column' => env('MARKET_DATA_TICKERS_ID_COLUMN', 'ticker_id'),
        'code_column' => env('MARKET_DATA_TICKERS_CODE_COLUMN', 'ticker_code'),
        'active_column' => env('MARKET_DATA_TICKERS_ACTIVE_COLUMN', 'is_active'),
        'active_value' => (int) env('MARKET_DATA_TICKERS_ACTIVE_VALUE', 1),
        'listed_date_column' => env('MARKET_DATA_TICKERS_LISTED_DATE_COLUMN', 'listed_date'),
        'delisted_date_column' => env('MARKET_DATA_TICKERS_DELISTED_DATE_COLUMN', 'delisted_date'),
        'temporal_projection_version' => 'legacy_ticker_temporal_projection_v1',
    ],
    'governance' => [
        'config_snapshot_schema_version' => 'market_data_config_snapshot_v1',
        'config_serialization_version' => 'canonical_json_v1',
        'config_registry_revision' => 'platform_config_registry_v2',
        'config_resolver_version' => 'market_data_config_resolver_v1',
        'build_id' => env('MARKET_DATA_BUILD_ID', 'development-worktree'),
        'environment_profile' => env('MARKET_DATA_ENVIRONMENT_PROFILE', 'local'),
        'credential_profile' => env('MARKET_DATA_CREDENTIAL_PROFILE', 'bootstrap-public-access'),
    ],
    'sectors' => [
        'table' => env('MARKET_DATA_SECTORS_TABLE', 'market_data_sectors'),
        'membership_table' => env('MARKET_DATA_SECTOR_MEMBERSHIP_TABLE', 'ticker_sector_memberships'),
        'classification_system' => env('MARKET_DATA_SECTOR_CLASSIFICATION_SYSTEM', 'IDX-IC'),
        'index_provider' => env('MARKET_DATA_SECTOR_INDEX_PROVIDER', 'manual_sector_index_csv'),
        'index_api' => [
            'provider' => env('MARKET_DATA_SECTOR_INDEX_API_PROVIDER', 'yahoo_finance'),
            'symbol_suffix' => env('MARKET_DATA_SECTOR_INDEX_API_SYMBOL_SUFFIX', '.JK'),
            'provider_symbols' => json_decode(env('MARKET_DATA_SECTOR_INDEX_API_PROVIDER_SYMBOLS_JSON', '{}'), true) ?: [],
        ],
    ],
    'event_risk' => [
        'corporate_actions_table' => env('MARKET_DATA_CORPORATE_ACTIONS_TABLE', 'market_data_corporate_actions'),
        'trading_status_events_table' => env('MARKET_DATA_TRADING_STATUS_EVENTS_TABLE', 'market_data_trading_status_events'),
        'trading_status_event_types_table' => env('MARKET_DATA_TRADING_STATUS_EVENT_TYPES_TABLE', 'market_data_trading_status_event_types'),
        'corporate_action_types_table' => env('MARKET_DATA_CORPORATE_ACTION_TYPES_TABLE', 'market_data_corporate_action_types'),
        'price_scale_breaks_table' => env('MARKET_DATA_PRICE_SCALE_BREAKS_TABLE', 'market_data_price_scale_breaks'),
        'corporate_action_source_name' => env('MARKET_DATA_CORPORATE_ACTION_SOURCE_NAME', 'manual_corporate_action_csv'),
        'trading_status_source_name' => env('MARKET_DATA_TRADING_STATUS_SOURCE_NAME', 'manual_trading_status_csv'),
    ],
    'evidence' => [
        'output_directory' => env('MARKET_DATA_EVIDENCE_OUTPUT_DIRECTORY', 'storage/app/market_data/evidence'),
        'invalid_bars_export_sample_limit' => (int) env('MARKET_DATA_INVALID_BARS_EXPORT_SAMPLE_LIMIT', 1000),
    ],
    'provider' => [
        'api_retry_max' => (int) env('MARKET_DATA_API_RETRY_MAX', 3),
        'api_backoff_ms' => (int) env('MARKET_DATA_API_BACKOFF_MS', 500),
        'api_throttle_qps' => (int) env('MARKET_DATA_API_THROTTLE_QPS', 5),
        'circuit_breaker_error_rate' => (float) env('MARKET_DATA_CIRCUIT_BREAKER_ERROR_RATE', 0.5),
    ],
    'session_snapshot' => [
        /*
         * Explicit feature state, defaulting to disabled.
         *
         * The snapshot is optional supplemental reference data and has never been captured — the
         * table holds zero rows. Without a declared state a reader cannot tell whether that means
         * "switched off by decision" or "switched on and failing", and the stage 17 outcome
         * requires the feature state to be explicit rather than inferred from emptiness.
         */
        'enabled' => filter_var(env('MARKET_DATA_SESSION_SNAPSHOT_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'retention_days' => (int) env('MARKET_DATA_SESSION_SNAPSHOT_RETENTION_DAYS', 30),
        'scope_default' => env('MARKET_DATA_SESSION_SNAPSHOT_SCOPE_DEFAULT', 'eligibility_set'),
        'slot_tolerance_minutes' => (int) env('MARKET_DATA_SESSION_SNAPSHOT_SLOT_TOLERANCE_MINUTES', 3),
    ],
];
