# Platform Configuration Registry (STRATEGY LOCKED)

## Purpose

Every market-data run and publication must bind the exact resolved configuration that can affect acquisition, canonical facts, analytical products, eligibility, readability, serialization, or replay. A version label alone is insufficient.

This registry owns configuration identity and snapshot semantics. Domain contracts own the meaning of each configured rule.

## Snapshot object

Before a run writes output, the platform creates an immutable configuration snapshot containing:

- a stable `config_snapshot_id`;
- schema/serialization version;
- effective and recorded/known timestamps;
- canonical, sorted, typed key/value content;
- redacted secret references as described below;
- `SHA-256` content hash; and
- provenance identifying the registry revision, deployment/build, environment profile, and resolver that produced it.

The same non-null snapshot ID and hash bind the run, every output artifact, the publication manifest, and the dataset seal. The resolved snapshot—not the current environment or registry—is used for replay.

Any unresolved, unknown, or null required configuration binding prevents sealing and consumer readability.

### Status of artifacts sealed before this gate was enforced (LOCKED)

The rule above is a gate on future sealing. It says nothing about publications already sealed without a binding, and that silence leaves the existing corpus in an undefined state where non-conformant artifacts are indistinguishable from conformant ones.

Therefore:

- A sealed publication whose run carries no non-null config snapshot ID and hash is **`CONFIG_UNBOUND`**. It is not retroactively invalid as a record of what was published, and it is not admissible as evidence of reproducibility, because the configuration that produced it cannot be recovered.
- `CONFIG_UNBOUND` publications may not be cited in any conformance, replay, or activation claim without naming the state explicitly. Publication replay over them is `BLOCKED`, not `PASS`, since a required bound input is absent.
- They are not silently deleted or resealed. Resealing would attach a configuration the artifact was not produced under, which is a stronger falsehood than the missing binding.
- Closing the state requires re-derivation under a bound configuration through the normal publication lifecycle, or explicit retirement of the affected range.
- The count of `CONFIG_UNBOUND` publications is reported until it reaches zero. A shrinking count is progress; an unreported count is the condition this section exists to prevent.

**A gate that was never enforced does not make its corpus conformant by silence.** Where a locked rule would have blocked what already exists, the honest reading is that the rule was unimplemented, not that the existing artifacts satisfied it.

## Output-affecting key families

The snapshot includes every applicable key in these families, including resolved defaults and feature states.

### Acquisition and normalization

- selected acquisition source and adapter version;
- provider endpoint/data class, request-window semantics, rate limit, retry/backoff, timeout, and circuit-breaker rules;
- immutable observation-envelope and payload-retention versions;
- provider-symbol mapping and provider schema/field-mapping versions;
- response schema, timestamp, timezone, stale-date, duplicate, and invalid-value validation rules;
- canonicalization and numeric/unit normalization versions.

### Temporal identity and market expectation

- universe/listing/provider-mapping snapshot or revision selectors;
- Regular-Market calendar and session-completion revision selectors;
- trading-status/suspension revision selectors;
- requested-date, latest-expected-date, cutoff, activation, grace-period, and freshness rules;
- intentional dataset start `2023-01-02` and operational activation date/state.

### Coverage, quality, and data usability

- bar-expectation and delivery definitions;
- coverage threshold such as `COVERAGE_MIN`;
- quality rules, accepted/blocked reason sets, and quarantine policy;
- liquidity and status/event-risk fact versions;
- data-usability decision/reason registry versions.

Dormancy, zero volume, current active state, and provider absence are not configurable denominator exclusions.

Watchlist thresholds for tradability, ranking, entry/exit, or portfolio preference are not market-data configuration and must not enter this snapshot. A factual metric's required-input validation may be configured, but a strategy preference cannot change data usability.

### Price products and corporate actions

- canonical `RAW` product version;
- selected analytical price product, defaulting to `STRUCTURAL_ADJUSTED` for the initial EOD indicator profile;
- corporate-action type, lifecycle, verification hierarchy, and effective/ex-date semantics;
- factor construction, factor-set revision, rounding, and volume-transformation rules;
- detector thresholds and quarantine behavior;
- contamination horizons and reason-code registry version.

No configuration may enable synthetic candidates, provider adjustment fields, or price jumps to become verified factors automatically. No force flag may enable in-place published-history repair.

### Indicators and daily metrics

- formula/indicator registry versions and required field set;
- exact window definitions, inclusive/exclusive endpoints, ATR seed and recursive-state version;
- warm-up/history dependency rules expressed in trading sessions;
- price basis, benchmark/sector dependency versions, precision, rounding, nullability, and reason rules;
- actual traded-value field/version and close-volume proxy label/formula;
- daily aggregate partialness/completeness rules.

