# WS Downside Stability C14 Design Note

Status: C14_IMPLEMENTED_SEEDED_DETERMINISTIC / IS_QUALITY_FAILED / OOS_NOT_RUN / NOT_PRODUCTION_READY

## Purpose

C14 is the first catalog created after C13 exit-axis support. It tests whether the C13-authorized variable risk-exit axes can improve locked IS quality while preserving all historical catalogs as immutable evidence.

## Catalog Identity

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06
catalog_version=C14
catalog_count=12
catalog_hash=079430de7c94fd0226d0f3b47d5eb1e9f906fd6a
```

Historical identities R1/R2/C01/C02/C03/C04/C05/C06/C07 remain unchanged. C14 does not patch or reinterpret any failed historical catalog.

## Evidence Basis

C14 follows the C10/C11/C12/C13 evidence chain:

```text
C10: target-hit share was weak and stop-or-timeout dominated.
C11: exit-model catalog was not authorized until the contract boundary was explicit.
C12: first-phase future axes were limited to risk.min_rr and risk.stop_atr_mult.
C13: support for VARIABLE_RISK_EXIT_AXIS_V1 was implemented and audited.
```

C14 allowed axes:

```text
risk.stop_atr_mult=0.80..1.70
risk.min_rr=0.75..1.20
```

C14 blocked axes:

```text
backtest.holding_days
backtest.target_pct
backtest.stop_pct
sector whitelist/filter
```

C14 reuses the C07 candidate-selection confirmation layer. During validation, the runtime enrichment scope was fixed so the same C07 optional metrics are available when the C07 extension is used by C14. This is support plumbing, not gate relaxation.

## IS Result

C14 seed:

```text
status=PASS
catalog_count=12
catalog_hash=079430de7c94fd0226d0f3b47d5eb1e9f906fd6a
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
c04_immutable=1
c05_immutable=1
c06_immutable=1
c07_immutable=1
oos_executed=0
production_ready=0
```

C14 IS-only calibration run 1 and run 2:

```text
status=C14_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C14_NO_VALID_IS_CANDIDATE
catalog_count=12
catalog_hash=079430de7c94fd0226d0f3b47d5eb1e9f906fd6a
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=12
artifact_hash=70d021daafc254fb2ed826ff05015d42bac5dd8d
param_id_best_is=
best_is_binding_hash=
oos_executed=0
production_ready=0
```

## Forensic Summary

```text
minimum_trade_count_passed=12/12
minimum_coverage_passed=12/12
median_return_non_negative_passed=0/12
p25_downside_bound_passed=5/12
monthly_win_rate_floor_passed=0/12
monthly_average_floor_passed=0/12
```

Metric ranges:

```text
picks_count=729..1359
avg_ret_net_top=-0.5216%..-0.3528%
median_ret_net_top=-1.5648%..-0.4848%
p25_ret_net_top=-3.5375%..-2.6583%
month_win_rate_min=14.81%..30.77%
month_avg_ret_net_min=-2.5674%..-1.4889%
```

Failure distribution:

```text
WS_BT_EVAL_ROBUST_RETURN_FAIL=12
WS_BT_EVAL_STABILITY_FAIL=12
WS_BT_EVAL_DOWNSIDE_FAIL=7
```

C14 improved p25 downside for some rows, but robust return and monthly stability still failed every row. The failure is strategy quality, not coverage or trade-count sufficiency.

## Decision

```text
C14_REJECTED_AS_STRATEGY_QUALITY_CATALOG
OOS_NOT_ELIGIBLE
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

OOS cannot be considered for C14 because `is_valid_param_count=0`, `param_id_best_is` is empty, and `best_is_binding_hash` is empty.

## Next Step

The next catalog session should not select the best failed C14 row and should not continue only adjusting `risk.stop_atr_mult` or `risk.min_rr`. Evidence points to a need for candidate-selection or strategy-family redesign that improves median return and monthly stability before OOS can be discussed.
