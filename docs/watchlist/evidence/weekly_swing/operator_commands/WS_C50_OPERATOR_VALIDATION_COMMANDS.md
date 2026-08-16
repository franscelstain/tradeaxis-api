# WS C50 Operator Validation Commands

Do not claim PASS or COMPLETED unless these commands are run in the supported project PHP environment.

## PHPUnit C50 only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC50"
```

Expected marker when valid:

```text
OK (... tests, ... assertions)
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected marker when valid:

```text
OK (... tests, ... assertions)
```

## Runtime C50

```powershell
php artisan watchlist:backtest-c50-is-validation-anti-overfit-check `
  --c49-artifact=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json `
  --expected-c49-hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json `
  --progress
```

Expected completed marker:

```text
status=C50_IS_VALIDATION_COMPLETED
production_ready=0
c49_hash_match=1
```

Blocked markers:

```text
C50_BLOCKED_MISSING_C49_ARTIFACT
C50_BLOCKED_C49_HASH_MISMATCH
C50_BLOCKED_UNEXPECTED_C49_STATUS
C50_BLOCKED_UNEXPECTED_C49_CONCLUSION
C50_BLOCKED_C49_NEXT_STEP_UNEXPECTED
C50_BLOCKED_C49_PRODUCTION_READY_NOT_FALSE
C50_BLOCKED_C49_OOS_PROOF_FLAG_INVALID
C50_BLOCKED_MISSING_C49_PRIMARY_CANDIDATE
C50_BLOCKED_VALIDATION_PERIOD_TOUCHES_OOS_RESERVED
C50_SOURCE_ROWS_NOT_EVALUABLE
```

## Read artifact

```powershell
$run = Get-Content storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json | ConvertFrom-Json
```

## Breakdown - C49 hash validation

```powershell
$run | Select-Object `
  run_code,
  status,
  production_ready,
  diagnostic_conclusion,
  next_step_recommendation,
  expected_c49_hash,
  actual_c49_hash,
  c49_hash_match,
  c49_status,
  c49_diagnostic_conclusion,
  c49_next_step_recommendation |
  Format-List
```

## Breakdown - C49 carry-forward

```powershell
$run.c49_carry_forward_summary | Format-List
```

## Breakdown - IS validation period

```powershell
$run.is_validation_period | Format-List
```

## Breakdown - OOS reserved period

```powershell
$run.oos_reserved_period | Format-List
```

## Breakdown - source reconstruction

```powershell
$run.source_reconstruction_summary | Format-List
```

## Breakdown - locked candidate replay

```powershell
$run.locked_candidate_replay_results |
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

## Breakdown - rolling validation

```powershell
$run.rolling_validation_summary | Format-List

$run.rolling_validation_results |
  Select-Object `
    validation_window_code,
    window_from,
    window_to,
    candidate_code,
    evaluated_picks_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    month_win_rate_min,
    bad_month_like_count,
    coverage_months,
    quality_pass,
    stability_pass,
    coverage_pass |
  Format-Table -AutoSize
```

## Breakdown - leave-one-month-out

```powershell
$run.leave_one_month_out_summary | Format-List

$run.leave_one_month_out_results |
  Select-Object `
    exclude_month,
    candidate_code,
    evaluated_picks_count_after_exclusion,
    avg_ret_net_after_exclusion,
    median_ret_net_after_exclusion,
    win_rate_after_exclusion,
    month_win_rate_min_after_exclusion,
    quality_delta,
    stability_delta,
    rank_stable,
    dependency_on_excluded_month |
  Format-Table -AutoSize
```

## Breakdown - regime robustness

```powershell
$run.regime_robustness_validation_summary | Format-List

$run.regime_robustness_validation_results |
  Select-Object `
    candidate_code,
    regime_field,
    regime_bucket,
    row_count,
    evaluated_picks_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    loss_count,
    loss_share,
    coverage_share,
    regime_bucket_pass |
  Format-Table -AutoSize
```

## Breakdown - concentration/dependency

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
    concentration_validation_pass |
  Format-Table -AutoSize

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

## Breakdown - material difference validation

```powershell
$run.material_difference_validation | Format-List
```

## Breakdown - source reconstruction bias check

```powershell
$run.source_reconstruction_bias_check | Format-List
```

## Breakdown - candidate validation scorecard

```powershell
$run.candidate_validation_scorecard |
  Select-Object `
    candidate_code,
    profile_code,
    family_code,
    candidate_role,
    evaluated_picks_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    month_win_rate_min,
    bad_month_like_count,
    rolling_validation_pass,
    loo_validation_pass,
    regime_robustness_validation_pass,
    concentration_validation_pass,
    material_selection_difference_pass,
    anti_shared_core_pass,
    source_bias_validation_pass,
    overall_is_validation_pass,
    anti_overfit_pass,
    candidate_ready_for_c51 |
  Format-Table -AutoSize
