# Weekly Swing C19 Selection Model Redesign Design Note

## Scope

C19 redesigns selection after scoring for Weekly Swing backtest diagnostics. It does not change the canonical evaluation model and does not introduce production behavior.

Canonical evaluation remains:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

## Problem Proven by C18

C18 final diagnostic proved that raw candidates and scored candidates are available, but selection collapses after scoring.

```text
PRIMARY_ROOT_CAUSE=selection_collapse_after_scored_pool
SECONDARY_ROOT_CAUSE=volume_dv20_atr_entry_quality_grouping_guards_too_restrictive
SECONDARY_ALWAYS_ZERO_OBSERVED=true
```

Therefore C19 must not be a new static catalog-only iteration. It must redesign the path from scored pool to TOP/SECONDARY/recommendation.

## C19 Design Principle

C19 separates canonical fatal guards from selection-extension quality shaping.

Keep fatal:

```text
minimum liquidity
minimum volume
ATR min/max
score chase block
risk component floor
breakout component floor
minimum component balance
minimum trend safety
```

Convert to bounded penalty candidate after scored pool:

```text
DV20 bucket overflow
volume spike above strong bucket
ATR outside ideal but inside allowed range
ROC5 pullback miss
ROC20 segment miss
score window miss below score-chase area
borderline component/trend state
```

## SECONDARY Role

SECONDARY should become a controlled recovery buffer.

```text
SECONDARY_IS_BUFFER=true
SECONDARY_IS_NOT_FINAL_TRADE=true
SECONDARY_REQUIRES_RECOMMENDATION=true
SECONDARY_REQUIRES_FUTURE_PRICE_EVALUATION=true
```

This fixes the current C17/C18 behavior where SECONDARY is only leftover after the same hard guards and can remain zero.

## Monthly Coverage-Aware Selector

Monthly coverage can be an objective, not a blacklist.

Allowed:

```text
rank quality candidates per month
fill underrepresented months first
fill globally after monthly ranking
skip month if no candidate passes quality floor
```

Not allowed:

```text
month blacklist
forced low-quality pick
lowering canonical downside gates
ticker blacklist
sector whitelist without contract and evidence
```

## C19 Current Decision

```text
C19_DIAGNOSTIC_IMPLEMENTED=true
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

Next eligible step is operator validation of the C19 diagnostic/prototype. Catalog implementation is blocked until price-evaluated IS evidence proves sample recovery without damaging downside.

## C19 v3 Diagnostic Clarification

C19 v3 fixes diagnostic mapping and selector simulation. It does not change production behavior.

Mapping corrections:

```text
PlanGrouping groups.TOP_PICKS and groups.SECONDARY are direct item arrays.
Recommendation selected count must be read from summary.recommended_count when present.
Candidate raw/eligible counts must be read from CandidateUniverse top-level counters when present.
```

Selector simulation source:

```text
scored candidates only
not existing TOP/SECONDARY after collapse
```

The v3 selector can propose TOP/SECONDARY buffers for analysis, but those buffers are not final recommendations, not CONFIRM signals, not orders, and not production-ready output.

```text
C19_V3_DIAGNOSTIC_MAPPING_FIXED=true
C19_V3_SELECTOR_SIMULATION_FROM_SCORED_POOL=true
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
```


## C19 v3.1 Diagnostic Patch Note

C19 v3.1 fixes a diagnostic component-key alias gap. The selector simulation must resolve both canonical extension keys (`score_momentum`, `score_breakout`, `score_volume`, `score_risk`) and scored/test payload keys (`momentum`, `breakout`, `volume`, `risk`). Without this alias, valid borderline recovery candidates can be incorrectly treated as component-balance failures, causing proposed SECONDARY recovery to stay zero in the unit fixture.

This patch does not create a catalog, does not run OOS, does not mutate C18 or earlier catalogs, and does not change the canonical evaluation model.

## C19 Tahap 4 Price Diagnostic Note

Tahap 4 adds price evaluation for proposed selection candidates while keeping the same diagnostic-only boundary.

It evaluates this chain:

```text
C19 selector proposed recommendation
-> frozen trade candidate
-> ENTRY NEXT_OPEN
-> EXIT STOP_TP_OR_TIME
-> HOLD 5
-> FEE IDR_FIXED
-> SLIP 0
-> GAP OPEN
-> PX IDX_BANDS
```

Tahap 4 is allowed:

```text
use C19 proposed selector output
freeze proposed recommendations before price read
read targeted published EOD price series
use WatchlistBacktestRuntimeArtifactService and WatchlistBacktestMetricsService
report evaluated_picks_count, price_missing_count, returns, and monthly distribution
```

Tahap 4 is not allowed:

```text
create C19 catalog
seed C19 param grid
run OOS
set production_ready=1
change ENTRY/EXIT/HOLD/FEE/SLIP/GAP/PX
turn Watchlist into broker/order execution
```

Status remains:

```text
C19_PRICE_EVALUATION_DIAGNOSTIC_IMPLEMENTED=true
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## Tahap 5 Quality Recovery Tuning Note

Tahap 4 proved evaluated sample recovery but all rows still had negative average return. Tahap 5 therefore introduces profile-based quality recovery diagnostics, not a catalog.

Profiles are constrained to pre-price selector inputs:

```text
quality_score
penalty_total
score_total
score_components
score_metrics
current_extension_failures
```

Profiles must not use future return, stop/target outcome, ticker blacklist, month blacklist, sector whitelist, OOS, or production/runtime mutation. The output is only valid as evidence for whether the next redesign should continue, not as an automatic catalog candidate.

## C19 Tahap 5B design note — hybrid quality backfill

Tahap 5A operator evidence showed that no-overextension filtering is the strongest quality signal, but strict no-overextension alone cannot preserve the canonical sample target. Tahap 5B therefore tests hybrid selection:

```text
quality core = Q02 no-overextension signal
controlled backfill = downside-aware / low-ATR / low-penalty candidates
sample target = diagnostic attempt to restore 120+ evaluated picks
```

This remains diagnostic-only. It does not authorize any catalog, OOS, broker action, or production readiness.

Decision ranking is explicitly split so small-sample averages cannot be promoted:

```text
best_any_sample_profile_summary = diagnostic clue only
best_sample_qualified_profile_summary = sample-safe decision candidate
best_profile_summary = decision-safe aggregate profile
```
