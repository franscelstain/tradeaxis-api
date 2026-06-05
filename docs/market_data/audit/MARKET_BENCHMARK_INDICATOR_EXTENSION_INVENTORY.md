# MARKET BENCHMARK + INDICATOR EXTENSION INVENTORY

## 2026-06-02 Addendum - Weekly Swing Priority 1 Extension

Status: `ENFORCED_FULL_MARKETDATA_PHPUNIT_PASS_RUNTIME_PENDING`, not production-ready relocked.

Additional nullable equity indicator fields:
- `roc5`
- `roc10`
- `ll20`
- `close_to_ll20_pct`
- `range_20_pct`
- `range_position_20_pct`

Additional nullable IHSG benchmark indicator fields:
- `ma20_slope_pct`
- `close_to_ma20_pct`
- `close_to_ma50_pct`

Targeted validation:
- `IndicatorVectorServiceTest` -> OK (10 tests, 76 assertions).
- `BenchmarkIndicatorVectorServiceTest` -> OK (3 tests, 21 assertions).
- `MarketBenchmarkReadModel` -> OK (3 tests, 23 assertions).
- `MarketDataWatchlistReadModel` -> OK (3 tests, 28 assertions).
- `MarketDataSqliteSchemaSync` -> OK (5 tests, 214 assertions).
- Full `tests/Unit/MarketData` -> OK (586 tests, 8771 assertions).

Remaining proof required before LOCKED/full production-ready relock:
- Daily/promote/evidence/replay runtime proof after the new columns are in place.

## Session Scope
- Add market benchmark foundation inside `market-data`.
- Keep benchmark/index data separate from equity ticker universe.
- Add IHSG benchmark master data with Yahoo Finance provider symbol `^JKSE`.
- Add benchmark EOD bars ingest, benchmark indicators, and equity indicator extension.
- Preserve current readable publication, evidence export, replay determinism, and no-bypass read-side contracts.

## Baseline Production-Ready Status
- Baseline before this session: `MARKET_DATA_PRODUCTION_READY_LOCKED: YES`.
- Baseline full MarketData PHPUnit before this session: `OK (495 tests, 7616 assertions)`.
- Current full MarketData PHPUnit after this session: `OK (511 tests, 7871 assertions)`.
- This session is now locked as `PASS` because migration/schema, tests, static guards, runtime proof, evidence, replay, benchmark DB proof, and audit docs are synchronized.

## Schema Changes
- Added `market_benchmarks`.
- Added `market_benchmark_bars`.
- Added `market_benchmark_indicators`.
- Added nullable extension columns to `eod_indicators` and `eod_indicators_history`:
  - `ma20`
  - `ma50`
  - `close_to_hh20_pct`
  - `close_vs_ma20_pct`
  - `close_vs_ma50_pct`
  - `ma20_slope_pct`
  - `rs_20_vs_ihsg`

## Benchmark Contract
- `tickers` remains the equity universe.
- `market_benchmarks` owns benchmark/index instruments.
- Required seed:
  - `benchmark_code=IHSG`
  - `benchmark_name=Jakarta Composite Index`
  - `provider=yahoo_finance`
  - `provider_symbol=^JKSE`
  - `instrument_type=INDEX`
  - `is_active=1`
- Benchmark bars are uniquely keyed by `(benchmark_code, trade_date)`.
- Benchmark indicators are uniquely keyed by `(benchmark_code, trade_date, indicator_set_version)`.

## Provider Symbol Contract
- Equity provider symbols use equity resolution: `BBCA -> BBCA.JK`.
- Benchmark/index provider symbols use master-data provider symbol as-is: `IHSG -> ^JKSE`.
- Forbidden:
  - `IHSG.JK`
  - `^JKSE.JK`
  - inserting IHSG into equity ticker universe as a normal ticker.

