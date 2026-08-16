# WS C45 - Operator Validation Commands

## PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter C45
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c45-is-validation-anti-overfit-check-for-c44-refinement `
  --c44-artifact=storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json `
  --expected-c44-hash=606cd3109371b0d99419082daee18ff65f1cd99b `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c45-is-validation-and-anti-overfit-check-for-c44-refinement.json `
  --overwrite `
  --progress
```

## Artifact inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c45-is-validation-and-anti-overfit-check-for-c44-refinement.json | ConvertFrom-Json
$run | Select-Object status,production_ready,diagnostic_conclusion,next_step_recommendation,expected_c44_hash,actual_c44_hash,c44_hash_match,c44_status,c44_diagnostic_conclusion | Format-List
$run.source_c44_summary | Format-List
$run.source_evidence_summary | Format-List
$run.validation_target | Format-List
$run.validation_summary | Format-List
$run.full_is_validation | ConvertTo-Json -Depth 8
$run.yearly_validation.slices | Select-Object validation_slice,result,@{n='delta_avg';e={$_.comparison_vs_baseline.delta_avg_ret_net}},@{n='delta_month_min';e={$_.comparison_vs_baseline.delta_month_avg_ret_net_min}},@{n='delta_bad_months';e={$_.comparison_vs_baseline.delta_bad_month_like_count}} | Format-Table -AutoSize
$run.rolling_window_validation | Select-Object result,slice_count,pass_count,warning_count,fail_count | Format-List
$run.rolling_window_validation.slices | Where-Object result -ne PASS | Select-Object validation_slice,result,@{n='delta_avg';e={$_.comparison_vs_baseline.delta_avg_ret_net}},@{n='delta_month_min';e={$_.comparison_vs_baseline.delta_month_avg_ret_net_min}} | Format-Table -AutoSize
$run.bad_month_like_stress_validation | ConvertTo-Json -Depth 8
$run.non_bad_month_validation | ConvertTo-Json -Depth 8
$run.ticker_concentration_validation | Format-List
$run.branch_concentration_validation | Format-List
$run.month_coverage_validation | Format-List
$run.downside_stability_validation | Format-List
$run.candidate_safety_audit | Format-List
Get-FileHash storage/app/watchlist/backtest/c45-is-validation-and-anti-overfit-check-for-c44-refinement.json -Algorithm SHA1
```

Validated markers:

```text
PHPUNIT_C45=PASS - OK (11 tests, 76 assertions)
FULL_WATCHLIST_PHPUNIT=PASS - OK (675 tests, 13179 assertions)
ARTISAN_C45_RUNTIME=COMPLETED
overall_anti_overfit_result=WARNING
artifact_hash=47970ba6e772bcf7fec68f306883f9f3d6cdd976
file_sha1=CF7D7D78103B543814C1B84F29B33AEA3E4FAF78
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

Do not claim PASS if a command was not run. C45 must never run OOS proof or interpret `WARNING` as OOS authorization.

