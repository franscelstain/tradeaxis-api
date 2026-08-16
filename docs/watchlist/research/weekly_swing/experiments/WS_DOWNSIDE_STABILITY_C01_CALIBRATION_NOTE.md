# WS Downside/Stability C01 Calibration Reference Note

## Status

```text
LAST_UPDATED=2026-06-11
SESSION=WATCHLIST - WEEKLY SWING DOWNSIDE/STABILITY C01 SEED AND IS TWO-RUN VALIDATION SESSION
SCOPE=C01_IS_CALIBRATION_EXECUTION_INFRASTRUCTURE
DONE for downside/stability C01 calibration execution infrastructure
LOCAL_C01_IS_CALIBRATION_EXECUTED
C01_GRID_FAILED_IS_QUALITY
OOS_NOT_READ
NOT_PRODUCTION_READY
```

This note records the IS-only diagnostic, deterministic C01 catalog implementation, seed result, and two-run IS calibration result. It is not an OOS proof, not a promotion decision, and not production readiness evidence.

## Source Evidence Used

Repository evidence available in this workspace:

- `storage/app/watchlist/backtest/r2-is-run-1.json`
- `storage/app/watchlist/backtest/r2-is-run-2.json`
- `storage/app/watchlist/backtest/c01-is-run-1.json`
- `storage/app/watchlist/backtest/c01-is-run-2.json`
- `storage/app/watchlist/backtest/oos-is-evaluation-matrix-execution-corrected.csv`
- `docs/watchlist/research/weekly_swing/experiments/WS_R2_ENTRY_QUALITY_CALIBRATION_NOTE.md`
- `docs/watchlist/evidence/weekly_swing/locks/WS_OOS_EVIDENCE_NOTE.md`

Repository evidence requested by name but not present in this workspace:

- `storage/app/watchlist/backtest/r1-final-is-failed.json`
- `storage/app/watchlist/backtest/r1-final-is-evaluation-matrix.csv`

Because those two R1 filenames are absent, this note does not reconstruct them. R1 details below use only the available corrected IS matrix and tracker/reference notes.

## Preserved Historical Evidence

R1 remains immutable historical failed-IS evidence:

```text
catalog_code=WS_BT_GRID_BOOTSTRAP_2026_06
catalog_version=R1
catalog_count=24
catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
runtime_artifact_hash=f4ec8464f08515b31d7d26636851acea930307d6
valid_is_rows=0
failed_is_rows=24
oos_executed=false
```

R2 remains immutable historical failed-IS evidence:

```text
catalog_code=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
catalog_version=R2
catalog_count=12
catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
is_window=2023-01-02..2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
valid_is_rows=0
failed_is_rows=12
artifact_hash=8a8521fc9a3726d90f2b77506532a1e5392def8b
production_ready=false
```

`r2-is-run-1.json` and `r2-is-run-2.json` have identical file SHA1 in this workspace:

```text
124d41bfe9635de633d38dd959336b5a8d1b146f
```

The artifact-level canonical hash embedded in both R2 runs is:

```text
8a8521fc9a3726d90f2b77506532a1e5392def8b
```

## R2 Failure Diagnostic

R2 completed all 12 rows through canonical metric-gate evaluation. There is no evidence of a runtime/source failure in the R2 artifact:

```text
diagnostic_summary.count=0
all_rows_reached_canonical_gates=true
minimum_trade_count=true for all rows
minimum_coverage=true for all rows
strict_is_boundary_all_evaluations=true
max_requested_market_data_date=2025-05-21
oos_service_invoked=false
oos_repository_invoked=false
oos_table_unchanged=true
oos_executed=false
```

All 12 R2 rows failed the same three canonical failure classes:

```text
WS_BT_EVAL_ROBUST_RETURN_FAIL=12
WS_BT_EVAL_DOWNSIDE_FAIL=12
WS_BT_EVAL_STABILITY_FAIL=12
```

R2 metric distance from gates:

| Row | Avg | Median | P25 | Month win min | Month avg min | Picks | Days |
|---|---:|---:|---:|---:|---:|---:|---:|
| `04_LOW_VOLATILITY_QUALITY` | -0.001248 | -0.035518 | -0.068452 | 0.206897 | -0.027246 | 1418 | 507 |
| `05_RISK_WEIGHTED_ENTRY` | -0.002166 | -0.034870 | -0.067597 | 0.189655 | -0.031016 | 1421 | 508 |
| `10_DEFENSIVE_ENTRY` | -0.002647 | -0.030198 | -0.062986 | 0.175439 | -0.029694 | 1382 | 508 |
| `11_CONCENTRATED_ENTRY_QUALITY` | -0.005469 | -0.039377 | -0.069648 | 0.100000 | -0.053174 | 1066 | 505 |
| `09_HIGH_LIQ_BALANCED_STRICT` | -0.004841 | -0.040268 | -0.073097 | 0.050000 | -0.057808 | 1390 | 506 |
| `00_R1_BASELINE_CONTROL` | -0.002545 | -0.050716 | -0.082112 | 0.220339 | -0.031574 | 1415 | 507 |

