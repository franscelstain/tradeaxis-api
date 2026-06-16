# WS C19 Selection Model Diagnostic Result

## Status

```text
C19_PHASE=FASE_A_B_SELECTION_MODEL_REDESIGN_DIAGNOSTIC_V3
C19_DIAGNOSTIC_IMPLEMENTED=true
C19_V3_DIAGNOSTIC_MAPPING_FIXED=true
C19_V3_SELECTOR_SIMULATION_FROM_SCORED_POOL=true
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## Why v3 Exists

Operator validation for the first C19 runtime diagnostic passed safely, but its artifact exposed two mapping defects:

```text
current_top_count=0 while current_recommended_count>0
raw_count=0 and eligible_count=0 while scored_count>0
dominant_current_drop_reasons=UNKNOWN
proposed_top/secondary/recommended=0
```

C19 v3 fixes the diagnostic mapping and implements a real selector simulation from the scored pool.

## v3 Mapping Fix

The C19 diagnostic now treats output structures explicitly:

```text
CandidateUniverse input_candidate_count / eligible_count / rejected_count
Scoring summary.scored_count / summary.excluded_count
PlanGrouping groups.TOP_PICKS / groups.SECONDARY / groups.WATCH_ONLY / groups.AVOID as item arrays
Recommendation summary.recommended_count as recommendation output count
```

It also records `debug_output_keys` inside each diagnostic entry so future mapping mistakes are visible in the artifact.

## v3 Selector Simulation

The proposed path now starts from scored candidates, not from already-collapsed TOP/SECONDARY output:

```text
scored candidates
-> preserve fatal guards
-> convert selected C17 extension rejects into bounded penalties
-> proposed TOP buffer
-> proposed SECONDARY buffer
-> monthly coverage-aware proposed recommendation selection
```

Fatal guards preserved:

```text
score chase block >=0.90
score below buffer floor
risk component floor
breakout component floor
component balance failure
trend safety failure
candidate universe DV20/volume/ATR gates indirectly preserved because only scored candidates are used
```

Penalty candidates:

```text
WATCHLIST_C17_DV20_SAMPLE_RECOVERY_RANGE_FAIL
WATCHLIST_C17_VOLUME_RECOVERY_RANGE_FAIL
WATCHLIST_C17_ATR_SEGMENT_RANGE_FAIL
WATCHLIST_C17_ROC20_SEGMENT_RANGE_FAIL
WATCHLIST_C17_ROC5_CONTROLLED_PULLBACK_RANGE_FAIL
WATCHLIST_C17_SCORE_WINDOW_LOW_FAIL
WATCHLIST_C17_SCORE_OVEREXTENSION_FAIL
borderline trend confirmation
```


## v3.1 Patch After Operator Rerun

Operator rerun of C19 v3 failed the unit assertion that proposed secondary recovery must be non-zero. Root cause was not catalog/runtime logic. Two diagnostic-test issues were fixed:

```text
C19_V3_1_COMPONENT_ALIAS_FIX=true
C19_V3_1_TEST_FIXTURE_SECONDARY_BUFFER_FIX=true
```

The selector's `componentValue()` now resolves both canonical C17 extension keys such as `score_momentum` and scored-output/test keys such as `momentum`. The fake fixture also now includes a deliberate borderline candidate that passes fatal component balance while still receiving bounded penalties, so the unit test actually exercises SECONDARY recovery instead of only TOP/fatal paths.

Agent self-check with the same fake fixture returned:

```text
max_current_top_count=2
max_current_secondary_count=0
max_current_recommended_count=2
max_proposed_top_count=2
max_proposed_secondary_count=2
max_proposed_recommended_count=4
params_with_proposed_secondary_recovery=1
```

This remains diagnostic-only. C19 catalog is still deferred, OOS is still not run, and production readiness remains `0`.

## Diagnostic Command

```powershell
php artisan watchlist:backtest-c19-selection-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c19-selection-model-redesign-analysis-v3.json `
  --overwrite
```

