# WS C42 — Operator Validation Commands

## 1. PHPUnit C42 only

```bash
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC42"
```

Expected marker jika environment valid:

```text
PHPUNIT_C42=PASS
```

Jangan klaim PASS jika command belum dijalankan atau environment memblokir.

## 2. Full Watchlist PHPUnit

```bash
vendor/bin/phpunit tests/Unit/Watchlist
```

Expected marker jika environment valid:

```text
FULL_WATCHLIST_PHPUNIT=PASS
```

## 3. Runtime C42

```powershell
php artisan watchlist:backtest-c42-is-rolling-normal-month-evidence-expansion `
  --c41-artifact=storage/app/watchlist/backtest/c41-is-review-or-evidence-expansion-before-oos.json `
  --expected-c41-hash=fa3afd197cfe07d67d90edf87d69aec81310d791 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c42-is-rolling-normal-month-evidence-expansion.json `
  --progress
```

Expected completed marker:

```text
status=C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED
production_ready=0
c41_hash_match=1
diagnostic_conclusion=C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE
next_step_recommendation=C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC
```

Blocked markers yang valid:

```text
C42_BLOCKED_MISSING_C41_ARTIFACT
C42_BLOCKED_C41_HASH_MISMATCH
C42_BLOCKED_UNEXPECTED_C41_STATUS
C42_BLOCKED_UNEXPECTED_C41_CONCLUSION
C42_BLOCKED_C41_PRODUCTION_READY_NOT_FALSE
C42_BLOCKED_C41_OOS_TUNING_FLAG_NOT_FALSE
C42_BLOCKED_MISSING_C41_TARGET_CANDIDATE
C42_BLOCKED_C41_DIRECT_OOS_FLAG_INVALID
C42_BLOCKED_C41_OOS_UNLOCK_FLAG_INVALID
C42_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED
C42_BLOCKED_MISSING_IS_EVIDENCE
```

## 4. Artifact summary

```powershell
$run = Get-Content storage/app/watchlist/backtest/c42-is-rolling-normal-month-evidence-expansion.json | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  diagnostic_conclusion,
  next_step_recommendation,
  expected_c41_hash,
  actual_c41_hash,
  c41_hash_match,
  c41_status,
  c41_diagnostic_conclusion |
  Format-List
```

## 5. C41 hash validation

```powershell
$run | Select-Object `
  input_c41_artifact,
  expected_c41_hash,
  actual_c41_hash,
  c41_hash_match,
  c41_status,
  c41_diagnostic_conclusion,
  c41_next_step_recommendation |
  Format-List
```

## 6. Source C41 summary

```powershell
$run.source_c41_summary | Format-List
```

Expected key values:

```text
target_candidate_code=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
overall_anti_overfit_result=WARNING
warning_layers_count=2
failed_layers_count=0
rolling_warning_windows=3
non_bad_month_warning=True
guard_blockers_resolved=True
direct_oos_proof_recommended=False
oos_proof_unlocked=False
production_ready=False
```

## 7. Warning window expansion

```powershell
$run.warning_window_expansion |
  Select-Object `
    validation_slice,
    window_code,
    result_from_c41,
    suspected_warning_month,
    target_selected_rows,
    baseline_selected_rows,
    target_month_avg_ret_net_min,
    baseline_month_avg_ret_net_min,
    delta_avg_ret_net_vs_baseline,
    delta_month_avg_ret_net_min_vs_baseline,
    delta_bad_month_like_count_vs_baseline,
    target_g16_rows,
    target_g21_rows,
    target_g16_avg_ret_net,
    target_g21_avg_ret_net,
    target_g21_win_rate,
    candidate_warning_explained,
    candidate_warning_explanation_code |
  Format-Table -AutoSize
```

## 8. Non-bad-month warning expansion

```powershell
$run.non_bad_month_warning_expansion | Format-List
```

## 9. Guard preservation audit

```powershell
$run.guard_preservation_audit | Format-List
```

Expected marker:

```text
coverage_guard_preserved=True
branch_guard_preserved=True
c39_guard_preservation_result=PASS
```

## 10. Pre-trade field availability matrix

```powershell
$run.pre_trade_field_availability_matrix |
  Select-Object `
    field_name,
    available,
    source_artifact,
    safe_for_selection,
    safe_for_diagnostic_only,
    field_classification,
    unsafe_reason |
  Format-Table -AutoSize
```

Expected classifications:

```text
SAFE_PRE_TRADE_SELECTION_FIELD
DIAGNOSTIC_ONLY_EVALUATION_FIELD
UNSAFE_FUTURE_OR_RETURN_FIELD
UNAVAILABLE_FIELD
```

