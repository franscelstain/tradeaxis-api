# MARKET DATA CONSUMER READ MODEL INVENTORY

## SESSION SCOPE

MARKET_DATA_CONSUMER_READ_MODEL_STATUS: PASS

This session adds official market-data consumer read surfaces for:
- Watchlist market-data rows.
- Portfolio official price rows.
- Benchmark IHSG context.
- Market-data readiness status.

The scope remains market-data only. Watchlist ranking, buy/sell decisions, target price, stop loss, take profit, recommendation output, portfolio market value, allocation, and P/L computation remain downstream module responsibilities.

## BASELINE PRODUCTION-READY STATUS

BASELINE_PRODUCTION_READY_PRESERVED: YES

Source baseline before this session:
- MARKET_DATA_PRODUCTION_READY_LOCKED: YES  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- FULL_MARKET_DATA_PHPUNIT: PASSED
- Full MarketData suite: OK (513 tests, 7980 assertions)
- MARKET_BENCHMARK_INDICATOR_EXTENSION_STATUS: PASS
- RUNTIME_VALIDATION: PASS
- EVIDENCE_EXPORT: PASS
- REPLAY_VERIFY: PASS
- REMAINING_BLOCKERS: none

This session does not change ingest, promote, finalize, evidence export, or replay behavior except for reason-code registry support needed by the read-side readiness surface.

## CONSUMER READ BOUNDARY

Market-data supplies official prices, indicators, benchmark context, publication proof metadata, and readiness status.

Downstream ownership boundary:
- watchlist owns ranking and candidate selection.
- portfolio owns holding valuation, allocation, and P/L.
- signal/strategy owns buy/sell decisions and recommendations.

The consumer read surface returns rows only after the official current readable publication pointer resolves.

## WATCHLIST READ MODEL CONTRACT

WATCHLIST_READ_SURFACE: PASS

Owner classes:
- `MarketDataWatchlistReadService`
- `MarketDataWatchlistReadRepository`

Contract:
- Input is an explicit requested `trade_date`.
- Data must be resolved through `EodPublicationRepository::resolveCurrentReadablePublicationForTradeDate($tradeDate)`.
- Publication must be current, SEALED, owned by a SUCCESS run, publishability READABLE, and coverage PASS.
- Rows are scoped by exact `trade_date + publication_id`.
- Returned rows include ticker identity, close/volume, liquidity and momentum indicators, benchmark-relative strength, indicator set version, source name, `publication_id`, `publication_version`, and `run_id`.
- No watchlist ranking or buy/sell output is produced.
- If no readable current publication exists, the result is blocked with an explicit reason code and empty rows.

## PORTFOLIO PRICE READ MODEL CONTRACT

PORTFOLIO_PRICE_SURFACE: PASS

Owner classes:
- `MarketDataPortfolioPriceService`
- `MarketDataPortfolioPriceRepository`

Contract:
- Input is an explicit requested `trade_date` and explicit ticker list.
- Official current-day prices are scoped by exact `trade_date + publication_id` from the resolved current readable publication.
- Returned rows include close, adjusted close, previous close when a previous readable publication exists, change amount, change percent, source name, `publication_id`, `publication_version`, and `run_id`.
- Missing requested tickers are returned in `missing_tickers`.
- No fallback to another requested date is allowed.
- Portfolio market value, unrealized P/L, allocation, and recommendation output are not computed in market-data.
- If previous close is unavailable, `previous_close_price` is null with a reason code.

## BENCHMARK READ MODEL CONTRACT

BENCHMARK_READ_SURFACE: PASS

Owner classes:
- `MarketBenchmarkReadService`
- `MarketBenchmarkReadRepository`

Contract:
- Benchmark context is readable only after market-data readiness for the requested date is proven.
- IHSG is read from `market_benchmarks`, `market_benchmark_bars`, and `market_benchmark_indicators`.
- IHSG is not treated as an equity ticker.
- Provider symbol `^JKSE` is preserved and is not suffixed as `^JKSE.JK`.
- Insufficient benchmark history returns nullable indicators and `IND_INSUFFICIENT_HISTORY`, never fabricated benchmark values.

## FRESHNESS / READINESS CONTRACT

READINESS_SURFACE: PASS

Owner class:
- `MarketDataReadinessService`

`is_ready=true` only when all conditions hold:
- current pointer resolves.
- publication is current.
- publication is SEALED.
- run terminal status is SUCCESS.
- publishability state is READABLE.
- coverage gate state is PASS.

Ready output uses `READABLE_PUBLICATION_RESOLVED` and `RESOLVED_READABLE_CURRENT`.

