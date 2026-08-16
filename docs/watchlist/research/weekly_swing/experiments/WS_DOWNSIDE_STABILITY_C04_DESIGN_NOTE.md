# WS Downside Stability C04 Design Note

Status: IMPLEMENTED / IS_QUALITY_FAILED
Catalog code: `WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06`
Catalog version: `C04`
Catalog count: `10`
Catalog hash: `0ce3a313c45432c5a4d607def12b3f774988f324`
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-11

## 1. Purpose

C04 was created because C02 and C03 were deterministic engineering successes but strategy-quality failures. C04 is a new catalog identity. It is not a mutation of C03 and does not loosen canonical IS gates.

The design target was to test whether weak trades could be reduced before evaluation by using candidate-selection fields already available in the runtime.

## 2. Evidence used

C02 evidence:

- coverage and trade count were sufficient;
- every row failed robust return, downside, and stability;
- median return was negative;
- p25 downside was worse than `-3%`;
- monthly stability was far below target.

C03 evidence:

- count `10`, hash `29e15ceab1b3f7dc31a21f339ac6ab7483e14800`;
- all rows failed robust return, downside, and stability;
- per-row picks were `1104..1432`;
- median return was `-1.7639%..-1.0634%`;
- p25 downside was `-5.4058%..-3.7924%`;
- month win-rate minimum was `10.00%..20.69%`;
- C03 had no best IS binding and did not unlock OOS.

C01 drilldown evidence:

- moderate ROC, volume, and liquidity buckets were relatively better than broad high-momentum chasing;
- high momentum alone was not sufficient;
- breakout, volume, and risk score components were more useful as quality controls than as a pure sample-reduction tool.

## 3. Runtime-supported axes

C04 uses only fields already produced or consumed by the watchlist runtime:

- hard universe/filter fields: `min_dv20_idr`, `min_atr14_pct`, `max_atr14_pct`, `min_vol_ratio`;
- scoring setup fields: `roc20`, `close_to_hh20_pct`, `bo_near_below_pct`, `bo_max_ext_pct`;
- scoring component fields: `score_momentum`, `score_breakout`, `score_volume`, `score_risk`;
- trend/strength fields available in scoring factor breakdown: `ma20_slope_pct`, `rs_20_vs_ihsg`, `close_vs_ma20_pct`, `close_vs_ma50_pct`;
- grouping fields: `top_min_score_q`, `secondary_min_score_q`.

No sector filter is used. Sector evidence remains diagnostic-only.

## 4. Candidate-selection extension

C04 introduces a C04-only extension under:

```text
bt_grid_resolution.candidate_selection_extension
```

Mode:

```text
C04_BALANCED_COMPONENT_AND_TREND_FLOOR
```

The extension is embedded into the paramset snapshot and paramset hash. It is consumed by `WatchlistPlanGroupingService` before TOP/SECONDARY grouping. Candidates that fail the extension are moved to `excluded` with:

```text
WATCHLIST_C04_ENTRY_QUALITY_FLOOR_FAIL
```

The extension adds these pre-selection checks:

```text
score_momentum >= 0.35
score_breakout >= 0.80
score_volume >= 0.40
score_risk >= 0.70
ma20_slope_pct >= -0.005
rs_20_vs_ihsg >= -0.020
close_vs_ma20_pct >= -0.030
close_vs_ma50_pct >= -0.050
roc20 within the catalog row roc_lo..roc_hi
close_to_hh20_pct within -bo_near_below_pct..bo_max_ext_pct
```

This is candidate selection, not canonical gate relaxation.

## 5. Catalog row intent

C04 rows cover:

- C03 low-ATR stability reference row;
- balanced component floor core row;
- breakout-volume-risk core row;
- moderate liquidity and moderate ROC bucket row;
- prior-strength row without chase entry;
- low-ATR downside-control row;
- monthly-stability/liquidity row;
- anti-reversal-trap confirmation row;
- broader sample row with quality floor;
- strict balanced final probe.

All rows keep fixed execution semantics:

```text
ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS
risk.stop_atr_mult=1.50
risk.min_rr=1.50
grouping.top_picks_target=5
grouping.secondary_target=10
```

## 6. Validation outcome

C04 implementation validation:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC04"
OK (14 tests, 499 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (264 tests, 5142 assertions)
```

C04 seed:

```text
status=PASS
catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
oos_executed=0
production_ready=0
```

C04 IS calibration run 1 and run 2:

```text
status=C04_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C04_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=10
artifact_hash=fe964ee879dddc8aa8a83372e8c2d05aed5e8259
oos_executed=0
production_ready=0
```

## 7. Forensic interpretation

C04 reduced picks materially and improved some average/p25 metrics compared with C03, but it did not produce a valid IS candidate.

Observed C04 ranges:

```text
picks_count=82..176
avg_ret_net_top=-0.3039%..0.4019%
median_ret_net_top=-1.2712%..-0.0501%
p25_ret_net_top=-3.8881%..-3.0868%
month_win_rate_min=0.00%..0.00%
```

Failure distribution:

```text
WS_BT_EVAL_DOWNSIDE_FAIL=10
WS_BT_EVAL_MIN_TRADES_FAIL=7
WS_BT_EVAL_ROBUST_RETURN_FAIL=10
WS_BT_EVAL_STABILITY_FAIL=10
```

The C04 candidate floor appears too brittle: it improved some trade-quality metrics but reduced sample size too far and did not fix monthly stability.

## 8. Final decision

```text
C04_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C04 is not eligible for OOS because:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
```

OOS remains `NOT_RUN`. Production readiness remains false.
