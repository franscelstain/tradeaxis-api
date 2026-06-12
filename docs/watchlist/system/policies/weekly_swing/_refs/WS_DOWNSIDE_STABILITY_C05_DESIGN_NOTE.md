# WS Downside Stability C05 Design Note

Status: IMPLEMENTED / IS_QUALITY_FAILED
Catalog code: `WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06`
Catalog version: `C05`
Catalog count: `12`
Catalog hash: `476af5dde18079b1270556bc44bbc632edd46e27`
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-11

## 1. Purpose

C05 was created because C04 improved some average/p25 metrics but over-filtered candidates and failed strategy quality. C05 is a new catalog identity. It is not a mutation of C04 and does not loosen canonical IS gates.

## 2. Design change from C04

C04 used an all-component hard floor:

```text
C04_BALANCED_COMPONENT_AND_TREND_FLOOR
```

C05 uses a softer sample-aware extension:

```text
C05_SOFT_BALANCED_SAMPLE_STABILITY_FLOOR
```

The C05 extension requires:

```text
score_component_required_pass_count=3
score_component_average_min=0.58
trend_metric_required_pass_count=3
ROC and close-to-HH20 tolerance around catalog bands
```

This preserves candidate quality filtering but allows one noisy component/trend metric, directly addressing C04's minimum-trade failure.

## 3. Runtime-supported axes

C05 uses only fields already produced or consumed by the runtime:

- `score_momentum`, `score_breakout`, `score_volume`, `score_risk`;
- `ma20_slope_pct`, `rs_20_vs_ihsg`, `close_vs_ma20_pct`, `close_vs_ma50_pct`;
- `roc20`;
- `close_to_hh20_pct`;
- existing liquidity, volume, ATR, score-weight, and grouping-quantile fields.

No sector filter is used.

## 4. Validation outcome

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC05"
OK (13 tests, 523 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (277 tests, 5665 assertions)
```

C05 seed:

```text
status=PASS
catalog_hash=476af5dde18079b1270556bc44bbc632edd46e27
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
c04_immutable=1
oos_executed=0
production_ready=0
```

C05 IS calibration:

```text
status=C05_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C05_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
artifact_hash=f8288cb2d395e397f433dae854c0ad80b4650a8d
oos_executed=0
production_ready=0
```

## 5. Forensic interpretation

C05 restored sample size:

```text
picks_count=370..886
```

But C05 still failed all canonical quality families:

```text
WS_BT_EVAL_DOWNSIDE_FAIL=12
WS_BT_EVAL_ROBUST_RETURN_FAIL=12
WS_BT_EVAL_STABILITY_FAIL=12
```

Metric ranges:

```text
median_ret_net_top=-1.6122%..-0.7301%
p25_ret_net_top=-4.0209%..-3.2708%
month_win_rate_min=0.00%..18.75%
```

C05 confirms that sample-aware soft filtering alone is not enough. The remaining issue is not minimum sample size; it is still return direction, downside, and month-to-month stability.

## 6. Final decision

```text
C05_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C05 is not eligible for OOS because:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
```

OOS remains `NOT_RUN`. Production readiness remains false.
