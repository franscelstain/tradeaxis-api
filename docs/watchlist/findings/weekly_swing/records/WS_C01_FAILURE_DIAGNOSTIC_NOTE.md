# WS C01 Failure Diagnostic Note

## Status

```text
LAST_UPDATED=2026-06-11
SESSION=WATCHLIST - WEEKLY SWING C01 FAILURE DIAGNOSTIC AND NEXT SEMANTIC CATALOG DESIGN SESSION
SCOPE=C01_FAILURE_DIAGNOSTIC_ONLY
DONE for C01 failure diagnostic scope
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_READ
NOT_PRODUCTION_READY
```

This note is a deterministic diagnostic over the existing IS-only C01 artifacts. It does not create a new catalog, does not run OOS, does not select best-of-failed, and does not promote any paramset.

## Source Evidence Used

Available in the current ZIP/workspace:

- `storage/app/watchlist/backtest/c01-is-run-1.json`
- `storage/app/watchlist/backtest/c01-is-run-2.json`
- `storage/app/watchlist/backtest/r2-is-run-1.json`
- `storage/app/watchlist/backtest/r2-is-run-2.json`
- `docs/watchlist/research/weekly_swing/experiments/WS_DOWNSIDE_STABILITY_C01_CALIBRATION_NOTE.md`
- `docs/watchlist/research/weekly_swing/experiments/WS_R2_ENTRY_QUALITY_CALIBRATION_NOTE.md`

Requested R1 filenames remain absent in this workspace and are not reconstructed:

- `storage/app/watchlist/backtest/r1-final-is-failed.json`
- `storage/app/watchlist/backtest/r1-final-is-evaluation-matrix.csv`

## Preserved Evidence

```text
R1 catalog_code=WS_BT_GRID_BOOTSTRAP_2026_06
R1 catalog_version=R1
R1 catalog_count=24
R1 catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c

R2 catalog_code=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
R2 catalog_version=R2
R2 catalog_count=12
R2 catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
R2 artifact_hash=8a8521fc9a3726d90f2b77506532a1e5392def8b
R2 file_sha1_run_1=124d41bfe9635de633d38dd959336b5a8d1b146f
R2 file_sha1_run_2=124d41bfe9635de633d38dd959336b5a8d1b146f

C01 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
C01 catalog_version=C01
C01 catalog_count=8
C01 catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
C01 artifact_hash_run_1=c8505ce5a9045629234a685984d9138b3990c775
C01 artifact_hash_run_2=c8505ce5a9045629234a685984d9138b3990c775
C01 file_sha1_run_1=04F6C664A0C9006C16542A8380034A0A633041DC
C01 file_sha1_run_2=04F6C664A0C9006C16542A8380034A0A633041DC
C01 best_is_binding=null
```

Two-run proof from the artifacts:

```text
catalog_hash_equal=true
is_date_hash_equal=true
all_evaluation_metric_equality=true
best_binding_equality=true
file_sha1_equality=true
max_requested_market_data_date_run_1=2025-05-21
max_requested_market_data_date_run_2=2025-05-21
oos_service_invoked_run_1=false
oos_repository_invoked_run_1=false
oos_table_unchanged_run_1=true
production_ready=false
```

## C01 Gate Floors

```text
min_trades=120
min_days_covered=390
avg_ret_net_top > 0
median_ret_net_top >= 0
min_p25_ret_net_top=-0.03
min_month_win_rate_min=0.45
min_month_avg_ret_net_min=-0.01
```

## Per Param Status / Failure Codes / Key Metrics

