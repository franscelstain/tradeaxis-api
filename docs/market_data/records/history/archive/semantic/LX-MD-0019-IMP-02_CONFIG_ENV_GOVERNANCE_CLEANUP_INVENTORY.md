# Legacy Semantic Extract — LX-MD-0019-IMP-02

- Source ID: `LS-MD-0019`
- Original path: `audit/CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md`
- Original SHA1: `B522643CB68AFF2ECC9A8268A482C11CE2D61598`
- Extract role: `IMPLEMENTATION`
- Source range: `L28-L111`
- Extract body SHA1: `63116A9DCE2B73567825EF5A39E7DE841894B03F`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Schema / Config Alignment Matrix

| Table | Column | Schema Type | Config Key | Config Value | Expected Type | Mismatch? | Decision |
|---|---|---|---|---|---|---:|---|
| `tickers` | `is_active` | migration: boolean default true; SQL: `TINYINT(1) NOT NULL DEFAULT 1` | `market_data.tickers.active_value` / `MARKET_DATA_TICKERS_ACTIVE_VALUE` | `1` | integer/boolean-like | no after patch | Keep numeric `1`; remove old `active_yes_value`/`Yes` semantics |
| `market_calendar` | `is_trading_day` | boolean/TINYINT default 1 | none | n/a | integer/boolean-like | no | No config/env surface added |
| `eod_reason_codes` | `is_active` | `TINYINT(1) NOT NULL DEFAULT 1` | none | n/a | integer/boolean-like | no | Registry/seed remain separate from ticker config |
| `eod_runs` | coverage fields | decimal/string telemetry | `market_data.coverage_gate.*` | typed env defaults | float/string/bool/int | no | Keep active coverage gate config |

## Config / ENV Inventory Matrix

