# WS C56 Operator Validation Commands

## PHPUnit C56 only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC56"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime C56

```powershell
php artisan watchlist:backtest-c56-rolling-stability-redesign-continuation-is-only `
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
  --output=storage/app/watchlist/backtest/c56-rolling-stability-redesign-continuation-is-only.json `
  --progress `
  --overwrite
```

## Artifact read

```powershell
$run = Get-Content storage/app/watchlist/backtest/c56-rolling-stability-redesign-continuation-is-only.json | ConvertFrom-Json
```

## Source lock summary

```powershell
$run | Select-Object `
  run_code,status,production_ready,diagnostic_conclusion,next_step_recommendation,`
  expected_c55_hash,actual_c55_hash,c55_hash_match,expected_c55_file_sha1,actual_c55_file_sha1,c55_file_sha1_match,`
  expected_c54_hash,actual_c54_hash,c54_hash_match,expected_c54_file_sha1,actual_c54_file_sha1,c54_file_sha1_match,`
  expected_c53_hash,actual_c53_hash,c53_hash_match,expected_c53_file_sha1,actual_c53_file_sha1,c53_file_sha1_match,`
  expected_c52_hash,actual_c52_hash,c52_hash_match,expected_c52_file_sha1,actual_c52_file_sha1,c52_file_sha1_match |
  Format-List
```

## Boundary summaries

```powershell
$run.is_validation_period | Format-List
$run.oos_reserved_period | Format-List
$run.c55_carry_forward_summary | Format-List
$run.c55_root_cause_summary | Format-List
$run.c54_carry_forward_summary | Format-List
$run.c53_evidence_carry_forward | Format-List
$run.c52_sector_reconstruction_carry_forward | Format-List
```

## Near-pass rolling attribution

```powershell
$run.near_pass_rolling_attribution_summary | Format-List
$run.near_pass_rolling_attribution_results |
  Select-Object candidate_code,source_c55_candidate_code,rolling_window_count,rolling_pass_count,rolling_pass_rate,failed_window_count,failed_window_codes,failed_window_loss_cluster_share,failed_window_max_branch_share,failed_window_max_bucket_share,failed_window_max_sector_share,failure_reason_codes |
  Format-Table -AutoSize
```

## Regime field reconstruction

```powershell
$run.regime_field_reconstruction_summary | Format-List
$run.regime_field_coverage_results |
  Select-Object field_name,required,rows_required,rows_available,coverage_rate,asof_safe,future_lookup_detected,oos_rows_requested,reconstruction_pass,failure_reason_codes |
  Format-Table -AutoSize
$run.missing_regime_field_results | Format-Table -AutoSize
$run.asof_safety_validation_results | Format-List
```

## Source reconstruction

```powershell
$run.source_reconstruction_summary | Format-List
$run.source_reconstruction_bias_check | Format-List
```

## Redesign definitions and replay

```powershell
$run.redesign_candidate_definitions |
  Select-Object candidate_code,candidate_role,source_candidates_used,branch_cap,bucket_cap,sector_cap,ticker_cap,month_cap,loss_cluster_cap,monthly_quota_rule,monthly_exposure_equalizer_rule,rolling_smoothing_rule,rolling_stress_smoother_rule,g16_cap_rule,g21_backfill_rule,g13_limit_rule,return_used_for_selection,future_path_used_for_selection,adverse_month_exclusion_used,failed_window_exclusion_used,oos_return_used_for_selection |
  Format-Table -AutoSize

$run.candidate_replay_results |
  Select-Object candidate_code,candidate_role,evaluated_picks_count,avg_ret_net,median_ret_net,p25_ret_net,win_rate,month_win_rate_min,bad_month_like_count,coverage_months,quality_pass,full_is_stability_pass,coverage_pass,failure_reason_codes |
  Format-Table -AutoSize
```

## Dependency validations

```powershell
$run.concentration_dependency_validation_results | Format-List
$run.branch_dependency_validation_results | Format-Table -AutoSize
$run.bucket_dependency_validation_results | Format-Table -AutoSize
$run.sector_dependency_validation_results | Format-Table -AutoSize
$run.ticker_dependency_validation_results | Format-Table -AutoSize
$run.month_dependency_validation_results | Format-Table -AutoSize
```

