# WS C38 IS Redesign Or Evidence Expansion Diagnostic

## Purpose

C38 diagnoses why the C37 candidate failed anti-overfit advancement and defines IS-only redesign/evidence expansion requirements. C38 is not OOS proof, not OOS tuning, not production rollout, not catalog promotion, not best-of-OOS selection, and not PLAN/CONFIRM mutation.

C38 does not select a new candidate. It confirms the IS evidence expansion needed before another controlled redesign candidate can be formed.

## Input C37 Artifact

```text
input_c37_artifact=storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json
expected_c37_hash=5938e353296cb2188b6668093522d0b40d6cb9d2
expected_c37_file_sha1=C17254C01D2405DE8F77999DD7131AEE0663A287
expected_c37_status=C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED
expected_c37_diagnostic_conclusion=C37_CANDIDATE_FAILED_ANTI_OVERFIT_CHECK
expected_c37_next_step=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC
production_ready=false
```

C38 blocks if the C37 artifact is missing, unreadable, hash-mismatched, status-unexpected, not failed anti-overfit input, unexpected next step, production-ready, or OOS-tuning flagged.

## C37 Evidence Summary

```text
overall_anti_overfit_result=FAIL
candidate_c37_decision=C37_CANDIDATE_REQUIRES_IS_REDESIGN_OR_EVIDENCE_EXPANSION
failing_layers=month_coverage_result,overall_anti_overfit_result
warning_layers=rolling_validation_result,branch_concentration_result
source_evidence=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
g21_rows=1770
g16_rows=1320
```

## Boundary C38

```text
IS_ONLY_DIAGNOSTIC=true
C37_ARTIFACT_HASH_LOCK=true
C38_FROM_C37_FAILED_ANTI_OVERFIT=true
NO_NEW_CANDIDATE_SELECTED=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C37_ARTIFACT_MUTATION=true
production_ready=false
```

Return remains evaluation-only. C38 may identify structural failure months from C37 validation, but it does not use return, future path, realized exit, MFE/MAE, profile return, OOS return, or OOS bad-month evidence to select a production rule or candidate.

## IS Period

```text
from=2023-01-02
to=2025-05-21
oos_reserved_from=2025-05-22
oos_reserved_to=2026-05-29
oos_data_used_for_tuning=false
```

## Validation Target Context

```text
baseline_candidate_code=C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR
target_candidate_code=C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR
target_candidate_is_not_production=true
```

## Month Coverage Failure Diagnostic

```text
result=CONFIRMED_REDESIGN_REQUIRED
c37_result=FAIL
c37_reason_code=C37_MONTH_COVERAGE_FAIL
baseline_months_covered=27
candidate_months_covered=26
zero_pick_month_count=1
zero_pick_months=2023-03
zero_pick_month_2023_03_baseline_rows=168
zero_pick_month_2023_03_candidate_rows=0
zero_pick_month_2023_03_g21_rows_available_for_diagnostic=168
zero_pick_month_2023_03_g16_rows_available_for_diagnostic=0
zero_pick_month_2023_03_baseline_avg_ret_net=-0.012346978309652848
zero_pick_month_2023_03_g21_avg_ret_net_evaluation_only=-0.012346978309652848
return_used_for_selection=false
future_path_used_for_selection=false
```

Interpretation: C37's candidate fully suppresses the only available target branch rows in `2023-03`, creating a structural zero-pick month. This requires a pre-trade coverage/diversification guard before any new candidate can be considered.

## Branch Concentration Diagnostic

```text
result=CONFIRMED_BRANCH_DIVERSIFICATION_REQUIRED
c37_result=WARNING
c37_reason_code=C37_BRANCH_CONCENTRATION_WARNING
baseline_selected_rows=3090
baseline_top_branch_share=0.5728155339805825
baseline_g21_share=0.5728155339805825
baseline_g16_share=0.42718446601941745
candidate_selected_rows=1320
candidate_top_branch_share=1
candidate_g21_share=0
candidate_g16_share=1
suppressed_g21_rows=1770
candidate_selected_rows_share_vs_baseline=0.42718446601941745
```

Interpretation: C38 confirms the C36/C37 candidate is 100% G16 after G21 suppression. Future redesign requires a pre-trade G21 retention/diversification guard, not blanket G21 suppression.

## Rolling Warning Diagnostic