Gate floors used by the artifact:

```text
min_trades=120
min_days_covered=390
avg_ret_net_top > 0
median_ret_net_top >= 0
min_p25_ret_net_top=-0.03
min_month_win_rate_min=0.45
min_month_avg_ret_net_min=-0.01
```

Confirmed diagnostic conclusions:

- Candidate count/coverage is not the primary failure; every row had more than 1000 picks except no R2 row fell below the `120` minimum, and every row exceeded the `390` effective coverage gate.
- Missing publication, calendar inconsistency, price-read boundary failure, direct raw market-data read, and OOS leakage are not supported by the R2 artifact.
- R2 strictness did not improve stability. The strict high-liquidity/high-quantile rows worsened month-win and month-average minima.
- R2 did not test the low/ultra-low ATR region seen in R1 rows with `max_atr14_pct <= 0.04`.
- Available R1 corrected IS matrix shows some ultra-low ATR rows got much closer to the downside floor, with param 24 passing downside only (`p25_ret=-0.024886`) but still failing robust return and stability. That supports a downside/stability design, not a best-of-failed choice.
- Failure is best classified as distribution quality failure across entry/ranking semantics: downside tail plus monthly consistency, with robust-return weakness still present.

## Failure Type Classification

| Failure class | Status | Evidence |
|---|---|---|
| Downside tail | Confirmed | R2 best p25 is `-0.062986`, far below `-0.03`; R1 low ATR evidence suggests ATR ceiling is a relevant axis. |
| Robust return | Confirmed | All R2 averages and medians are negative; only R1 param 9 had positive average but failed median/downside/stability. |
| Stability/monthly consistency | Confirmed | R2 best month-win minimum is `0.220339`, far below `0.45`; strict rows degrade to `0.05` or `0.10`. |
| Candidate count/coverage | Not primary | All R2 rows pass trade-count and coverage gates. |
| Over-filtering | Partial | R2 strict high-liquidity/high-quantile rows worsen monthly metrics; C01 avoids pure strictness. |
| Under-filtering | Partial | Broad rows keep coverage but have severe p25/median failures; C01 tests lower ATR and lower breakout weight. |
| Scoring/ranking drift | Plausible | Breakout-heavy and high-liquidity rows do not improve outcomes; C01 reduces breakout weight and raises risk/volume/momentum balance. |
| Setup filter drift | Plausible | R2 tighter breakout extension alone did not solve the tail; C01 keeps tight extension but pairs it with lower ATR and broader quantiles. |
| Price execution issue | Not supported | R2 artifact preserves corrected eval model and no runtime diagnostics. |

## C01 Design Preconditions

The following axes are eligible for the design because they are registered, `bt_target=true`, persisted/projected in the R2 schema, and consumed by runtime services:

- `risk.min_atr14_pct`, `risk.max_atr14_pct`, `risk.atr_ideal_low`, `risk.atr_ideal_high`
- `liquidity.min_dv20_idr`, `liquidity.dv20_strong_idr`
- `volume.min_vol_ratio`, `volume.strong_vol_ratio`
- `setup.roc_lo`, `setup.roc_hi`, `setup.mom_roc20_soft_min`
- `setup.bo_near_below_pct`, `setup.bo_max_ext_pct`
- `scoring.weights.value.momentum`, `scoring.weights.value.breakout`, `scoring.weights.value.volume`, `scoring.weights.value.risk`
- `grouping.top_min_score_q`, `grouping.secondary_min_score_q`

Frozen axes for this design:

```text
risk.stop_atr_mult=1.50
risk.min_rr=1.50
grouping.top_picks_target=5
grouping.secondary_target=10
ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS
```

No exit-axis grid, fee/slippage change, holding-horizon change, gap-fill change, price-band change, PLAN mutation, RECOMMENDATION mutation, CONFIRM mutation, promotion, or OOS read is part of this design.

## C01 Design Manifest

