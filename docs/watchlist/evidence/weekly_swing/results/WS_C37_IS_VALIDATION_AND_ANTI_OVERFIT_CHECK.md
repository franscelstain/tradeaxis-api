# WS C37 IS Validation And Anti-Overfit Check

## Purpose

C37 validates the C36 candidate inside IS only. C37 is not OOS proof, not OOS tuning, not production rollout, not catalog promotion, not best-of-OOS selection, and not PLAN/CONFIRM mutation.

C37 answers whether `C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR` is robust enough inside IS to be locked for a later C38 OOS proof. C37 itself does not run OOS proof and does not claim production readiness.

## Input C36 Artifact

```text
input_c36_artifact=storage/app/watchlist/backtest/c36-is-controlled-redesign-candidate-formation.json
expected_c36_hash=8bc5198cf3b79fc9b58c39fc19f319826406b4b1
expected_c36_file_sha1=A5D7E25594238C2743E5DB2E68657AE95BA8B927
expected_c36_status=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED
expected_c36_diagnostic_conclusion=C36_COMBINED_CANDIDATE_FORMED
production_ready=false
```

C37 blocks if the C36 artifact is missing, unreadable, hash-mismatched, status-unexpected, candidate-missing, production-ready, or OOS-tuning flagged.

## C36 Evidence Summary

```text
c36_status=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED
c36_diagnostic_conclusion=C36_COMBINED_CANDIDATE_FORMED
candidate_formed=true
best_is_candidate_code=C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR
best_is_candidate_is_not_production=true
source_evidence=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
g21_rows=1770
g16_rows=1320
```

## Boundary C37

```text
IS_ONLY_VALIDATION=true
ANTI_OVERFIT_VALIDATION=true
C36_ARTIFACT_HASH_LOCK=true
C37_CANDIDATE_FROM_C36_CANDIDATE=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C36_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
production_ready=false
```

Return is used only as post-selection evaluation evidence. Candidate selection remains the C36 branch/bucket rule, not realized return, future path, MFE/MAE, exit result, OOS return, or OOS bad-month evidence.

## IS-Only Validation Rule

```text
from=2023-01-02
to=2025-05-21
oos_reserved_from=2025-05-22
oos_reserved_to=2026-05-29
oos_data_used_for_tuning=false
```

C37 rejects periods touching `2025-05-22` or later.

## Validation Target

```text
baseline_candidate_code=C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR
target_candidate_code=C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR
target_candidate_is_not_production=true
secondary_candidate_codes=C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE,C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE
```

## Full IS Validation Summary

```text
result=PASS
selected_rows=1320
avg_ret_net=0.011291069675265837
median_ret_net=0.015366845779139255
p25_ret_net=-0.0005000750112516877
p10_ret_net=-0.004498875281179705
win_rate=0.7196969696969697
month_win_rate_min=0
month_avg_ret_net_min=-0.009164590269622934
bad_month_like_count=5
loss_concentration=0.2803030303030303
months_covered=26
zero_pick_months=1
delta_avg_ret_net_vs_baseline=0.008526983869452956
delta_median_ret_net_vs_baseline=0.008237257989813554
delta_p25_ret_net_vs_baseline=0.00531942029803442
delta_p10_ret_net_vs_baseline=0.013388279580765074
delta_win_rate_vs_baseline=0.19283612827302155
delta_bad_month_like_count_vs_baseline=-4
delta_loss_concentration_vs_baseline=-0.1928361282730215
```

## Yearly Validation Summary