| Key | Source | Default | Type | Caller Exists | Docs Exists | Test Exists | Status | Action |
|---|---|---|---|---:|---:|---:|---|---|
| `MARKET_DATA_PLATFORM_TIMEZONE` | `.env.example`, `.env.testing`, `config/market_data.php` | `Asia/Jakarta` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SEAL_REQUIRED_FOR_CONSUMERS` | `.env.example`, `.env.testing`, `config/market_data.php` | `true` | bool | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_PLATFORM_EOD_CUTOFF_TIME` | `.env.example`, `.env.testing`, `config/market_data.php` | `17:15:00` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_CUT_OFF_GRACE_MINUTES` | `.env.example`, `.env.testing`, `config/market_data.php` | `15` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_COVERAGE_MIN` | `.env.example`, `.env.testing`, `config/market_data.php` | `0.98` | float | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_COVERAGE_GATE_ENABLED` | `.env.example`, `.env.testing`, `config/market_data.php` | `true` | bool | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_COVERAGE_THRESHOLD_MODE` | `.env.example`, `.env.testing`, `config/market_data.php` | `MIN_RATIO` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_COVERAGE_BLOCK_ZERO_UNIVERSE` | `.env.example`, `.env.testing`, `config/market_data.php` | `true` | bool | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_COVERAGE_REQUIRE_CANONICAL_BAR_EVIDENCE` | `.env.example`, `.env.testing`, `config/market_data.php` | `true` | bool | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_COVERAGE_UNIVERSE_BASIS` | `.env.example`, `.env.testing`, `config/market_data.php` | `ACTIVE_LISTED_EQUITY_AS_OF_DATE` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_COVERAGE_CONTRACT_VERSION` | `.env.example`, `.env.testing`, `config/market_data.php` | `coverage_gate_v1` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_COVERAGE_MISSING_SAMPLE_LIMIT` | `.env.example`, `.env.testing`, `config/market_data.php` | `25` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES` | `.env.example`, `.env.testing`, `config/market_data.php` | `60` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_PRICE_BASIS_DEFAULT` | `.env.example`, `.env.testing`, `config/market_data.php` | `close` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_LOT_SIZE` | removed 2026-07-30 | `100` | int | no | no | no | REMOVE | Only caller was the turnover formula, which was wrong: IDX provider volume is in shares, so the lot multiplier inflated `dv20_idr` by 100x. Lot size for position sizing is owned by the watchlist backtest calibration doc, not by market data. |
| `MARKET_DATA_DAILY_ENABLED` | `.env.example`, `.env.testing`, `config/market_data.php` | `false` | bool | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_DEFAULT_SOURCE_MODE` | `.env.example`, `.env.testing`, `config/market_data.php` | `api` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SCHEDULER_OUTPUT_PATH` | `.env.example`, `.env.testing`, `config/market_data.php` | `storage/logs/market-data-scheduler.log` | string | yes | yes | yes | ACTIVE_USED | Keep; scheduler output/no-silent-failure proof |
| `MARKET_DATA_SCHEDULER_WITHOUT_OVERLAPPING_MINUTES` | `.env.example`, `.env.testing`, `config/market_data.php` | `120` | int | yes | yes | yes | ACTIVE_USED | Keep; scheduler overlap guard proof |
| `MARKET_DATA_INDICATOR_SET_VERSION` | `.env.example`, `.env.testing`, `config/market_data.php` | `v1` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_DV_WINDOW_DAYS` | `.env.example`, `.env.testing`, `config/market_data.php` | `20` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_ATR_WINDOW_DAYS` | `.env.example`, `.env.testing`, `config/market_data.php` | `14` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_VOL_RATIO_LOOKBACK_DAYS` | `.env.example`, `.env.testing`, `config/market_data.php` | `20` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_ROC_LOOKBACK_DAYS` | `.env.example`, `.env.testing`, `config/market_data.php` | `20` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_HH_WINDOW_DAYS` | `.env.example`, `.env.testing`, `config/market_data.php` | `20` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_HASH_ALGORITHM` | `.env.example`, `.env.testing`, `config/market_data.php` | `SHA-256` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_HASH_DELIMITER` | `.env.example`, `.env.testing`, `config/market_data.php` | `|` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_HASH_LINE_SEPARATOR` | `.env.example`, `.env.testing`, `config/market_data.php` | `"\n"` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_HASH_NULL_TOKEN` | `.env.example`, `.env.testing`, `config/market_data.php` | `[empty]` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_API_RETRY_MAX` | `.env.example`, `.env.testing`, `config/market_data.php` | `3` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_API_BACKOFF_MS` | `.env.example`, `.env.testing`, `config/market_data.php` | `500` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_API_THROTTLE_QPS` | `.env.example`, `.env.testing`, `config/market_data.php` | `5` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_CIRCUIT_BREAKER_ERROR_RATE` | `.env.example`, `.env.testing`, `config/market_data.php` | `0.5` | float | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SESSION_SNAPSHOT_RETENTION_DAYS` | `.env.example`, `.env.testing`, `config/market_data.php` | `30` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SESSION_SNAPSHOT_SCOPE_DEFAULT` | `.env.example`, `.env.testing`, `config/market_data.php` | `eligibility_set` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SESSION_SNAPSHOT_SLOT_TOLERANCE_MINUTES` | `.env.example`, `.env.testing`, `config/market_data.php` | `3` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_LOCAL_DIRECTORY` | `.env.example`, `.env.testing`, `config/market_data.php` | `storage/app/market_data/eod_bars` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_FILE_TEMPLATE_JSON` | `.env.example`, `.env.testing`, `config/market_data.php` | `{date}.json` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_FILE_TEMPLATE_CSV` | `.env.example`, `.env.testing`, `config/market_data.php` | `{date}.csv` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_DEFAULT_NAME` | `.env.example`, `.env.testing`, `config/market_data.php` | `YAHOO_FINANCE` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_PROVIDER` | `.env.example`, `.env.testing`, `config/market_data.php` | `yahoo_finance` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_ENDPOINT_TEMPLATE` | `.env.example`, `.env.testing`, `config/market_data.php` | `https://query1.finance.yahoo.com/v8/finance/chart/{symbol}{symbol_suffix}?interval=1d&range=10d&includePrePost=false&events=div%2Csplits&corsDomain=finance.yahoo.com` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_RESPONSE_FORMAT` | `.env.example`, `.env.testing`, `config/market_data.php` | `json` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_ROWS_PATH` | `.env.example`, `.env.testing`, `config/market_data.php` | `` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_TIMEOUT_SECONDS` | `.env.example`, `.env.testing`, `config/market_data.php` | `20` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_AUTH_HEADER_NAME` | `.env.example`, `.env.testing`, `config/market_data.php` | `` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_AUTH_TOKEN` | `.env.example`, `.env.testing`, `config/market_data.php` | `` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_NAME` | `.env.example`, `.env.testing`, `config/market_data.php` | `YAHOO_FINANCE` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_YAHOO_SYMBOL_SUFFIX` | `.env.example`, `.env.testing`, `config/market_data.php` | `.JK` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_YAHOO_RANGE` | `.env.example`, `.env.testing`, `config/market_data.php` | `10d` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_YAHOO_INTERVAL` | `.env.example`, `.env.testing`, `config/market_data.php` | `1d` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_FIELD_TICKER_CODE` | `.env.example`, `.env.testing`, `config/market_data.php` | `ticker_code` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_FIELD_TRADE_DATE` | `.env.example`, `.env.testing`, `config/market_data.php` | `trade_date` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_FIELD_OPEN` | `.env.example`, `.env.testing`, `config/market_data.php` | `open` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_FIELD_HIGH` | `.env.example`, `.env.testing`, `config/market_data.php` | `high` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_FIELD_LOW` | `.env.example`, `.env.testing`, `config/market_data.php` | `low` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_FIELD_CLOSE` | `.env.example`, `.env.testing`, `config/market_data.php` | `close` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_FIELD_VOLUME` | `.env.example`, `.env.testing`, `config/market_data.php` | `volume` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_FIELD_ADJ_CLOSE` | `.env.example`, `.env.testing`, `config/market_data.php` | `adj_close` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_FIELD_SOURCE_ROW_REF` | `.env.example`, `.env.testing`, `config/market_data.php` | `source_row_ref` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_SOURCE_API_FIELD_CAPTURED_AT` | `.env.example`, `.env.testing`, `config/market_data.php` | `captured_at` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_TICKERS_TABLE` | `.env.example`, `.env.testing`, `config/market_data.php` | `tickers` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_TICKERS_ID_COLUMN` | `.env.example`, `.env.testing`, `config/market_data.php` | `ticker_id` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_TICKERS_CODE_COLUMN` | `.env.example`, `.env.testing`, `config/market_data.php` | `ticker_code` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_TICKERS_ACTIVE_COLUMN` | `.env.example`, `.env.testing`, `config/market_data.php` | `is_active` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_TICKERS_ACTIVE_VALUE` | `.env.example`, `.env.testing`, `config/market_data.php` | `1` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_TICKERS_LISTED_DATE_COLUMN` | `.env.example`, `.env.testing`, `config/market_data.php` | `listed_date` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_TICKERS_DELISTED_DATE_COLUMN` | `.env.example`, `.env.testing`, `config/market_data.php` | `delisted_date` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_EVIDENCE_OUTPUT_DIRECTORY` | `.env.example`, `.env.testing`, `config/market_data.php` | `storage/app/market_data/evidence` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_INVALID_BARS_EXPORT_SAMPLE_LIMIT` | `.env.example`, `.env.testing`, `config/market_data.php` | `1000` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |


<!-- LEGACY_EXTRACT_BODY_END -->
