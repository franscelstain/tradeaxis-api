# WS C38 Operator Validation Commands

C38 is IS redesign/evidence expansion diagnostic only. Do not run OOS proof, do not tune from OOS, do not create best-of-OOS, do not promote a catalog, and do not claim production readiness.

## PHPUnit C38 Only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC38"
```

Expected completed marker after the validated run:

```text
PHPUNIT_C38=PASS
PHPUNIT_C38_RESULT=OK (15 tests, 137 assertions)
```

If this command is not run, record:

```text
PHPUNIT_C38=NOT_RUN
PHPUNIT_C38_REASON=OPERATOR_VALIDATION_REQUIRED
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected completed marker after the validated run:

```text
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (576 tests, 12290 assertions)
```

If this command is not run, record:

```text
FULL_WATCHLIST_PHPUNIT=NOT_RUN
FULL_WATCHLIST_PHPUNIT_REASON=OPERATOR_VALIDATION_REQUIRED
```

## C38 Runtime

```powershell
php artisan watchlist:backtest-c38-is-redesign-evidence-expansion-diagnostic `
  --c37-artifact=storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json `
  --expected-c37-hash=5938e353296cb2188b6668093522d0b40d6cb9d2 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json `
  --progress
```

If the artifact already exists and the operator intends to rerun:

```powershell
php artisan watchlist:backtest-c38-is-redesign-evidence-expansion-diagnostic `
  --c37-artifact=storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json `
  --expected-c37-hash=5938e353296cb2188b6668093522d0b40d6cb9d2 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json `
  --progress `
  --overwrite
```

Expected completed or blocked markers:

```text
C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED
C38_BLOCKED_MISSING_C37_ARTIFACT
C38_BLOCKED_C37_HASH_MISMATCH
C38_BLOCKED_UNEXPECTED_C37_STATUS
C38_BLOCKED_C37_NOT_FAILED_ANTI_OVERFIT_INPUT
C38_BLOCKED_UNEXPECTED_C37_NEXT_STEP
C38_BLOCKED_C37_PRODUCTION_READY_NOT_FALSE
C38_BLOCKED_C37_OOS_TUNING_FLAG_NOT_FALSE
C38_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED
C38_BLOCKED_MISSING_IS_EVIDENCE
```

## Read Artifact C38

```powershell
$run = Get-Content storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  diagnostic_conclusion,
  next_step_recommendation,
  expected_c37_hash,
  actual_c37_hash,
  c37_hash_match,
  c37_status,
  c37_diagnostic_conclusion |
  Format-List
```

## C37 Hash Validation Breakdown

```powershell
$run | Select-Object `
  expected_c37_hash,
  actual_c37_hash,
  c37_hash_match,
  c37_status,
  c37_diagnostic_conclusion,
  c37_next_step_recommendation |
  Format-List
```

## Source C37 Summary

```powershell
$run.source_c37_summary | Format-List
```

## Validation Target

```powershell
$run.validation_target | Format-List
```

## Month Coverage Failure Diagnostic

```powershell
$run.month_coverage_failure_diagnostic | Format-List

$run.month_coverage_failure_diagnostic.zero_pick_month_details |
  Select-Object `
    trade_month,
    baseline_rows,
    candidate_rows,
    g21_rows_available_for_diagnostic,
    g16_rows_available_for_diagnostic,
    baseline_avg_ret_net,
    g21_avg_ret_net_evaluation_only,
    return_used_for_selection,
    future_path_used_for_selection |
  Format-Table -AutoSize
```

## Branch Concentration Diagnostic

```powershell
$run.branch_concentration_diagnostic | Format-List
```

## Rolling Warning Diagnostic

```powershell
$run.rolling_warning_diagnostic | Format-List

$run.rolling_warning_diagnostic.warning_or_fail_windows |
  Select-Object `
    validation_slice,
    window_code,
    result,
    reason_code,
    candidate_selected_rows,
    delta_avg_ret_net_vs_baseline,
    delta_month_win_rate_min_vs_baseline,
    g21_rows_in_window_for_diagnostic,
    candidate_rows_in_window,
    baseline_rows_in_window |
  Format-Table -AutoSize
```

## Not-Evaluable Candidate Diagnostic

```powershell
$run.not_evaluable_candidate_diagnostic | Format-List

$run.not_evaluable_candidate_diagnostic.items |
  Select-Object candidate_code,reason_code,c38_interpretation |
  Format-Table -AutoSize
```

## Evidence Expansion Requirements

```powershell
$run.evidence_expansion_requirements |
  Select-Object requirement_code,priority,reason,required_evidence |
  Format-Table -AutoSize
```

## Redesign Hypotheses

```powershell
$run.redesign_hypotheses |
  Select-Object hypothesis_code,support,basis,candidate_selected,production_ready |
  Format-Table -AutoSize
```

## C38 Decision Summary

```powershell
$run.c38_decision_summary | Format-List
```

## Candidate Safety Audit

```powershell
$run.candidate_safety_audit |
  Select-Object `
    validation_layer,
    passed,
    reason_code,
    return_used_for_selection,
    future_path_used_for_selection,
    oos_data_used_for_tuning,
    production_ready,
    no_new_candidate_selected,
    no_oos_proof,
    no_best_of_oos,
    no_production_catalog |
  Format-Table -AutoSize
```

## Diagnostic Conclusion

```powershell
$run | Select-Object status,diagnostic_conclusion,next_step_recommendation | Format-List
```

Valid C38 diagnostic conclusion markers:

```text
C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_BLOCKED
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json -Algorithm SHA1
```

Record this only after the runtime artifact is actually created by the operator command.

## Final Operator Validation Result

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

Do not claim C38 PASS for future reruns unless these commands are rerun and the output is available. C38 did not run OOS proof, did not select a new candidate, and did not unlock production readiness.
