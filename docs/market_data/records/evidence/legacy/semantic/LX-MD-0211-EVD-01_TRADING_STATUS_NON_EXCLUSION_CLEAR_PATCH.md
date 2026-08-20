# Legacy Semantic Extract — LX-MD-0211-EVD-01

- Source ID: `LS-MD-0211`
- Original path: `patches/TRADING_STATUS_NON_EXCLUSION_CLEAR_PATCH.md`
- Original SHA1: `D7FA2DA1E496AD9DA1448E9736FD87DDADE28E6C`
- Extract role: `EVIDENCE`
- Source range: `L29-L87`
- Extract body SHA1: `BB2932E4F39CBB96674CF7400B011BD14F4EB46D`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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

<!-- LEGACY_EXTRACT_BODY_END -->
