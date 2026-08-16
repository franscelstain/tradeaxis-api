# WS C05 Operator Forensic Final Result

Status: C05_IMPLEMENTED / C05_IS_QUALITY_FAILED / C05_REJECTED_AS_STRATEGY_CATALOG
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-11

## 1. Verdict

```text
C05_IMPLEMENTED
C05_SEEDED
C05_IS_EXECUTED
C05_DETERMINISTIC_TWO_RUN
C05_FAILED_IS_QUALITY
C05_REJECTED_AS_STRATEGY_QUALITY_CATALOG
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C05 is an engineering success and a strategy-quality failure. It must not be promoted, must not unlock OOS, and must not be marked production ready.

## 2. Catalog identity

| Field | Value |
| --- | --- |
| catalog_code | `WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06` |
| catalog_version | `C05` |
| catalog_count | `12` |
| catalog_hash | `476af5dde18079b1270556bc44bbc632edd46e27` |

## 3. Design source

C05 was derived from C04 forensic evidence:

- C04 reduced picks to `82..176` and improved some p25/average values;
- C04 also created `WS_BT_EVAL_MIN_TRADES_FAIL` on 7/10 rows and month-win minimum remained `0%`;
- C05 therefore replaced C04's all-component hard floor with a soft pass-count/average floor;
- C05 still used only runtime-supported score components, trend/relative-strength fields, ROC band, close-to-HH20 setup, and existing grouping quantiles;
- no sector filter was added.

## 4. Validation evidence

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC05"
OK (13 tests, 523 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (277 tests, 5665 assertions)
```

C05 seed passed with R1/R2/C01/C02/C03/C04 immutability preserved:

```text
status=PASS
catalog_hash=476af5dde18079b1270556bc44bbc632edd46e27
inserted_count=12
updated_count=0
existing_count=0
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
c04_immutable=1
oos_executed=0
production_ready=0
```

## 5. C05 IS calibration evidence

Run 1 and run 2 were deterministic:

```text
status=C05_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C05_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06
catalog_version=C05
catalog_count=12
catalog_hash=476af5dde18079b1270556bc44bbc632edd46e27
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=12
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
max_requested_market_data_date=2025-05-21
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=f8288cb2d395e397f433dae854c0ad80b4650a8d
production_ready=0
```

## 6. Quality failure assessment

C05 failure reason distribution:

```text
WS_BT_EVAL_DOWNSIDE_FAIL=12
WS_BT_EVAL_ROBUST_RETURN_FAIL=12
WS_BT_EVAL_STABILITY_FAIL=12
```

Per-row metric ranges from `storage/app/watchlist/backtest/c05-is-run-1.json`:

```text
picks_count=370..886
avg_ret_net_top=-0.5847%..0.0719%
median_ret_net_top=-1.6122%..-0.7301%
p25_ret_net_top=-4.0209%..-3.2708%
month_win_rate_min=0.00%..18.75%
win_rate_top=36.62%..41.31%
```

Interpretation:

- C05 successfully restored sample size relative to C04 and removed aggregate minimum-trade failures;
- C05 did not preserve C04's near-threshold median/p25 improvement strongly enough;
- all medians remained negative, all downside p25 values remained below `-3%`, and all rows still failed monthly stability.

## 7. Forensic artifacts

```text
storage/app/watchlist/backtest/c05-is-run-1.json
storage/app/watchlist/backtest/c05-is-run-2.json
storage/app/watchlist/backtest/c05-forensic-summary.csv
docs/watchlist/evidence/weekly_swing/artifacts/c05-forensic-summary.csv
```

## 8. Final decision

```text
C05_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C05 must not advance to OOS because:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
```

OOS remains `NOT_RUN`. Production readiness remains false. OOS has not been run and must not be claimed PASS.
