# WS C49 - IS Broader Strategy Redesign From C48 Failure Attribution

## Purpose

C49 builds an IS-only broader strategy redesign after C48 identified that the locked C44 refinement failed mainly from shared-core selection failure, G21 fragility, market/regime weakness, ticker/sector concentration, and post-entry path decay.

C49 is not OOS proof, not OOS tuning, not a retry of C47, not catalog promotion, and not production rollout. C48 is used only as a diagnostic hypothesis source.

## Input C48 artifact

```text
input_c48_artifact=storage/app/watchlist/backtest/c48-oos-failure-attribution.json
expected_c48_hash=1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7
expected_c48_file_sha1=EEA350AF2D8A42C881B78701C48A1E301230362C
required_status=C48_OOS_FAILURE_ATTRIBUTION_COMPLETED
required_next_step=C49_BROADER_STRATEGY_REDESIGN
production_ready_required=false
```

Current C48 evidence summary from the source workspace:

```text
actual_c48_hash=1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7
c48_hash_match=true
c48_status=C48_OOS_FAILURE_ATTRIBUTION_COMPLETED
c48_diagnostic_conclusion=C48_SHARED_CORE_SELECTION_FAILURE_IDENTIFIED
c48_next_step_recommendation=C49_BROADER_STRATEGY_REDESIGN
production_ready=false
```

## Boundary

```text
C49_IS_BROADER_STRATEGY_REDESIGN_ONLY=true
C48_ARTIFACT_HASH_LOCK=true
C48_USED_FOR_HYPOTHESIS_ONLY=true
IS_ONLY_SELECTION=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_OOS_PROOF_RERUN=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_OOS_RETURN_SELECTION=true
NO_OOS_BAD_MONTH_THRESHOLD_SELECTION=true
NO_OOS_TICKER_SECTOR_EXCLUSION_RULE=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_CANDIDATE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C48_MUTATION=true
NO_C01_TO_C48_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
RETURN_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
FUTURE_PATH_PRICE_USED_FOR_SELECTION=false
PROFILE_RET_NET_USED_FOR_SELECTION=false
DERIVED_MFE_MAE_USED_FOR_EXECUTION=false
OOS_DATA_USED_FOR_TUNING=false
OOS_RETURN_USED_FOR_CANDIDATE_SELECTION=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
production_ready=false
```

Canonical execution model remains `ENTRY=NEXT_OPEN`, `EXIT=STOP_TP_OR_TIME`, `HOLD=5`, `FEE=IDR_FIXED`, `SLIP=0`, `GAP=OPEN`, `PX=IDX_BANDS`.

## Periods

```text
IS_REDESIGN_PERIOD=2023-01-02..2025-05-21
OOS_RESERVED_PERIOD=2025-05-22..2026-05-29
OOS_RESERVED_USED_FOR_SELECTION=false
OOS_RESERVED_USED_FOR_TUNING=false
OOS_RESERVED_USED_FOR_PROOF=false
```

## C48 diagnostic hypothesis carry-forward

C49 carries forward these C48 fields for IS-only redesign planning:

```text
dominant_failure_source=shared_core_selection_and_oos_month_cluster
dominant_failure_branch=G21
g21_quota_fragility=true
market_extension_control_insufficient=true
market_regime_failure=true
ticker_concentration_failure=true
sector_bucket_failure=true
post_entry_path_failure=true
selection_overlap_failure=true
is_oos_generalization_failure=true
c48_used_for_hypothesis_only=true
oos_return_used_for_selection=false
oos_data_used_for_tuning=false
```

## IS source universe summary

C49 reconstructs the source universe from IS-only rows. Default source:

```text
storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
```

The service can also consume injected `source_rows` and `pre_trade_source_rows` for unit tests. If source rows are absent, the artifact records:

```text
C49_SOURCE_ROWS_NOT_EVALUABLE
```

Pre-trade enrichment is attempted from safe signal-date fields when available:

```text
dv20_idr
atr14_pct
vol_ratio
roc20
ma20_slope_pct
rs_20_vs_ihsg
rs_20_vs_sector
sector_roc20
sector_code
market_index_roc20
```

If the pre-trade join is unavailable, metadata-only profiles remain evaluable and the artifact records a not-evaluable reason for pre-trade diagnostics.

## Shared-core escape redesign

C49 creates a C44 comparator plus broader IS redesign families:

```text
C49_F00_C44_SHARED_CORE_COMPARATOR
C49_F01_BRANCH_BALANCED_CORE_ESCAPE
C49_F02_G21_CAP_10_IS_ONLY
C49_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
C49_F04_TICKER_SECTOR_CONCENTRATION_GUARD
C49_F05_POST_ENTRY_PATH_ROBUSTNESS_PROXY_GUARD
C49_F06_COMBINED_BROADER_REDESIGN
C49_F07_CONSERVATIVE_COVERAGE_PRESERVING_REDESIGN
C49_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN
```

Each profile records safe pre-trade fields, selected row count, IS return metrics, month stability, overlap with C44/baseline, concentration, regime robustness, path-proxy safety, and failure reason codes.

## Branch / G21 quota fragility IS diagnostic