Optional focused run:

```powershell
php artisan watchlist:backtest-c19-selection-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=149,150 `
  --output=storage/app/watchlist/backtest/c19-selection-model-redesign-param-149-150-v3.json `
  --overwrite
```

## Expected PASS Markers

```text
status=PASS
reason_code=WS_BT_C19_SELECTION_MODEL_ANALYSIS_READY
scope=IS_ONLY_DIAGNOSTIC
diagnostic_param_count=<source row count>
max_current_top_count=<integer mapped from PlanGrouping>
max_current_secondary_count=<integer mapped from PlanGrouping>
max_current_recommended_count=<integer mapped from Recommendation summary.recommended_count>
max_proposed_top_count=>0 expected if selector simulation finds quality candidates
max_proposed_secondary_count=>0 expected if recovery buffer works
max_proposed_recommended_count=>0 expected if selector simulation works
params_with_proposed_secondary_recovery=>0 expected
params_with_non_unknown_drop_reasons=>0 expected
c19_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## Expected Artifact Contract

Artifact type:

```text
C19_SELECTION_MODEL_REDESIGN_ANALYSIS
```

Required fields:

```text
scope=IS_ONLY_DIAGNOSTIC
source_path_findings present
proposed_selection_model present
diagnostics present
diagnostics[].debug_output_keys present
diagnostics[].current_path.candidate_raw_count present
diagnostics[].current_path.scored_candidate_count present
diagnostics[].current_path.plan_top_picks_count present
diagnostics[].current_path.recommendation_output_count present
diagnostics[].dominant_current_drop_reasons not UNKNOWN-only
diagnostics[].proposed_path.candidate_buffer_count present
diagnostics[].proposed_path.fatal_reject_reason_counts present
diagnostics[].proposed_path.penalty_reason_counts present
cross_param_summary present
c19_catalog_decision.C19_CATALOG_IMPLEMENTATION_DEFERRED=true
safety_boundaries.C19_V3_DIAGNOSTIC_MAPPING_FIXED=true
safety_boundaries.C19_V3_SELECTOR_SIMULATION_FROM_SCORED_POOL=true
safety_boundaries.OOS_NOT_RUN=true
safety_boundaries.production_ready=0
```

## Operator Evidence Carried Forward

Operator supplied post-signature-fix validation for C19 v2:

```text
POST_FIX_PHPUNIT_C19_FILTER=PASS: OK (5 tests, 70 assertions)
POST_FIX_FULL_WATCHLIST=PASS: OK (377 tests, 9121 assertions)
C19_V2_FULL_DIAGNOSTIC=PASS artifact_hash=6737a7a07e2a1c71be38797d1406e8fc7c7e79e7
C19_V2_PARAM_149_150_DIAGNOSTIC=PASS artifact_hash=5b0943bc8b3d17138bd2cf77fd209f4fccdcd34a
```

That v2 runtime was safe but diagnostically insufficient because proposed recovery stayed zero and current mapping used ambiguous keys.

## Result in Current Agent Environment

Runtime diagnostic and PHPUnit cannot be executed in this environment due missing PHP extensions:

```text
PHPUNIT_C19_FILTER=BLOCKED_IN_AGENT_ENV_MISSING_DOM_MBSTRING_XML_XMLWRITER
FULL_WATCHLIST_PHPUNIT=NOT_RUN_SAME_BLOCKER
C19_V3_RUNTIME_DIAGNOSTIC=OPERATOR_VALIDATION_REQUIRED
```

PHP lint passed for changed PHP files:

```text
PHP_LINT_CHANGED_FILES=PASS
```

## Catalog Decision

```text
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_CATALOG_CODE=NOT_CREATED
```

C19 v3 is still diagnostic/prototype only. Catalog creation requires later price-evaluated IS proof, two-run stability, and evidence that downside gates are preserved.
