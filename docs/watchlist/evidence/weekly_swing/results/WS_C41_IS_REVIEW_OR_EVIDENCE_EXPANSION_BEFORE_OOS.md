# WS C41 - IS Review Or Evidence Expansion Before OOS

## Scope

C41 reviews the locked C40 warning artifact before any OOS proof. It expands the audit evidence around the remaining IS warnings and keeps the C39 guarded candidate non-production.

C41 does not run OOS proof, does not tune from OOS, does not create best-of-OOS, does not create a production catalog, does not select a new candidate, does not promote a candidate, and does not mutate PLAN/CONFIRM behavior.

## Source Lock

```text
source_artifact=storage/app/watchlist/backtest/c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate.json
expected_c40_hash=0b40ee2464ed820d47ad0b83acbacd78b440d5bd
actual_c40_hash=0b40ee2464ed820d47ad0b83acbacd78b440d5bd
c40_hash_match=true
c40_status=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED
c40_diagnostic_conclusion=C40_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS
c40_next_step=C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS
expected_c40_file_sha1=306E01AD1274944991F1AFE6CFEBBDB3C0E06BFC
```

## Runtime Output

```text
status=C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED
artifact_path=storage/app/watchlist/backtest/c41-is-review-or-evidence-expansion-before-oos.json
artifact_hash=fa3afd197cfe07d67d90edf87d69aec81310d791
file_sha1=9B44AD084DBD7637E0794A8AF5085E3A846D9486
diagnostic_conclusion=C41_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
next_step_recommendation=C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_OR_GUARD_REFINEMENT
production_ready=false
```

## Review Target

```text
target_candidate_code=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
source_c40_overall_anti_overfit_result=WARNING
source_c40_warning_layers=2
source_c40_failed_layers=0
source_c40_not_evaluable_layers=0
candidate_c40_decision=C40_CANDIDATE_REQUIRES_REVIEW_BEFORE_OOS
```

## Warning Layer Review

Rolling warning windows:

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

## Guard Blocker Recheck

```text
month_coverage_result=PASS
candidate_months_covered=27
candidate_zero_pick_months=0
candidate_min_selected_rows_per_month=13
branch_concentration_result=PASS
candidate_top_branch_share=0.79374624173181
candidate_g16_share=0.79374624173181
candidate_g21_share=0.20625375826819
removed_or_suppressed_g21_rows=1427
prior_c37_coverage_branch_blocker_resolved=true
```

## Evidence Expansion Requirements

```text
C41_REQ_ROLLING_WARNING_WINDOW_PRE_TRADE_SPLIT_REVIEW=REQUIRED_BEFORE_OOS
C41_REQ_NON_BAD_MONTH_STABILITY_REVIEW=REQUIRED_BEFORE_OOS
C41_REQ_G21_PRE_TRADE_QUALITY_FIELD_EXPANSION=REQUIRED_BEFORE_OOS
C41_REQ_ROLLING_STABILITY_PRE_TRADE_SPLIT_FIELD_EXPANSION=REQUIRED_BEFORE_OOS
C41_REQ_PRESERVE_C39_COVERAGE_BRANCH_GUARDS=PRESERVE
```

Carry-forward evidence gaps:

```text
C39_G21_PRE_TRADE_QUALITY_GATE_FIELD_EXPANSION_REQUIRED
C39_ROLLING_STABILITY_PRE_TRADE_SPLIT_EXPANSION_REQUIRED
```

## Decision Summary

```text
candidate_decision=C41_REQUIRES_EVIDENCE_EXPANSION_BEFORE_OOS
rolling_warning_windows=3
non_bad_month_warning=true
carry_forward_gap_count=2
guard_blockers_resolved=true
evidence_requirements_count=5
direct_oos_proof_recommended=false
oos_proof_unlocked=false
new_candidate_selected=false
candidate_reselected=false
production_ready=false
```

## Safety Audit

```text
IS_ONLY_REVIEW=true
EVIDENCE_EXPANSION_REVIEW_ONLY=true
C40_ARTIFACT_HASH_LOCK=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C40_ARTIFACT_MUTATION=true
NO_C41_CANDIDATE_RESELECTION=true
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
PHPUNIT_C41=PASS
PHPUNIT_C41_RESULT=OK (18 tests, 123 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (627 tests, 12763 assertions)
ARTISAN_C41_RUNTIME=COMPLETED
```

## Decision

C41 confirms that the C39 guarded candidate still has no failed C40 validation layers and that the C37 month coverage and branch concentration blockers remain resolved. However, rolling and non-bad-month warnings remain and C40 carries forward missing pre-trade evidence gaps. Therefore C41 does not unlock direct OOS proof and keeps `production_ready=false`. The next step is C42 IS rolling/normal-month evidence expansion or guard refinement.