```text
2023 result=PASS selected_rows=535 avg_ret_net=0.010269305876787078 median_ret_net=0.015978187869006927 p25_ret_net=0.0031534509943543054 win_rate=0.7570093457943925 delta_avg=0.006977231623805074 delta_p25=0.009271499236669135 bad_month_like_count=1
2024 result=PASS selected_rows=595 avg_ret_net=0.010700437504033251 median_ret_net=0.014761259225787016 p25_ret_net=-0.0005002000800320128 win_rate=0.5966386554621849 delta_avg=0.00939776316377679 delta_p25=0.005319295229254095 bad_month_like_count=4
2025_partial_to_2025_05_21 result=PASS selected_rows=190 avg_ret_net=0.01601775269668465 median_ret_net=0.017116260447425053 p25_ret_net=0.016510508989617624 win_rate=1 delta_avg=0.010299263323155191 delta_p25=0.01701058400086931 bad_month_like_count=0
```

## Rolling Window Validation Summary

```text
rolling_windows_total=57
rolling_windows_pass=56
rolling_windows_warning=1
rolling_windows_fail=0
rolling_validation_result=WARNING
warning_window=2024-06_to_2024-11
warning_window_code=6_month_window
warning_selected_rows=265
warning_delta_avg_ret_net_vs_baseline=0.008671181552065938
warning_delta_month_win_rate_min_vs_baseline=-0.15254237288135594
```

## Bad-Month-Like Stress Validation Summary

```text
result=PASS
selected_rows=345
avg_ret_net=0.003430306238965614
median_ret_net=-0.0005000750112516877
p25_ret_net=-0.004498875281179705
p10_ret_net=-0.005358677518831202
win_rate=0.36231884057971014
bad_month_like_count=5
loss_concentration=0.6376811594202898
```

## Non-Bad-Month Validation Summary

```text
result=PASS
selected_rows=975
avg_ret_net=0.014072570583495137
median_ret_net=0.01634822727044782
p25_ret_net=0.014605892722236212
p10_ret_net=-0.0005007360820405997
win_rate=0.8461538461538461
bad_month_like_count=0
loss_concentration=0.15384615384615385
```

## Ticker Concentration Validation Summary

```text
result=PASS
baseline_top_1_ticker_share=0.08414239482200647
candidate_top_1_ticker_share=0.08333333333333333
candidate_unique_ticker_count=51
delta_top_1_ticker_share_vs_baseline=-0.0008090614886731434
delta_top_3_ticker_share_vs_baseline=0.06176816710797292
delta_top_5_ticker_share_vs_baseline=0.09364518976169461
delta_loss_top_1_ticker_share_vs_baseline=0.03578955152142567
delta_loss_top_3_ticker_share_vs_baseline=0.2308204237068806
```

## Branch Concentration Validation Summary

```text
result=WARNING
baseline_top_branch_share=0.5728155339805825
candidate_top_branch_share=1
candidate_g21_share=0
candidate_g16_share=1
removed_or_suppressed_g21_rows=1770
kept_g16_rows=1320
selected_rows_share_vs_baseline=0.42718446601941745
reason=C37 suppresses G21 and keeps G16 as comparator, increasing branch concentration.
```

## Month Coverage Validation Summary

```text
result=FAIL
baseline_months_covered=27
candidate_months_covered=26
baseline_min_selected_rows_per_month=40
candidate_min_selected_rows_per_month=0
baseline_median_selected_rows_per_month=115
candidate_median_selected_rows_per_month=45
zero_pick_months=1
reason=Candidate creates one zero-pick IS month.
```

## Downside Stability Validation Summary

```text
result=PASS
candidate_p25_ret_net=-0.0005000750112516877
candidate_p10_ret_net=-0.004498875281179705
candidate_worst_month_avg_ret_net=-0.009164590269622934
candidate_bad_month_like_count=5
candidate_loss_concentration=0.2803030303030303
delta_p25_ret_net_vs_baseline=0.00531942029803442
delta_p10_ret_net_vs_baseline=0.013388279580765074
delta_month_avg_ret_net_min_vs_baseline=0.0031823880400299147
delta_bad_month_like_count_vs_baseline=-4
delta_loss_concentration_vs_baseline=-0.1928361282730215
```

## Candidate Comparison Table

