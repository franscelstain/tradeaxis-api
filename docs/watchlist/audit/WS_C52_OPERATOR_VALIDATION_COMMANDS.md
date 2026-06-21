# WS C52 Operator Validation Commands

## PHPUnit and runtime

```powershell
vendor\bin\phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC52"
vendor\bin\phpunit tests/Unit/Watchlist

php artisan watchlist:backtest-c52-concentration-dependency-redesign-continuation `
  --c51-artifact=storage/app/watchlist/backtest/c51-concentration-dependency-redesign-review.json `
  --expected-c51-hash=a786034b8e344207592e58efe262287102b0ef36 `
  --c50-artifact=storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json `
  --expected-c50-hash=1f2b919662a395444f43403e8f7f4d0b91e146aa `
  --c49-artifact=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json `
  --expected-c49-hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json `
  --overwrite `
  --progress
```

Do not claim PASS or COMPLETED unless the corresponding command actually ran. If the environment cannot run it, record `OPERATOR_VALIDATION_REQUIRED`.

Completed markers:

```text
C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED
C52_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED_WITH_SECTOR_NOT_EVALUABLE
```

Blocked markers include missing/hash-mismatched C51/C50/C49, unexpected C51 status/conclusion/next step, invalid C51 production/OOS flags, missing continuation reason, and an IS period touching reserved OOS.

## Read artifact and source locks

```powershell
$run = Get-Content storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json | ConvertFrom-Json

$run | Select-Object run_code,status,production_ready,diagnostic_conclusion,next_step_recommendation,
  expected_c51_hash,actual_c51_hash,c51_hash_match,c51_status,c51_diagnostic_conclusion,c51_next_step_recommendation,
  expected_c50_hash,actual_c50_hash,c50_hash_match,expected_c49_hash,actual_c49_hash,c49_hash_match | Format-List

$run | Select-Object input_c51_artifact,expected_c51_hash,actual_c51_hash,c51_hash_match,c51_status,c51_diagnostic_conclusion,c51_next_step_recommendation | Format-List
$run | Select-Object input_c50_artifact,expected_c50_hash,actual_c50_hash,c50_hash_match,c50_status,c50_diagnostic_conclusion,c50_next_step_recommendation | Format-List
$run | Select-Object input_c49_artifact,expected_c49_hash,actual_c49_hash,c49_hash_match | Format-List
$run.c51_carry_forward_summary | Format-List
$run.c51_root_cause_summary | Format-List
$run.is_validation_period | Format-List
$run.oos_reserved_period | Format-List
```

## Sector metadata audit

```powershell
$run.sector_metadata_reconstruction_summary | Format-List
$run.sector_metadata_selected_source | Format-List
$run.sector_metadata_source_candidates | Select-Object source_name,source_available,source_row_count,source_unique_ticker_count,source_unique_sector_count,join_key_used,join_date_key_used,asof_safe,lookahead_guard_pass | Format-Table -AutoSize
$run.sector_metadata_join_results | Select-Object source_name,rows_attempted,rows_joined,join_coverage_rate,rows_with_sector_code,rows_with_sector_name,sector_code_coverage_rate,sector_name_coverage_rate,unknown_sector_count,unknown_sector_share,unique_sector_count,max_sector_share_after_join,sector_reconstruction_pass,failure_reason_codes | Format-Table -AutoSize
$run.sector_metadata_validation_results | Format-Table -AutoSize
$run.sector_metadata_conflict_results | Format-Table -AutoSize
```

## Reconstruction and candidate definitions

```powershell
$run.source_reconstruction_summary | Format-List
$run.redesign_candidate_definitions | Select-Object candidate_code,candidate_role,source_candidates_used,selection_rule_description,sector_metadata_source_used,branch_cap,bucket_cap,sector_cap,branch_quota,bucket_quota,sector_quota,downsampling_rule,backfill_rule,g13_limit_rule,loss_cluster_control_rule,return_used_for_selection,future_path_used_for_selection,oos_return_used_for_selection | Format-Table -AutoSize
$run.candidate_replay_results | Select-Object candidate_code,profile_code,family_code,candidate_role,evaluated_picks_count,avg_ret_net,median_ret_net,p25_ret_net,win_rate,month_win_rate_min,bad_month_like_count,coverage_months,quality_pass,stability_pass,coverage_pass | Format-Table -AutoSize
```

