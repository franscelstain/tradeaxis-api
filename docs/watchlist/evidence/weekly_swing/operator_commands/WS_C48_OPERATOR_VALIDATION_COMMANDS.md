# WS C48 - Operator Validation Commands

## PHPUnit C48 only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC48"
```

Expected completed marker:

```text
C48 PHPUnit: PASS
```

Do not claim PASS if this command is not run in the supported operator environment.

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected completed marker:

```text
Full Watchlist PHPUnit: PASS
```

Do not claim PASS if this command is not run in the supported operator environment.

## Runtime C48

```powershell
php artisan watchlist:backtest-c48-oos-failure-attribution `
  --c47-artifact=storage/app/watchlist/backtest/c47-oos-proof-with-locked-c44-refinement.json `
  --expected-c47-hash=1c742e257847752def1f582dc24d6061a4c4e735 `
  --from=2025-05-22 `
  --to=2026-05-29 `
  --output=storage/app/watchlist/backtest/c48-oos-failure-attribution.json `
  --progress
```

Expected completed marker:

```text
status=C48_OOS_FAILURE_ATTRIBUTION_COMPLETED
production_ready=0
c47_hash_match=1
c47_status=C47_OOS_PROOF_FAILED
c47_diagnostic_conclusion=C47_LOCKED_C44_REFINEMENT_OOS_PROOF_FAILED
```

If the artifact already exists during validation, either remove the artifact first or use `--overwrite` only to validate the same locked input. Do not use `--overwrite` to tune, search, or retry OOS proof.

## Read artifact summary

```powershell
$run = Get-Content storage/app/watchlist/backtest/c48-oos-failure-attribution.json | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  diagnostic_conclusion,
  next_step_recommendation,
  expected_c47_hash,
  actual_c47_hash,
  c47_hash_match,
  c47_status,
  c47_diagnostic_conclusion |
  Format-List
```

## C47 hash validation

```powershell
$run | Select-Object `
  input_c47_artifact,
  expected_c47_hash,
  actual_c47_hash,
  c47_hash_match,
  c47_status,
  c47_diagnostic_conclusion,
  c47_next_step_recommendation |
  Format-List
```

## Source C47 summary

```powershell
$run.source_c47_summary | Format-List
```

## Month failure attribution

```powershell
$run.month_failure_attribution |
  Select-Object `
    trade_month,
    target_selected_rows,
    baseline_selected_rows,
    target_avg_ret_net,
    baseline_avg_ret_net,
    target_win_rate,
    target_loss_count,
    target_bad_like_month,
    target_vs_baseline_delta_avg_ret_net,
    target_vs_baseline_delta_win_rate |
  Format-Table -AutoSize
```

## Branch failure attribution

```powershell
$run.branch_failure_attribution |
  Select-Object `
    selected_source_code,
    row_count,
    avg_ret_net,
    median_ret_net,
    p25_ret_net,
    p10_ret_net,
    win_rate,
    loss_count,
    loss_share,
    bad_month_like_contribution,
    failure_dominant_branch |
  Format-Table -AutoSize
```

## Baseline-target overlap attribution

```powershell
$run.baseline_target_overlap_attribution | Format-List
```

## Ticker failure attribution

```powershell
$run.ticker_failure_attribution |
  Select-Object `
    ticker,
    selected_rows,
    avg_ret_net,
    median_ret_net,
    win_rate,
    loss_count,
    loss_share,
    worst_ret_net,
    months_present,
    branches_present,
    share_of_total_losses |
  Format-Table -AutoSize
```

## Sector / bucket attribution

```powershell
$run.sector_bucket_failure_attribution |
  Select-Object `
    field_name,
    field_value,
    row_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    loss_count,
    loss_share,
    bad_month_like_contribution,
    months_present,
    failure_label |
  Format-Table -AutoSize
```

## Market regime attribution

```powershell
$run.market_regime_failure_attribution |
  Select-Object `
    regime_field,
    regime_bucket,
    row_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    loss_count,
    loss_share,
    bad_month_like_contribution,
    market_regime_failure,
    oos_regime_shift_vs_is |
  Format-Table -AutoSize