```text
FULL_IS delta_avg=0.008526983869452956 delta_median=0.008237257989813554 delta_p25=0.00531942029803442 delta_win_rate=0.19283612827302155 delta_bad_month_like_count=-4 result=PASS
YEARLY_2023 delta_avg=0.006977231623805074 delta_p25=0.009271499236669135 delta_win_rate=0.16976399592725344 delta_bad_month_like_count=-1 result=PASS
YEARLY_2024 delta_avg=0.00939776316377679 delta_p25=0.005319295229254095 delta_win_rate=0.14812448821961405 delta_bad_month_like_count=-2 result=PASS
YEARLY_2025_PARTIAL delta_avg=0.010299263323155191 delta_p25=0.01701058400086931 delta_win_rate=0.38521400778210113 delta_bad_month_like_count=-1 result=PASS
ROLLING_WARNING_2024-06_to_2024-11 delta_avg=0.008671181552065938 delta_month_win_rate_min=-0.15254237288135594 result=WARNING
MONTH_COVERAGE delta_months_covered=-1 delta_min_selected_rows_per_month=-40 result=FAIL
```

## Anti-Overfit Summary

```text
full_is_result=PASS
yearly_validation_result=PASS
rolling_validation_result=WARNING
bad_month_stress_result=PASS
normal_month_result=PASS
ticker_concentration_result=PASS
branch_concentration_result=WARNING
month_coverage_result=FAIL
downside_stability_result=PASS
overall_anti_overfit_result=FAIL
candidate_c37_decision=C37_CANDIDATE_REQUIRES_IS_REDESIGN_OR_EVIDENCE_EXPANSION
candidate_c37_decision_reason=Candidate failed at least one material IS anti-overfit validation layer.
```

## Candidate Safety Audit

```text
return_used_for_selection=false
future_path_used_for_selection=false
profile_ret_net_used_for_selection=false
future_path_price_used_for_selection=false
derived_mfe_mae_used_for_execution=false
oos_data_used_for_tuning=false
no_oos_proof=true
no_best_of_oos=true
no_production_catalog=true
no_candidate_promoted=true
production_ready=false
candidate_is_not_production=true
```

The safety audit preserves selection-input safety. The advancement decision is not safe to advance because `MONTH_COVERAGE` is `FAIL`.

## Not Evaluable Reasons

```text
C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD=C36_BLOCKED_G21_EARLIER_EXIT_PRICE_PATH_UNAVAILABLE
C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK=C36_BLOCKED_REGIME_PRE_TRADE_FEATURE_UNAVAILABLE
C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE=C36_BLOCKED_G16_DELAY_DAMAGE_PRE_TRADE_FIELD_UNAVAILABLE
```

## Runtime Result

```text
PHPUNIT_C37=PASS
PHPUNIT_C37_RESULT=OK (17 tests, 343 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (561 tests, 12153 assertions)
ARTISAN_C37_RUNTIME=COMPLETED
C37_FINAL_STATUS=C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED
C37_ARTIFACT_PATH=storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json
C37_ARTIFACT_HASH=5938e353296cb2188b6668093522d0b40d6cb9d2
C37_FILE_SHA1=C17254C01D2405DE8F77999DD7131AEE0663A287
DIAGNOSTIC_CONCLUSION=C37_CANDIDATE_FAILED_ANTI_OVERFIT_CHECK
NEXT_STEP=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC
production_ready=false
```

## Final Status

```text
C37_STATUS=C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED
C37_ANTI_OVERFIT_RESULT=FAIL
C37_CANDIDATE_DECISION=C37_CANDIDATE_REQUIRES_IS_REDESIGN_OR_EVIDENCE_EXPANSION
OOS_PROOF_UNLOCKED=false
PRODUCTION_READY=false
```

C37 completed its IS-only validation but does not validate the candidate for C38 OOS proof. The candidate improves many return/downside metrics, but the zero-pick IS month and branch concentration warning make it too fragile for direct OOS proof without C38 IS redesign or evidence expansion diagnostic.