Blocked output is fail-closed with explicit reason codes, including:
- NO_READABLE_PUBLICATION
- PUBLICATION_NOT_SEALED
- RUN_TERMINAL_STATUS_NOT_SUCCESS
- RUN_PUBLISHABILITY_NOT_READABLE
- RUN_COVERAGE_GATE_NOT_PASS
- POINTER_NOT_RESOLVED / NOT_RESOLVED_READABLE_CURRENT semantics through the blocked payload.

## AFFECTED REPOSITORIES / SERVICES

Added:
- `app/Application/MarketData/Services/MarketDataReadinessService.php`
- `app/Application/MarketData/Services/MarketDataWatchlistReadService.php`
- `app/Application/MarketData/Services/MarketDataPortfolioPriceService.php`
- `app/Application/MarketData/Services/MarketBenchmarkReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPortfolioPriceRepository.php`
- `app/Infrastructure/Persistence/MarketData/MarketBenchmarkReadRepository.php`

Updated:
- `docs/market_data/registry/Reason_Codes_Registry.md`
- `docs/market_data/registry/Reason_Codes_Seed.sql`
- `docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md`

## NO RAW / STAGING / LATEST / MAX(DATE) ENFORCEMENT

READ_SIDE_CONTRACT_STATUS: current readable publication only; no raw/staging/latest/MAX(date)

Consumer read surfaces must not use:
- `MAX(trade_date)`
- `max('trade_date')`
- `latest('trade_date')`
- `orderByDesc('trade_date')`
- `orderBy('trade_date', 'desc')`
- raw or staging table fallback
- candidate publication fallback
- unsealed publication fallback
- evidence historical publication resolver
- silent fallback to another requested date

Static guard owner:
- `MarketDataConsumerReadModelStaticGuardTest`

## TESTS ADDED / UPDATED

Added:
- `tests/Unit/MarketData/MarketDataWatchlistReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPortfolioPriceReadModelTest.php`
- `tests/Unit/MarketData/MarketBenchmarkReadModelTest.php`
- `tests/Unit/MarketData/MarketDataReadinessServiceTest.php`
- `tests/Unit/MarketData/MarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Support/MarketData/SeedsConsumerReadModelFixture.php`

Updated:
- `tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php`

