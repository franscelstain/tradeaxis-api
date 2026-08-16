# WS C51 Operator Validation Commands

## PHPUnit C51 only

```bash
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC51"
```

Expected marker if run in supported PHP environment:

```text
OK (... tests, ... assertions)
```

Do not claim PASS unless this command is actually run by the operator.

## Full Watchlist PHPUnit

```bash
vendor/bin/phpunit tests/Unit/Watchlist
```

Expected marker if run in supported PHP environment:

```text
OK (... tests, ... assertions)
```

Do not claim PASS unless this command is actually run by the operator.

## Runtime C51

```powershell
php artisan watchlist:backtest-c51-concentration-dependency-redesign-review `
  --c50-artifact=storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json `
  --expected-c50-hash=1f2b919662a395444f43403e8f7f4d0b91e146aa `
  --c49-artifact=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json `
  --expected-c49-hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c51-concentration-dependency-redesign-review.json `
  --overwrite `
  --progress
```

Expected completed marker:

```text
status=C51_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED
production_ready=0
c50_hash_match=1
c49_hash_match=1
```

Valid blocked markers include:

```text
C51_BLOCKED_MISSING_C50_ARTIFACT
C51_BLOCKED_C50_HASH_MISMATCH
C51_BLOCKED_UNEXPECTED_C50_STATUS
C51_BLOCKED_UNEXPECTED_C50_CONCLUSION
C51_BLOCKED_C50_NEXT_STEP_UNEXPECTED
C51_BLOCKED_C50_PRODUCTION_READY_NOT_FALSE
C51_BLOCKED_C50_OOS_PROOF_FLAG_INVALID
C51_BLOCKED_MISSING_C50_CONCENTRATION_FAILURE
C51_BLOCKED_MISSING_C49_ARTIFACT
C51_BLOCKED_C49_HASH_MISMATCH
C51_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED
C51_SOURCE_ROWS_NOT_EVALUABLE
```

## Read artifact C51

```powershell
$run = Get-Content storage/app/watchlist/backtest/c51-concentration-dependency-redesign-review.json | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  diagnostic_conclusion,
  next_step_recommendation,
  expected_c50_hash,
  actual_c50_hash,
  c50_hash_match,
  c50_status,
  c50_diagnostic_conclusion,
  c50_next_step_recommendation,
  expected_c49_hash,
  actual_c49_hash,
  c49_hash_match |
  Format-List
```

## Breakdown: C50 hash validation

```powershell
$run | Select-Object `
  input_c50_artifact,
  expected_c50_hash,
  actual_c50_hash,
  c50_hash_match,
  c50_status,
  c50_diagnostic_conclusion,
  c50_next_step_recommendation |
  Format-List
```

## Breakdown: C49 hash validation

```powershell
$run | Select-Object `
  input_c49_artifact,
  expected_c49_hash,
  actual_c49_hash,
  c49_hash_match |
  Format-List
```

## Breakdown: C50 carry-forward

```powershell
$run.c50_carry_forward_summary | Format-List
```

## Breakdown: C50 root cause

```powershell
$run.c50_root_cause_summary | Format-List
```

## Breakdown: IS validation period

```powershell
$run.is_validation_period | Format-List
```

## Breakdown: OOS reserved period

```powershell
$run.oos_reserved_period | Format-List
```

## Breakdown: source reconstruction

```powershell
$run.source_reconstruction_summary | Format-List
```

## Breakdown: redesign candidate definitions

```powershell
$run.redesign_candidate_definitions |
  Select-Object `
    candidate_code,
    candidate_role,
    source_candidates_used,
    selection_rule_description,
    branch_cap,
    bucket_cap,
    branch_quota,
    bucket_quota,
    downsampling_rule,
    backfill_rule,
    loss_cluster_control_rule,
    return_used_for_selection,
    future_path_used_for_selection,
    oos_return_used_for_selection |
  Format-Table -AutoSize
```

## Breakdown: candidate replay

```powershell
$run.candidate_replay_results |
  Select-Object `
    candidate_code,
    profile_code,
    family_code,
    candidate_role,
    evaluated_picks_count,
    avg_ret_net,
    median_ret_net,
    p25_ret_net,
    win_rate,
    month_win_rate_min,
    bad_month_like_count,
    coverage_months,
    quality_pass,
    stability_pass,
    coverage_pass |
  Format-Table -AutoSize
```