This is the code-owned C01 manifest that was seeded by the operator and executed twice on the exact IS window. It remains failed-IS quality evidence only; it is not an OOS proof, not a promotion, and not production readiness evidence.

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
catalog_version=C01
catalog_count=8
catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
search_mode=CURATED_DETERMINISTIC
random_or_bayesian=false
oos_used=false
implementation_status=IMPLEMENTED_UNIT_STATIC
seed_status=PASS
runtime_status=C01_GRID_FAILED_IS_QUALITY
artifact_hash=c8505ce5a9045629234a685984d9138b3990c775
```

Ordered row hashes:

```text
00_R2_DEFENSIVE_REFERENCE=ce502a65e3c488a3ad41e52ab21b22a1bc957cf0
01_LOW_ATR_BREADTH=1d9eb142fe35b9fea24e4a85a24cca92a4ed8f98
02_ULTRA_LOW_ATR_BREADTH=f7451083a2b63ab759901e162f017cba37083a1b
03_LOW_ATR_VOLUME_STABLE=fad7ad555836e46daf55df535ba0630248e3d8fa
04_RISK_FIRST_NOT_CHASING=af04460a896500b6c7c7a5bcb912fd4befc63b0e
05_STABILITY_BREADTH_MOMENTUM=3e389b35206c058ec93b796d0548f178f637ded9
06_HIGH_LIQ_LOW_ATR_MODERATE_Q=580e99963f76d42e7eae2e6a41cc114b816f4ffd
07_DOWNSIDE_CAP_BALANCED_FIXED_EXIT=d9e60e5cc2180498d9572f3ea851fe58a64c6d47
```

Ordered design parameter hashes:

```text
00_R2_DEFENSIVE_REFERENCE=11a897e0974ba9d5107362e2af1b44fd32cbdf3a
01_LOW_ATR_BREADTH=beeea7345dca233402bf8f8113b3d87fd4c69a48
02_ULTRA_LOW_ATR_BREADTH=8ab9abbeb1de22316028469aef216a43cda973a1
03_LOW_ATR_VOLUME_STABLE=c3b0a8d4d1849c511c470ffe209f7ffa446a8a30
04_RISK_FIRST_NOT_CHASING=bb89d9872ab7894d8be3fc82a749c4bbf2852924
05_STABILITY_BREADTH_MOMENTUM=b8819786508bffa340753a9fa0743e1948631c1d
06_HIGH_LIQ_LOW_ATR_MODERATE_Q=de207a53bf9b6dd0af51f4532c8a8228a3f743c9
07_DOWNSIDE_CAP_BALANCED_FIXED_EXIT=8d69a98132d8fa741999af5119a272015231717b
```

## C01 Curated Rows

| Row | ATR band | Liquidity | Volume | Setup | Weights M/B/V/R | Q top/sec | Rationale |
|---|---|---|---|---|---|---|---|
| `00_R2_DEFENSIVE_REFERENCE` | `0.015..0.065`, ideal `0.025..0.045` | `7.5b/15b` | `1.40/2.30` | ROC `0.020..0.090`, BO `0.010/0.025` | `0.25/0.30/0.15/0.30` | `0.95/0.82` | Reference copy of R2 defensive row 10 for drift measurement only. |
| `01_LOW_ATR_BREADTH` | `0.012..0.055`, ideal `0.022..0.040` | `2.5b/7.5b` | `1.30/2.10` | ROC `0.018..0.085`, BO `0.012/0.020` | `0.25/0.20/0.20/0.35` | `0.88/0.72` | Lower ATR ceiling with moderate quantiles to test downside without starving coverage. |
| `02_ULTRA_LOW_ATR_BREADTH` | `0.010..0.040`, ideal `0.018..0.032` | `1b/5b` | `1.25/2.00` | ROC `0.015..0.075`, BO `0.015/0.020` | `0.25/0.10/0.25/0.40` | `0.85/0.70` | Ultra-low ATR breadth probe derived from R1 downside-near-pass evidence. |
| `03_LOW_ATR_VOLUME_STABLE` | `0.012..0.050`, ideal `0.020..0.038` | `2.5b/7.5b` | `1.50/2.40` | ROC `0.015..0.080`, BO `0.012/0.018` | `0.25/0.10/0.30/0.35` | `0.90/0.75` | Low ATR plus stronger participation while keeping broad enough quantiles. |
| `04_RISK_FIRST_NOT_CHASING` | `0.015..0.055`, ideal `0.022..0.040` | `5b/10b` | `1.35/2.20` | ROC `0.015..0.075`, BO `0.010/0.015` | `0.25/0.10/0.20/0.45` | `0.90/0.76` | Risk-first ranking with a small breakout weight and tight extension cap. |
| `05_STABILITY_BREADTH_MOMENTUM` | `0.015..0.060`, ideal `0.025..0.045` | `2.5b/7.5b` | `1.25/2.10` | ROC `0.010..0.080`, BO `0.015/0.025` | `0.35/0.10/0.25/0.30` | `0.86/0.70` | Broader candidate coverage with momentum and risk emphasis for monthly stability. |
| `06_HIGH_LIQ_LOW_ATR_MODERATE_Q` | `0.012..0.050`, ideal `0.020..0.038` | `5b/12.5b` | `1.30/2.20` | ROC `0.015..0.080`, BO `0.010/0.020` | `0.25/0.15/0.25/0.35` | `0.90/0.74` | High liquidity and low ATR with moderate grouping cutoffs. |
| `07_DOWNSIDE_CAP_BALANCED_FIXED_EXIT` | `0.010..0.045`, ideal `0.018..0.035` | `2.5b/7.5b` | `1.40/2.20` | ROC `0.015..0.075`, BO `0.012/0.018` | `0.25/0.10/0.25/0.40` | `0.92/0.78` | Separates ATR downside effect from exit-axis drift under fixed stop/RR. |

## Design Rationale

C01 is deliberately not a full Cartesian search. It tests a small set of hypotheses supported by R1/R2 IS evidence:

1. Lower ATR ceilings may reduce downside tail, because available R1 rows with `max_atr14_pct <= 0.04` were closest to the p25 floor.
2. Pure strictness is not enough. R2 strict high-liquidity/high-quantile rows worsened stability, so C01 uses moderate quantiles and several breadth rows.
3. Breakout-heavy ranking did not repair return distribution in R2, so C01 lowers breakout weight and raises risk/volume/momentum balance.
4. The reference row is included only for drift measurement against R2 defensive behavior and must not be interpreted as best-of-failed selection.

## Implemented Unit/Static Scope

C01 implementation now adds:

- immutable catalog class `WatchlistBacktestC01ParamGridCatalog`;
- repository allowlist support for `WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06`;
- paramset factory projection for the C01 identity;
- idempotent seed command `watchlist:backtest-c01-param-grid-seed`;
- database seeder `WatchlistBacktestC01ParamGridSeeder`;
- IS-only artifact labels for C01 (`WATCHLIST_C01_IS_CALIBRATION_V1`, `WEEKLY_SWING_DOWNSIDE_STABILITY_C01_IS_ONLY`);
- static guards proving no `_R3_`, `_R4_`, `_R5_`, latest catalog fallback, random/Bayesian search, OOS call, OOS write, or execution semantics drift.

Executed IS commands:

```powershell
php artisan watchlist:backtest-is-calibrate `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage\app\watchlist\backtest\c01-is-run-1.json `
  --overwrite

