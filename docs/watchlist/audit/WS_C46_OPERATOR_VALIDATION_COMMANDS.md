# WS C46 - Operator Validation Commands

## PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter C46
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c46-is-review-evidence-expansion-before-oos `
  --c45-artifact=storage/app/watchlist/backtest/c45-is-validation-and-anti-overfit-check-for-c44-refinement.json `
  --expected-c45-hash=47970ba6e772bcf7fec68f306883f9f3d6cdd976 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c46-is-review-or-evidence-expansion-before-oos.json `
  --overwrite `
  --progress
```

## Artifact inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c46-is-review-or-evidence-expansion-before-oos.json | ConvertFrom-Json
$run | Select-Object status,production_ready,diagnostic_conclusion,next_step_recommendation,expected_c45_hash,actual_c45_hash,c45_hash_match,c45_status,c45_diagnostic_conclusion | Format-List
$run.source_c45_summary | Format-List
$run.review_thresholds | Format-List
$run.warning_layer_inventory | Format-List
$run.yearly_warning_review.warning_slices | Format-Table -AutoSize
$run.rolling_warning_review | Select-Object result,slice_count,pass_count,warning_count,fail_count,warning_share,worst_delta_avg_ret_net,worst_delta_month_avg_ret_net_min,avg_hard_fail_budget_share_used,month_min_hard_fail_budget_share_used,warning_slices_with_bad_month_increase | Format-List
$run.non_bad_month_warning_review | Format-List
$run.corroborating_pass_review | Format-List
$run.guard_and_safety_recheck | Format-List
$run.prior_warning_gap_resolution | Format-List
$run.evidence_expansion_requirements | Format-Table -AutoSize
$run.review_decision_summary | Format-List
$run.candidate_safety_audit | Format-List
Get-FileHash storage/app/watchlist/backtest/c46-is-review-or-evidence-expansion-before-oos.json -Algorithm SHA1
```

Validated markers:

```text
PHPUNIT_C46=PASS - OK (11 tests, 82 assertions)
FULL_WATCHLIST_PHPUNIT=PASS - OK (686 tests, 13261 assertions)
ARTISAN_C46_RUNTIME=COMPLETED
artifact_hash=d531dd5b911f55d8824ac514ccc7600470a076bd
file_sha1=59A80EA0BAE12034F42395EA0605536D9F9B2E5D
warning_review_result=C46_WARNING_BOUNDED_AND_EXPLAINED
oos_proof_unlocked=true
oos_proof_executed=false
production_ready=false
```

Do not claim C47 OOS success from this artifact. C46 only authorizes a future locked proof and never executes OOS itself.