```

## Breakdown - selected C50 candidates for C51

```powershell
$run.selected_c50_candidates_for_c51 | Format-List
```

## Breakdown - C51 readiness decision

```powershell
$run.c51_readiness_decision | Format-List
```

## Breakdown - candidate safety audit

```powershell
$run.candidate_safety_audit |
  Select-Object candidate_code,review_layer,passed,reason_code,message |
  Format-Table -AutoSize
```

## Breakdown - not evaluable reasons

```powershell
$run.not_evaluable_reasons |
  Select-Object validation_layer,validation_slice,reason_code,message |
  Format-Table -AutoSize
```

## Breakdown - diagnostic conclusion

```powershell
$run.diagnostics |
  Select-Object reason_code,message |
  Format-Table -AutoSize
```

## Artifact hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json -Algorithm SHA1
```

## Required final operator report fields

```text
C50_PHPUNIT=PASS|FAIL|NOT_RUN
FULL_WATCHLIST_PHPUNIT=PASS|FAIL|NOT_RUN
C50_RUNTIME_STATUS=COMPLETED|BLOCKED|FAIL|NOT_RUN
ARTIFACT_PATH=storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json
ARTIFACT_HASH=<sha1/internal artifact hash if created>
production_ready=false
direct_oos_proof_recommended=false
oos_proof_unlocked=false
```

No PASS can be claimed from documentation alone.

## Final C50 operator validation result

These results were produced in the supported project environment after the PowerShell JSON duplicate-key fix.

```text
C50_PHPUNIT=PASS
C50_PHPUNIT_RESULT=OK (12 tests, 218 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (735 tests, 13865 assertions)
C50_RUNTIME_STATUS=COMPLETED
ARTIFACT_PATH=storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json
ARTIFACT_HASH=1f2b919662a395444f43403e8f7f4d0b91e146aa
POWERSHELL_CONVERTFROM_JSON=PASS
status=C50_IS_VALIDATION_COMPLETED
diagnostic_conclusion=C50_C49_PRIMARY_CANDIDATE_OVERFIT_RISK_IDENTIFIED
next_step_recommendation=C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW
production_ready=false
```

Final source lock evidence:

```text
expected_c49_hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8
actual_c49_hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8
c49_hash_match=true
c49_status=C49_BROADER_STRATEGY_REDESIGN_COMPLETED
c49_diagnostic_conclusion=C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION
c49_next_step_recommendation=C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN
```

Final C51 readiness evidence:

```text
primary_candidate_code=C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
defensive_comparator_code=C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN
primary_candidate_validation_pass=false
defensive_comparator_validation_pass=false
rolling_validation_pass=true
loo_validation_pass=true
regime_robustness_validation_pass=true
concentration_validation_pass=false
material_difference_validation_pass=true
source_bias_validation_pass=true
anti_overfit_pass=false
c51_recommendation=C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW
decision_reason=concentration_dependency_issue
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

Final concentration/dependency evidence:

```text
F03_max_ticker_share=0.07681564245810056
F03_max_sector_share=0.21578212290502793
F03_max_bucket_share=0.9217877094972067
F03_max_branch_share=0.9217877094972067
F03_max_month_share=0.09427374301675978
F03_unique_ticker_count=61
F03_unique_sector_count=10
F03_unique_bucket_count=2
F03_unique_branch_count=2
F03_loss_cluster_share=0.12910798122065728
F03_concentration_validation_pass=false
F03_failure_reason_codes=C50_CONCENTRATION_DEPENDENCY_WARNING
F03_G16_branch_row_count=1320
F03_G16_branch_share=0.9217877094972067
F03_G21_branch_row_count=112
F03_G21_branch_share=0.0782122905027933
```

```text
F08_max_ticker_share=0.06782106782106782
F08_max_sector_share=0.1717171717171717
F08_max_bucket_share=0.5411255411255411
F08_max_branch_share=0.5411255411255411
F08_max_month_share=0.03896103896103896
F08_unique_ticker_count=60
F08_unique_sector_count=10
F08_unique_bucket_count=2
F08_unique_branch_count=3
F08_loss_cluster_share=0.08032128514056225
F08_concentration_validation_pass=true
F08_G13_branch_share=0.22510822510822512
F08_G16_branch_share=0.5411255411255411
F08_G21_branch_share=0.23376623376623376
```

```text
F00_C44_SHARED_CORE_concentration_validation_pass=true
F00_C44_SHARED_CORE_material_selection_difference_pass=false
F00_C44_SHARED_CORE_ROLE=comparator_only_not_redesign_candidate
```