## Dependency slices

```powershell
$run.concentration_dependency_validation_results | Select-Object candidate_code,max_ticker_share,max_sector_share,max_bucket_share,max_branch_share,max_month_share,unique_ticker_count,unique_sector_count,unique_bucket_count,unique_branch_count,sector_metadata_coverage_rate,sector_concentration_evaluable,sector_concentration_not_evaluable,loss_cluster_share,concentration_validation_pass,concentration_validation_level,failure_reason_codes | Format-List
$run.branch_dependency_validation_results | Select-Object candidate_code,branch_code,branch_row_count,branch_share,branch_avg_ret_net,branch_median_ret_net,branch_win_rate,branch_loss_share,branch_dependency_detected | Format-Table -AutoSize
$run.bucket_dependency_validation_results | Select-Object candidate_code,bucket_code,bucket_row_count,bucket_share,bucket_avg_ret_net,bucket_median_ret_net,bucket_win_rate,bucket_loss_share,bucket_dependency_detected | Format-Table -AutoSize
$run.sector_dependency_validation_results | Select-Object candidate_code,sector_code,sector_name,sector_row_count,sector_share,sector_avg_ret_net,sector_median_ret_net,sector_win_rate,sector_loss_share,sector_dependency_detected,sector_metadata_source | Format-Table -AutoSize
```

## Robustness, difference, and readiness

```powershell
$run.rolling_validation_results | Format-Table -AutoSize
$run.rolling_validation_summary | Format-List
$run.leave_one_month_out_results | Format-Table -AutoSize
$run.leave_one_month_out_summary | Format-List
$run.regime_robustness_validation_results | Format-Table -AutoSize
$run.regime_robustness_validation_summary | Format-List
$run.material_difference_validation_results | Select-Object candidate_code,overlap_with_c44,overlap_with_f00,overlap_with_f03,overlap_with_f08,overlap_with_c51_r05,overlap_with_c51_r06,overlap_with_c51_r08,overlap_with_c51_r09,overlap_with_c51_r10,overlap_with_c51_r12,material_difference_score,material_selection_difference_pass,anti_shared_core_pass,failure_reason_codes | Format-Table -AutoSize
$run.source_reconstruction_bias_check | Format-List
$run.candidate_scorecard | Select-Object candidate_code,candidate_role,evaluated_picks_count,avg_ret_net,median_ret_net,win_rate,month_win_rate_min,max_branch_share,max_bucket_share,max_sector_share,sector_metadata_coverage_rate,sector_concentration_evaluable,loss_cluster_share,sector_metadata_reconstruction_pass,concentration_validation_pass,rolling_validation_pass,loo_validation_pass,regime_robustness_validation_pass,material_selection_difference_pass,anti_shared_core_pass,overall_is_redesign_pass,anti_overfit_pass,candidate_ready_for_c53,failure_reason_codes | Format-Table -AutoSize
$run.selected_c52_candidates_for_c53 | Format-List
$run.c53_readiness_decision | Format-List
$run.candidate_safety_audit | Select-Object candidate_code,review_layer,passed,reason_code,message | Format-Table -AutoSize
$run.not_evaluable_reasons | Select-Object validation_layer,validation_slice,reason_code,message | Format-Table -AutoSize
$run.diagnostics | Select-Object reason_code,message | Format-Table -AutoSize
```

## JSON and hash validation

```powershell
$keys = $run.safety_boundaries.PSObject.Properties.Name
$normalized = $keys | ForEach-Object { $_.ToLowerInvariant() }
$normalized.Count -eq ($normalized | Select-Object -Unique).Count

Get-FileHash storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json -Algorithm SHA1
```

Expected safety result is `True`. C52 may route only to C53 IS validation/pre-OOS lock review, IS evidence expansion, sector metadata evidence expansion, concentration redesign continuation, shared-core redesign, or IS-only recalibration. It must never recommend direct OOS proof.
