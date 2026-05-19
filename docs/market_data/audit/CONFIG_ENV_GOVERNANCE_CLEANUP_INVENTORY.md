# Config / ENV Governance Cleanup Inventory

Status: DONE / LOCKED_LOCAL_PHPUNIT_PASS  
Last updated: 2026-05-17  
Related implementation: Config / ENV Governance Cleanup  
Related contract: CONFIG_ENV_GOVERNANCE_CLEANUP_CONTRACT

## Scope

This inventory records the config/env cleanup for market-data. It is intentionally scoped to schema/config/runtime alignment and pruning of stale config surfaces. It does not rewrite source-mode, coverage, read-side pointer, publication, replay, evidence, or DB integrity policy.

## Existing Contract / Test / Doc Matrix

| Existing Contract / Test / Doc | Role | Current Status | Relevance to Config/ENV Cleanup | Reuse / Extend / Do Not Touch |
|---|---|---|---|---|
| `AUDIT_UPDATE_GOVERNANCE.md` | Audit update rules | LOCKED | Append-only audit-doc update and anti-duplication rule | Reuse |
| `LUMEN_IMPLEMENTATION_STATUS.md` | Implementation evidence | UPDATED | Records active session, patch, runtime status, and validation limits | Extend |
| `LUMEN_CONTRACT_TRACKER.md` | Contract lifecycle | UPDATED | Records `CONFIG_ENV_GOVERNANCE_CLEANUP_CONTRACT` status | Extend |
| `Database_Schema_MariaDB.sql` | Schema truth for market-data | LOCKED | Proves `tickers.is_active` is `TINYINT(1) NOT NULL DEFAULT 1` | Reuse |
| `2026_03_22_000001_create_tickers_table.php` | Migration truth | LOCKED | Proves `tickers.is_active` is Laravel boolean default true | Reuse |
| `TickerMasterRepository.php` | Runtime ticker universe resolver | PATCHED | Uses strict numeric active value, no `Yes` fallback | Extend |
| `ConfigEnvGovernanceCleanupStaticGuardTest.php` | Static policy guard | ADDED | Prevents config/env/schema mismatch regression | Extend |
| `TickerMasterRepositoryTest.php` | Behavioral guard | ADDED | Proves numeric active filtering excludes stale string value | Extend |

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
| `MARKET_DATA_LOT_SIZE` | `.env.example`, `.env.testing`, `config/market_data.php` | `100` | int | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_DAILY_ENABLED` | `.env.example`, `.env.testing`, `config/market_data.php` | `false` | bool | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
| `MARKET_DATA_DEFAULT_SOURCE_MODE` | `.env.example`, `.env.testing`, `config/market_data.php` | `api` | string | yes | yes | yes | ACTIVE_USED | Keep; caller/purpose traced |
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

## `tickers.is_active` Decision Matrix

| Area | Current Value / Behavior Before Patch | Expected | Patch Needed | Status |
|---|---|---|---:|---|
| Migration | `$table->boolean('is_active')->default(true)` | boolean/numeric | no | VERIFIED |
| SQL schema doc | `is_active TINYINT(1) NOT NULL DEFAULT 1` | numeric `1/0` | no | VERIFIED |
| Config before patch | `active_yes_value => env(..., 'Yes')` | numeric active value | yes | PATCHED |
| ENV before patch | `MARKET_DATA_TICKERS_ACTIVE_YES_VALUE=Yes` | `MARKET_DATA_TICKERS_ACTIVE_VALUE=1` | yes | PATCHED |
| Repository before patch | accepted configured `Yes`, `1`, and `true` | strict numeric active value | yes | PATCHED |
| Tests before patch | seeded `is_active => 'Yes'` in two integration fixtures | seed `1` | yes | PATCHED |
| Generic DB ticker doc before patch | `ENUM('Yes','No') atau BOOLEAN canonical` | boolean/TINYINT canonical | yes | PATCHED |

## Pruning Matrix

| Key | Location | Problem | Runtime Caller Exists? | Decision | Patch |
|---|---|---|---:|---|---|
| `MARKET_DATA_TICKERS_ACTIVE_YES_VALUE` / `market_data.tickers.active_yes_value` | config/env/repository/tests | Misleading string `Yes` value against numeric schema | yes, but semantically wrong | RENAME_AND_UPDATE_CALLERS | Replaced with `MARKET_DATA_TICKERS_ACTIVE_VALUE=1` and `market_data.tickers.active_value` |
| `MARKET_DATA_MULTI_SOURCE_MODE` / `market_data.coverage_edge_cases.multi_source_mode` | config/book doc only | No runtime caller; could imply multi-source behavior exists | no | REMOVE | Removed from config; book doc marks it pruned |
| `MARKET_DATA_ALLOW_MIXED_SOURCES` / `market_data.coverage_edge_cases.allow_mixed_sources` | config/book doc only | No runtime caller; could imply row-level mixing can be enabled | no | REMOVE | Removed from config; book doc marks it pruned |
| `MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES` | config/service | Used by delayed coverage behavior but missing from env templates | yes | KEEP_ACTIVE | Added to `.env.example` and `.env.testing` |
| `market_data.source.local_input_file` | command/runtime override | Runtime config key set by command/tests, intentionally not env-backed | yes | DEFER_WITH_REASON | Not added to env; remains command override to avoid persistent operator input file |

## Caller Trace Matrix

| Config Key | Caller File | Method / Command | Purpose | Required | Status |
|---|---|---|---|---:|---|
| `market_data.tickers.active_value` | `TickerMasterRepository.php` | `getUniverseForTradeDate()` | strict active ticker universe filter | yes | PATCHED |
| `market_data.tickers.table/id_column/code_column/active_column/listed_date_column/delisted_date_column` | `TickerMasterRepository.php` | `resolveTickerIdsByCodes()`, `getUniverseForTradeDate()` | ticker table/schema mapping | yes | ACTIVE_USED |
| `market_data.coverage_gate.*` | `CoverageGateEvaluator.php`, `MarketDataPipelineService.php`, command/test surfaces | coverage PASS/FAIL telemetry and publishability | yes | ACTIVE_USED |
| `market_data.coverage_edge_cases.delay_window_minutes` | `MarketDataPipelineService.php` | delayed data classification | yes | ACTIVE_USED |
| `market_data.source.*` | `LocalFileEodBarsAdapter.php`, commands, pipeline | local/manual source resolution | yes | ACTIVE_USED |
| `market_data.source.api.*` and `market_data.provider.*` | source adapters/pipeline telemetry | provider acquisition and retry telemetry | yes | ACTIVE_USED |
| `market_data.indicators.*` | `EodIndicatorsComputeService.php` | indicator computation | yes | ACTIVE_USED |
| `market_data.hash.*` | `DeterministicHashService.php`, pipeline | deterministic hash/seal metadata | yes | ACTIVE_USED |
| `market_data.platform.*` | services/commands/backfills | timezone, cutoff, lot size, price basis | yes | ACTIVE_USED |
| `market_data.evidence.*` | evidence/export surfaces | evidence output and invalid bars sample limit | yes | ACTIVE_USED |
| `market_data.session_snapshot.*` | session snapshot service/commands | snapshot retention/scope/tolerance | yes | ACTIVE_USED |

## Patch Matrix

| Gap | File | Change | Why Safe | Test Coverage | Status |
|---|---|---|---|---|---|
| ticker active config used `Yes` against numeric schema | `config/market_data.php`, `.env.example`, `.env.testing`, `TickerMasterRepository.php` | renamed to numeric `active_value`; repository uses strict `where(is_active, 1)` | Schema already numeric/boolean; stale string no longer accepted | static guard + repository test | PATCHED |
| stale multi-source env/config surface | `config/market_data.php`, `Coverage_Edge_Cases_Contract_LOCKED.md` | removed unused config keys and documented pruned state | No runtime caller existed; hard rule remains no source mixing | static guard | PATCHED |
| delay-window env missing from templates | `.env.example`, `.env.testing` | added `MARKET_DATA_COVERAGE_DELAY_WINDOW_MINUTES=60` | Caller already existed | static guard | PATCHED |
| generic ticker DB doc allowed ambiguous `Yes/No` | `docs/db/02_TICKERS_MASTER.md` | normalized to boolean/TINYINT `1/0` | Aligns with migration and locked SQL schema | static guard | PATCHED |
| audit docs missing active session | audit docs | active session and current working entries added | append-only; old history preserved | static guard | PATCHED |

## Validation Matrix

| Command | Result | Tests | Assertions | Status |
|---|---:|---:|---:|---|
| `php -l config/market_data.php` | No syntax errors detected | n/a | n/a | PASS |
| `php -l app/Infrastructure/Persistence/MarketData/TickerMasterRepository.php` | No syntax errors detected | n/a | n/a | PASS |
| `php -l tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php` | No syntax errors detected | n/a | n/a | PASS_AFTER_RUN |
| `php -l tests/Unit/MarketData/TickerMasterRepositoryTest.php` | No syntax errors detected | n/a | n/a | PASS_AFTER_RUN |
| `php vendor/bin/phpunit ...` | blocked in container by missing `dom`, `mbstring`, `xml`, `xmlwriter` | n/a | n/a | BLOCKED_CONTAINER_RUNTIME_ENV |

## Final Decision

Config/env governance status for this ZIP is `READY_FOR_LOCAL_RUNTIME_VALIDATION`. Static patching and syntax proof are complete in this container, but PHPUnit cannot be promoted to DONE/LOCKED here because required PHP extensions are missing. Operator-local targeted and full MarketData PHPUnit output is required for final `DONE_LOCAL_PHPUNIT_PASS` / `LOCKED_LOCAL_PHPUNIT_PASS` promotion.


## Production-Ready Reconciliation Addendum

Current canonical status for this scope is LOCKED in `LUMEN_CONTRACT_TRACKER.md`. Historical pending/local-validation wording above is retained as session history only. The full production-ready proof pack records final operator-local validation: AuditDocs OK (10 tests, 363 assertions), Replay OK (57 tests, 904 assertions), StaticGuard OK (170 tests, 3950 assertions), and full `tests/Unit/MarketData` OK (453 tests, 6671 assertions).
