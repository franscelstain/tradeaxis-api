# Trading Status Semantics + Long Suspension Coverage Closure

> SUPERSEDED CURRENT MODEL NOTE (2026-07-02): This patch is retained as historical evidence only. Current trading-status source truth is `TRADING_STATUS_SOURCE_MODEL_SIMPLIFICATION_2026_07_02.md`: source rows use only `event_type_code` (`SUSPENDED`, `UNSUSPENDED`, `SPECIAL_MONITORING_START`, `SPECIAL_MONITORING_END`, `UMA`), and coverage semantics live in `market_data_trading_status_event_types` instead of source-row columns such as `coverage_exclusion_flag`.


[LAST_UPDATED] 2026-07-02

## Status

COMPLETED / VALIDATED

## Scope

This closure records the final Market Data trading-status semantics and the 2026-06-29 coverage recovery proof after importing IDX long-suspension evidence.

The session closed three related issues:

1. Historical `SUSPENDED` state must not remain effective after a newer official non-exclusion status.
2. `SPECIAL_MONITORING`, `SPECIAL_MONITORING_EXIT`, and `UMA` must remain event-risk context only when `coverage_exclusion_flag=0`; they must not block import or coverage.
3. IDX `Suspensi Lebih Dari 6 Bulan` / potential-delisting data is a separate long-suspension evidence source and must be imported as coverage exclusion.

## Final domain rules

- Suspension does not expire by age.
- Effective coverage exclusion is determined by the latest official semantic status as of the target trade date.
- `SUSPENDED`, `HALT`, `LONG_SUSPENSION_GT_6M`, or any row with `coverage_exclusion_flag=1` excludes the ticker from the coverage universe.
- `SPECIAL_MONITORING`, `SPECIAL_MONITORING_EXIT`, `UMA`, `WATCHLIST`, or `NOTASI_KHUSUS` with `coverage_exclusion_flag=0` clears older suspension carry-forward for coverage purposes.
- `SPECIAL_MONITORING` and `UMA` remain event-risk signals when applicable, but they are not import or coverage blockers.
- `SPECIAL_MONITORING_EXIT` clears special-monitoring event risk and must not force an unrelated UMA flag when the source row does not carry UMA information.
- IDX long-suspension / potential-delisting sources must be represented by `LONG_SUSPENSION_GT_6M`, `is_suspended=1`, and `coverage_exclusion_flag=1`.

## Code validation

Operator-local validation after the final v2 fix:

```text
vendor\bin\phpunit tests\Unit\MarketData\EventRiskSourceRepositoryTest.php
OK (12 tests, 91 assertions)

vendor\bin\phpunit tests\Unit\MarketData\ImportTradingStatusEventsCommandTest.php
OK (5 tests, 37 assertions)

vendor\bin\phpunit tests\Unit\MarketData\MarketDataSqliteSchemaSyncTest.php
OK (5 tests, 301 assertions)

vendor\bin\phpunit tests\Unit\MarketData
OK (651 tests, 9627 assertions)
```

## Long-suspension source import proof

Command executed locally:

```powershell
php artisan market-data:events:import-trading-status storage/app/market_data/events/idx_suspension_gt_6m.csv --apply -vvv
```

Result:

```text
status=APPLIED
reason_code=COMMAND_APPLY_CONFIRMED
input_file=storage/app/market_data/events/idx_suspension_gt_6m.csv
source_name=manual_trading_status_csv
operation_mode=APPLY
row_count=59
valid_row_count=59
upserted_count=59
error_count=0
status_codes=LONG_SUSPENSION_GT_6M
```

Database verification:

```text
status_code=LONG_SUSPENSION_GT_6M
source_name=idx_suspension_gt_6m
source_ref=20260630-pengumuman-potensi-delisting-juni-2026.xlsx
total=59
```

## 2026-06-29 final lifecycle proof

Command executed locally:

```powershell
php artisan market-data:backfill:lifecycle 2026-06-29 2026-06-29 `
  --source_mode=api `
  --with-evidence `
  --with-replay `
  -vvv
```

Final result:

```text
run_id=37961
publication_id=38228
ticker_count=887
tickers=872/887
coverage=PASS
promote=SUCCESS / PROMOTED
evidence=EXPORTED
fixture=GENERATED
replay=VERIFIED
readable=YES
final_reason_code=RUN_SOURCE_PARTIAL_RESPONSE
```

Coverage proof from `run_summary.json`:

```text
coverage_expected_count=887
coverage_available_count=872
coverage_missing_count=15
coverage_ratio=0.983089
coverage_min_threshold=0.98
coverage_gate_state=PASS
coverage_reason_code=COVERAGE_THRESHOLD_MET
publishability_state=READABLE
```

Publication-specific debug proof:

```text
trade_date=2026-06-29
publication_id=38228
base_universe=956
suspended=69
coverage_universe=887
candidate_bars=872
available_in_universe=872
missing=15
```

Residual non-blocking missing requested-date rows:

```text
COWL
DUCK
ENVY
GOLL
LCGP
LMAS
MABA
MTRA
OCAP
PLAS
SCPI
SRIL
SUGI
TDPM
TRIL
```

## Evidence caution

When the same evidence output directory is reused across multiple reruns, `source_acquisition_checkpoint.json` can retain older failed entries from earlier universe states. Final coverage decisions for this session must be based on:

1. `dates/<trade_date>/run_<run_id>/evidence/run_summary.json` for the final run; and
2. publication-specific debug output using the final `publication_id`.

For 2026-06-29, the authoritative final run is `run_id=37961` and `publication_id=38228`.

## Closure verdict

`TRADING_STATUS_SEMANTICS_AND_LONG_SUSPENSION_COVERAGE_CLOSURE=COMPLETED`

The 2026-06-29 date is no longer held or unreadable. It is PASS / PROMOTED / READABLE with 15 residual non-blocking provider-missing tickers.
