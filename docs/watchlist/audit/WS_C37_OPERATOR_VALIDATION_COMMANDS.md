# WS C37 Operator Validation Commands

C37 is IS validation and anti-overfit checking only. Do not run OOS proof, do not tune from OOS, do not create best-of-OOS, do not promote a catalog, and do not claim production readiness.

## PHPUnit C37 Only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC37"
```

Expected completed marker after the validated run:

```text
PHPUNIT_C37=PASS
PHPUNIT_C37_RESULT=OK (17 tests, 343 assertions)
```

If this command is not run, record:

```text
PHPUNIT_C37=NOT_RUN
PHPUNIT_C37_REASON=OPERATOR_VALIDATION_REQUIRED
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected completed marker after the validated run:

```text
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (561 tests, 12153 assertions)
```

If this command is not run, record:

```text
FULL_WATCHLIST_PHPUNIT=NOT_RUN
FULL_WATCHLIST_PHPUNIT_REASON=OPERATOR_VALIDATION_REQUIRED
```

## C37 Runtime

```powershell
php artisan watchlist:backtest-c37-is-validation-anti-overfit-check `
  --c36-artifact=storage/app/watchlist/backtest/c36-is-controlled-redesign-candidate-formation.json `
  --expected-c36-hash=8bc5198cf3b79fc9b58c39fc19f319826406b4b1 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json `
  --progress
```

If the artifact already exists and the operator intends to rerun:

```powershell
php artisan watchlist:backtest-c37-is-validation-anti-overfit-check `
  --c36-artifact=storage/app/watchlist/backtest/c36-is-controlled-redesign-candidate-formation.json `
  --expected-c36-hash=8bc5198cf3b79fc9b58c39fc19f319826406b4b1 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json `
  --progress `
  --overwrite
```

Expected completed or blocked markers:

```text
C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED
C37_BLOCKED_MISSING_C36_ARTIFACT
C37_BLOCKED_C36_HASH_MISMATCH
C37_BLOCKED_UNEXPECTED_C36_STATUS
C37_BLOCKED_NO_C36_CANDIDATE_FORMED
C37_BLOCKED_C36_PRODUCTION_READY_NOT_FALSE
C37_BLOCKED_C36_OOS_TUNING_FLAG_NOT_FALSE
C37_BLOCKED_C36_BEST_CANDIDATE_PRODUCTION_FLAG_INVALID
C37_BLOCKED_MISSING_C36_BEST_CANDIDATE
C37_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED
C37_BLOCKED_MISSING_IS_EVIDENCE
```

## Read Artifact C37

```powershell
$run = Get-Content storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  diagnostic_conclusion,
  next_step_recommendation,
  expected_c36_hash,
  actual_c36_hash,
  c36_hash_match,
  c36_status,
  c36_diagnostic_conclusion |
  Format-List
```

## C36 Hash Validation Breakdown

```powershell
$run | Select-Object `
  expected_c36_hash,
  actual_c36_hash,
  c36_hash_match,
  c36_status,
  c36_diagnostic_conclusion |
  Format-List
```

## Source C36 Summary

```powershell
$run.source_c36_summary | Format-List
```

## Validation Target

```powershell
$run.validation_target | Format-List
```

## Validation Summary

```powershell
$run.validation_summary | Format-List
```

## Full IS Validation

```powershell
$run.full_is_validation.target_candidate | Format-List
$run.full_is_validation.comparison_vs_baseline | Format-List
```

## Yearly Validation

```powershell
$run.yearly_validation |
  Select-Object `
    validation_slice,
    result,
    reason_code,
    @{Name='selected_rows';Expression={$_.target_candidate.selected_rows}},
    @{Name='avg_ret_net';Expression={$_.target_candidate.avg_ret_net}},
    @{Name='median_ret_net';Expression={$_.target_candidate.median_ret_net}},
    @{Name='p25_ret_net';Expression={$_.target_candidate.p25_ret_net}},
    @{Name='win_rate';Expression={$_.target_candidate.win_rate}},
    @{Name='delta_avg';Expression={$_.comparison_vs_baseline.delta_avg_ret_net_vs_baseline}},
    @{Name='delta_p25';Expression={$_.comparison_vs_baseline.delta_p25_ret_net_vs_baseline}} |
  Format-Table -AutoSize