| Row | Param | Status | Failure codes | Picks | Days | Win | Loss | Avg | Median | P25 | Month win min | Month avg min | Min | Fail gates | Nearest failed gap | Worst gap |
|---|---:|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---|---|---|
| `00_R2_DEFENSIVE_REFERENCE` | 37 | `GATES_FAILED` | `WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_STABILITY_FAIL` | 1382 | 508 | 0.3907 | 0.6093 | -0.0026 | -0.0302 | -0.0630 | 0.1754 | -0.0297 | -0.1612 | `average_return_positive,median_return_non_negative,monthly_average_floor,monthly_win_rate_floor,p25_downside_bound` | `average_return_positive` -0.0026 | `monthly_win_rate_floor` -0.2746 |
| `01_LOW_ATR_BREADTH` | 38 | `GATES_FAILED` | `WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_STABILITY_FAIL` | 1435 | 508 | 0.3951 | 0.6049 | -0.0017 | -0.0210 | -0.0541 | 0.1404 | -0.0327 | -0.1321 | `average_return_positive,median_return_non_negative,monthly_average_floor,monthly_win_rate_floor,p25_downside_bound` | `average_return_positive` -0.0017 | `monthly_win_rate_floor` -0.3096 |
| `02_ULTRA_LOW_ATR_BREADTH` | 39 | `GATES_FAILED` | `WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_STABILITY_FAIL` | 1429 | 508 | 0.3765 | 0.6235 | -0.0042 | -0.0199 | -0.0442 | 0.2281 | -0.0212 | -0.1620 | `average_return_positive,median_return_non_negative,monthly_average_floor,monthly_win_rate_floor,p25_downside_bound` | `average_return_positive` -0.0042 | `monthly_win_rate_floor` -0.2219 |
| `03_LOW_ATR_VOLUME_STABLE` | 40 | `GATES_FAILED` | `WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_STABILITY_FAIL` | 1408 | 508 | 0.3864 | 0.6136 | -0.0038 | -0.0216 | -0.0515 | 0.1724 | -0.0260 | -0.1377 | `average_return_positive,median_return_non_negative,monthly_average_floor,monthly_win_rate_floor,p25_downside_bound` | `average_return_positive` -0.0038 | `monthly_win_rate_floor` -0.2776 |
| `04_RISK_FIRST_NOT_CHASING` | 41 | `GATES_FAILED` | `WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_STABILITY_FAIL` | 1419 | 508 | 0.3897 | 0.6103 | -0.0033 | -0.0240 | -0.0530 | 0.1724 | -0.0274 | -0.1321 | `average_return_positive,median_return_non_negative,monthly_average_floor,monthly_win_rate_floor,p25_downside_bound` | `average_return_positive` -0.0033 | `monthly_win_rate_floor` -0.2776 |
| `05_STABILITY_BREADTH_MOMENTUM` | 42 | `GATES_FAILED` | `WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_STABILITY_FAIL` | 1422 | 508 | 0.3889 | 0.6111 | -0.0038 | -0.0347 | -0.0611 | 0.2041 | -0.0206 | -0.1306 | `average_return_positive,median_return_non_negative,monthly_average_floor,monthly_win_rate_floor,p25_downside_bound` | `average_return_positive` -0.0038 | `monthly_win_rate_floor` -0.2459 |
| `06_HIGH_LIQ_LOW_ATR_MODERATE_Q` | 43 | `GATES_FAILED` | `WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_STABILITY_FAIL` | 1419 | 508 | 0.4024 | 0.5976 | -0.0018 | -0.0205 | -0.0508 | 0.1754 | -0.0273 | -0.1321 | `average_return_positive,median_return_non_negative,monthly_average_floor,monthly_win_rate_floor,p25_downside_bound` | `average_return_positive` -0.0018 | `monthly_win_rate_floor` -0.2746 |
| `07_DOWNSIDE_CAP_BALANCED_FIXED_EXIT` | 44 | `GATES_FAILED` | `WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_STABILITY_FAIL` | 1421 | 508 | 0.3892 | 0.6108 | -0.0035 | -0.0215 | -0.0465 | 0.2034 | -0.0237 | -0.1482 | `average_return_positive,median_return_non_negative,monthly_average_floor,monthly_win_rate_floor,p25_downside_bound` | `average_return_positive` -0.0035 | `monthly_win_rate_floor` -0.2466 |