## Rolling / LOO / Regime / Material

```powershell
$run.rolling_validation_summary | Format-List
$run.leave_one_month_out_summary | Format-List
$run.regime_robustness_validation_summary | Format-List
$run.material_difference_validation_results | Format-Table -AutoSize
```

## Scorecard and C57 decision

```powershell
$run.candidate_scorecard |
  Select-Object candidate_code,candidate_role,evaluated_picks_count,avg_ret_net,median_ret_net,win_rate,month_win_rate_min,max_branch_share,max_bucket_share,max_sector_share,max_month_share,loss_cluster_share,full_is_stability_pass,concentration_validation_pass,rolling_validation_pass,loo_validation_pass,regime_robustness_validation_pass,regime_fully_evaluable,material_selection_difference_pass,anti_shared_core_pass,overall_is_redesign_pass,anti_overfit_pass,candidate_ready_for_c57,failure_reason_codes |
  Format-Table -AutoSize

$run.selected_c56_candidates_for_c57 | Format-List
$run.c57_readiness_decision | Format-List
$run.candidate_safety_audit | Format-Table -AutoSize
$run.not_evaluable_reasons | Format-Table -AutoSize
$run.diagnostics | Format-Table -AutoSize
```

## Artifact SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c56-rolling-stability-redesign-continuation-is-only.json -Algorithm SHA1
```

## Expected markers

```text
completed_marker=C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED
blocked_marker_prefix=C56_BLOCKED_
production_ready=false
direct_oos_proof_recommended=false
oos_proof_unlocked=false
```

Do not claim PASS, COMPLETED, or artifact hash unless the commands above were actually run. The artifact must remain PowerShell-compatible: lowercase snake_case safety boundary keys and no case-insensitive duplicate keys.


## Final operator validation result

```text
C56_PHPUNIT_STATUS=PASS
C56_PHPUNIT_RESULT=OK (9 tests, 337 assertions)
FULL_WATCHLIST_PHPUNIT_STATUS=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (795 tests, 15782 assertions)
C56_RUNTIME_STATUS=COMPLETED
status=C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED
artifact_path=storage/app/watchlist/backtest/c56-rolling-stability-redesign-continuation-is-only.json
artifact_hash=f7edab247dc824dcd33a15f00575dd04f76f4786
production_ready=false
```

## Final source lock result

```text
c55_hash_match=true
c55_file_sha1_match=true
c54_hash_match=true
c54_file_sha1_match=true
c53_hash_match=true
c53_file_sha1_match=true
c52_hash_match=true
c52_file_sha1_match=true
```

## Final C57 readiness command result

```text
validation_completed=true
candidate_ready_for_c57_count=0
rolling_validation_pass_candidate_count=4
concentration_validation_pass_candidate_count=0
loss_cluster_pass_candidate_count=0
c57_recommendation=C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY
decision_reason=regime_field_reconstruction_not_fully_evaluable
diagnostic_conclusion=C56_REGIME_FIELD_RECONSTRUCTION_GAP_REMAINS
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

## Final regime field result

```text
regime_field_reconstruction_attempted=true
required_field_count=9
evaluable_field_count=7
missing_field_count=2
regime_field_coverage_min=0
regime_fully_evaluable=false
market_index_regime_fields_reconstructed=false
asof_safe=true
future_lookup_detected=false
oos_rows_requested=0
reconstruction_pass=false
failure_reason_codes={C56_REGIME_FIELD_NOT_EVALUABLE}

market_index_roc20: rows_required=15750, rows_available=0, coverage_rate=0
market_index_ma20_slope_pct: rows_required=15750, rows_available=0, coverage_rate=0
```

## Final strategy interpretation

C56 completed and produced a valid artifact. Rolling stability improved to four full rolling-pass candidates, but no candidate is ready for C57 pre-OOS lock review because concentration/loss-cluster pass count remains zero and regime robustness is not fully evaluable. C56 must not be used as production readiness evidence or as OOS proof unlock evidence.