```

## Rolling Window Validation

```powershell
$run.rolling_window_validation |
  Group-Object result |
  Select-Object Name,Count |
  Format-Table -AutoSize

$run.rolling_window_validation |
  Where-Object {$_.result -ne 'PASS'} |
  Select-Object `
    validation_slice,
    window_code,
    result,
    reason_code,
    @{Name='selected_rows';Expression={$_.target_candidate.selected_rows}},
    @{Name='delta_avg';Expression={$_.comparison_vs_baseline.delta_avg_ret_net_vs_baseline}},
    @{Name='delta_month_win_rate_min';Expression={$_.comparison_vs_baseline.delta_month_win_rate_min_vs_baseline}} |
  Format-Table -AutoSize
```

## Bad-Month-Like Stress Validation

```powershell
$run.bad_month_like_stress_validation.target_candidate | Format-List
$run.bad_month_like_stress_validation.comparison_vs_baseline | Format-List
```

## Non-Bad-Month Validation

```powershell
$run.non_bad_month_validation.target_candidate | Format-List
$run.non_bad_month_validation.comparison_vs_baseline | Format-List
```

## Ticker Concentration Validation

```powershell
$run.ticker_concentration_validation | Format-List
```

## Branch Concentration Validation

```powershell
$run.branch_concentration_validation | Format-List
```

## Month Coverage Validation

```powershell
$run.month_coverage_validation | Format-List
```

## Downside Stability Validation

```powershell
$run.downside_stability_validation | Format-List
```

## Candidate Comparison Table

```powershell
$run.candidate_comparison_table |
  Select-Object `
    validation_layer,
    validation_slice,
    window_code,
    candidate_code,
    selected_rows,
    delta_avg_ret_net_vs_baseline,
    delta_median_ret_net_vs_baseline,
    delta_p25_ret_net_vs_baseline,
    delta_win_rate_vs_baseline,
    delta_bad_month_like_count_vs_baseline,
    delta_loss_concentration_vs_baseline,
    result |
  Format-Table -AutoSize
```

## Anti-Overfit Summary

```powershell
$run.anti_overfit_summary | Format-List
```

## Candidate Safety Audit

```powershell
$run.candidate_safety_audit |
  Select-Object `
    candidate_code,
    validation_layer,
    passed,
    reason_code,
    return_used_for_selection,
    future_path_used_for_selection,
    oos_data_used_for_tuning,
    production_ready,
    candidate_is_not_production,
    no_oos_proof,
    no_best_of_oos,
    no_production_catalog |
  Format-Table -AutoSize
```

## Not Evaluable Reasons

```powershell
$run.not_evaluable_reasons |
  Select-Object validation_layer,validation_slice,reason_code,message |
  Format-Table -AutoSize
```

## Diagnostic Conclusion

```powershell
$run | Select-Object status,diagnostic_conclusion,next_step_recommendation | Format-List
```

Valid C37 diagnostic conclusion markers:

```text
C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED
C37_CANDIDATE_VALIDATED_FOR_OOS_PROOF_NEXT
C37_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS
C37_CANDIDATE_FAILED_ANTI_OVERFIT_CHECK
C37_CANDIDATE_TOO_SPARSE_FOR_VALIDATION
C37_INSUFFICIENT_IS_EVIDENCE_FOR_VALIDATION
C37_NO_VALID_C36_CANDIDATE_FOUND
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json -Algorithm SHA1
```

Record this only after the runtime artifact is actually created by the operator command.

## Final Operator Validation Result

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

Do not claim C37 PASS for future reruns unless these commands are rerun and the output is available. C37 did not run OOS proof and did not unlock production readiness.
