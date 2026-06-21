# WS C57 Operator Validation Commands

## 1. PHPUnit C57 only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC57"
```

Expected marker after successful implementation validation:

```text
OK (... tests, ... assertions)
```

Do not claim PASS if this command has not actually been executed.

## 2. Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected marker:

```text
OK (... tests, ... assertions)
```

Do not claim PASS if this command has not actually been executed.

## 3. Runtime C57

```powershell
php artisan watchlist:backtest-c57-regime-field-reconstruction-continuation-is-only `
  --c56-artifact=storage/app/watchlist/backtest/c56-rolling-stability-redesign-continuation-is-only.json `
  --expected-c56-hash=f7edab247dc824dcd33a15f00575dd04f76f4786 `
  --c55-artifact=storage/app/watchlist/backtest/c55-rolling-stability-redesign-continuation-is-only.json `
  --expected-c55-hash=a4145d6f356e678d0dadf95be5d356198ebfed79 `
  --expected-c55-file-sha1=18875FCAD7FD7CDA6607BB09A60917E853E68D2B `
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
  --output=storage/app/watchlist/backtest/c57-regime-field-reconstruction-continuation-is-only.json `
  --progress
```

Expected completed marker:

```text
status=C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY_COMPLETED
production_ready=0
```

Blocked markers are valid if source locks or source data are not available. Do not convert blocked diagnostics into PASS.

## 4. Artifact summary

```powershell
$run = Get-Content storage/app/watchlist/backtest/c57-regime-field-reconstruction-continuation-is-only.json | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  diagnostic_conclusion,
  next_step_recommendation,
  expected_c56_hash,
  actual_c56_hash,
  c56_hash_match,
  c56_status,
  c56_diagnostic_conclusion,
  c56_next_step_recommendation,
  expected_c55_hash,
  actual_c55_hash,
  c55_hash_match,
  expected_c55_file_sha1,
  actual_c55_file_sha1,
  c55_file_sha1_match,
  expected_c54_hash,
  actual_c54_hash,
  c54_hash_match,
  expected_c54_file_sha1,
  actual_c54_file_sha1,
  c54_file_sha1_match,
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

## 5. Source lock validation breakdown

### C56

```powershell
$run | Select-Object expected_c56_hash,actual_c56_hash,c56_hash_match,c56_status,c56_diagnostic_conclusion,c56_next_step_recommendation | Format-List
```

### C55

```powershell
$run | Select-Object expected_c55_hash,actual_c55_hash,c55_hash_match,expected_c55_file_sha1,actual_c55_file_sha1,c55_file_sha1_match | Format-List
```

### C54

```powershell
$run | Select-Object expected_c54_hash,actual_c54_hash,c54_hash_match,expected_c54_file_sha1,actual_c54_file_sha1,c54_file_sha1_match | Format-List
```

### C53

```powershell
$run | Select-Object expected_c53_hash,actual_c53_hash,c53_hash_match,expected_c53_file_sha1,actual_c53_file_sha1,c53_file_sha1_match | Format-List
```

### C52

```powershell
$run | Select-Object expected_c52_hash,actual_c52_hash,c52_hash_match,expected_c52_file_sha1,actual_c52_file_sha1,c52_file_sha1_match | Format-List
```

## 6. C56 carry-forward and root cause

```powershell
$run.c56_carry_forward_summary | Format-List
$run.c56_root_cause_summary | Format-List
```

## 7. C56 regime field gap

```powershell
$run.regime_field_coverage_results |
  Select-Object field_name,required,rows_required,rows_available,coverage_rate,asof_safe,future_lookup_detected,oos_rows_requested,reconstruction_pass,failure_reason_codes |
  Format-Table -AutoSize

$run.missing_regime_field_results |
  Select-Object field_name,reason_code,message |
  Format-Table -AutoSize
```

## 8. C56 rolling/concentration/LOO summary

```powershell
$run.rolling_validation_summary | Format-List
$run.leave_one_month_out_summary | Format-List
$run.concentration_dependency_validation_results |
  Select-Object candidate_code,max_ticker_share,max_sector_share,max_bucket_share,max_branch_share,max_month_share,loss_cluster_share,concentration_validation_pass,failure_reason_codes |
  Format-Table -AutoSize
```

## 9. IS validation period and OOS reserved period

```powershell
$run.is_validation_period | Format-List
$run.oos_reserved_period | Format-List
```

Expected OOS flags:

```text
oos_data_used_for_tuning=false
oos_return_used_for_selection=false
oos_proof_executed=false
used_for_selection=false
used_for_tuning=false
used_for_proof=false
```

## 10. Market index source discovery

```powershell
$run.market_index_source_discovery_summary | Format-List

$run.market_index_source_discovery_results |
  Select-Object source_name,source_table,identifier,rows_available,date_min,date_max,selected_for_reconstruction,failure_reason_codes |
  Format-Table -AutoSize
```

## 11. Market index reconstruction

```powershell
$run.market_index_reconstruction_results |
  Select-Object field_name,source_identifier,source_table,rows_required,rows_reconstructed,coverage_rate,exact_date_match_count,previous_trading_day_fallback_count,computed_from_bars,indicator_source_used,asof_safe,future_lookup_detected,oos_rows_requested,reconstruction_pass,failure_reason_codes |
  Format-Table -AutoSize
```

## 12. Market index date coverage

```powershell
$run.market_index_date_coverage_results |
  Select-Object source_identifier,source_table,required_date_count,available_date_count,missing_date_count,date_coverage_rate,coverage_pass,failure_reason_codes |
  Format-Table -AutoSize