Required validation commands:
- `vendor/bin/phpunit tests/Unit/MarketData --filter "WatchlistRead"` -> OK (3 tests, 22 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "PortfolioPrice"` -> OK (4 tests, 21 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "BenchmarkRead"` -> OK (3 tests, 17 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Readiness"` -> OK (22 tests, 289 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "ConsumerReadModel"` -> OK (5 tests, 110 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (206 tests, 5262 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (534 tests, 8287 assertions)

Raw operator proof artifact:
- `storage/app/market_data/evidence/consumer-read-model/operator_command_proof.txt`

## RUNTIME VALIDATION COMMANDS

If local DB already contains `2026-05-19`, `run_id=3`, and `publication_id=2`, validate via read-model tests or read-only preview commands if added.

Runtime artifact verified in this source tree:
- `storage/app/market_data/daily/2026-05-19/market_data_daily_summary.json` records `run_id=3`, `publication_id=2`, accepted rows `913`, and `benchmark_import_status=COMPLETED`.
- `storage/app/market_data/promote/2026-05-19/market_data_promote_summary.json` records `run_id=3`, `publication_id=2`, `terminal_status=SUCCESS`, `publishability_state=READABLE`, `coverage_gate_state=PASS`, `seal_state=SEALED`, `pointer_switched=true`, `coverage_ratio=1`.

If local DB does not contain the state, operator-local commands:

```bash
php artisan market-data:daily --requested_date=2026-05-19 --source_mode=api --output_dir=storage/app/market_data/daily/2026-05-19 -vvv
php artisan market-data:promote --requested_date=2026-05-19 --source_mode=api --run_id=<RUN_ID> --output_dir=storage/app/market_data/promote/2026-05-19 -vvv
```

Manual database proof:

```sql
SELECT *
FROM eod_current_publication_pointer
WHERE trade_date = '2026-05-19';
```

```sql
SELECT
  p.publication_id,
  p.trade_date,
  p.publication_version,
  p.is_current,
  p.seal_state,
  r.run_id,
  r.terminal_status,
  r.publishability_state,
  r.coverage_gate_state
FROM eod_publications p
JOIN eod_runs r ON r.run_id = p.run_id
WHERE p.trade_date = '2026-05-19'
ORDER BY p.publication_id DESC;
```

Expected publication/run proof:
- publication_id=2
- run_id=3
- is_current=1
- seal_state=SEALED
- terminal_status=SUCCESS
- publishability_state=READABLE
- coverage_gate_state=PASS

Indicator proof:

```sql
SELECT
  ticker_code,
  trade_date,
  roc5,
  roc10,
  roc20,
  hh20,
  ll20,
  ma20,
  ma50,
  close_to_hh20_pct,
  close_to_ll20_pct,
  range_20_pct,
  range_position_20_pct,
  close_vs_ma20_pct,
  close_vs_ma50_pct,
  ma20_slope_pct,
  rs_20_vs_ihsg,
  sector_code,
  sector_roc20,
  rs_20_vs_sector,
  sector_rs_20_vs_ihsg
FROM eod_indicators
WHERE trade_date = '2026-05-19'
ORDER BY ticker_code
LIMIT 20;
```

Benchmark proof:

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

2026-06-02 weekly-swing extension note:
- Watchlist read output now exposes `roc_5`, `roc_10`, `ll20`, `close_to_ll20_pct`, `range_20_pct`, and `range_position_20_pct` from the same pointer-scoped `eod_indicators` join.
- Benchmark read output now exposes IHSG `ma20_slope_pct`, `close_to_ma20_pct`, and `close_to_ma50_pct` from `market_benchmark_indicators`.
- The read model remains current-readable-publication only and does not add scoring/ranking/entry decisions.

2026-06-03 sector-code source surface note:
- Watchlist read output now exposes nullable `sector_code`, `sector_name`, and `sector_index_code` from the pointer-scoped indicator row and active sector taxonomy.
- Missing membership remains NULL; read-side code does not infer sector from raw/latest ticker metadata.

2026-06-03 sector-rotation source surface note:
- Watchlist read output now exposes nullable `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg` from the pointer-scoped indicator row.
- Missing sector index history remains NULL; read-side code does not infer, forward-fill, or fabricate sector rotation values.

## EVIDENCE / REPLAY IMPACT

EVIDENCE_EXPORT: UNAFFECTED_BY_READ_MODEL
REPLAY_VERIFY: UNAFFECTED_BY_READ_MODEL

This session adds read-only consumer surfaces and reason-code registry support. It does not alter evidence artifact generation, evidence admission, replay hashing, replay comparison, candidate publication promotion, or current pointer switching.

## REMAINING RISKS

REMAINING_BLOCKERS: none

Residual note:
- Operator-local live DB preview remains optional unless a fresh live database proof is required; seeded contract tests cover read-side publication behavior.

## DONE CRITERIA

Done criteria for this inventory:
- Watchlist read surface returns indicator rows from current readable publication.
- Watchlist read surface blocks when no readable publication exists.
- Portfolio price read surface returns official close/adjusted close from current readable publication.
- Portfolio price read surface returns missing tickers and does not fallback to another date.
- Benchmark read surface returns IHSG from benchmark tables and preserves `IND_INSUFFICIENT_HISTORY`.
- Readiness service returns ready only for `SEALED / SUCCESS / READABLE / PASS / current pointer`.
- Static guard forbids raw/staging/latest/MAX(date) bypass in consumer read model classes.
- Audit docs include `MARKET_DATA_CONSUMER_READ_MODEL_CONTRACT`.
- Full `vendor/bin/phpunit tests/Unit/MarketData` passes.

## FINAL STATUS PLACEHOLDERS

MARKET_DATA_CONSUMER_READ_MODEL_STATUS: PASS
BASELINE_PRODUCTION_READY_PRESERVED: YES
WATCHLIST_READ_SURFACE: PASS
PORTFOLIO_PRICE_SURFACE: PASS
BENCHMARK_READ_SURFACE: PASS
READINESS_SURFACE: PASS
READ_SIDE_CONTRACT_STATUS: current readable publication only; no raw/staging/latest/MAX(date)
STATIC_GUARD_STATUS: PASS
TEST_RESULT: PASS; vendor/bin/phpunit tests/Unit/MarketData -> OK (534 tests, 8287 assertions); raw proof artifact stored at storage/app/market_data/evidence/consumer-read-model/operator_command_proof.txt
RUNTIME_VALIDATION: PASS; seeded current-readable-publication contract tests passed and 2026-05-19 runtime promote artifact records run_id=3/publication_id=2 as SUCCESS/READABLE/PASS/SEALED/current
DOCS_UPDATED: YES
REMAINING_BLOCKERS: none
NEXT_ACTION: none for market-data consumer read model scope
