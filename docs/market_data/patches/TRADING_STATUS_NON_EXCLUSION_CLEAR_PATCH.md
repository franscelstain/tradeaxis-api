# Trading Status Non-Exclusion Clear Patch

> **HISTORICAL PATCH RECORD — NON-AUTHORITATIVE UNDER CURRENT V2.** Retained for traceability; current owner contracts override any legacy behavior stated below.


> SUPERSEDED CURRENT MODEL NOTE (2026-07-02): This patch is retained as historical evidence only. Current trading-status source truth is `TRADING_STATUS_SOURCE_MODEL_SIMPLIFICATION_2026_07_02.md`: source rows use only `event_type_code` (`SUSPENDED`, `UNSUSPENDED`, `SPECIAL_MONITORING_START`, `SPECIAL_MONITORING_END`, `UMA`), and coverage semantics live in `market_data_trading_status_event_types` instead of source-row columns such as `coverage_exclusion_flag`.


[LAST_UPDATED] 2026-07-02

## Purpose

This patch refines the Market Data trading-status resolver so that an official latest non-exclusion status clears old suspension carry-forward for coverage purposes, without removing event-risk context.

## Domain rule

- `coverage_exclusion_flag = 1` means the ticker is excluded from coverage, e.g. `SUSPENDED`, `HALT`, `LONG_SUSPENSION_GT_6M`.
- `coverage_exclusion_flag = 0` on an official non-exclusion status clears old coverage exclusion carry-forward.
- `SPECIAL_MONITORING`, `SPECIAL_MONITORING_EXIT`, `UMA`, `WATCHLIST`, and `NOTASI_KHUSUS` are not coverage blockers when `coverage_exclusion_flag = 0`.
- `SPECIAL_MONITORING` and `UMA` may remain event-risk signals even when they are not coverage blockers.
- `SPECIAL_MONITORING_EXIT` clears special-monitoring event risk and must not be treated as suspended.
- Long-suspension sources such as IDX `Suspensi Lebih Dari 6 Bulan` must be imported as `coverage_exclusion_flag = 1`.

## Changed files

- `app/Infrastructure/Persistence/MarketData/EventRiskSourceRepository.php`
- `tests/Unit/MarketData/EventRiskSourceRepositoryTest.php`

## Validation performed in this sandbox

PHP syntax validation was performed for `app/**/*.php` and `tests/**/*.php`.

PHPUnit was not executed in this sandbox because the uploaded ZIP does not include the `vendor/` directory / `vendor/bin/phpunit`.

## Required local validation

Run locally from `D:\Laravel\tradeaxis-api` after applying the ZIP:

```powershell
vendor\bin\phpunit tests\Unit\MarketData\EventRiskSourceRepositoryTest.php
vendor\bin\phpunit tests\Unit\MarketData\ImportTradingStatusEventsCommandTest.php
vendor\bin\phpunit tests\Unit\MarketData\MarketDataSqliteSchemaSyncTest.php
vendor\bin\phpunit tests\Unit\MarketData
```

Then re-check 2026-06-29:

```powershell
php debug_status_semantics_20260629.php

php artisan market-data:backfill:lifecycle 2026-06-29 2026-06-29 `
  --source_mode=api `
  --with-evidence `
  --with-replay `
  -vvv
```


## Final closure evidence

Patch final status: PASS / CLOSED.

Operator-local final validation after fix v2:

```text
EventRiskSourceRepositoryTest.php: OK (12 tests, 91 assertions)
ImportTradingStatusEventsCommandTest.php: OK (5 tests, 37 assertions)
MarketDataSqliteSchemaSyncTest.php: OK (5 tests, 301 assertions)
tests/Unit/MarketData: OK (651 tests, 9627 assertions)
```

Final runtime closure after importing IDX long-suspension source:

```text
LONG_SUSPENSION_GT_6M import: row_count=59, valid_row_count=59, upserted_count=59, error_count=0
DB source verification: source_name=idx_suspension_gt_6m, source_ref=20260630-pengumuman-potensi-delisting-juni-2026.xlsx, total=59
2026-06-29 lifecycle: run_id=37961, publication_id=38228, coverage=PASS, promote=PROMOTED, readable=READABLE, evidence=EXPORTED, fixture=GENERATED, replay=VERIFIED
coverage_expected=887, coverage_available=872, coverage_missing=15, coverage_ratio=0.983089
```

Residual non-blocking missing tickers for 2026-06-29:

```text
COWL, DUCK, ENVY, GOLL, LCGP, LMAS, MABA, MTRA, OCAP, PLAS, SCPI, SRIL, SUGI, TDPM, TRIL
```

See also: `docs/market_data/patches/TRADING_STATUS_SEMANTICS_LONG_SUSPENSION_CLOSURE_2026_07_02.md`.