```

## 13. Market index as-of safety

```powershell
$run.market_index_asof_safety_results |
  Select-Object validation_layer,source_identifier,lookup_basis,asof_safe,future_lookup_detected,oos_rows_requested,max_trade_date_lookup_used,validation_pass,failure_reason_codes |
  Format-Table -AutoSize
```

## 14. Regime field reconstruction

```powershell
$run.regime_field_reconstruction_summary | Format-List
```

## 15. Source reconstruction

```powershell
$run.source_reconstruction_summary | Format-List
$run.source_reconstruction_bias_check | Format-List
```

## 16. Anchor candidate definitions

```powershell
$run.anchor_candidate_definitions |
  Select-Object candidate_code,candidate_role,source_c56_candidate_code,anchor_priority,selected_for_regime_recheck,comparator_only |
  Format-Table -AutoSize
```

## 17. Candidate replay

```powershell
$run.candidate_replay_results |
  Select-Object candidate_code,candidate_role,row_count,evaluated_picks_count,quality_pass,full_is_stability_pass,return_used_for_selection,future_path_used_for_selection,oos_return_used_for_selection,production_ready |
  Format-Table -AutoSize
```

## 18. Rolling validation

```powershell
$run.rolling_validation_results |
  Select-Object candidate_code,validation_window_code,rolling_validation_pass,failure_reason_codes |
  Format-Table -AutoSize

$run.rolling_validation_summary | Format-List
```

## 19. Leave-one-month-out

```powershell
$run.leave_one_month_out_results |
  Select-Object candidate_code,excluded_month,loo_validation_pass,failure_reason_codes |
  Format-Table -AutoSize

$run.leave_one_month_out_summary | Format-List
```

## 20. Regime robustness

```powershell
$run.regime_robustness_validation_results |
  Select-Object candidate_code,candidate_role,regime_fully_evaluable,regime_robustness_validation_pass,market_index_regime_fields_reconstructed,failure_reason_codes |
  Format-Table -AutoSize

$run.regime_robustness_validation_summary | Format-List
```

## 21. Material difference validation

```powershell
$run.material_difference_validation_results |
  Select-Object candidate_code,material_selection_difference_pass,anti_shared_core_pass,failure_reason_codes |
  Format-Table -AutoSize
```

## 22. Candidate scorecard

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
    max_ticker_share,
    max_month_share,
    loss_cluster_share,
    full_is_stability_pass,
    concentration_validation_pass,
    rolling_validation_pass,
    loo_validation_pass,
    regime_robustness_validation_pass,
    regime_fully_evaluable,
    material_selection_difference_pass,
    anti_shared_core_pass,
    overall_is_redesign_pass,
    anti_overfit_pass,
    candidate_ready_for_c58,
    failure_reason_codes |
  Format-Table -AutoSize
```

## 23. Selected C57 candidates for C58

```powershell
$run.selected_c57_candidates_for_c58 | Format-List
```

## 24. C58 readiness decision

```powershell
$run.c58_readiness_decision | Format-List
```

Required flags:

```text
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

## 25. Candidate safety audit

```powershell
$run.candidate_safety_audit |
  Select-Object candidate_code,review_layer,passed,reason_code,message |
  Format-Table -AutoSize
```

## 26. Not evaluable reasons

```powershell
$run.not_evaluable_reasons |
  Select-Object validation_layer,validation_slice,reason_code,message |
  Format-Table -AutoSize
```

## 27. Diagnostic conclusion

```powershell
$run.diagnostics |
  Select-Object reason_code,message |
  Format-Table -AutoSize
```

## 28. Artifact hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c57-regime-field-reconstruction-continuation-is-only.json -Algorithm SHA1
```

## 29. PowerShell-compatible JSON guard

```powershell
$run = Get-Content storage/app/watchlist/backtest/c57-regime-field-reconstruction-continuation-is-only.json | ConvertFrom-Json
$run.safety_boundaries | Format-List
```

The JSON must not contain duplicate keys after case-insensitive normalization.

## 30. Operator validation rule

If PHPUnit, full Watchlist PHPUnit, or runtime cannot be executed in the operator environment, record:

```text
OPERATOR_VALIDATION_REQUIRED
```

Do not claim PASS from static inspection alone.

## C57 fix2 market-index mapping validation

After applying C57 fix2, run the normal C57 PHPUnit and runtime commands, then inspect these fields:

```powershell
$run = Get-Content storage/app/watchlist/backtest/c57-regime-field-reconstruction-continuation-is-only.json | ConvertFrom-Json

$run.market_index_source_discovery_summary |
  Select-Object `
    source_found,
    selected_source_name,
    selected_source_table,
    selected_identifier,
    required_date_count,
    required_date_min,
    required_date_max,
    source_row_date_field_detected,
    source_row_min_date,
    source_row_max_date |
  Format-List

$run.market_index_reconstruction_results |
  Select-Object `
    field_name,
    source_identifier,
    source_table,
    rows_required,
    rows_reconstructed,
    coverage_rate,
    exact_date_match_count,
    previous_trading_day_fallback_count,
    computed_from_bars,
    indicator_source_used,
    reconstruction_pass,
    failure_reason_codes |
  Format-Table -AutoSize

$run.regime_field_reconstruction_summary | Format-List
$run.regime_field_coverage_results | Format-Table -AutoSize
$run.c58_readiness_decision | Format-List
```

Expected fix2 marker:

- `required_date_count` must not be `0` when source rows are available.
- `selected_source_table` should be `market_benchmark_indicators` when IHSG benchmark indicators exist.
- `market_index_roc20` should map from `roc_20` or fallback to benchmark bars.
- `market_index_ma20_slope_pct` should map from `ma20_slope_pct` or fallback to benchmark bars.
- C57 still must keep `production_ready=false`, `direct_oos_proof_recommended=false`, and `oos_proof_unlocked=false`.
