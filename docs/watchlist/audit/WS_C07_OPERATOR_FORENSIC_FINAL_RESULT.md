# WS C07 Operator Forensic Final Result

Status: C07_IMPLEMENTED / C07_IS_QUALITY_FAILED / C07_REJECTED_AS_STRATEGY_CATALOG
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-12

## 1. Verdict

```text
C07_IMPLEMENTED
C07_SEEDED
C07_IS_EXECUTED
C07_DETERMINISTIC_TWO_RUN
C07_FAILED_IS_QUALITY
C07_REJECTED_AS_STRATEGY_QUALITY_CATALOG
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C07 is an engineering success and a strategy-quality failure. It must not be promoted, must not unlock OOS, and must not be marked production ready.

## 2. Catalog identity

| Field | Value |
| --- | --- |
| catalog_code | `WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06` |
| catalog_version | `C07` |
| catalog_count | `12` |
| catalog_hash | `233b45b06cbf34da221d5d7de2d9725fdf4d3441` |

## 3. Runtime feature audit and design source

C07 was derived from C01/C04/C05/C06 forensic evidence and a current runtime feature audit:

- C04 showed stricter entry-quality floors could improve direction but collapsed sample and monthly stability;
- C05 restored sample size but remained negative on median return and below the `-3%` p25 downside threshold;
- C06 tested moderate liquidity/volume/ROC caps and showed that stricter rows could become too sparse while broader rows still failed quality;
- runtime audit found additional persisted metrics already selected by the market-data repository and available for pass-through: `roc5`, `roc10`, `ll20`, `close_to_ll20_pct`, `range_20_pct`, `range_position_20_pct`, `sector_roc20`, `rs_20_vs_sector`, `sector_rs_20_vs_ihsg`, `corporate_action_flag`, `is_suspended`, `is_uma`, and `event_risk_flag`;
- database coverage audit found broad non-null coverage for the new continuous metrics across the current workspace dataset, while risk flags were present on a narrower market-data subset;
- C07 therefore used short-term momentum, range-position, not-overextended, sector-relative confirmation, event-risk flag exclusion, score component confirmation, trend confirmation, and raw setup guard tolerances;
- no sector whitelist or unsupported sector filter was added.

## 4. Validation evidence

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07"
OK (10 tests, 376 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (300 tests, 6544 assertions)
```

C07 seed passed with R1/R2/C01/C02/C03/C04/C05/C06 immutability preserved:

```text
status=PASS
catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
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
c06_immutable=1
oos_executed=0
production_ready=0
```

## 5. C07 IS calibration evidence

Run 1 and run 2 were deterministic:

```text
status=C07_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C07_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
catalog_version=C07
catalog_count=12
catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
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
artifact_hash=c562d0a37ec7911c17c50072413fbbae25bb6114
production_ready=0
```

## 6. Quality failure assessment

C07 failure reason distribution:

```text
WS_BT_EVAL_DOWNSIDE_FAIL=12
WS_BT_EVAL_ROBUST_RETURN_FAIL=12
WS_BT_EVAL_STABILITY_FAIL=12
```

Per-row metric ranges from `storage/app/watchlist/backtest/c07-is-run-1.json`:

```text
picks_count=728..1355
avg_ret_net_top=-0.4106%..-0.2499%
median_ret_net_top=-1.0279%..-0.6993%
p25_ret_net_top=-4.0156%..-3.4276%
month_win_rate_min=17.86%..25.00%
win_rate_top=36.99%..39.05%
```

Interpretation:

- C07 recovered meaningful sample size versus the sparse C06 rows;
- C07 improved monthly stability versus C06's zero-minimum monthly win-rate rows, but the best row was still only `25.00%`, far below the locked `45%` gate;
- every row retained a negative median return and p25 downside worse than `-3%`;
- short-term range, sector-relative confirmation, and event-risk avoidance did not remove the weak return distribution enough to create a valid IS candidate;
- the current downside-stability candidate-selection family remains strategy-quality blocked under the canonical gates.

## 7. Forensic artifacts

```text
storage/app/watchlist/backtest/c07-is-run-1.json
storage/app/watchlist/backtest/c07-is-run-2.json
storage/app/watchlist/backtest/c07-forensic-summary.csv
docs/watchlist/audit/_artifacts/c07-forensic-summary.csv
```

## 8. Final decision

```text
C07_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C07 must not advance to OOS because:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
```

OOS remains `NOT_RUN`. Production readiness remains false. OOS has not been run and must not be claimed PASS.

## 9. Evidence-based next-step constraint

A next step should not be a same-shape C08 that only retunes C04/C05/C06/C07 thresholds. The available candidate-selection axes now tested in this family have not produced non-negative median, acceptable p25 downside, or monthly stability. The next strategy-quality work should start with deeper per-trade/ticker/month diagnostics or an explicitly new strategy family/exit model, while keeping all historical catalogs immutable and keeping OOS locked until a future catalog produces a valid frozen IS binding.
