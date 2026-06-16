# WS Downside Stability C17 Design Note

## Purpose

C17 is a quality-preserving sample-recovery catalog following the final C16 result. C16 is immutable and rejected as a strategy catalog because all 12 rows failed IS quality, primarily from minimum-trade and monthly-stability gates.

## Immutable identity

```text
CATALOG_CODE=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06
CATALOG_VERSION=C17
CATALOG_COUNT=12
CATALOG_HASH=d411bfbee6fb14c17d821aa92e7e0fea06925d67
RUNTIME_MODE=C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16
```

## Design principles

- Recover sample without lowering canonical IS gates.
- Use C16 evidence as diagnostic direction only.
- Keep C16, C15, C14, C01-C07, R1, and R2 immutable.
- Keep OOS forbidden until a valid IS candidate exists.
- Keep `production_ready=0`.
- Keep Watchlist as PLAN/RECOMMENDATION/CONFIRM only, not an execution/order/broker system.

## C17 entry-quality recovery

C17 introduces row-specific score windows that are consumed by runtime guard:

```text
0.65..0.80
0.68..0.82
0.70..0.85
```

C17 blocks score chase:

```text
0.90..1.00 is not allowed as sample recovery.
```

C17 avoids a free `0.80..0.90` bucket. Rows that reach above `0.80` must stay segmented by low ATR, negative ROC20, DV20 bounds, and volume bounds.

## Runtime guard

Runtime mode:

```text
C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16
```

Required runtime consumption:

```text
CandidateUniverseService must expose needed runtime metrics.
ScoringService must preserve score metrics and components.
PlanGroupingService must enforce C17 score windows and recovery guards.
```

## Forbidden behavior

```text
BEST_OF_FAILED_BINDING=false
TICKER_BLACKLIST=false
MONTH_BLACKLIST=false
SECTOR_WHITELIST=false
OOS_EXECUTION=false
PARAMSET_PROMOTION=false
PRODUCTION_READY=false
CANONICAL_GATE_LOWERING=false
PLAN_CONFIRM_BOUNDARY_CHANGE=false
```

## Status

```text
C17_IMPLEMENTED_SOURCE_LEVEL=true
C17_RUNTIME_VALIDATED=true
C17_SEED_PASS=true
C17_DIAGNOSE_BATCH_PASS=true
C17_IS_CALIBRATION_DETERMINISTIC=true
C17_GRID_FAILED_IS_QUALITY=true
C17_REJECTED_AS_STRATEGY_CATALOG=true
reason_code=WS_BT_C17_NO_VALID_IS_CANDIDATE
artifact_hash=23c30d70aeefa88701de8d9a59dd9217ee340ae6
OOS_NOT_RUN=true
production_ready=0
```

## Final C17 result

C17 is a valid engineering/runtime iteration but not a valid strategy catalog. It preserved downside quality better than C16, but failed sample and stability: all 12 rows failed minimum trade count and monthly stability, while 5 rows also failed robust return. No OOS proof or production promotion is allowed.

Next policy work is now active as C18 diagnostic-first Fase A. C18 must first diagnose funnel and monthly coverage root cause from C17 failure evidence. A new immutable C18 catalog may only be designed after diagnostic evidence supports controlled sample recovery without lowering canonical gates or using best-of-failed binding, ticker/month blacklist, or sector whitelist.
