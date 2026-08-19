# WS C47 - Operator Validation Commands

## PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter '/WatchlistBacktestC47.*::/'
vendor\bin\phpunit tests\Unit\Watchlist
```

## One-shot runtime

The final artifact should not already exist when the official proof is first executed.

```powershell
php artisan watchlist:backtest-c47-oos-proof-with-locked-c44-refinement `
  --c46-artifact=storage/app/watchlist/backtest/c46-is-review-or-evidence-expansion-before-oos.json `
  --expected-c46-hash=d531dd5b911f55d8824ac514ccc7600470a076bd `
  --c44-artifact=storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json `
  --expected-c44-hash=606cd3109371b0d99419082daee18ff65f1cd99b `
  --oos-source-artifact=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json `
  --expected-oos-source-hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9 `
  --from=2025-05-22 `
  --to=2026-05-29 `
  --output=storage/app/watchlist/backtest/c47-oos-proof-with-locked-c44-refinement.json `
  --progress
```

Do not use `--overwrite` to search for a better result. The recorded C47 artifact is final OOS evidence.

## Artifact inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c47-oos-proof-with-locked-c44-refinement.json | ConvertFrom-Json
$run | Select-Object status,production_ready,diagnostic_conclusion,next_step_recommendation,expected_c46_hash,actual_c46_hash,c46_hash_match,expected_c44_hash,actual_c44_hash,c44_hash_match,expected_oos_source_hash,actual_oos_source_hash,oos_source_hash_match | Format-List
$run.locked_candidate | Format-List
$run.oos_source_summary | Format-List
$run.oos_source_and_selection_audit | Format-List
$run.baseline_oos_result | ConvertTo-Json -Depth 8
$run.target_oos_result | ConvertTo-Json -Depth 8
$run.comparison_vs_baseline | Format-List
$run.oos_gate.thresholds | Format-List
$run.oos_gate.checks | Format-List
$run.oos_gate.failed_checks
$run.diagnostics | Format-Table -AutoSize
$run.safety_boundaries | Format-List
Get-FileHash storage/app/watchlist/backtest/c47-oos-proof-with-locked-c44-refinement.json -Algorithm SHA1
```

Validated markers:

```text
PHPUNIT_C47=PASS - OK (12 tests, 75 assertions)
FULL_WATCHLIST_PHPUNIT=PASS - OK (698 tests, 13336 assertions)
ARTISAN_C47_RUNTIME=COMPLETED
status=C47_OOS_PROOF_FAILED
artifact_hash=1c742e257847752def1f582dc24d6061a4c4e735
file_sha1=351B0805F43D2B610B6826C4CDE1513B93FF2FE0
failed_checks=avg_pass,median_pass,month_win_rate_pass
production_ready=false
```

Do not claim OOS PASS or production readiness. Do not rerun with modified candidate, quota, ranking, window, or thresholds.