```

## Entry / path attribution

```powershell
$run.entry_path_failure_attribution |
  Select-Object `
    path_field,
    path_bucket,
    row_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    loss_count,
    loss_share,
    failure_label,
    safe_for_selection,
    diagnostic_only |
  Format-Table -AutoSize
```

## IS vs OOS contrast

```powershell
$run.is_vs_oos_contrast |
  Select-Object `
    metric_name,
    is_value,
    oos_value,
    delta_oos_vs_is,
    interpretation |
  Format-Table -AutoSize
```

## Failure attribution summary

```powershell
$run.failure_attribution_summary | Format-List
```

## C49 readiness decision

```powershell
$run.c49_readiness_decision | Format-List
```

## Candidate safety audit

```powershell
$run.candidate_safety_audit |
  Select-Object candidate_code,review_layer,passed,reason_code,message |
  Format-Table -AutoSize
```

## Not-evaluable reasons

```powershell
$run.not_evaluable_reasons |
  Select-Object validation_layer,validation_slice,reason_code,message |
  Format-Table -AutoSize
```

## Diagnostic conclusion and diagnostics

```powershell
$run | Select-Object status,diagnostic_conclusion,next_step_recommendation,production_ready | Format-List
$run.diagnostics | Select-Object reason_code,message,fatal | Format-Table -AutoSize
```

Expected completed markers:

```text
status=C48_OOS_FAILURE_ATTRIBUTION_COMPLETED
failure_attribution_completed=true
production_ready=false
direct_oos_proof_recommended=false
oos_proof_unlocked=false
```

Blocked markers:

```text
C48_BLOCKED_MISSING_C47_ARTIFACT
C48_BLOCKED_C47_HASH_MISMATCH
C48_BLOCKED_UNEXPECTED_C47_STATUS
C48_BLOCKED_UNEXPECTED_C47_CONCLUSION
C48_BLOCKED_C47_PRODUCTION_READY_NOT_FALSE
C48_BLOCKED_C47_OOS_TUNING_FLAG_NOT_FALSE
C48_BLOCKED_C47_BEST_OF_OOS_FLAG_INVALID
C48_BLOCKED_C47_SELECTION_SAFETY_INVALID
C48_BLOCKED_C47_NEXT_STEP_UNEXPECTED
C48_BLOCKED_ATTRIBUTION_PERIOD_MISMATCH
```

## Artifact hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c48-oos-failure-attribution.json -Algorithm SHA1
```

Final operator artifact markers:

```text
artifact_hash_internal=1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7
file_sha1=EEA350AF2D8A42C881B78701C48A1E301230362C
```

Do not claim production-ready. Do not claim C48 fixed OOS. Do not run or recommend OOS proof from C48.

## Final operator validation result

```text
C48 PHPUnit: PASS — OK (13 tests, 115 assertions)
Full Watchlist PHPUnit: PASS — OK (711 tests, 13451 assertions)
Runtime C48: COMPLETED
status=C48_OOS_FAILURE_ATTRIBUTION_COMPLETED
artifact_path=storage/app/watchlist/backtest/c48-oos-failure-attribution.json
artifact_hash_internal=1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7
file_sha1=EEA350AF2D8A42C881B78701C48A1E301230362C
expected_c47_hash=1c742e257847752def1f582dc24d6061a4c4e735
actual_c47_hash=1c742e257847752def1f582dc24d6061a4c4e735
c47_hash_match=true
c47_status=C47_OOS_PROOF_FAILED
c47_diagnostic_conclusion=C47_LOCKED_C44_REFINEMENT_OOS_PROOF_FAILED
diagnostic_conclusion=C48_SHARED_CORE_SELECTION_FAILURE_IDENTIFIED
next_step_recommendation=C49_BROADER_STRATEGY_REDESIGN
production_ready=false
```

The first runtime attempt returned `C48_OPERATOR_VALIDATION_REQUIRED` because the artifact already existed. The artifact was backed up, removed, and regenerated from the same locked C47 input. The final runtime completed cleanly.