```text
result=CONFIRMED_ROLLING_STABILITY_REVIEW_REQUIRED
warning_or_fail_window_count=1
warning_window=2024-06_to_2024-11
warning_window_code=6_month_window
warning_candidate_selected_rows=265
warning_delta_avg_ret_net_vs_baseline=0.008671181552065938
warning_delta_month_win_rate_min_vs_baseline=-0.15254237288135594
warning_g21_rows_in_window_for_diagnostic=420
warning_candidate_rows_in_window=265
warning_baseline_rows_in_window=685
```

Interpretation: Rolling validation does not fail, but the warning window should receive IS split expansion before a new candidate is formed.

## Not-Evaluable Candidate Diagnostic

```text
result=PRE_TRADE_FIELD_EXPANSION_REQUIRED
not_evaluable_count=3
C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD=C36_BLOCKED_G21_EARLIER_EXIT_PRICE_PATH_UNAVAILABLE
C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK=C36_BLOCKED_REGIME_PRE_TRADE_FEATURE_UNAVAILABLE
C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE=C36_BLOCKED_G16_DELAY_DAMAGE_PRE_TRADE_FIELD_UNAVAILABLE
```

C38 keeps these candidates not evaluable and does not force them into evaluated status without pre-trade fields.

## Evidence Expansion Requirements

```text
C38_REQ_MONTH_COVERAGE_GUARD priority=HIGH
C38_REQ_BRANCH_DIVERSIFICATION_GUARD priority=HIGH
C38_REQ_ROLLING_STABILITY_EXPANSION priority=MEDIUM
C38_REQ_PRE_TRADE_FIELD_EXPANSION_FOR_C36_BLOCKED_CANDIDATES priority=MEDIUM
```

Required evidence:

```text
Pre-trade coverage guard or branch fallback evidence that can preserve monthly picks without using realized return.
Pre-trade G21 quality/diversification metadata that can decide whether any G21 row may be retained.
Additional IS split diagnostics for the C37 rolling warning window.
D2 close/path availability, regime pre-trade fields, or gap/delay pre-trade fields depending on blocked C36 candidate.
```

## Redesign Hypotheses

```text
C38_HYP_COVERAGE_GUARD_REQUIRED_BEFORE_OOS=STRONG_IS_SUPPORT
C38_HYP_G21_REINTRODUCTION_REQUIRES_PRE_TRADE_QUALITY_GATE=STRONG_IS_SUPPORT
C38_HYP_ROLLING_WARNING_REQUIRES_IS_SPLIT_EXPANSION=MODERATE_IS_SUPPORT
```

These are diagnostic hypotheses only. No new candidate is selected in C38.

## Decision Summary

```text
requirements_count=4
month_coverage_failure_confirmed=true
branch_concentration_warning_confirmed=true
candidate_c38_decision=C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_NEW_CANDIDATE
candidate_c38_decision_reason=C37 anti-overfit failed; C38 confirms IS redesign/evidence expansion is required before any new candidate formation or OOS proof.
direct_oos_proof_recommended=false
new_candidate_selected=false
production_ready=false
diagnostic_conclusion=C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
next_step_recommendation=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS
```

## Candidate Safety Audit

```text
return_used_for_selection=false
future_path_used_for_selection=false
profile_ret_net_used_for_selection=false
future_path_price_used_for_selection=false
derived_mfe_mae_used_for_execution=false
oos_data_used_for_tuning=false
production_ready=false
candidate_is_not_production=true
no_new_candidate_selected=true
no_oos_proof=true
no_best_of_oos=true
no_oos_winner=true
no_production_catalog=true
no_candidate_promoted=true
no_plan_confirm_mutation=true
```

## Runtime Result

```text
PHPUNIT_C38=PASS
PHPUNIT_C38_RESULT=OK (15 tests, 137 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (576 tests, 12290 assertions)
ARTISAN_C38_RUNTIME=COMPLETED
C38_FINAL_STATUS=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED
C38_ARTIFACT_PATH=storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json
C38_ARTIFACT_HASH=7fe69c9ee9797615df676b0fe0c7378b452da429
C38_FILE_SHA1=74AF66E0170D4C6FF8AE3B7E45F8EC72D9774A7B
DIAGNOSTIC_CONCLUSION=C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
NEXT_STEP=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS
production_ready=false
```

## Final Status

```text
C38_STATUS=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED
C38_DECISION=C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_NEW_CANDIDATE
OOS_PROOF_UNLOCKED=false
NEW_CANDIDATE_SELECTED=false
PRODUCTION_READY=false
```

C38 completed the requested IS-only redesign/evidence expansion diagnostic. The next work is C39 controlled redesign with explicit coverage and branch-diversification guards, still before any OOS proof.
