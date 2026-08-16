# WS C06 Operator Forensic Final Result

Status: C06_IMPLEMENTED / C06_IS_QUALITY_FAILED / C06_REJECTED_AS_STRATEGY_CATALOG
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-12

## 1. Verdict

```text
C06_IMPLEMENTED
C06_SEEDED
C06_IS_EXECUTED
C06_DETERMINISTIC_TWO_RUN
C06_FAILED_IS_QUALITY
C06_REJECTED_AS_STRATEGY_QUALITY_CATALOG
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C06 is an engineering success and a strategy-quality failure. It must not be promoted, must not unlock OOS, and must not be marked production ready.

## 2. Catalog identity

| Field | Value |
| --- | --- |
| catalog_code | `WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06` |
| catalog_version | `C06` |
| catalog_count | `12` |
| catalog_hash | `6c93d67fb77319a02cecc3d96fd99bb0e139a1ac` |

## 3. Design source

C06 was derived from C01/C04/C05 forensic evidence:

- C01 drilldown evidence showed moderate DV20 and moderate volume buckets had better direction than high-liquidity/high-participation chase buckets;
- C04 showed strict quality filters could improve average, median, and p25 downside but collapsed sample size and monthly stability;
- C05 restored sample size but medians stayed negative and p25 downside remained below the locked `-3%` threshold;
- C06 therefore used only runtime-supported metric bounds: DV20 between catalog min and `dv20_strong_idr`, volume ratio between catalog min and `strong_vol_ratio`, ATR band, ROC band, close-to-HH20 setup band, score component majority/average, and trend pass count;
- no sector filter was added.

## 4. Validation evidence

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC06"
OK (13 tests, 503 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (290 tests, 6168 assertions)
```

C06 seed passed with R1/R2/C01/C02/C03/C04/C05 immutability preserved:

```text
status=PASS
catalog_hash=6c93d67fb77319a02cecc3d96fd99bb0e139a1ac
inserted_count=12
updated_count=0
existing_count=0
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
c04_immutable=1
c05_immutable=1
oos_executed=0
production_ready=0
```

## 5. C06 IS calibration evidence

Run 1 and run 2 were deterministic:

```text
status=C06_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C06_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06
catalog_version=C06
catalog_count=12
catalog_hash=6c93d67fb77319a02cecc3d96fd99bb0e139a1ac
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=12
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
max_requested_market_data_date=2025-05-16
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=ede8ca6f53ea49141a5e047e6094b7a282cdb232
production_ready=0
```

## 6. Quality failure assessment

C06 failure reason distribution:

```text
WS_BT_EVAL_DOWNSIDE_FAIL=5
WS_BT_EVAL_MIN_TRADES_FAIL=9
WS_BT_EVAL_ROBUST_RETURN_FAIL=10
WS_BT_EVAL_STABILITY_FAIL=12
```

Per-row metric ranges from `storage/app/watchlist/backtest/c06-is-run-1.json`:

```text
picks_count=9..214
avg_ret_net_top=-0.7016%..1.4133%
median_ret_net_top=-1.6757%..1.6637%
p25_ret_net_top=-3.4390%..-0.6101%
month_win_rate_min=0.00%..0.00%
win_rate_top=30.36%..66.67%
```

Interpretation:

- C06 strict moderate-liquidity/volume/ROC caps can improve median and p25 on some rows;
- the rows with the best median/p25 evidence have too few picks and fail minimum trades;
- the rows that recover enough sample size fail robust return, p25 downside, and monthly stability;
- every C06 row still has `month_win_rate_min=0`, so the monthly stability gap remains unresolved.

## 7. Forensic artifacts

```text
storage/app/watchlist/backtest/c06-is-run-1.json
storage/app/watchlist/backtest/c06-is-run-2.json
storage/app/watchlist/backtest/c06-forensic-summary.csv
docs/watchlist/evidence/weekly_swing/artifacts/c06-forensic-summary.csv
```

## 8. Final decision

```text
C06_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C06 must not advance to OOS because:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
```

OOS remains `NOT_RUN`. Production readiness remains false. OOS has not been run and must not be claimed PASS.

## 9. Evidence-based next-step constraint

A next same-focus catalog should not simply tighten C06 further: the strict rows already collapse trade count and still fail monthly stability. It should also not revert to broad C05-style sampling without a new runtime-supported quality axis, because broader rows reintroduced robust-return and downside failures. The remaining gap points to candidate-selection evidence not currently captured by the C04/C05/C06 axes, or to a separate approved strategy family after further runtime/data audit.