php artisan watchlist:backtest-is-calibrate `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage\app\watchlist\backtest\c01-is-run-2.json `
  --overwrite
```

After those commands, C01 runtime status is:

```text
C01_GRID_FAILED_IS_QUALITY
LOCAL_C01_IS_CALIBRATION_EXECUTED
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF - no valid IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE - OOS proof missing
NOT_PRODUCTION_READY
```

## Unit/Static Validation

Validation executed in this implementation unit-static scope:

```text
php -l changed PHP files: PASS
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC01": PASS, 12 tests, 381 assertions, exit code 0
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestParamGrid": PASS, 4 tests, 636 assertions, exit code 0
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestR2": PASS, 26 tests, 530 assertions, exit code 0
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestIsCalibration": PASS, 3 tests, 26 assertions, exit code 0
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestMetricsServiceTest": PASS, 15 tests, 113 assertions, exit code 0
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestPublishedPrice": PASS, 18 tests, 177 assertions, exit code 0
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestOos": PASS, 24 tests, 228 assertions, exit code 0
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest": PASS, 130 tests, 2829 assertions, exit code 0
vendor\bin\phpunit tests\Unit\Watchlist: PASS, 222 tests, 3717 assertions, exit code 0
vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataPublishedEodSeries": PASS, 7 tests, 37 assertions, exit code 0
vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataTradingCalendar": PASS, 4 tests, 16 assertions, exit code 0
vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest": PASS, 3 tests, 41 assertions, exit code 0
```

No OOS proof, promotion, or production runtime command was executed. The C01 seed and C01 IS calibration commands were executed by the operator later and are recorded in the `C01 Seed And Runtime Evidence` section below.

## C01 Seed And Runtime Evidence

Seed command executed in this workspace:

```powershell
php artisan watchlist:backtest-c01-param-grid-seed
```

Seed result:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
catalog_version=C01
catalog_count=8
catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
inserted_count=8
updated_count=0
existing_count=0
r1_catalog_count=24
r1_catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
r2_catalog_count=12
r2_catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
r1_immutable=1
r2_immutable=1
oos_executed=0
production_ready=0
exit_code=0
```

