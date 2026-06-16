# WS Downside Stability C18 Design Note

## Purpose

C18 is a diagnostic-first phase after the final C17 result. C17 is immutable and rejected as a strategy catalog because all 12 rows failed canonical IS quality, mainly minimum-trade and monthly-stability gates.

C18 does not define a new catalog. It creates and validates an IS-only funnel and monthly coverage diagnostic so the next design step is evidence-based, not another blind catalog iteration.

## Source baseline

```text
SOURCE_CATALOG_CODE=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06
SOURCE_CATALOG_VERSION=C17
SOURCE_CATALOG_COUNT=12
SOURCE_CATALOG_HASH=d411bfbee6fb14c17d821aa92e7e0fea06925d67
SOURCE_RESULT=C17_GRID_FAILED_IS_QUALITY
SOURCE_REASON=WS_BT_C17_NO_VALID_IS_CANDIDATE
SOURCE_VALID_PARAM_COUNT=0
SOURCE_MAX_PICKS_COUNT=42
CANONICAL_MIN_TRADES=120
OOS_NOT_RUN=true
production_ready=0
```

## Diagnostic principle

C18 must answer the root cause before any new catalog exists:

```text
RAW_CANDIDATE_SUPPLY?
CANDIDATE_UNIVERSE_FILTER_IMPACT?
SCORING_RUNTIME_GUARD_IMPACT?
PLAN_GROUPING_CAP_IMPACT?
RECOMMENDATION_SELECTION_IMPACT?
PRICE_EVALUATION_OR_BOUNDARY_CENSORING_IMPACT?
MONTHLY_EMPTY_VS_NEGATIVE_MONTHS?
```

## Runtime boundary

C18 follows the existing Watchlist weekly swing pipeline:

```text
CandidateUniverse -> Scoring -> PlanGrouping -> Recommendation -> PublishedPriceBacktestEvaluation
```

It does not change:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
```

## Operator validation evidence

Operator validation completed on 2026-06-16:

```text
PHPUNIT_C18_FUNNEL=PASS: 6 tests, 95 assertions
FULL_WATCHLIST_PHPUNIT=PASS: 372 tests, 9051 assertions
COMMAND_HELP_CONFIRMED_OPTIONS=--deep-funnel,--progress-every
```

Runtime-first full 12 evidence:

```text
C18_RUNTIME_FIRST_FULL_12_PASS=true
diagnostic_param_count=12
max_evaluated_picks_count=42
max_recommended_count_before_price_evaluation=0
params_with_empty_evaluation_months=12
artifact_hash=b03a79896f3cfd985f6462bd1456494eaac8e405
oos_executed=0
production_ready=0
```

Deep funnel evidence:

```text
PARAM_150_DEEP_PASS=true
PARAM_150_RAW=402887
PARAM_150_ELIGIBLE=40342
PARAM_150_SCORED=40342
PARAM_150_TOP=64
PARAM_150_SECONDARY=0
PARAM_150_RECOMMENDED=46
PARAM_150_EVALUATED=42
PARAM_150_ARTIFACT_HASH=8b47719f082525a71346aeafd67a5927c1ed1bdd

PARAM_149_DEEP_PASS=true
PARAM_149_RAW=402887
PARAM_149_ELIGIBLE=39594
PARAM_149_SCORED=39594
PARAM_149_TOP=83
PARAM_149_SECONDARY=0
PARAM_149_RECOMMENDED=38
PARAM_149_EVALUATED=35
PARAM_149_ARTIFACT_HASH=3dd342f47f7e1397d7ec8defb9e15af26184ca33
```

## Final root-cause conclusion

C18 proved that raw candidate supply is not the main problem.

```text
RAW_CANDIDATE_NOT_INSUFFICIENT=true
SCORING_POOL_AVAILABLE=true
PRIMARY_ROOT_CAUSE=selection_collapse_after_scored_pool
SECONDARY_ROOT_CAUSE=volume_dv20_atr_entry_quality_grouping_guards_too_restrictive
MONTHLY_EMPTY_CAUSED_BY_SELECTION_COLLAPSE=true
PRICE_AVAILABILITY_NOT_PRIMARY=true
```

Rationale:

- Deep rows have `402887` raw candidates.
- Eligible/scored pools remain large: `39594` to `40342`.
- Sample collapses after scoring into only `64` to `83` TOP candidates.
- Recommendations fall further to `38` to `46`.
- Evaluated picks remain only `35` to `42`, far below canonical `min_trades=120`.
- Multiple months have more than 1000 scored candidates but zero recommended picks.
- `SECONDARY=0` in both deep rows, so no risk-controlled sample buffer path is active.
- Price availability/boundary censoring is not the primary blocker.

## Forbidden behavior

```text
C17_MUTATION=false
C16_MUTATION=false
C15_C14_C01_C07_R1_R2_MUTATION=false
C18_CATALOG_CREATION=false
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

## Final C18 status

```text
C18_DIAGNOSTIC_FIRST=true
C18_PHASE_A_DIAGNOSTIC_DONE=true
C18_FUNNEL_DIAGNOSTIC_IMPLEMENTED=true
C18_FUNNEL_DIAGNOSTIC_RUNTIME_VALIDATED=true
C18_CATALOG_IMPLEMENTATION_DEFERRED=true
C18_CATALOG_CODE=NOT_CREATED
C17_UNCHANGED=true
C01_TO_C17_IMMUTABLE=true
WATCHLIST_SCOPE_ONLY=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
OOS_NOT_RUN=true
production_ready=0
```

## Next design direction

C18 is closed as diagnostic-first result. The next session should be C19 strategy model redesign, not C18 catalog implementation.

C19 must address:

```text
PlanGrouping collapse from ~40k scored candidates to only 64-83 TOP.
Recommendation collapse from 64-83 TOP to only 38-46 recommended.
SECONDARY remaining 0.
Volume/DV20/ATR/entry-quality/ROC guards being too restrictive.
Monthly coverage-aware selection without month blacklist.
Downside preservation from C17.
No OOS before a valid IS candidate exists.
No production_ready=1.
```
