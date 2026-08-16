# WS C55 — Operator Validation Commands

## Final operator validation evidence

```text
PHPUNIT_C55=PASS
PHPUNIT_C55_RESULT=OK (9 tests, 293 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (786 tests, 15445 assertions)
ARTISAN_C55_RUNTIME=COMPLETED
ARTIFACT_PATH=storage/app/watchlist/backtest/c55-rolling-stability-redesign-continuation-is-only.json
ARTIFACT_HASH=a4145d6f356e678d0dadf95be5d356198ebfed79
FILE_SHA1=18875FCAD7FD7CDA6607BB09A60917E853E68D2B
DIAGNOSTIC_CONCLUSION=C55_ROLLING_STABILITY_GAP_REMAINS
NEXT_STEP_RECOMMENDATION=C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
```

Operator validation confirms C55 is technically completed. It also confirms C55 did not solve strategy readiness: `candidate_ready_for_c56_count=0`, `rolling_validation_pass_candidate_count=0`, and `concentration_validation_pass_candidate_count=0`.

## PHPUnit C55 only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC55"
```

Expected marker after operator run:

```text
PHPUNIT_C55=PASS|FAIL|OPERATOR_VALIDATION_REQUIRED
```

Do not claim PASS unless the command is actually run successfully in the documented operator environment.

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected marker:

```text
FULL_WATCHLIST_PHPUNIT=PASS|FAIL|OPERATOR_VALIDATION_REQUIRED
```

## Runtime C55

```powershell
php artisan watchlist:backtest-c55-rolling-stability-redesign-continuation-is-only `
  --c54-artifact=storage/app/watchlist/backtest/c54-rolling-stability-redesign-or-recalibration-is-only.json `
  --expected-c54-hash=8c71a4352a1024dbe985e0f0bb6329f5e1545150 `
  --expected-c54-file-sha1=75410BB1A30A32FFFF9661CAD6818C13E044F7E5 `
  --c53-artifact=storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json `
  --expected-c53-hash=6a1749d723e16b7efdb8aa1d7510388a9475d12c `
  --expected-c53-file-sha1=E35FEFB78B6F1931E54169BD8AABE286CB6F08C2 `
  --c52-artifact=storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json `
  --expected-c52-hash=5dbe51c9d18b175e65cddb60336baf43d6833b72 `
  --expected-c52-file-sha1=DADE6518BFF3912D8A43D7C67073FB803F7CF878 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c55-rolling-stability-redesign-continuation-is-only.json `
  --progress `
  --overwrite
```

Expected completed marker:

```text
status=C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED
production_ready=0
direct_oos_proof_recommended=false
oos_proof_unlocked=false
```

Blocked markers include:

```text
C55_BLOCKED_MISSING_C54_ARTIFACT
C55_BLOCKED_C54_HASH_MISMATCH
C55_BLOCKED_C54_FILE_SHA1_MISMATCH
C55_BLOCKED_UNEXPECTED_C54_STATUS
C55_BLOCKED_UNEXPECTED_C54_CONCLUSION
C55_BLOCKED_C54_NEXT_STEP_UNEXPECTED
C55_BLOCKED_C54_PRODUCTION_READY_NOT_FALSE
C55_BLOCKED_C54_OOS_PROOF_FLAG_INVALID
C55_BLOCKED_MISSING_C54_ROLLING_STABILITY_GAP
C55_BLOCKED_MISSING_C53_ARTIFACT
C55_BLOCKED_C53_HASH_MISMATCH
C55_BLOCKED_C53_FILE_SHA1_MISMATCH
C55_BLOCKED_MISSING_C52_ARTIFACT
C55_BLOCKED_C52_HASH_MISMATCH
C55_BLOCKED_C52_FILE_SHA1_MISMATCH
C55_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED
C55_SOURCE_ROWS_NOT_EVALUABLE
```

## Artifact summary

```powershell
$run = Get-Content storage/app/watchlist/backtest/c55-rolling-stability-redesign-continuation-is-only.json | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  diagnostic_conclusion,
  next_step_recommendation,
  expected_c54_hash,
  actual_c54_hash,
  c54_hash_match,
  expected_c54_file_sha1,
  actual_c54_file_sha1,
  c54_file_sha1_match,
  c54_status,
  c54_diagnostic_conclusion,
  c54_next_step_recommendation,
  expected_c53_hash,
  actual_c53_hash,
  c53_hash_match,
  expected_c53_file_sha1,
  actual_c53_file_sha1,
  c53_file_sha1_match,
  expected_c52_hash,
  actual_c52_hash,
  c52_hash_match,
  expected_c52_file_sha1,
  actual_c52_file_sha1,
  c52_file_sha1_match |
  Format-List
```

## Period checks