Representative windows include MA20, MA50, ROC5/10/20, range20, volume ratio 20, liquidity 20, and Wilder ATR14. A configured acquisition warm-up does not redefine the stable ATR seed/state.

### Publication, correction, and reads

- immutable snapshot/history schema versions;
- candidate, validation, sealing, activation/pointer-switch, supersession, and rollback policies;
- content and manifest hash definitions;
- minimum market-data consumer read-model version;
- freshness/readiness state machine and requested-versus-effective-date behavior;
- correction impact-graph and replay-mode versions.

### Hash and serialization

- hash algorithms;
- canonical table/column ordering;
- row sort keys;
- null, boolean, timestamp, decimal, collection, and text normalization;
- character encoding, delimiter/escaping, and line separator;
- inclusion rules for values, annotations, reason sets, lineage, config, and manifest references.

Every published field and semantic annotation belongs in its artifact hash. The config snapshot hash and ID belong in the publication/seal manifest even when two configurations happen to produce equal numeric rows.

## Resolved key register (LOCKED)

The families above classify configuration; this register enumerates the keys that actually resolve at runtime. It exists because this contract declares a key present in runtime code but absent from the registry to be a sealing error, and because the per-key metadata required below has no target list without it.

Columns record only what is verifiable from the resolver: canonical key, type, resolved default when no environment value is supplied, and the environment variable read. **Meaning, allowed values, and output-affecting classification stay with the owner contract** named in the last column, per the ownership rule above. This register must not restate them.

Generated from `config/market_data.php` on 2026-08-03: 128 keys. A key added, removed, or retyped in the resolver without updating this register is the sealing error described above.