## Indicator Formulas
- Benchmark `roc_20 = ((close_today - close_20_trading_days_ago) / close_20_trading_days_ago) * 100`.
- Benchmark `ma20 = average close over 20 trading days`.
- Benchmark `ma50 = average close over 50 trading days`.
- Equity `ma20 = average basis_close over 20 trading days`.
- Equity `ma50 = average basis_close over 50 trading days`.
- Equity `close_to_hh20_pct = ((close_price - hh20) / hh20) * 100`.
- Equity `close_vs_ma20_pct = ((close_price - ma20) / ma20) * 100`.
- Equity `close_vs_ma50_pct = ((close_price - ma50) / ma50) * 100`.
- Equity `ma20_slope_pct = ((ma20_today - ma20_5_trading_days_ago) / ma20_5_trading_days_ago) * 100`.
- Equity `rs_20_vs_ihsg = (roc20_equity * 100) - IHSG.roc_20`.
- All lookbacks use trading-day order, not calendar subtraction.
- Insufficient lookback and null/zero denominators produce `NULL` outputs; fake values are forbidden.

## Affected Commands, Services, Repositories
- `market-data:daily` ingest stage can import benchmark bars after equity bars when `source_mode=api`.
- `market-data:promote` computes benchmark indicators before equity indicator extension.
- `PublicApiEodBarsAdapter` now has separate equity and benchmark provider-symbol resolution.
- `BenchmarkBarsIngestService` ingests benchmark EOD bars.
- `BenchmarkIndicatorComputeService` computes benchmark indicators.
- `BenchmarkIndicatorVectorService` owns benchmark formula computation.
- `MarketBenchmarkRepository` owns benchmark master, bars, indicators, and IHSG ROC lookup.
- `IndicatorVectorService` owns equity indicator extension computation.
- `EodArtifactRepository` snapshots/restores/copies the new equity indicator columns.

## Read-Side / Publication Contract Impact
- Existing current-readable publication contract remains the only consumer-safe path.
- New equity indicator fields are part of the readable indicator artifact after publication.
- Consumers must not read raw/staging/latest shortcuts or `MAX(trade_date)` to infer market-data state.
- Benchmark tables are upstream support artifacts and do not alter equity coverage-gate universe membership.
- `rs_20_vs_ihsg` is nullable when IHSG benchmark ROC is unavailable; it is never hardcoded.

## Tests Added / Updated
- Added `BenchmarkProviderSymbolResolverTest`.
- Added `BenchmarkBarsIngestServiceTest`.
- Added `BenchmarkIndicatorVectorServiceTest`.
- Added `MarketBenchmarkIndicatorExtensionStaticGuardTest`.
- Updated `IndicatorVectorServiceTest`.
- Updated `MarketDataSqliteSchemaSyncTest`.
- Updated `DbIntegrityConstraintEnforcementStaticGuardTest`.
- Updated SQLite schema support.

## Runtime Validation Commands
```bash
php artisan market-data:daily --requested_date=2026-05-19 --source_mode=api --output_dir=storage/app/market_data/daily/2026-05-19 -vvv
php artisan market-data:promote --requested_date=2026-05-19 --source_mode=api --run_id=<RUN_ID> --output_dir=storage/app/market_data/promote/2026-05-19 -vvv
php artisan market-data:evidence:export --run_id=<RUN_ID> --output_dir=storage/app/market_data/evidence/2026-05-19/run -vvv
php artisan market-data:replay:fixture:generate <RUN_ID> --case=valid_case --output_dir=storage/app/market_data/replay-fixtures/2026-05-19/valid_case -vvv
php artisan market-data:replay:verify <RUN_ID> storage/app/market_data/replay-fixtures/2026-05-19/valid_case --output_dir=storage/app/market_data/evidence/2026-05-19/replay -vvv
```

## Manual Database Checks
```sql
SELECT *
FROM market_benchmarks
WHERE benchmark_code = 'IHSG';

SELECT *
FROM market_benchmark_bars
WHERE benchmark_code = 'IHSG'
ORDER BY trade_date DESC
LIMIT 10;

SELECT *
FROM market_benchmark_indicators
WHERE benchmark_code = 'IHSG'
ORDER BY trade_date DESC
LIMIT 10;
```

## Evidence Export Status
- PASS. Operator-local evidence export for `run_id=3` completed with `evidence_completeness_state=COMPLETE`, `evidence_admission_state=ADMITTED_COMPLETE`, `pointer_resolve_status=RESOLVED_READABLE_CURRENT`, and `file_count=11`.
- Static compatibility is preserved by keeping current publication/pointer/evidence repository behavior intact.