```powershell
$run.is_validation_period | Format-List
$run.oos_reserved_period | Format-List
```

Expected:

```text
oos_data_used_for_tuning=false
oos_return_used_for_selection=false
oos_proof_executed=false
used_for_selection=false
used_for_tuning=false
used_for_proof=false
```

## Source lock breakdown

```powershell
$run | Select-Object expected_c54_hash,actual_c54_hash,c54_hash_match,expected_c54_file_sha1,actual_c54_file_sha1,c54_file_sha1_match | Format-List
$run | Select-Object expected_c53_hash,actual_c53_hash,c53_hash_match,expected_c53_file_sha1,actual_c53_file_sha1,c53_file_sha1_match | Format-List
$run | Select-Object expected_c52_hash,actual_c52_hash,c52_hash_match,expected_c52_file_sha1,actual_c52_file_sha1,c52_file_sha1_match | Format-List
```

## Carry-forward layers

```powershell
$run.c54_carry_forward_summary | Format-List
$run.c54_root_cause_summary | Format-List
$run.c53_evidence_carry_forward | Format-List
$run.c52_sector_reconstruction_carry_forward | Format-List
```

## Near-pass attribution

```powershell
$run.near_pass_rolling_attribution_summary | Format-List
$run.near_pass_rolling_attribution_results |
  Select-Object candidate_code,rolling_window_count,rolling_pass_count,rolling_pass_rate,failed_window_count,failed_window_codes,failure_reason_codes |
  Format-Table -AutoSize
```

Expected safety:

```text
failed_window_exclusion_used=false
adverse_month_exclusion_used=false
```

## Source reconstruction

```powershell
$run.source_reconstruction_summary | Format-List
$run.source_reconstruction_bias_check | Format-List
```

## Redesign candidate definitions

```powershell
$run.redesign_candidate_definitions |
  Select-Object `
    candidate_code,
    candidate_role,
    source_candidates_used,
    selection_rule_description,
    branch_cap,
    bucket_cap,
    sector_cap,
    ticker_cap,
    month_cap,
    monthly_quota_rule,
    rolling_smoothing_rule,
    g16_cap_rule,
    g21_backfill_rule,
    g13_limit_rule,
    return_used_for_selection,
    future_path_used_for_selection,
    adverse_month_exclusion_used,
    failed_window_exclusion_used,
    oos_return_used_for_selection |
  Format-Table -AutoSize
```

## Candidate replay

```powershell
$run.candidate_replay_results |
  Select-Object `
    candidate_code,
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
    full_is_stability_pass,
    coverage_pass,
    failure_reason_codes |
  Format-Table -AutoSize
```

## Dependency validations

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

$run.branch_dependency_validation_results | Format-Table -AutoSize
$run.bucket_dependency_validation_results | Format-Table -AutoSize
$run.sector_dependency_validation_results | Format-Table -AutoSize
$run.month_dependency_validation_results | Format-Table -AutoSize
```

## Rolling / LOO / regime / material difference

```powershell
$run.rolling_validation_summary | Format-List
$run.leave_one_month_out_summary | Format-List
$run.regime_robustness_validation_summary | Format-List
$run.material_difference_validation_results | Format-Table -AutoSize
```

## Candidate scorecard and C56 readiness

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
    max_sector_share,
    max_month_share,
    loss_cluster_share,
    full_is_stability_pass,
    concentration_validation_pass,
    rolling_validation_pass,
    loo_validation_pass,
    regime_robustness_validation_pass,
    material_selection_difference_pass,
    anti_shared_core_pass,
    overall_is_redesign_pass,
    anti_overfit_pass,
    candidate_ready_for_c56,
    failure_reason_codes |
  Format-Table -AutoSize

$run.selected_c55_candidates_for_c56 | Format-List
$run.c56_readiness_decision | Format-List
```

## Safety audit / not evaluable / diagnostics

```powershell
$run.candidate_safety_audit |
  Select-Object candidate_code,review_layer,passed,reason_code,message |
  Format-Table -AutoSize

$run.not_evaluable_reasons |
  Select-Object validation_layer,validation_slice,reason_code,message |
  Format-Table -AutoSize

$run.diagnostics |
  Select-Object reason_code,message |
  Format-Table -AutoSize
```

## Artifact SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c55-rolling-stability-redesign-continuation-is-only.json -Algorithm SHA1
```

## PowerShell compatibility check

```powershell
$keys = $run.safety_boundaries.PSObject.Properties.Name
$normalized = $keys | ForEach-Object { $_.ToLowerInvariant() }
if (($normalized | Select-Object -Unique).Count -ne $normalized.Count) { throw "Duplicate safety key after lowercase normalization" }
```

The artifact must not contain duplicate case-insensitive keys in `safety_boundaries`.