| Key | Type | Default | Environment input | Owner contract |
|---|---|---|---|---|
| `market_data.scope.market_code` | string | — | — | `../book/Terminology_and_Scope.md` |
| `market_data.scope.market_segment` | string | — | — | `../book/Terminology_and_Scope.md` |
| `market_data.scope.frequency` | string | — | — | `../book/Terminology_and_Scope.md` |
| `market_data.scope.timezone` | string | `Asia/Jakarta` | — | `../book/Terminology_and_Scope.md` |
| `market_data.scope.dataset_start` | string | `2023-01-02` | `MARKET_DATA_DATASET_START` | `../book/Terminology_and_Scope.md` |
| `market_data.scope.operational_start_date` | null | `null` | `MARKET_DATA_OPERATIONAL_START_DATE` | `../book/Terminology_and_Scope.md` |
| `market_data.scope.canonical_product_code` | string | — | — | `../book/Terminology_and_Scope.md` |
| `market_data.scope.raw_product_code` | string | — | — | `../book/Terminology_and_Scope.md` |
| `market_data.scope.structural_adjusted_product_code` | string | — | — | `../book/Terminology_and_Scope.md` |
| `market_data.scope.total_return_product_code` | string | — | — | `../book/Terminology_and_Scope.md` |
| `market_data.scope.data_usability_field` | string | `data_usable` | — | `../book/Terminology_and_Scope.md` |
| `market_data.scope.compatibility_eligibility_field` | string | `eligible` | — | `../book/Terminology_and_Scope.md` |
| `market_data.scope.contract_version` | string | `market_data_scope_v2` | — | `../book/Terminology_and_Scope.md` |
| `market_data.platform.timezone` | string | `Asia/Jakarta` | `MARKET_DATA_PLATFORM_TIMEZONE` | `../book/Terminology_and_Scope.md` |
| `market_data.platform.seal_required_for_consumers` | bool | `true` | `MARKET_DATA_SEAL_REQUIRED_FOR_CONSUMERS` | `../book/Terminology_and_Scope.md` |
| `market_data.platform.cutoff_time` | string | `17:15:00` | `MARKET_DATA_PLATFORM_EOD_CUTOFF_TIME` | `../book/Terminology_and_Scope.md` |
| `market_data.platform.cutoff_grace_minutes` | int | `MARKET_DATA_CUT_OFF_GRACE_MINUTES` | `MARKET_DATA_CUT_OFF_GRACE_MINUTES` | `../book/Terminology_and_Scope.md` |
| `market_data.platform.coverage_min` | float | `0.98` | `MARKET_DATA_COVERAGE_MIN` | `../book/Terminology_and_Scope.md` |
| `market_data.indicators.price_product_default` | string | `STRUCTURAL_ADJUSTED` | `MARKET_DATA_INDICATOR_PRICE_PRODUCT_DEFAULT` | `Indicator_Registry_Baseline_LOCKED.md`, `Price_Adjustment_Contract_LOCKED.md` |
| ~~`market_data.platform.price_basis_default`~~ | — | — | ~~`MARKET_DATA_PRICE_BASIS_DEFAULT`~~ | **PRUNED 2026-08-11 (`F-024`). Not an active config key; do not reintroduce.** The entry previously permitted it as a legacy RAW-field selector "while compatibility code exists". That condition expired: the key was written into the indicator vector config by `EodIndicatorsComputeService::vectorConfig()` and read by nothing, so it named a selection the platform no longer makes while still appearing to govern one. Removed from `config/market_data.php`, both env templates, and the vector config in the same change. The analytical product is decided solely by `AnalyticalProductIdentityService`, which throws unless the selection is `STRUCTURAL_ADJUSTED`. `CoherentPriceProductBoundaryTest::test_legacy_adj_close_selector_cannot_become_an_analytical_fallback` retains the guard by injecting the key anyway and proving it is ignored. |
| `market_data.pipeline.daily_enabled` | bool | `false` | `MARKET_DATA_DAILY_ENABLED` | `../ops/Scheduling_and_Locking_Contract_LOCKED.md` |
| `market_data.pipeline.default_source_mode` | string | `api` | `MARKET_DATA_DEFAULT_SOURCE_MODE` | `../ops/Scheduling_and_Locking_Contract_LOCKED.md` |
| `market_data.pipeline.active_run_stale_minutes` | int | `MARKET_DATA_ACTIVE_RUN_STALE_MINUTES` | `MARKET_DATA_ACTIVE_RUN_STALE_MINUTES` | `../ops/Scheduling_and_Locking_Contract_LOCKED.md` |
| `market_data.scheduler.output_path` | string | `storage/logs/market-data-scheduler.log` | `MARKET_DATA_SCHEDULER_OUTPUT_PATH` | `../ops/Scheduling_and_Locking_Contract_LOCKED.md` |
| `market_data.scheduler.without_overlapping_minutes` | int | `MARKET_DATA_SCHEDULER_WITHOUT_OVERLAPPING_MINUTES` | `MARKET_DATA_SCHEDULER_WITHOUT_OVERLAPPING_MINUTES` | `../ops/Scheduling_and_Locking_Contract_LOCKED.md` |
| `market_data.coverage_edge_cases.delay_window_minutes` | int | `MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES` | `MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES` | `../book/Coverage_Edge_Cases_Contract_LOCKED.md` |
| `market_data.coverage_gate.enabled` | bool | `true` | `MARKET_DATA_COVERAGE_GATE_ENABLED` | `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` |
| `market_data.coverage_gate.min_ratio` | float | `0.98` | `MARKET_DATA_COVERAGE_MIN` | `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` |
| `market_data.coverage_gate.threshold_mode` | string | `MARKET_DATA_COVERAGE_THRESHOLD_MODE` | `MARKET_DATA_COVERAGE_THRESHOLD_MODE` | `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` |
| `market_data.coverage_gate.blocked_on_zero_universe` | bool | `true` | `MARKET_DATA_COVERAGE_BLOCK_ZERO_UNIVERSE` | `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` |
| `market_data.coverage_gate.require_canonical_bar_evidence` | bool | `true` | `MARKET_DATA_COVERAGE_REQUIRE_CANONICAL_BAR_EVIDENCE` | `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` |
| `market_data.coverage_gate.universe_basis` | string | `MARKET_DATA_COVERAGE_UNIVERSE_BASIS` | `MARKET_DATA_COVERAGE_UNIVERSE_BASIS` | `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` |
| `market_data.coverage_gate.contract_version` | string | `coverage_gate_v1` | `MARKET_DATA_COVERAGE_CONTRACT_VERSION` | `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` |
| `market_data.coverage_gate.missing_sample_limit` | int | `MARKET_DATA_COVERAGE_MISSING_SAMPLE_LIMIT` | `MARKET_DATA_COVERAGE_MISSING_SAMPLE_LIMIT` | `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md` |
| `market_data.activity.dormant_absence_trading_days` | int | `MARKET_DATA_COVERAGE_DORMANT_ABSENCE_TRADING_DAYS` during compatibility migration | `MARKET_DATA_COVERAGE_DORMANT_ABSENCE_TRADING_DAYS` | activity/liquidity fact only; **must not change coverage denominator or data usability** |
| `market_data.coverage_gate.dormant_absence_trading_days` | int | legacy alias | `MARKET_DATA_COVERAGE_DORMANT_ABSENCE_TRADING_DAYS` | **DEPRECATED misleading namespace.** If still resolved by runtime it is snapshotted for reproducibility, but any use to exclude denominator rows is a V2 migration failure. |
| `market_data.indicators.set_version` | string | `v1` | `MARKET_DATA_INDICATOR_SET_VERSION` | `Indicator_Registry_Baseline_LOCKED.md` |
| `market_data.indicators.dv_window_days` | int | `MARKET_DATA_DV_WINDOW_DAYS` | `MARKET_DATA_DV_WINDOW_DAYS` | `Indicator_Registry_Baseline_LOCKED.md` |
| `market_data.indicators.atr_window_days` | int | `MARKET_DATA_ATR_WINDOW_DAYS` | `MARKET_DATA_ATR_WINDOW_DAYS` | `Indicator_Registry_Baseline_LOCKED.md` |
| `market_data.indicators.vol_ratio_lookback_days` | int | `MARKET_DATA_VOL_RATIO_LOOKBACK_DAYS` | `MARKET_DATA_VOL_RATIO_LOOKBACK_DAYS` | `Indicator_Registry_Baseline_LOCKED.md` |
| `market_data.indicators.roc_lookback_days` | int | `MARKET_DATA_ROC_LOOKBACK_DAYS` | `MARKET_DATA_ROC_LOOKBACK_DAYS` | `Indicator_Registry_Baseline_LOCKED.md` |
| `market_data.indicators.hh_window_days` | int | `MARKET_DATA_HH_WINDOW_DAYS` | `MARKET_DATA_HH_WINDOW_DAYS` | `Indicator_Registry_Baseline_LOCKED.md` |
| `market_data.price_scale_break.contract_version` | string | `price_scale_break_v1` | `MARKET_DATA_PRICE_SCALE_BREAK_CONTRACT_VERSION` | `Price_Scale_Break_Detection_LOCKED.md` |
| `market_data.price_scale_break.min_ratio` | float | `1.7` | `MARKET_DATA_PRICE_SCALE_BREAK_MIN_RATIO` | `Price_Scale_Break_Detection_LOCKED.md` |
| `market_data.price_scale_break.min_price_idr` | float | `MARKET_DATA_PRICE_SCALE_BREAK_MIN_PRICE_IDR` | `MARKET_DATA_PRICE_SCALE_BREAK_MIN_PRICE_IDR` | `Price_Scale_Break_Detection_LOCKED.md` |
| `market_data.price_scale_break.action_match_trading_days` | int | `MARKET_DATA_PRICE_SCALE_BREAK_ACTION_MATCH_TRADING_DAYS` | `MARKET_DATA_PRICE_SCALE_BREAK_ACTION_MATCH_TRADING_DAYS` | `Price_Scale_Break_Detection_LOCKED.md` |
| `market_data.price_scale_break.ratio_tolerance` | float | `0.08` | `MARKET_DATA_PRICE_SCALE_BREAK_RATIO_TOLERANCE` | `Price_Scale_Break_Detection_LOCKED.md` |
| `market_data.hash.algorithm` | string | `SHA-256` | `MARKET_DATA_HASH_ALGORITHM` | `../book/Audit_Hash_and_Reproducibility_Contract_LOCKED.md` |
| `market_data.hash.delimiter` | string | `\|` | `MARKET_DATA_HASH_DELIMITER` | `../book/Audit_Hash_and_Reproducibility_Contract_LOCKED.md` |
| `market_data.hash.line_separator` | string | `
` | `MARKET_DATA_HASH_LINE_SEPARATOR` | `../book/Audit_Hash_and_Reproducibility_Contract_LOCKED.md` |
| `market_data.hash.null_token` | string | empty string (zero bytes) | — | `../book/Audit_Hash_and_Reproducibility_Contract_LOCKED.md` |
| `market_data.source.adapter_contract_version` | string | `provider_neutral_eod_source_v2` | — | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.observation_schema_version` | string | `source_observation_v2` | — | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.observation_retention_version` | string | `bounded_payload_v1` | — | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.canonicalization_version` | string | `idx_regular_raw_v2` | — | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.mapping_revision` | string | `temporal_provider_mapping_v1` | — | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.bounded_payload_bytes` | int | `MARKET_DATA_SOURCE_BOUNDED_PAYLOAD_BYTES` | `MARKET_DATA_SOURCE_BOUNDED_PAYLOAD_BYTES` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.local_directory` | string | `storage/app/market_data/eod_bars` | `MARKET_DATA_SOURCE_LOCAL_DIRECTORY` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.file_template_json` | string | `{date}.json` | `MARKET_DATA_SOURCE_FILE_TEMPLATE_JSON` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.file_template_csv` | string | `{date}.csv` | `MARKET_DATA_SOURCE_FILE_TEMPLATE_CSV` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.default_source_name` | string | `MARKET_DATA_SOURCE_DEFAULT_NAME` | `MARKET_DATA_SOURCE_DEFAULT_NAME` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.api.provider` | string | `yahoo_finance` | `MARKET_DATA_SOURCE_API_PROVIDER` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.adapter_version` | string | `yahoo_chart_v2` | `MARKET_DATA_SOURCE_API_ADAPTER_VERSION` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.schema_version` | string | `yahoo_chart_schema_v1` | `MARKET_DATA_SOURCE_API_SCHEMA_VERSION` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.endpoint_template` | string | `https://query1.finance.yahoo.com/v8/finance...` | `MARKET_DATA_SOURCE_API_ENDPOINT_TEMPLATE` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.response_format` | string | `json` | `MARKET_DATA_SOURCE_API_RESPONSE_FORMAT` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.response_rows_path` | string | `` | `MARKET_DATA_SOURCE_API_ROWS_PATH` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.timeout_seconds` | int | `MARKET_DATA_SOURCE_API_TIMEOUT_SECONDS` | `MARKET_DATA_SOURCE_API_TIMEOUT_SECONDS` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.user_agent` | string | `Mozilla/5.0 (Windows NT 10.0; Win64; x64) A...` | `MARKET_DATA_SOURCE_API_USER_AGENT` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.auth_header_name` | string | `` | `MARKET_DATA_SOURCE_API_AUTH_HEADER_NAME` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.auth_token` | string | `` | `MARKET_DATA_SOURCE_API_AUTH_TOKEN` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.source_name` | string | `MARKET_DATA_SOURCE_API_NAME` | `MARKET_DATA_SOURCE_API_NAME` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.yahoo.symbol_suffix` | string | `.JK` | `MARKET_DATA_SOURCE_YAHOO_SYMBOL_SUFFIX` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.yahoo.range` | string | `10d` | `MARKET_DATA_SOURCE_YAHOO_RANGE` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.yahoo.interval` | string | `1d` | `MARKET_DATA_SOURCE_YAHOO_INTERVAL` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.field_map.ticker_code` | string | `ticker_code` | `MARKET_DATA_SOURCE_API_FIELD_TICKER_CODE` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.field_map.trade_date` | string | `trade_date` | `MARKET_DATA_SOURCE_API_FIELD_TRADE_DATE` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.field_map.open` | string | `open` | `MARKET_DATA_SOURCE_API_FIELD_OPEN` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.field_map.high` | string | `high` | `MARKET_DATA_SOURCE_API_FIELD_HIGH` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.field_map.low` | string | `low` | `MARKET_DATA_SOURCE_API_FIELD_LOW` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.field_map.close` | string | `close` | `MARKET_DATA_SOURCE_API_FIELD_CLOSE` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.field_map.volume` | string | `volume` | `MARKET_DATA_SOURCE_API_FIELD_VOLUME` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.field_map.adj_close` | string | `adj_close` | `MARKET_DATA_SOURCE_API_FIELD_ADJ_CLOSE` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.field_map.source_row_ref` | string | `source_row_ref` | `MARKET_DATA_SOURCE_API_FIELD_SOURCE_ROW_REF` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api.field_map.captured_at` | string | `captured_at` | `MARKET_DATA_SOURCE_API_FIELD_CAPTURED_AT` | `../book/Source_Mapping_Contract_LOCKED.md` |
| `market_data.source.api_backfill.window_days` | int | `MARKET_DATA_API_BACKFILL_WINDOW_DAYS` | `MARKET_DATA_API_BACKFILL_WINDOW_DAYS` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.api_backfill.warmup_days` | int | `MARKET_DATA_API_BACKFILL_WARMUP_DAYS` | `MARKET_DATA_API_BACKFILL_WARMUP_DAYS` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.api_backfill.warmup_trading_days` | int | `MARKET_DATA_API_BACKFILL_WARMUP_TRADING_DAYS` | `MARKET_DATA_API_BACKFILL_WARMUP_TRADING_DAYS` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.api_backfill.concurrency` | int | `MARKET_DATA_API_BACKFILL_CONCURRENCY` | `MARKET_DATA_API_BACKFILL_CONCURRENCY` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.api_backfill.max_dates_per_run` | int | `MARKET_DATA_API_BACKFILL_MAX_DATES_PER_RUN` | `MARKET_DATA_API_BACKFILL_MAX_DATES_PER_RUN` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.api_backfill.collect_all_errors` | bool | `false` | `MARKET_DATA_API_BACKFILL_COLLECT_ALL_ERRORS` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
| `market_data.source.api_backfill.default_error_policy` | string | `stop_on_error` | `MARKET_DATA_API_BACKFILL_DEFAULT_ERROR_POLICY` | `../book/Source_Data_Acquisition_Contract_LOCKED.md` |
### Legacy current-ticker projection keys (TRANSITIONAL / NOT HISTORICAL-UNIVERSE AUTHORITY)

The `market_data.tickers.*` keys below describe the current legacy runtime projection while migration is incomplete. They may be snapshotted because they affect compatibility code, but they **must not** determine point-in-time universe membership, provider-symbol resolution, canonical row identity, or new API/schema keys. V2 authority is the temporal issuer/instrument/listing/symbol model using stable `listing_id`/`instrument_id`; current `is_active` is not historical truth.

| `market_data.tickers.table` | string | `tickers` | `MARKET_DATA_TICKERS_TABLE` | `../book/Tickers_and_Identity_Dependency_Contract_LOCKED.md` |
| `market_data.tickers.id_column` | string | `ticker_id` | `MARKET_DATA_TICKERS_ID_COLUMN` | `../book/Tickers_and_Identity_Dependency_Contract_LOCKED.md` |
| `market_data.tickers.code_column` | string | `ticker_code` | `MARKET_DATA_TICKERS_CODE_COLUMN` | `../book/Tickers_and_Identity_Dependency_Contract_LOCKED.md` |
| `market_data.tickers.active_column` | string | `is_active` | `MARKET_DATA_TICKERS_ACTIVE_COLUMN` | `../book/Tickers_and_Identity_Dependency_Contract_LOCKED.md` |
| `market_data.tickers.active_value` | int | `MARKET_DATA_TICKERS_ACTIVE_VALUE` | `MARKET_DATA_TICKERS_ACTIVE_VALUE` | `../book/Tickers_and_Identity_Dependency_Contract_LOCKED.md` |
| `market_data.tickers.listed_date_column` | string | `listed_date` | `MARKET_DATA_TICKERS_LISTED_DATE_COLUMN` | `../book/Tickers_and_Identity_Dependency_Contract_LOCKED.md` |
| `market_data.tickers.delisted_date_column` | string | `delisted_date` | `MARKET_DATA_TICKERS_DELISTED_DATE_COLUMN` | `../book/Tickers_and_Identity_Dependency_Contract_LOCKED.md` |
| `market_data.tickers.temporal_projection_version` | string | `legacy_ticker_temporal_projection_v1` | — | `../book/Tickers_and_Identity_Dependency_Contract_LOCKED.md` |
| `market_data.governance.config_snapshot_schema_version` | string | `market_data_config_snapshot_v1` | — | `Platform_Config_Registry_LOCKED.md` |
| `market_data.governance.config_serialization_version` | string | `canonical_json_v1` | — | `Platform_Config_Registry_LOCKED.md` |
| `market_data.governance.config_registry_revision` | string | `platform_config_registry_v2` | — | `Platform_Config_Registry_LOCKED.md` |
| `market_data.governance.config_resolver_version` | string | `market_data_config_resolver_v1` | — | `Platform_Config_Registry_LOCKED.md` |
| `market_data.governance.build_id` | string | `development-worktree` | `MARKET_DATA_BUILD_ID` | `Platform_Config_Registry_LOCKED.md` |
| `market_data.governance.environment_profile` | string | `local` | `MARKET_DATA_ENVIRONMENT_PROFILE` | `Platform_Config_Registry_LOCKED.md` |
| `market_data.governance.credential_profile` | string | `bootstrap-public-access` | `MARKET_DATA_CREDENTIAL_PROFILE` | `Platform_Config_Registry_LOCKED.md` |
| `market_data.sectors.table` | string | `market_data_sectors` | `MARKET_DATA_SECTORS_TABLE` | `../book/Sector_Classification_Contract_LOCKED.md` |
| `market_data.sectors.membership_table` | string | `ticker_sector_memberships` | `MARKET_DATA_SECTOR_MEMBERSHIP_TABLE` | `../book/Sector_Classification_Contract_LOCKED.md` |
| `market_data.sectors.classification_system` | string | `IDX-IC` | `MARKET_DATA_SECTOR_CLASSIFICATION_SYSTEM` | `../book/Sector_Classification_Contract_LOCKED.md` |
| `market_data.sectors.index_provider` | string | `manual_sector_index_csv` | `MARKET_DATA_SECTOR_INDEX_PROVIDER` | `../book/Sector_Classification_Contract_LOCKED.md` |
| `market_data.sectors.index_api.provider` | string | `yahoo_finance` | `MARKET_DATA_SECTOR_INDEX_API_PROVIDER` | `../book/Sector_Classification_Contract_LOCKED.md` |
| `market_data.sectors.index_api.symbol_suffix` | string | `.JK` | `MARKET_DATA_SECTOR_INDEX_API_SYMBOL_SUFFIX` | `../book/Sector_Classification_Contract_LOCKED.md` |
| `market_data.sectors.index_api.provider_symbols` | list | `(kosong)` | `MARKET_DATA_SECTOR_INDEX_API_PROVIDER_SYMBOLS_JSON` | `../book/Sector_Classification_Contract_LOCKED.md` |
| `market_data.event_risk.corporate_actions_table` | string | `market_data_corporate_actions` | `MARKET_DATA_CORPORATE_ACTIONS_TABLE` | `Corporate_Action_Type_Registry_LOCKED.md` |
| `market_data.event_risk.trading_status_events_table` | string | `market_data_trading_status_events` | `MARKET_DATA_TRADING_STATUS_EVENTS_TABLE` | `../book/Trading_Status_Source_Contract_LOCKED.md` |
| `market_data.event_risk.trading_status_event_types_table` | string | `market_data_trading_status_event_types` | `MARKET_DATA_TRADING_STATUS_EVENT_TYPES_TABLE` | `../book/Trading_Status_Source_Contract_LOCKED.md` |
| `market_data.event_risk.corporate_action_types_table` | string | `market_data_corporate_action_types` | `MARKET_DATA_CORPORATE_ACTION_TYPES_TABLE` | `Corporate_Action_Type_Registry_LOCKED.md` |
| `market_data.event_risk.price_scale_breaks_table` | string | `market_data_price_scale_breaks` | `MARKET_DATA_PRICE_SCALE_BREAKS_TABLE` | `Corporate_Action_Type_Registry_LOCKED.md` |
| `market_data.event_risk.corporate_action_source_name` | string | `manual_corporate_action_csv` | `MARKET_DATA_CORPORATE_ACTION_SOURCE_NAME` | `Corporate_Action_Type_Registry_LOCKED.md` |
| `market_data.event_risk.trading_status_source_name` | string | `manual_trading_status_csv` | `MARKET_DATA_TRADING_STATUS_SOURCE_NAME` | `../book/Trading_Status_Source_Contract_LOCKED.md` |
| `market_data.evidence.output_directory` | string | `storage/app/market_data/evidence` | `MARKET_DATA_EVIDENCE_OUTPUT_DIRECTORY` | `../ops/Audit_Evidence_Pack_Contract_LOCKED.md` |
| `market_data.evidence.invalid_bars_export_sample_limit` | int | `MARKET_DATA_INVALID_BARS_EXPORT_SAMPLE_LIMIT` | `MARKET_DATA_INVALID_BARS_EXPORT_SAMPLE_LIMIT` | `../ops/Audit_Evidence_Pack_Contract_LOCKED.md` |
| `market_data.provider.api_retry_max` | int | `MARKET_DATA_API_RETRY_MAX` | `MARKET_DATA_API_RETRY_MAX` | `../book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md` |
| `market_data.provider.api_backoff_ms` | int | `MARKET_DATA_API_BACKOFF_MS` | `MARKET_DATA_API_BACKOFF_MS` | `../book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md` |
| `market_data.provider.api_throttle_qps` | int | `MARKET_DATA_API_THROTTLE_QPS` | `MARKET_DATA_API_THROTTLE_QPS` | `../book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md` |
| `market_data.provider.circuit_breaker_error_rate` | float | `0.5` | `MARKET_DATA_CIRCUIT_BREAKER_ERROR_RATE` | `../book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md` |
| `market_data.session_snapshot.retention_days` | int | `MARKET_DATA_SESSION_SNAPSHOT_RETENTION_DAYS` | `MARKET_DATA_SESSION_SNAPSHOT_RETENTION_DAYS` | `../session_snapshot/Session_Snapshot_Contract_LOCKED.md` |
| `market_data.session_snapshot.scope_default` | string | `data_usability_set` target (`eligibility_set` accepted only as legacy compatibility alias) | `MARKET_DATA_SESSION_SNAPSHOT_SCOPE_DEFAULT` | `../session_snapshot/Session_Snapshot_Contract_LOCKED.md` |
| `market_data.session_snapshot.slot_tolerance_minutes` | int | `MARKET_DATA_SESSION_SNAPSHOT_SLOT_TOLERANCE_MINUTES` | `MARKET_DATA_SESSION_SNAPSHOT_SLOT_TOLERANCE_MINUTES` | `../session_snapshot/Session_Snapshot_Contract_LOCKED.md` |

## Registry metadata

Each key definition records at minimum:

- canonical key and type;
- authoritative meaning and owner contract;
- allowed values, unit, and default behavior;
- required/optional classification by run type;
- effective interval and recorded/known interval;
- output/lineage/security impact classification;
- change reason/ticket, author, and approval evidence; and
- compatible schema/formula/adapter versions.

Overlapping effective revisions for the same scope are invalid. Silent runtime overrides and undocumented defaults are forbidden.

## Environment and secret handling

Environment variables are inputs to the resolver, not replay authority. Their resolved non-secret values and origin/profile are captured in the immutable snapshot.

Secret values and credentials are never stored or hashed in cleartext. The snapshot records only a sanitized credential-profile/key identifier, provider account scope, and secret revision/version sufficient to explain behavior without exposing the secret. Rotating a secret that cannot affect returned content may be provenance-only; changing provider account scope, permissions, or data entitlement is output-affecting.

`.env.example`, application config, registry definitions, resolver validation, and documentation must remain synchronized. A key present in runtime code but absent from the registry is a sealing error.

## Effective-time and replay rules

Operational production selects the approved configuration effective for the run context and records when it became known. Two replay modes are distinct:

- publication replay uses the exact snapshot frozen with the publication;
- as-known replay resolves only revisions known by the declared knowledge cutoff, then freezes a new replay snapshot.

Current registry state must never leak into historical replay. Alternate-scenario runs are explicitly labeled and cannot impersonate the historical publication.

## Validation and acceptance proof

Before seal, validation proves:

1. every required output-affecting key was resolved exactly once;
2. snapshot serialization and hash are deterministic across independent executions;
3. run, artifacts, publication, and seal reference the identical non-null snapshot ID/hash;
4. a one-key semantic change produces a distinct snapshot hash and publication context;
5. current environment drift cannot change publication replay;
6. as-known replay cannot see later revisions; and
7. secrets are absent from snapshots, logs, observations, and manifests.

Until schema constraints and executed fixtures prove these properties, this registry is strategy-locked but configuration governance is not production-relocked.

## Controlled correction 2026-09-03 — date-level anomaly configuration

The following additive keys complete the resolved-key representation required by
`Run_Status_and_Quality_Gates_LOCKED.md`. The correction does not change the three date-level
measures, their non-destructive finding behavior, or any readiness/publishability semantic.

| Config key | Type | Resolved default | Environment input | Owner contract |
|---|---|---|---|---|
| `market_data.quality_gates.date_level_anomaly.zero_volume_share_max` | float | `0.30` | `MARKET_DATA_DATE_LEVEL_ANOMALY_ZERO_VOLUME_SHARE_MAX` | `../book/Run_Status_and_Quality_Gates_LOCKED.md` |
| `market_data.quality_gates.date_level_anomaly.flat_bar_share_max` | float | `0.20` | `MARKET_DATA_DATE_LEVEL_ANOMALY_FLAT_BAR_SHARE_MAX` | `../book/Run_Status_and_Quality_Gates_LOCKED.md` |
| `market_data.quality_gates.date_level_anomaly.cross_field_contradiction_max` | int | `0` | `MARKET_DATA_DATE_LEVEL_ANOMALY_CROSS_FIELD_CONTRADICTION_MAX` | `../book/Run_Status_and_Quality_Gates_LOCKED.md` |
| `market_data.quality_gates.date_level_anomaly.neighbour_trading_days` | int | `5` | `MARKET_DATA_DATE_LEVEL_ANOMALY_NEIGHBOUR_TRADING_DAYS` | `../book/Run_Status_and_Quality_Gates_LOCKED.md` |
| `market_data.quality_gates.date_level_anomaly.neighbour_elevation_factor` | float | `2.0` | `MARKET_DATA_DATE_LEVEL_ANOMALY_NEIGHBOUR_ELEVATION_FACTOR` | `../book/Run_Status_and_Quality_Gates_LOCKED.md` |
| `market_data.quality_gates.date_level_anomaly.minimum_rows` | int | `20` | `MARKET_DATA_DATE_LEVEL_ANOMALY_MINIMUM_ROWS` | `../book/Run_Status_and_Quality_Gates_LOCKED.md` |

This bounded additive correction is authorised by `D-MD-B17-A001-001` and
`DOC-CHG-20260903-001`. No other strategy row or threshold is changed.
