# WS C53 Operator Validation Commands

## PHPUnit and runtime

```powershell
vendor\bin\phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC53"
vendor\bin\phpunit tests/Unit/Watchlist

php artisan watchlist:backtest-c53-is-evidence-expansion-for-c52-redesign `
  --c52-artifact=storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json `
  --expected-c52-hash=5dbe51c9d18b175e65cddb60336baf43d6833b72 `
  --expected-c52-file-sha1=DADE6518BFF3912D8A43D7C67073FB803F7CF878 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json `
  --overwrite `
  --progress
```

Do not claim PASS or COMPLETED unless the corresponding command actually ran.

## Artifact inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json | ConvertFrom-Json

$run | Select-Object run_code,status,production_ready,diagnostic_conclusion,next_step_recommendation,
  expected_c52_hash,actual_c52_hash,c52_hash_match,
  expected_c52_file_sha1,actual_c52_file_sha1,c52_file_sha1_match,
  c52_status,c52_diagnostic_conclusion,c52_next_step_recommendation | Format-List

$run.is_validation_period | Format-List
$run.oos_reserved_period | Format-List
$run.c52_carry_forward_summary | Format-List
$run.locked_lineage_summary | Format-List
$run.evidence_expansion_thresholds | Format-List
$run.review_cohort_definition | Format-List
$run.review_cohort_results | Format-Table -AutoSize
$run.candidate_failure_inventory | Format-Table -AutoSize
$run.rolling_evidence_expansion_summary | Format-List
$run.leave_one_month_out_evidence_summary | Format-List
$run.adverse_month_attribution_results | Format-Table -AutoSize
$run.regime_field_availability_matrix | Format-Table -AutoSize
$run.regime_evidence_expansion_summary | Format-List
$run.structural_guard_preservation_audit | Format-Table -AutoSize
$run.cross_layer_corroboration_results | Format-Table -AutoSize
$run.c54_readiness_decision | Format-List
$run.candidate_safety_audit | Format-Table -AutoSize
$run.not_evaluable_reasons | Format-Table -AutoSize
$run.diagnostics | Format-Table -AutoSize
```

## JSON and hash guards

```powershell
$keys = $run.safety_boundaries.PSObject.Properties.Name
$normalized = $keys | ForEach-Object { $_.ToLowerInvariant() }
$normalized.Count -eq ($normalized | Select-Object -Unique).Count

Get-FileHash storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json -Algorithm SHA1
```

Expected safety result is `True`. C53 must not recommend direct OOS proof or production readiness.