C49 evaluates predeclared IS-only quota variants:

```text
G21_CAP_NONE
G21_CAP_13_CURRENT
G21_CAP_10
G21_CAP_8
G21_CAP_6
G21_DYNAMIC_BY_MARKET_REGIME_IS_ONLY
```

Quota variants are diagnostics only. They are not production rules and do not use OOS data.

## Regime-aware IS diagnostic

C49 computes regime buckets when fields are available:

```text
market_index_roc20
market_index_ma20_slope_pct
sector_roc20
rs_20_vs_ihsg
rs_20_vs_sector
roc20
ma20_slope_pct
```

If those fields are not available, the artifact records:

```text
C49_REGIME_AWARE_DIAGNOSTIC_NOT_EVALUABLE
```

## Concentration guard diagnostic

C49 measures ticker, sector, branch, unique ticker/sector count, and loss-cluster concentration for each redesign profile. If sector fields are absent, the artifact records:

```text
C49_SECTOR_BUCKET_CONCENTRATION_NOT_EVALUABLE
```

## Post-entry path diagnostic

C49 evaluates available path evidence only as diagnostic information:

```text
profile_exit_reason
profile_exit_day_offset
missing_path_data_flag
```

Every path diagnostic row is marked:

```text
safe_for_selection=false
diagnostic_only=true
```

Potential pre-trade proxy fields are recorded for C50/C51 validation, but future path fields are never used to select candidates.

## Candidate scorecard and C50 readiness

C49 can select IS redesign candidates for C50 validation only. Candidate scorecard includes:

```text
candidate_code
profile_code
family_code
candidate_role
safe_pre_trade_fields_used
evaluated_picks_count
avg_ret_net
median_ret_net
p25_ret_net
p10_ret_net
win_rate
month_win_rate_min
month_avg_ret_net_min
bad_month_like_count
coverage_months
overlap_with_c44
overlap_with_baseline
material_selection_difference_pass
coverage_pass
quality_pass
stability_pass
concentration_pass
regime_robustness_pass
path_proxy_pass
anti_shared_core_pass
candidate_selected_for_c50_validation
production_ready=false
```

C50 readiness can recommend only IS validation/evidence expansion:

```text
C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN
C50_IS_EVIDENCE_EXPANSION_FOR_C49_REDESIGN
```

C49 must not recommend OOS proof.

## Artifact output

```text
artifact_path=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json
artifact_type=C49_BROADER_STRATEGY_REDESIGN
production_ready=false
```

## Final operator validation result

Operator validation was completed in the supported project environment.

```text
C49_IMPLEMENTATION_STATUS=IMPLEMENTED
C49_PHPUNIT=PASS — OK (12 tests, 196 assertions)
C49_FULL_WATCHLIST_PHPUNIT=PASS — OK (723 tests, 13647 assertions)
C49_RUNTIME_STATUS=COMPLETED
status=C49_BROADER_STRATEGY_REDESIGN_COMPLETED
reason_code=C49_BROADER_STRATEGY_REDESIGN_COMPLETED
artifact_path=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json
artifact_hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8
production_ready=false
```

Source C48 lock validation:

```text
expected_c48_hash=1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7
actual_c48_hash=1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7
c48_hash_match=true
c48_status=C48_OOS_FAILURE_ATTRIBUTION_COMPLETED
c48_diagnostic_conclusion=C48_SHARED_CORE_SELECTION_FAILURE_IDENTIFIED
```

C49 source universe/runtime summary:

```text
source_evidence_artifact=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
source_rows_available=true
source_mode=C28_PICK_DIAGNOSTIC_ROWS
source_is_rows=15750
source_g21_rows=1770
source_g16_rows=1320
source_g13_rows=590
source_months=27
pre_trade_source_mode=DATABASE_AS_OF_SIGNAL_DATE_JOIN
pre_trade_source_row_count=482
oos_data_used_for_tuning=false
oos_return_used_for_selection=false
return_used_for_selection=false
future_path_used_for_selection=false
```

Selected C49 candidates for C50:

```text
primary_candidate=C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
primary_profile_code=C49_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
defensive_comparator=C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN
regime_comparator=C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
coverage_comparator=
concentration_guard_comparator=
selected_candidate_count=3
candidate_is_not_production=true
production_ready=false
```

C50 readiness decision:

```text
redesign_completed=true
shared_core_escape_achieved=true
material_selection_difference_achieved=true
g21_quota_fragility_confirmed_in_is=false
regime_aware_redesign_promising=true
concentration_guard_promising=false
path_proxy_redesign_promising=false
primary_candidate_code=C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
defensive_comparator_code=C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN
coverage_comparator_code=
c50_recommendation=C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN
diagnostic_conclusion=C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

## Final C49 status

```text
FINAL_STATUS=C49_BROADER_STRATEGY_REDESIGN_COMPLETED
DIAGNOSTIC_CONCLUSION=C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION
NEXT_STEP_RECOMMENDATION=C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN
```

C49 is complete as an IS broader redesign step. It does not authorize production, does not prove OOS recovery, and does not unlock OOS proof. The correct next step is C50 IS validation and anti-overfit check for the C49 redesign candidate.