## Replay Verify Status
- PASS. Runtime-generated replay fixture and verify for `run_id=3` completed with `replay_id=2`, `comparison_result=MATCH`, `replay_status=PASS`, and `mismatch_count=0`.
- Replay hash input now includes the new equity indicator columns so deterministic publication proof includes the extension.

## Config / ENV Governance
- No new ENV keys were added.
- Existing Yahoo/API config remains the source of provider endpoint, suffix, timeout, headers, and retry behavior.
- No unused benchmark ENV/config keys are introduced.

## Remaining Risks
- Yahoo/PublicApi availability can still affect future live runs, but current source-state runtime validation passed.
- A single-date benchmark import cannot compute benchmark `roc_20`, `ma20`, `ma50`, or non-null `rs_20_vs_ihsg` until sufficient historical IHSG benchmark bars exist. The current `IND_INSUFFICIENT_HISTORY` state is expected and non-blocking.
- Runtime status is no longer pending for this source state: daily/promote/evidence/replay commands passed for `2026-05-19`, `run_id=3`, `publication_id=2`.

## Done Criteria
- IHSG is not processed as an equity ticker.
- `^JKSE` is fetched without `.JK` suffix.
- Benchmark bars are written deterministically by `(benchmark_code, trade_date)`.
- Benchmark `roc_20`, `ma20`, and `ma50` are tested.
- Equity indicator extension formulas are tested.
- `rs_20_vs_ihsg` uses IHSG benchmark ROC, not a hardcoded value.
- Insufficient lookback and null/zero denominators produce deterministic `NULL`.
- Read-side contract remains current-readable-publication only.
- Evidence export and replay verify pass after runtime validation.
- Audit docs and static guards are synchronized.

## 2026-05-24 — Final Runtime Proof Lock

Status: `PASS`.

This reconciliation locks the market benchmark + indicator extension as production-ready for the current source state.

Validated proof:

- Migration: `php artisan migrate` -> `2026_05_24_000001_add_market_benchmark_indicator_extension` migrated successfully.
- Full MarketData PHPUnit: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (511 tests, 7871 assertions).
- Benchmark targeted tests: OK (14 tests, 84 assertions).
- Indicator targeted tests: OK (18 tests, 104 assertions).
- MarketBenchmarkIndicatorExtensionStaticGuardTest: OK (5 tests, 46 assertions).
- AuditDocsSynchronizationStaticGuardTest: OK (10 tests, 468 assertions).
- StaticGuard: OK (199 tests, 4930 assertions).
- Daily runtime: `run_id=3`, `source_final_status=SUCCESS`, `accepted_row_count=913`, `source_missing_ticker_count=0`, `benchmark_import_status=COMPLETED`, `benchmark_rows_written=1`.
- Promote runtime: `terminal_status=SUCCESS`, `publishability_state=READABLE`, `coverage_gate_state=PASS`, `coverage_ratio=1.0000`, `seal_state=SEALED`, `pointer_switched=true`, `publication_id=2`.
- Evidence export: `evidence_completeness_state=COMPLETE`, `evidence_admission_state=ADMITTED_COMPLETE`, `pointer_resolve_status=RESOLVED_READABLE_CURRENT`, `file_count=11`.
- Replay verify: `replay_id=2`, `comparison_result=MATCH`, `replay_status=PASS`, `mismatch_count=0`.
- Benchmark DB proof: `market_benchmarks` has `IHSG/^JKSE/INDEX/is_active=1`; `market_benchmark_bars` has `IHSG` for `2026-05-19`; `market_benchmark_indicators` has `IND_INSUFFICIENT_HISTORY`, which is expected until historical IHSG lookback exists.

Final decision:

```text
MARKET_BENCHMARK_INDICATOR_EXTENSION_STATUS: PASS
BASELINE_PRODUCTION_READY_PRESERVED: YES
FULL_MARKET_DATA_PHPUNIT: PASSED
FULL_MARKET_DATA_SUITE: OK (511 tests, 7871 assertions)
RUNTIME_VALIDATION: PASS
EVIDENCE_EXPORT: PASS
REPLAY_VERIFY: PASS
DOCS_UPDATED: YES
REMAINING_BLOCKERS: none
FULL_MARKET_DATA_PRODUCTION_READY: YES
```