## Aggregate Metric Summary

| Metric | Min | Max | Avg |
|---|---:|---:|---:|
| `picks_count` | 1382.000000 | 1435.000000 | 1416.875000 |
| `days_covered` | 508.000000 | 508.000000 | 508.000000 |
| `win_rate_top` | 0.376487 | 0.402396 | 0.389859 |
| `loss_rate_top` | 0.597604 | 0.623513 | 0.610141 |
| `avg_ret_net_top` | -0.004233 | -0.001727 | -0.003112 |
| `median_ret_net_top` | -0.034729 | -0.019922 | -0.024191 |
| `p25_ret_net_top` | -0.062986 | -0.044179 | -0.053005 |
| `month_win_rate_min` | 0.140351 | 0.228070 | 0.183950 |
| `month_avg_ret_net_min` | -0.032685 | -0.020636 | -0.026083 |
| `min_ret_net_top` | -0.162001 | -0.130550 | -0.141974 |
| `period_fail_count` | 19.000000 | 22.000000 | 20.125000 |

## Failure Distribution

| Gate / reason | Count |
|---|---:|
| `average_return_positive` | 8 |
| `median_return_non_negative` | 8 |
| `monthly_average_floor` | 8 |
| `monthly_win_rate_floor` | 8 |
| `p25_downside_bound` | 8 |
| `WS_BT_EVAL_DOWNSIDE_FAIL` | 8 |
| `WS_BT_EVAL_ROBUST_RETURN_FAIL` | 8 |
| `WS_BT_EVAL_STABILITY_FAIL` | 8 |

## Required Diagnostic Answers

| Question | Finding | Evidence / limitation |
|---|---|---|
| 1. Downside tail too bad? | `YES` | All 8 rows fail `p25_downside_bound`; best p25 is `-0.044179`, still below floor `-0.03`. |
| 2. Robust return not enough? | `YES` | All 8 rows fail positive average and non-negative median; best average is `-0.001727` and best median remains negative. |
| 3. Monthly stability bad? | `YES` | All 8 rows fail monthly win-rate and monthly average floors; best month-win minimum is `0.228070` versus required `0.45`. |
| 4. Too loose / still many bad trades? | `YES` | Picks remain high (`1382..1435`) while loss rate stays `0.597604..0.623513`; broad selection is not filtering bad trades enough. |
| 5. Too strict / coverage too low? | `NO` | Every row has `508` covered days and `>1380` picks; coverage and trade-count gates are not the failure. |
| 6. Scoring/ranking cannot distinguish good/bad? | `SUPPORTED` | Different weight/ATR/liquidity rows converge to similar negative distribution; best row only misses average by `-0.001727` but still has poor median and stability. |
| 7. Setup filter fails to separate fake momentum? | `SUPPORTED, NOT FULLY PROVEN` | ROC/breakout/volume variants do not materially improve median, p25, or month-win floor. Artifact lacks trade-level setup diagnostics. |
| 8. Breakout/extension too aggressive/permissive? | `SUPPORTED, NOT FULLY PROVEN` | Tight and moderate extension variants both fail; artifact lacks per-entry extension bucket analysis. |
| 9. Liquidity/volume not helping downside? | `YES` | Higher-liquidity/volume-stable rows still fail all quality gates. |
| 10. ATR band still admits volatile names? | `PARTIAL` | Lower ATR improves p25 versus reference but not enough; ultra-low ATR best p25 `-0.044179` still fails. |
| 11. One month/date/ticker cluster? | `NOT_DETERMINED` | Artifact exposes aggregate period minima and fail counts but not month/ticker/trade rows. |
| 12. Data quality/publication/calendar issue? | `NOT_SUPPORTED` | Artifact has `diagnostic_summary.count=0`, official publication read surface, and strict IS boundary. |
| 13. Duplicate eval / identity conflict / overwrite? | `NOT_SUPPORTED` | Two runs have identical artifact hash, SHA1, date hash, eval metrics, eval IDs, and null binding. |
| 14. Runtime used explicit IS and did not touch OOS? | `YES` | `max_requested_market_data_date=2025-05-21`, OOS service/repository false, OOS table unchanged. |
| 15. Dead parameter / silent default? | `NOT_SUPPORTED BY ARTIFACT` | Catalog rows are persisted/projected and row-level metrics differ, but artifact cannot prove every axis has material independent effect. Static guards cover runtime consumption at code level. |