## 11. Guard refinement feasibility

```powershell
$run.guard_refinement_feasibility | Format-List
```

Expected marker for current C42 artifact:

```text
feasibility_result=C42_NO_ADDITIONAL_SAFE_REFINEMENT_FIELD_AVAILABLE
safe_refinement_field_available=False
safe_refinement_candidate_formed=False
```

## 12. Refinement candidate results

```powershell
$run.refinement_candidate_results |
  Select-Object `
    candidate_code,
    candidate_status,
    selected_rows,
    avg_ret_net,
    median_ret_net,
    p25_ret_net,
    win_rate,
    month_win_rate_min,
    month_avg_ret_net_min,
    bad_month_like_count,
    months_covered,
    zero_pick_months,
    branch_concentration,
    coverage_guard_preserved,
    branch_guard_preserved,
    return_used_for_selection,
    future_path_used_for_selection,
    oos_data_used_for_tuning,
    production_ready |
  Format-Table -AutoSize
```

Current expected: empty array, because C42 did not form a refinement candidate.

## 13. Candidate comparison table

```powershell
$run.candidate_comparison_table |
  Select-Object `
    candidate_code,
    selected_rows,
    avg_ret_net,
    median_ret_net,
    p25_ret_net,
    win_rate,
    month_win_rate_min,
    month_avg_ret_net_min,
    bad_month_like_count,
    delta_avg_ret_net_vs_baseline,
    delta_median_ret_net_vs_baseline,
    delta_p25_ret_net_vs_baseline,
    delta_win_rate_vs_baseline,
    delta_month_win_rate_min_vs_baseline,
    delta_month_avg_ret_net_min_vs_baseline,
    delta_bad_month_like_count_vs_baseline |
  Format-Table -AutoSize
```

## 14. Warning explanation summary

```powershell
$run.warning_explanation_summary | Format-List
```

Expected marker:

```text
rolling_warning_explanation_result=C42_ROLLING_WARNING_EXPLAINED
normal_month_warning_explanation_result=C42_NORMAL_MONTH_WARNING_EXPLAINED
candidate_warning_explained=True
candidate_warning_acceptable_for_direct_oos=False
```

## 15. C42 decision summary

```powershell
$run.c42_decision_summary | Format-List
```

Expected marker:

```text
c39_candidate_lock_decision=C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE
c42_candidate_decision=C42_C39_CANDIDATE_REQUIRES_GUARD_REFINEMENT_BEFORE_OOS
direct_oos_proof_recommended=False
oos_proof_unlocked=False
requires_c43_oos_proof=False
production_ready=False
```

## 16. Candidate safety audit

```powershell
$run.candidate_safety_audit |
  Select-Object candidate_code,review_layer,passed,reason_code,message,return_used_for_selection,future_path_used_for_selection,oos_data_used_for_tuning,production_ready,no_oos_proof,no_production_catalog,no_plan_confirm_mutation |
  Format-Table -AutoSize
```

## 17. Not evaluable reasons

```powershell
$run.not_evaluable_reasons |
  Select-Object validation_layer,validation_slice,reason_code,message |
  Format-Table -AutoSize
```

## 18. Diagnostic conclusion

```powershell
$run | Select-Object status,diagnostic_conclusion,next_step_recommendation,production_ready | Format-List
```

## 19. Artifact hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c42-is-rolling-normal-month-evidence-expansion.json -Algorithm SHA1
```

Final operator validation marker:

```text
artifact_hash=939e85f179b3bf5d2511730fafb4271cf7c2ca11
file_sha1=CBB44B864DD9B2071DE5B10C426F01ED2776525D
```

## 20. Final operator validation evidence

Operator validation final:

```text
PHPUNIT_C42=PASS — OK (12 tests, 97 assertions)
FULL_WATCHLIST_PHPUNIT=PASS — OK (639 tests, 12860 assertions)
ARTISAN_C42_RUNTIME=COMPLETED
status=C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED
reason_code=C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED
artifact_hash=939e85f179b3bf5d2511730fafb4271cf7c2ca11
file_sha1=CBB44B864DD9B2071DE5B10C426F01ED2776525D
production_ready=0
diagnostic_conclusion=C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE
next_step_recommendation=C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC
```

Jika artifact sudah ada, command akan return `WATCHLIST_BACKTEST_ARTIFACT_EXISTS`. Accepted operator procedure: backup artifact lama, hapus output path, lalu rerun command clean.
