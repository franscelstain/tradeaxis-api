# WS C40 - IS Validation And Anti-Overfit Check For C39 Guarded Candidate

## Scope

C40 validates the locked C39 guarded IS candidate using the same IS-only anti-overfit validation posture as C37.

C40 does not run OOS proof, does not tune from OOS, does not create best-of-OOS, does not create a production catalog, does not promote a candidate, and does not mutate PLAN/CONFIRM behavior.

## Source Lock

```text
source_artifact=storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json
expected_c39_hash=504aaa061054ed2771ed08294d8a0570f08e18db
actual_c39_hash=504aaa061054ed2771ed08294d8a0570f08e18db
c39_hash_match=true
c39_status=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED
c39_diagnostic_conclusion=C39_GUARDED_IS_CANDIDATE_FORMED
c39_next_step=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE
expected_c39_file_sha1=B08233211E335C982E327D6A0C638428B906BFC9
```

## Runtime Output

```text
status=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED
artifact_path=storage/app/watchlist/backtest/c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate.json
artifact_hash=0b40ee2464ed820d47ad0b83acbacd78b440d5bd
file_sha1=306E01AD1274944991F1AFE6CFEBBDB3C0E06BFC
diagnostic_conclusion=C40_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS
next_step_recommendation=C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS
production_ready=false
```

## Validation Target

```text
baseline_candidate_code=C39_BASELINE_C36_CURRENT_BRANCH_BEHAVIOR
target_candidate_code=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
target_candidate_is_not_production=true
source_evidence=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
g21_rows=1770
g16_rows=1320
```

## Validation Summary

```text
total_validation_layers=9
passed_layers=7
warning_layers=2
failed_layers=0
not_evaluable_layers=0
overall_anti_overfit_result=WARNING
candidate_c40_decision=C40_CANDIDATE_REQUIRES_REVIEW_BEFORE_OOS
```

Layer results:

```text
full_is_result=PASS
yearly_validation_result=PASS
rolling_validation_result=WARNING
bad_month_stress_result=PASS
normal_month_result=WARNING
ticker_concentration_result=PASS
branch_concentration_result=PASS
month_coverage_result=PASS
downside_stability_result=PASS
```

## Full IS Evaluation

```text
delta_avg_ret_net_vs_baseline=0.006182075965237786
delta_median_ret_net_vs_baseline=0.007513179188710148
delta_p25_ret_net_vs_baseline=0.005319295229254095
delta_p10_ret_net_vs_baseline=0.01109562412245893
delta_win_rate_vs_baseline=0.15804595352493933
delta_month_win_rate_min_vs_baseline=-0.07894736842105263
delta_month_avg_ret_net_min_vs_baseline=0.0034795072171515656
delta_bad_month_like_count_vs_baseline=-3
delta_loss_concentration_vs_baseline=-0.15804595352493933
```

## Coverage And Branch Guards

Month coverage:

```text
baseline_months_covered=27
candidate_months_covered=27
baseline_min_selected_rows_per_month=40
candidate_min_selected_rows_per_month=13
baseline_zero_pick_months=0
candidate_zero_pick_months=0
month_coverage_result=PASS
```

Branch concentration:

```text
baseline_top_branch_share=0.5728155339805825
candidate_top_branch_share=0.79374624173181
candidate_g16_share=0.79374624173181
candidate_g21_share=0.20625375826819
removed_or_suppressed_g21_rows=1427
branch_concentration_result=PASS
```

## Warning Layers

Rolling warnings:

```text
2023-10_to_2024-03 6_month_window WARNING delta_avg=0.004891344784638333 delta_month_avg_min=-0.008026780276428322 delta_bad_month_like_count=1
2023-07_to_2024-03 9_month_window WARNING delta_avg=0.0034089788146250523 delta_month_avg_min=-0.004826148755050039 delta_bad_month_like_count=1
2023-04_to_2024-03 12_month_window WARNING delta_avg=0.002921899951438923 delta_month_avg_min=-0.004826148755050039 delta_bad_month_like_count=1
```

Non-bad-month warning:

```text
result=WARNING
delta_avg_ret_net_vs_baseline=0.0038820395035978495
delta_p25_ret_net_vs_baseline=0.004967101128253132
delta_win_rate_vs_baseline=0.1364206141962131
delta_month_avg_ret_net_min_vs_baseline=-0.008026780276428322
delta_bad_month_like_count_vs_baseline=1
```

## Safety Audit

```text
IS_ONLY_VALIDATION=true
ANTI_OVERFIT_VALIDATION=true
C39_ARTIFACT_HASH_LOCK=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C39_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
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
PHPUNIT_C40=PASS
PHPUNIT_C40_RESULT=OK (16 tests, 176 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (609 tests, 12640 assertions)
ARTISAN_C40_RUNTIME=COMPLETED
```

## Decision

C40 does not fail the C39 guarded candidate, and it confirms the C39 coverage and branch blockers are fixed. However, C40 has rolling and non-bad-month warnings. Therefore C40 does not unlock direct OOS proof and keeps `production_ready=false`. The next step is C41 IS review or evidence expansion before OOS.
