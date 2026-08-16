# WS C04 Operator Forensic Final Result

Status: C04_IMPLEMENTED / C04_IS_QUALITY_FAILED / C04_REJECTED_AS_STRATEGY_CATALOG
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-11

## 1. Verdict

```text
C04_IMPLEMENTED
C04_SEEDED
C04_IS_EXECUTED
C04_DETERMINISTIC_TWO_RUN
C04_FAILED_IS_QUALITY
C04_REJECTED_AS_STRATEGY_QUALITY_CATALOG
OOS_NOT_RUN
NOT_PRODUCTION_READY
C05_REQUIRED_IF_WORK_CONTINUES
```

C04 is an engineering success and a strategy-quality failure. It must not be promoted, must not unlock OOS, and must not be marked production ready.

## 2. Catalog identity

| Field | Value |
| --- | --- |
| catalog_code | `WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06` |
| catalog_version | `C04` |
| catalog_count | `10` |
| catalog_hash | `0ce3a313c45432c5a4d607def12b3f774988f324` |

## 3. Design source

C04 was derived from available C01/C02/C03 evidence and runtime-supported axes:

- C01 drilldown indicated relatively better buckets for moderate ROC, moderate volume, and moderate liquidity, while high momentum alone was weak;
- C02/C03 forensic showed enough data but persistent negative median return, weak downside, and unstable monthly behavior;
- C03 per-row artifact showed all 10 rows still failed robust-return, downside, and stability gates;
- runtime scoring already exposes `score_momentum`, `score_breakout`, `score_volume`, `score_risk`, `roc20`, `close_to_hh20_pct`, `ma20_slope_pct`, `rs_20_vs_ihsg`, `close_vs_ma20_pct`, and `close_vs_ma50_pct`;
- C04 therefore adds a C04-only candidate-selection extension in `bt_grid_resolution`, consumed by `WatchlistPlanGroupingService` before TOP/SECONDARY grouping.

C04 did not add a sector filter. Sector evidence remains diagnostic-only.

## 4. Validation evidence

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC04"
OK (14 tests, 499 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (264 tests, 5142 assertions)
```

C04 seed passed:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06
catalog_version=C04
catalog_count=10
catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
inserted_count=10
updated_count=0
existing_count=0
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
oos_executed=0
production_ready=0
```

## 5. C04 IS calibration evidence

### 5.1 Run 1

```text
status=C04_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C04_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06
catalog_version=C04
catalog_count=10
catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=10
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
max_requested_market_data_date=2025-05-07
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=fe964ee879dddc8aa8a83372e8c2d05aed5e8259
production_ready=0
```

### 5.2 Run 2

```text
status=C04_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C04_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06
catalog_version=C04
catalog_count=10
catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=10
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
max_requested_market_data_date=2025-05-07
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=fe964ee879dddc8aa8a83372e8c2d05aed5e8259
production_ready=0
```

## 6. Determinism assessment

| Marker | Run 1 | Run 2 | Verdict |
| --- | --- | --- | --- |
| status | `C04_GRID_FAILED_IS_QUALITY` | `C04_GRID_FAILED_IS_QUALITY` | SAME |
| reason_code | `WS_BT_C04_NO_VALID_IS_CANDIDATE` | `WS_BT_C04_NO_VALID_IS_CANDIDATE` | SAME |
| catalog_hash | `0ce3a313c45432c5a4d607def12b3f774988f324` | `0ce3a313c45432c5a4d607def12b3f774988f324` | SAME |
| is_trading_date_hash | `581dd450ebcbd56cb3a1c066c9fc80bbccb3a753` | `581dd450ebcbd56cb3a1c066c9fc80bbccb3a753` | SAME |
| is_valid_param_count | `0` | `0` | SAME |
| is_failed_param_count | `10` | `10` | SAME |
| artifact_hash | `fe964ee879dddc8aa8a83372e8c2d05aed5e8259` | `fe964ee879dddc8aa8a83372e8c2d05aed5e8259` | SAME |
| OOS guards | clean | clean | SAME |

## 7. Quality failure assessment

C04 failure reason distribution:

```text
WS_BT_EVAL_DOWNSIDE_FAIL=10
WS_BT_EVAL_MIN_TRADES_FAIL=7
WS_BT_EVAL_ROBUST_RETURN_FAIL=10
WS_BT_EVAL_STABILITY_FAIL=10
```

Per-row metric ranges from `storage/app/watchlist/backtest/c04-is-run-1.json`:

```text
picks_count=82..176
days_covered=496..502
avg_ret_net_top=-0.3039%..0.4019%
median_ret_net_top=-1.2712%..-0.0501%
p25_ret_net_top=-3.8881%..-3.0868%
month_win_rate_min=0.00%..0.00%
win_rate_top=37.80%..49.17%
```

Interpretation:

- C04 did reduce weak picks materially compared with C03 (`1104..1432` picks down to `82..176`);
- several C04 rows improved average return and p25 downside moved closer to the `-3%` threshold;
- however, all medians remained negative, every p25 remained below the downside threshold, and monthly stability collapsed to `0%` minimum monthly win rate;
- 7 of 10 rows also failed minimum trade count, which means the C04 quality floor became too restrictive for a meaningful robust sample on those rows.

C04 therefore did not solve IS quality. It produced useful forensic evidence for a future C05, but no valid strategy candidate.

## 8. Forensic artifacts

```text
storage/app/watchlist/backtest/c04-is-run-1.json
storage/app/watchlist/backtest/c04-is-run-2.json
storage/app/watchlist/backtest/c04-forensic-summary.csv
docs/watchlist/evidence/weekly_swing/artifacts/c04-forensic-summary.csv
```

The companion CSV records per-row `param_id`, `row_code`, failure reasons, picks, median return, p25 downside, monthly stability, coverage, and no-OOS markers.

## 9. Final decision

```text
C04_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C04 must not advance to OOS because:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
```

OOS remains `NOT_RUN`. Production readiness remains `false`. OOS has not been run and must not be claimed PASS.

## 10. Evidence-based next step

If work continues, the next same-focus attempt must be C05, not a mutation of C04.

C05 should use C04 evidence carefully:

- do not merely loosen C04 or canonical gates;
- keep the useful direction that average return and p25 moved closer to target;
- restore meaningful sample size before calibration by using a less brittle candidate-quality floor;
- directly address monthly stability, because C04 failed every row at `month_win_rate_min=0`;
- investigate score/rank stability and entry timing using only runtime-supported fields or explicitly implemented/tested new runtime fields.