## Diagnostic Reason Summary

C01 failed because entry selection quality remains weak even after downside/stability-focused parameter changes. The failure is not infrastructure, not coverage starvation, not an OOS issue, and not a deterministic persistence issue. The dominant signal is broad high-coverage selection with persistent negative median/average, poor p25 downside, and very weak minimum monthly win-rate.

The artifact supports a stronger hypothesis than simply "try stricter downside/stability again": the next useful work should first instrument IS-only trade/month/setup diagnostics to identify whether the failure root is `SCORE_RANKING`, `SETUP_FILTER`, or a more specific trade-frequency/candidate-quality issue. A new C02 downside/stability catalog is not justified from the current artifact alone.

## Next Catalog Decision

```text
NEXT_CATALOG_DECISION=A
DONE for C01 failure diagnostic scope
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_READ
NOT_PRODUCTION_READY
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF — no valid IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE — OOS proof missing
```

Reason:

- No C01 row passed IS gates.
- No best IS binding exists.
- OOS remains unread and not eligible.
- Artifact does not contain month/ticker/trade/setup buckets required to choose a new semantic focus safely.
- Repeating downside/stability as `C02` would be speculative without deeper IS-only diagnostics.

## Required Next Work

```text
WATCHLIST — WEEKLY SWING C01 IS FAILURE DRILLDOWN DIAGNOSTIC SESSION
```

Minimum scope for that next session:

- stay IS-only: `2023-01-02..2025-05-21`;
- do not run or read OOS;
- produce trade-level/month-level/ticker-level/setup-bucket diagnostics for C01 failed rows;
- determine whether the next catalog focus should be `SCORE_RANKING`, `SETUP_FILTER`, `TRADE_FREQUENCY`, `MARKET_REGIME`, or a justified `DOWNSIDE_STABILITY_C02`;
- do not create a new catalog until the drilldown supports the focus.

## Validation Performed In This Diagnostic Update

```text
JSON parse c01-is-run-1.json=PASS
JSON parse c01-is-run-2.json=PASS
C01 file SHA1 equality=PASS
C01 all_evaluations equality=PASS
C01 best_binding null equality=PASS
OOS service/repository invoked=false
OOS table unchanged=true
```

Local Artisan/PHPUnit runtime validation for this update is blocked in this container because the repository guard rejects PHP `8.4.16` for Lumen `8.3.4` clean output. No local Artisan/PHPUnit PASS is claimed by this diagnostic update.

## Supported Operator PHPUnit Validation After Diagnostic Update

The following validation was provided by the operator from the supported project environment after this diagnostic docs update:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC01"
PASS: 12 tests, 381 assertions, exit code 0

vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"
PASS: 130 tests, 2829 assertions, exit code 0

vendor\bin\phpunit tests\Unit\Watchlist
PASS: 222 tests, 3717 assertions, exit code 0
```

Interpretation:

- current Watchlist unit/static regression scope remains green in the operator environment;
- this is not an Artisan, seed, migration, database, calibration, replay, OOS, or production runtime proof;
- this does not change C01 status from `C01_GRID_FAILED_IS_QUALITY`;
- this does not create a valid IS parameter or best IS binding;
- this does not make OOS proof or promotion eligible.