## Breakdown: concentration/dependency validation

```powershell
$run.concentration_dependency_validation_results |
  Select-Object `
    candidate_code,
    max_ticker_share,
    max_sector_share,
    max_bucket_share,
    max_branch_share,
    max_month_share,
    unique_ticker_count,
    unique_sector_count,
    unique_bucket_count,
    unique_branch_count,
    loss_cluster_share,
    concentration_validation_pass,
    concentration_validation_level,
    failure_reason_codes |
  Format-List
```

## Breakdown: branch dependency

```powershell
$run.branch_dependency_validation_results |
  Select-Object `
    candidate_code,
    branch_code,
    branch_row_count,
    branch_share,
    branch_avg_ret_net,
    branch_median_ret_net,
    branch_win_rate,
    branch_loss_share,
    branch_dependency_detected |
  Format-Table -AutoSize
```

## Breakdown: bucket dependency

```powershell
$run.bucket_dependency_validation_results |
  Select-Object `
    candidate_code,
    bucket_code,
    bucket_row_count,
    bucket_share,
    bucket_avg_ret_net,
    bucket_median_ret_net,
    bucket_win_rate,
    bucket_loss_share,
    bucket_dependency_detected |
  Format-Table -AutoSize
```

## Breakdown: rolling validation

```powershell
$run.rolling_validation_summary | Format-List
```

## Breakdown: leave-one-month-out

```powershell
$run.leave_one_month_out_summary | Format-List
```

## Breakdown: regime robustness

```powershell
$run.regime_robustness_validation_summary | Format-List
```

## Breakdown: material difference validation

```powershell
$run.material_difference_validation_results |
  Select-Object `
    candidate_code,
    overlap_with_c44,
    overlap_with_f00,
    overlap_with_f03,
    overlap_with_f08,
    shared_core_row_count,
    candidate_only_row_count,
    material_difference_score,
    material_selection_difference_pass,
    anti_shared_core_pass,
    failure_reason_codes |
  Format-Table -AutoSize
```

## Breakdown: source reconstruction bias check

```powershell
$run.source_reconstruction_bias_check | Format-List
```

## Breakdown: candidate scorecard

```powershell
$run.candidate_scorecard |
  Select-Object `
    candidate_code,
    candidate_role,
    evaluated_picks_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    month_win_rate_min,
    max_branch_share,
    max_bucket_share,
    loss_cluster_share,
    concentration_validation_pass,
    rolling_validation_pass,
    loo_validation_pass,
    regime_robustness_validation_pass,
    material_selection_difference_pass,
    anti_shared_core_pass,
    overall_is_redesign_pass,
    anti_overfit_pass,
    candidate_ready_for_c52,
    failure_reason_codes |
  Format-Table -AutoSize
```

## Breakdown: selected C51 candidates for C52

```powershell
$run.selected_c51_candidates_for_c52 | Format-List
```

## Breakdown: C52 readiness decision

```powershell
$run.c52_readiness_decision | Format-List
```

Expected C52 recommendations are limited to:

```text
C52_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C51_REDESIGN
C52_IS_EVIDENCE_EXPANSION_FOR_C51_REDESIGN
C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION
C52_SHARED_CORE_REVERSION_REDESIGN_REQUIRED
C52_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY
```

C51 must not recommend direct OOS proof.

## Breakdown: candidate safety audit

```powershell
$run.candidate_safety_audit |
  Select-Object candidate_code,review_layer,passed,reason_code,message |
  Format-Table -AutoSize
```

## Breakdown: not evaluable reasons

```powershell
$run.not_evaluable_reasons |
  Select-Object validation_layer,validation_slice,reason_code,message |
  Format-Table -AutoSize
```

## Breakdown: diagnostic conclusion

```powershell
$run.diagnostics |
  Select-Object reason_code,message |
  Format-Table -AutoSize
```

## Artifact hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c51-concentration-dependency-redesign-review.json -Algorithm SHA1
```

## PowerShell compatibility guard

```powershell
$keys = $run.safety_boundaries.PSObject.Properties.Name
$normalized = $keys | ForEach-Object { $_.ToLowerInvariant() }
$normalized.Count -eq ($normalized | Select-Object -Unique).Count
```

Expected:

```text
True
```

## Notes

- Do not claim C51 PHPUnit PASS unless PHPUnit C51 was run in the supported environment.
- Do not claim full Watchlist PHPUnit PASS unless the full test suite was run in the supported environment.
- Do not claim Artisan runtime COMPLETED unless the documented Artisan command was run in the supported environment.
- Artifact JSON must remain PowerShell-compatible: no case-insensitive duplicate keys.
- `production_ready` must remain `false`.
- `direct_oos_proof_recommended` must remain `false`.
- `oos_proof_unlocked` must remain `false`.


---

## Final operator validation result

PHPUnit C51 only:

```text
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC51"
OK (14 tests, 378 assertions)
```

Full Watchlist PHPUnit:

```text
vendor/bin/phpunit tests/Unit/Watchlist
OK (749 tests, 14243 assertions)
```

Runtime C51:

```text
C51_IMPLEMENTATION_STATUS=IMPLEMENTED_FINAL
C51_PHPUNIT_STATUS=PASS
C51_PHPUNIT_RESULT=OK (14 tests, 378 assertions)
FULL_WATCHLIST_PHPUNIT_STATUS=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (749 tests, 14243 assertions)
C51_ARTISAN_RUNTIME_STATUS=COMPLETED
ARTIFACT_PATH=storage/app/watchlist/backtest/c51-concentration-dependency-redesign-review.json
C51_ARTISAN_REPORTED_ARTIFACT_HASH=a786034b8e344207592e58efe262287102b0ef36
C51_FILE_SHA1=0BFAD3BC9985602E1FE6318557754ECBE9A63F91
status=C51_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED
diagnostic_conclusion=C51_REDESIGNED_CANDIDATE_OVERFIT_RISK_REMAINS
next_step_recommendation=C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION
production_ready=false
direct_oos_proof_recommended=false
oos_proof_unlocked=false
```

Source lock validation:

```text
expected_c50_hash=1f2b919662a395444f43403e8f7f4d0b91e146aa
actual_c50_hash=1f2b919662a395444f43403e8f7f4d0b91e146aa
c50_hash_match=true
c50_status=C50_IS_VALIDATION_COMPLETED
c50_diagnostic_conclusion=C50_C49_PRIMARY_CANDIDATE_OVERFIT_RISK_IDENTIFIED
c50_next_step_recommendation=C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW
expected_c49_hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8
actual_c49_hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8
c49_hash_match=true
```

Readiness decision:

```text
best_redesigned_candidate_code=null
best_redesigned_profile_code=null
best_redesigned_candidate_pass=false
selected_candidate_count=0
primary_dependency_reduced=false
concentration_validation_pass=false
rolling_validation_pass=false
loo_validation_pass=false
regime_robustness_validation_pass=false
material_difference_validation_pass=false
source_bias_validation_pass=true
anti_overfit_pass=false
c52_recommendation=C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION
decision_reason=concentration_dependency_issue_remains
diagnostic_conclusion=C51_REDESIGNED_CANDIDATE_OVERFIT_RISK_REMAINS
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

PowerShell compatibility and forbidden-key guards:

```text
SAFETY_BOUNDARIES_DUPLICATE_KEY_CHECK=PASS
FORBIDDEN_TOP_LEVEL_KEY_CHECK=PASS
FORBIDDEN_KEYS_PRESENT=false
```

Negative guard validation:

```text
WRONG_C50_HASH_STATUS=C51_BLOCKED_C50_HASH_MISMATCH
WRONG_C50_HASH_PRODUCTION_READY=false
WRONG_C50_HASH_C50_HASH_MATCH=false
OOS_OVERLAP_STATUS=C51_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED
OOS_OVERLAP_PRODUCTION_READY=false
OOS_OVERLAP_USED_FOR_SELECTION=false
OOS_OVERLAP_USED_FOR_TUNING=false
OOS_OVERLAP_USED_FOR_PROOF=false
```

Final instruction:

```text
C51_FINAL_STATUS=C51_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED
C51_STRATEGY_OUTCOME=C51_REDESIGNED_CANDIDATE_OVERFIT_RISK_REMAINS
NEXT_STEP=C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION
DO_NOT_RUN_OOS_PROOF_FROM_C51=true
DO_NOT_CLAIM_PRODUCTION_READY=true
```