C01 IS run commands executed in this workspace:

```powershell
php artisan watchlist:backtest-is-calibrate `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage\app\watchlist\backtest\c01-is-run-1.json `
  --overwrite

php artisan watchlist:backtest-is-calibrate `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage\app\watchlist\backtest\c01-is-run-2.json `
  --overwrite
```

Both commands returned exit code `1` with domain-valid failed-quality status:

```text
status=C01_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C01_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
catalog_version=C01
catalog_count=8
catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=8
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
artifact_hash=c8505ce5a9045629234a685984d9138b3990c775
production_ready=0
```

Two-run deterministic proof:

```text
file_sha1_run_1=04F6C664A0C9006C16542A8380034A0A633041DC
file_sha1_run_2=04F6C664A0C9006C16542A8380034A0A633041DC
catalog_hash_equal=true
is_date_hash_equal=true
artifact_hash_equal=true
best_binding_both_null=true
evaluations_equal=true
eval_ids_equal=true
oos_table_unchanged_both=true
max_requested_market_data_date_run_1=2025-05-21
max_requested_market_data_date_run_2=2025-05-21
```

C01 failure distribution:

```text
WS_BT_EVAL_DOWNSIDE_FAIL=8
WS_BT_EVAL_ROBUST_RETURN_FAIL=8
WS_BT_EVAL_STABILITY_FAIL=8
```

Closest observed C01 rows still fail canonical gates:

```text
best_avg_row=01_LOW_ATR_BREADTH avg=-0.0017265908028315999 median=-0.021046271587533357 p25=-0.054058108716307444 month_win_min=0.14035087719298245 month_avg_min=-0.03268473419969533
best_p25_row=02_ULTRA_LOW_ATR_BREADTH avg=-0.0042327217604437965 median=-0.01992152127980682 p25=-0.04417871616550758 month_win_min=0.22807017543859648 month_avg_min=-0.021245659534341328
best_stability_row=02_ULTRA_LOW_ATR_BREADTH month_win_min=0.22807017543859648 month_avg_min=-0.021245659534341328
```

## Checklist Status

| Item | Status | Evidence |
|---|---:|---|
| R1 immutable | PASS | Seed output and artifact validation show count `24`, hash `9da8b0983c57bde1ce0a1fbf1c119756f8af431c`, `r1_immutable=true`. |
| R2 immutable | PASS | Seed output and artifact validation show count `12`, hash `0f2eaadaa446980a3d5e48cd498df2a8157c01a5`, `r2_immutable=true`. |
| C01 semantic naming | PASS | Uses semantic C-campaign identity and no forbidden R-series catalog code. |
| C01 seed | PASS | `status=PASS`, `inserted_count=8`, exit code `0`. |
| C01 two-run IS execution | PASS infrastructure / FAIL quality | Two artifacts match exactly, but `is_valid_param_count=0`. |
| C01 best IS binding | NOT_CREATED | `param_id_best_is` empty and `best_binding_both_null=true`. |
| C01 quality gates | FAIL | All 8 rows fail downside, robust-return, and stability. |
| OOS read/execution | PASS no-OOS | OOS service/repository `0`, OOS table unchanged, `oos_executed=0`. |
| Promotion | NOT_ELIGIBLE | OOS proof missing and no valid C01 IS parameter. |
| Next catalog | NOT_STARTED | Must be a separate future session; no new catalog is created in this session. |

## Final Boundary

```text
R1 infrastructure/runtime: PASS
R1 strategy/catalog quality: FAIL
R2 infrastructure/runtime: PASS
R2 strategy/catalog quality: FAIL
R2 is not an OOS acceptance failure
OOS was not read for C01 implementation
C01 IS runtime was executed twice and failed quality with no valid IS parameter
No best-of-failed binding exists
No acceptance gate was lowered
No execution price semantics changed
No PLAN/RECOMMENDATION/CONFIRM semantics changed
No promotion or production-ready claim is made
```

## Follow-up Failure Diagnostic

A dedicated C01 failure diagnostic note was added after the two-run failed-IS result:

```text
docs/watchlist/findings/weekly_swing/records/WS_C01_FAILURE_DIAGNOSTIC_NOTE.md
DONE for C01 failure diagnostic scope
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_READ
NOT_PRODUCTION_READY
```

The follow-up diagnostic confirms that C01 did not fail because of low coverage or low trade count. It failed because all rows still have negative robust-return metrics, p25 downside below the floor, and monthly stability far below the required minimum. No C02 or new-focus catalog is designed from the current artifact alone.
