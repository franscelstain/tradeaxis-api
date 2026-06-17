# Weekly Swing C20 Regime Trade-Date Quality Gate Design Note

## Scope

C20 adds an IS-only diagnostic gate that decides whether a trade_date is eligible to emit recommendation candidates. It does not create production behavior.

Canonical price evaluation remains unchanged:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

## Rationale

C19 proved that selector recovery can restore sample, but quality collapses when sample is forced back to 120+ evaluated picks. C20 therefore tests a different hypothesis: poor trade dates/regimes may be the source of bad return quality.

## Gate principle

The C20 gate may block a date before price evaluation if same-date EOD regime evidence is weak.

Allowed input:

```text
IHSG benchmark context as of trade_date EOD
same-date candidate breadth/count
same-date candidate aggregate ROC20, ATR14, volume ratio
same-date aggregate quality score
same-date aggregate sector momentum/relative strength, when available
```

Forbidden input:

```text
future return
future exit reason
future high/low
future price path
month/ticker blacklist
sector whitelist
```

## Profiles

```text
C20_G00_BASELINE_NO_DATE_GATE
C20_G01_MARKET_MOMENTUM_SAFE
C20_G02_BREADTH_HEALTHY
C20_G03_VOLATILITY_RISK_OFF_FILTER
C20_G04_SECTOR_CONFIRMATION
C20_G05_COMBINED_REGIME_QUALITY
C20_G06_NO_PICK_DAY_ALLOWED_QUALITY_FIRST
```

`C20_G04` uses aggregate sector metrics only. It must not allow/disallow a date because a specific sector code is on a whitelist.

## No-pick contract

C20 explicitly allows:

```text
no-pick day
no-pick week
no-pick month
```

This is intentional. C20 tests whether not trading bad dates improves the strategy. It must not backfill weak dates merely to satisfy monthly coverage optics.

## Artifact and decision

C20 must emit an artifact with:

```text
data_availability
profile_summaries
sample_quality_table
trade_date_gate_summary
monthly_evaluated_distribution
decision
safety_boundaries
```

Decision categories:

```text
PROMISING_CONTINUE_TO_C20_TUNING
C20_DATE_GATE_NOT_ENOUGH
C20_DIAGNOSTIC_BLOCKED
```

Catalog and OOS remain forbidden in all C20 diagnostic outcomes.

```text
C20_CATALOG_IMPLEMENTATION_DEFERRED=true
C20_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```


## Final diagnostic outcome

C20 was validated as an IS-only diagnostic and rejected as a strategy candidate.

```text
C20_ALL_PARAM_7_PROFILE=PASS
artifact_hash=8f8eec9913c107f22ec1f395eed9386da41756c0
decision_status=C20_DATE_GATE_NOT_ENOUGH
best_profile=C20_G03_VOLATILITY_RISK_OFF_FILTER
profiles_with_quality_improvement=4
profiles_with_promising_continue=0
profiles_with_quality_target_reached=0
best_quality_target_profile=null
C20_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

This policy note does not authorize production date gating. The tested C20 regime/date gate profiles are diagnostic evidence only. C20 should stay closed unless a new non-lookahead regime data source is introduced.
