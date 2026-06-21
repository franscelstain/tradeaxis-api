# WS C44 — Operator Validation Commands

## PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC44"
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c44-is-guard-refinement-candidate-formation `
  --c43-artifact=storage/app/watchlist/backtest/c43-pre-trade-field-expansion-diagnostic.json `
  --expected-c43-hash=41a91ba0447dcf6c0493e1bb27bce6df08fd3490 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json `
  --overwrite `
  --progress
```

## Artifact inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json | ConvertFrom-Json
$run | Select-Object status,production_ready,diagnostic_conclusion,next_step_recommendation,expected_c43_hash,actual_c43_hash,c43_hash_match,c43_status,c43_diagnostic_conclusion | Format-List
$run.source_c43_summary | Format-List
$run.source_evidence_summary | Format-List
$run.guard_configuration | Format-List
$run.candidate_summary | Format-List
$run.guard_preservation_summary | Format-List
$run.c44_decision_summary | Format-List
$run.candidate_comparison_table | Select-Object candidate_code,selected_rows,avg_ret_net,median_ret_net,p25_ret_net,win_rate,month_win_rate_min,month_avg_ret_net_min,bad_month_like_count,delta_avg_ret_net_vs_baseline,delta_p25_ret_net_vs_baseline,delta_month_avg_ret_net_min_vs_baseline,delta_bad_month_like_count_vs_baseline,delta_march_2024_avg_ret_net_vs_baseline,all_required_guards_passed,advancement_gate_passed | Format-Table -AutoSize
$run.candidate_safety_audit | Format-Table -AutoSize
Get-FileHash storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json -Algorithm SHA1
```

Final validated markers:

```text
PHPUNIT_C44=PASS — OK (12 tests, 137 assertions)
FULL_WATCHLIST_PHPUNIT=PASS — OK (664 tests, 13103 assertions)
ARTISAN_C44_RUNTIME=COMPLETED
artifact_hash=606cd3109371b0d99419082daee18ff65f1cd99b
file_sha1=4A9A7A915DD37278D9F44634C5D08006B310ED71
production_ready=false
```

Do not claim PASS if a command was not run. C44 must not run or recommend OOS proof.

