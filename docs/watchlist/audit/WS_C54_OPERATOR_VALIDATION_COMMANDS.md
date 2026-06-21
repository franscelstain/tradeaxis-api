# WS C54 Operator Validation Commands

## PHPUnit and runtime

```powershell
vendor\bin\phpunit tests/Unit/Watchlist --filter C54
vendor\bin\phpunit tests/Unit/Watchlist

php artisan watchlist:backtest-c54-rolling-stability-redesign-or-recalibration-is-only `
  --c53-artifact=storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json `
  --expected-c53-hash=6a1749d723e16b7efdb8aa1d7510388a9475d12c `
  --expected-c53-file-sha1=E35FEFB78B6F1931E54169BD8AABE286CB6F08C2 `
  --c52-artifact=storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json `
  --expected-c52-hash=5dbe51c9d18b175e65cddb60336baf43d6833b72 `
  --expected-c52-file-sha1=DADE6518BFF3912D8A43D7C67073FB803F7CF878 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c54-rolling-stability-redesign-or-recalibration-is-only.json `
  --overwrite `
  --progress
```

## Artifact inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c54-rolling-stability-redesign-or-recalibration-is-only.json -Raw | ConvertFrom-Json

$run | Select-Object run_code,status,production_ready,diagnostic_conclusion,next_step_recommendation,
  expected_c53_hash,actual_c53_hash,c53_hash_match,
  expected_c53_file_sha1,actual_c53_file_sha1,c53_file_sha1_match,
  expected_c52_hash,actual_c52_hash,c52_hash_match,
  expected_c52_file_sha1,actual_c52_file_sha1,c52_file_sha1_match | Format-List

$run.c53_evidence_carry_forward | Format-List
$run.locked_lineage_summary | Format-List
$run.source_reconstruction_summary | Format-List
$run.redesign_constraints | Format-List
$run.redesign_candidate_definitions | Format-Table -AutoSize
$run.candidate_scorecard | Format-Table -AutoSize
$run.rolling_stability_redesign_summary | Format-List
$run.c55_readiness_decision | Format-List
$run.not_evaluable_reasons | Format-Table -AutoSize
$run.diagnostics | Format-Table -AutoSize
```

## JSON and hash guards

```powershell
$keys = $run.safety_boundaries.PSObject.Properties.Name
$normalized = $keys | ForEach-Object { $_.ToLowerInvariant() }
$normalized.Count -eq ($normalized | Select-Object -Unique).Count

Get-FileHash storage/app/watchlist/backtest/c54-rolling-stability-redesign-or-recalibration-is-only.json -Algorithm SHA1
```

Expected safety result is `True`. C54 must retain `production_ready=false`, `direct_oos_proof_recommended=false`, and `oos_proof_unlocked=false`.
