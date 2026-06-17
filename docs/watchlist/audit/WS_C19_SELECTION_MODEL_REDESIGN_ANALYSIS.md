# WS C19 Selection Model Redesign Analysis

## Purpose

C19 starts from C18 final diagnostic evidence and audits the selection path after scoring. It is not a catalog iteration and not a best-of-failed continuation.

C18 proved that raw candidates and scored pool are available, but the path from scored candidates into TOP, SECONDARY, recommendation, requested pairs, and evaluated picks collapses. Therefore C19 focuses on PlanGrouping, Recommendation, TOP/SECONDARY usage, volume/DV20/ATR/risk/entry-quality guards, and a monthly coverage-aware selector.

## C18 Evidence Used

Operator-provided C18 final evidence is treated as immutable session evidence:

```text
C18_PHASE_A_DIAGNOSTIC_DONE=true
C18_DIAGNOSTIC_FIRST_SUCCESS=true
C18_FUNNEL_DIAGNOSTIC_RUNTIME_VALIDATED=true
C18_RUNTIME_FIRST_FULL_12_PASS=true
C18_DEEP_FUNNEL_PARAM_150_PASS=true
C18_DEEP_FUNNEL_PARAM_149_PASS=true
C18_CATALOG_IMPLEMENTATION_DEFERRED=true
C18_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

Key C18 root cause:

```text
RAW_CANDIDATE_NOT_INSUFFICIENT=true
SCORING_POOL_AVAILABLE=true
DATA_PRICE_AVAILABILITY_NOT_PRIMARY=true
MONTHLY_EMPTY_CAUSED_BY_SELECTION_COLLAPSE=true
PRIMARY_ROOT_CAUSE=selection_collapse_after_scored_pool
SECONDARY_ROOT_CAUSE=volume_dv20_atr_entry_quality_grouping_guards_too_restrictive
SECONDARY_ALWAYS_ZERO_OBSERVED=true
```

C18 deep evidence showed around 39k-40k scored candidates but only 64-83 TOP, 38-46 recommended, and 35-42 evaluated picks for the best rows. Several months had scored candidates but no TOP/recommended output.

## Source Path Audit

### 1. Candidate Universe

Source: `WatchlistCandidateUniverseService`.

Flow:

```text
raw market-data read model candidates
-> required metrics check
-> DV20 minimum hard gate
-> ATR min/max hard gate
-> volume ratio minimum hard gate
-> eligible candidate output
```

Code-level finding:

- `dv20_idr`, `atr14_pct`, and `vol_ratio` are required before scoring.
- Candidate universe canonical gates should remain hard reject in C19.
- Opening these gates would risk low-liquidity garbage and ATR/risk abuse.

C19 decision:

```text
CANDIDATE_UNIVERSE_DV20_VOLUME_ATR_GATES=KEEP_HARD_REJECT
```

### 2. Scoring

Source: `WatchlistScoringService`.

Flow:

```text
eligible candidates
-> metric availability check
-> weighted score_total
-> score components
-> deterministic ranking
```

Code-level finding:

- Scoring pool can remain large.
- C18 collapse happens after this stage, not before it.
- Score chase prevention remains necessary because C17 explicitly blocks `0.90..1.00` score chase.

C19 decision:

```text
SCORING_POOL_AVAILABLE=true
SCORE_CHASE_BLOCK_RETAINED=true
```

### 3. PlanGrouping

Source: `WatchlistPlanGroupingService`.

Flow:

```text
scored items
-> collectEligibleItems
-> candidateSelectionExtensionFailures
-> sort/deduplicate
-> score threshold/quantile cutoff
-> TOP_PICKS
-> SECONDARY
-> WATCH_ONLY
-> AVOID / excluded
```

Code-level finding:

- C17 extension turns DV20 range, volume range, ATR segment, ROC20, ROC5, score window, score chase, component floors, and trend floors into hard reject before group allocation.
- TOP and SECONDARY are assigned after the same pre-filter. SECONDARY is not currently a separate recovery buffer; it only receives leftovers that survived the same C17 hard extension and secondary threshold.
- This explains why `SECONDARY=0` can happen without a runtime bug.

C19 interpretation:

```text
SECONDARY_ZERO_CAUSE=design_cutoff_guard_behavior_not_runtime_bug
TOP_CUTOFF_LOCATION=WatchlistPlanGroupingService::groupScoredOutput
HARD_REJECT_LOCATION=WatchlistPlanGroupingService::candidateSelectionExtensionFailures / c17QualityFloorFailures
```

### 4. Recommendation

Source: `WatchlistRecommendationService`.

Flow:

```text
TOP_PICKS + SECONDARY
-> recommendation_score = score_total
-> min_recommendation_score
-> max_recommended_items cap
-> recommendation items
```

Code-level finding:

- Recommendation consumes only TOP/SECONDARY.
- If SECONDARY is zero and TOP is sparse, recommendation has no recovery path.
- `min_recommendation_score` and daily cap can reduce TOP/SECONDARY further.

C19 interpretation:

```text
RECOMMENDATION_DROP_LOCATION=WatchlistRecommendationService::recommendFromPlanOutput / dynamicTargetCount
RECOMMENDATION_TOO_SMALL_WHEN_TOP_SECONDARY_SMALL=true
```

### 5. Price Runtime Evaluation

Source: `WatchlistBacktestPublishedPriceRuntimeService` and strategy runtime.

C19 Fase A/B intentionally does not call price runtime. Canonical evaluation model remains untouched:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

## Root Cause Summary

```text
PRIMARY_CODE_LEVEL_ROOT_CAUSE=selection_collapse_after_scored_pool
ROOT_CAUSE_STAGE=PlanGrouping before Recommendation
SECONDARY_ZERO_CAUSE=design_cutoff_guard_behavior_not_runtime_bug
CURRENT_SECONDARY_ROLE=leftover_bucket_after_same_hard_guards
CURRENT_RECOVERY_PATH=insufficient
PRICE_AVAILABILITY_NOT_PRIMARY=true
RAW_CANDIDATE_NOT_INSUFFICIENT=true
```

## Guard Treatment Decision

C19 does not blindly lower risk. The proposed redesign separates canonical gates from selection-extension guards.

Hard reject stays hard reject:

```text
candidate universe DV20 minimum
candidate universe volume minimum
candidate universe ATR min/max
score chase >= 0.90
risk component floor
breakout component floor
minimum component balance
minimum trend safety
```

Penalty/ranking candidates after scored pool:

```text
DV20 bucket overflow above strong liquidity
volume spike above strong volume
ATR outside ideal band but still within canonical min/max
ROC5 pullback miss
ROC20 segment miss
score window low or moderate overextension below score-chase block
component/trend borderline state
```

This keeps downside guardrails while giving the large scored pool a controlled path into buffer selection.

## Proposed C19 Model

Concept:

```text
C19_SELECTION_MODEL_REDESIGN_SAMPLE_RECOVERY_PROTOTYPE
```

Proposed flow:

```text
scored pool only
-> preserve fatal canonical and risk guards
-> compute quality_score = score_total - bounded penalties
-> TOP from high quality candidates
-> SECONDARY as controlled recovery buffer
-> monthly coverage-aware selector
-> recommendation candidate output for future price evaluation
```

SECONDARY role:

```text
SECONDARY_RECOVERY_BUFFER=true
SECONDARY_IS_NOT_FINAL_TRADE=true
SECONDARY_MUST_PASS_RECOMMENDATION=true
SECONDARY_MUST_PASS_FUTURE_PRICE_EVALUATION=true
```

Monthly coverage-aware selector:

```text
MONTHLY_AWARE=true
MONTH_BLACKLIST=false
FORCE_BAD_PICK=false
QUALITY_FLOOR_REQUIRED=true
GLOBAL_FILL_AFTER_MONTHLY_RANKING=true
```

## Implemented in C19 Fase A/B

Implemented source files:

- `app/Application/Watchlist/Services/WatchlistBacktestC19SelectionModelRedesignAnalysisService.php`
- `app/Console/Commands/Watchlist/RunBacktestC19SelectionDiagnoseCommand.php`
- `tests/Unit/Watchlist/WatchlistBacktestC19SelectionModelRedesignAnalysisServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestC19StaticGuardTest.php`

Registered command:

```text
watchlist:backtest-c19-selection-diagnose
```

The command is diagnostic/prototype only. It does not create C19 catalog, does not seed, does not promote, does not run OOS, and does not set production readiness.

## C19 Catalog Decision

```text
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_IMPLEMENTED_SOURCE_LEVEL=false
C19_CATALOG_CODE=NOT_CREATED
```

Reason:

C19 Fase A/B creates diagnostic/prototype evidence only. It does not yet provide canonical price-evaluated IS proof, two-run IS calibration, or stability proof that the sample recovery preserves downside.

Next gate before catalog:

1. Operator runs C19 PHPUnit filter.
2. Operator runs full Watchlist PHPUnit.
3. Operator runs C19 selection diagnose command on the C17 source catalog.
4. Compare current vs proposed TOP/SECONDARY/recommended/monthly distribution.
5. Only if sample recovery is strong and quality-preserving, implement source-level runtime mode.
6. Only after source-level runtime mode has price-evaluated two-run IS evidence should C19 catalog be considered.

## C19 v3 Diagnostic Mapping Fix

Operator validation of the initial C19 diagnostic showed the runtime command was safe, but the artifact was not yet a reliable selector diagnostic:

```text
current_top_count=0 while current_recommended_count>0
raw_count=0 and eligible_count=0 while scored_count>0
dominant_current_drop_reasons=UNKNOWN
proposed_top_count=0
proposed_secondary_count=0
proposed_recommended_count=0
```

C19 v3 fixes that by mapping source outputs according to the actual service contracts:

```text
CandidateUniverse: input_candidate_count, eligible_count, rejected_count
Scoring: summary.scored_count, summary.excluded_count
PlanGrouping: groups.TOP_PICKS / groups.SECONDARY / groups.WATCH_ONLY / groups.AVOID as direct arrays
Recommendation: summary.recommended_count as selected output, not raw items count
```

The artifact now includes per-param `debug_output_keys` to expose output shapes and prevent silent zero defaults.

## C19 v3 Selector Simulation

The proposed selector no longer depends on collapsed TOP/SECONDARY. It starts from scored candidates:

```text
scored candidates
-> preserve fatal candidate-universe and downside guards
-> mirror C17 extension failure reasons
-> convert bounded DV20/volume/ATR/ROC/score-window misses into penalties
-> keep score chase/component/trend/risk/breakout failures fatal
-> produce proposed TOP and SECONDARY buffers
-> apply monthly coverage-aware recommendation simulation
```

This remains diagnostic-only. It does not mutate `PlanGroupingService`, `RecommendationService`, C17, C18, or any prior catalog.

## C19 v3 Catalog Decision

```text
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_CATALOG_CODE=NOT_CREATED
```

C19 v3 can show whether a recovery buffer exists, but catalog creation still requires price-evaluated IS proof and two-run calibration. No OOS is allowed at this phase.

## Tahap 4 Price Diagnostic Extension

C19 Tahap 4 adds an IS-only diagnostic command that converts the selector simulation's proposed recommendations into frozen trade candidates and evaluates them through the existing published-price runtime artifact/metrics path.

This stage answers a different question from selector simulation:

```text
Selector simulation question:
Can scored candidates be recovered into proposed recommendations without opening unsafe gates?

Tahap 4 question:
Do those proposed recommendations survive canonical price evaluation from trade_date -> NEXT_OPEN entry -> STOP_TP_OR_TIME exit?
```

Tahap 4 does not change canonical evaluation, does not run OOS, does not create a catalog, and does not set production readiness.

Implementation source:

```text
app/Application/Watchlist/Services/WatchlistBacktestC19ProposedSelectionPriceDiagnosticService.php
app/Console/Commands/Watchlist/RunBacktestC19ProposedSelectionPriceDiagnoseCommand.php
```

Runtime source reused:

```text
WatchlistBacktestRuntimeArtifactService
WatchlistBacktestMetricsService
MarketDataPublishedEodSeriesReadService
```

Required status until further evidence:

```text
C19_PRICE_EVALUATION_DIAGNOSTIC_IMPLEMENTED=true
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```
