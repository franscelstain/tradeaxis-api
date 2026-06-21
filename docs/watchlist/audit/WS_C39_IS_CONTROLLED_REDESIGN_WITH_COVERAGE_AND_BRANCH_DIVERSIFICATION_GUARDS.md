# WS C39 - IS Controlled Redesign With Coverage And Branch Diversification Guards

## Scope

C39 is an IS-only guarded candidate formation step after C38 confirms that the C37 candidate failed anti-overfit validation.

C39 locks the C38 artifact, reconstructs the C28 IS evidence rows, and forms a guarded replacement candidate that addresses:

```text
C38_REQ_MONTH_COVERAGE_GUARD
C38_REQ_BRANCH_DIVERSIFICATION_GUARD
C38_REQ_ROLLING_STABILITY_EXPANSION
C38_REQ_PRE_TRADE_FIELD_EXPANSION_FOR_C36_BLOCKED_CANDIDATES
```

C39 does not run OOS proof, does not tune from OOS, does not create best-of-OOS, does not create a production catalog, does not promote a candidate, and does not mutate PLAN/CONFIRM behavior.

## Source Lock

```text
source_artifact=storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json
expected_c38_hash=7fe69c9ee9797615df676b0fe0c7378b452da429
actual_c38_hash=7fe69c9ee9797615df676b0fe0c7378b452da429
c38_hash_match=true
c38_status=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED
c38_diagnostic_conclusion=C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
c38_next_step=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS
expected_c38_file_sha1=74AF66E0170D4C6FF8AE3B7E45F8EC72D9774A7B
```

## Runtime Output

```text
status=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED
artifact_path=storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json
artifact_hash=504aaa061054ed2771ed08294d8a0570f08e18db
file_sha1=B08233211E335C982E327D6A0C638428B906BFC9
diagnostic_conclusion=C39_GUARDED_IS_CANDIDATE_FORMED
next_step_recommendation=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE
production_ready=false
```

## Guard Configuration

```text
baseline_months_required=27
c38_zero_pick_months=2023-03
max_top_branch_share=0.80
metadata_monthly_g21_quota_per_month=13
metadata_monthly_g21_quota_required_rows=330
metadata_monthly_g21_quota_selected_rows=343
selection_ordering_fields=trade_month,trade_date,ticker,param_id,row_code
return_used_for_selection=false
future_path_used_for_selection=false
oos_data_used_for_tuning=false
```

## Candidate Summary

```text
total_candidates=6
evaluated_candidates=4
not_evaluable_candidates=2
candidate_formed=true
best_is_candidate_code=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
best_is_candidate_is_not_production=true
best_candidate_requires_C40_validation=true
```

## Best Candidate Guard Result

```text
best_candidate_selected_rows=1663
best_candidate_zero_pick_month_count=0
best_candidate_month_coverage_passed=true
best_candidate_branch_diversification_passed=true
best_candidate_top_branch_share=0.79374624173181
```

## Best Candidate IS Evaluation

```text
baseline_rows=3090
baseline_avg_ret_net=0.002764085805812881
baseline_p25_ret_net=-0.005819495309286108
baseline_win_rate=0.5268608414239482
baseline_bad_month_like_count=9

best_candidate_rows=1663
best_candidate_avg_ret_net=0.008946161771050667
best_candidate_p25_ret_net=-0.0005002000800320128
best_candidate_win_rate=0.6849067949488875
best_candidate_bad_month_like_count=6

delta_avg_ret_net_vs_baseline=0.006182075965237786
delta_p25_ret_net_vs_baseline=0.005319295229254095
delta_win_rate_vs_baseline=0.15804595352493933
delta_bad_month_like_count_vs_baseline=-3
```

Returns above are post-selection IS evaluation evidence only. Candidate row selection uses branch/calendar metadata and C38 structural guard requirements.

## Candidate Results

```text
C39_BASELINE_C36_CURRENT_BRANCH_BEHAVIOR=EVALUATED
C39_REFERENCE_C37_G16_ONLY_FAILED_COVERAGE_BRANCH_GUARD=EVALUATED_REFERENCE
C39_COVERAGE_GUARD_G16_PLUS_C38_ZERO_MONTH_G21_FALLBACK=EVALUATED
C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA=EVALUATED_AND_FORMED
C39_G21_PRE_TRADE_QUALITY_GATE_FIELD_EXPANSION_REQUIRED=NOT_EVALUABLE
C39_ROLLING_STABILITY_PRE_TRADE_SPLIT_EXPANSION_REQUIRED=NOT_EVALUABLE
```

## Safety Audit

```text
IS_ONLY_CANDIDATE_FORMATION=true
C38_ARTIFACT_HASH_LOCK=true
COVERAGE_GUARD_REQUIRED=true
BRANCH_DIVERSIFICATION_GUARD_REQUIRED=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C38_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
CANDIDATE_REQUIRES_C40_VALIDATION=true
production_ready=false
oos_data_used_for_tuning=false
return_used_for_selection=false
future_path_used_for_selection=false
future_path_price_used_for_selection=false
profile_ret_net_used_for_selection=false
derived_mfe_mae_used_for_execution=false
```

## Validation

```text
PHPUNIT_C39=PASS
PHPUNIT_C39_RESULT=OK (17 tests, 174 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (593 tests, 12464 assertions)
ARTISAN_C39_RUNTIME=COMPLETED
```

## Decision

C39 forms a guarded IS candidate that fixes the C37 zero-pick month and branch concentration blocker while remaining non-production. The candidate is not ready for OOS proof. The next required step is C40 IS validation and anti-overfit check for the guarded C39 candidate.
