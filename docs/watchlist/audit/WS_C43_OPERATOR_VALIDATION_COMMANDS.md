# WS C43 — Operator Validation Commands

## PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC43"
vendor\bin\phpunit tests\Unit\Watchlist
```

Do not claim PASS unless the commands were actually executed.

## Runtime

```powershell
php artisan watchlist:backtest-c43-pre-trade-field-expansion-diagnostic `
  --c42-artifact=storage/app/watchlist/backtest/c42-is-rolling-normal-month-evidence-expansion.json `
  --expected-c42-hash=939e85f179b3bf5d2511730fafb4271cf7c2ca11 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c43-pre-trade-field-expansion-diagnostic.json `
  --overwrite `
  --progress
```

Completed marker: `C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC_COMPLETED`. Valid blocked markers include missing/hash-mismatched/unexpected C42 input, invalid C42 safety flags, and an IS period touching the OOS reservation.

## Artifact and source lock

```powershell
$run = Get-Content storage/app/watchlist/backtest/c43-pre-trade-field-expansion-diagnostic.json | ConvertFrom-Json
$run | Select-Object run_code,status,production_ready,diagnostic_conclusion,next_step_recommendation,expected_c42_hash,actual_c42_hash,c42_hash_match,c42_status,c42_diagnostic_conclusion | Format-List
$run.is_period | Format-List
$run.source_c42_summary | Format-List
```

## Field discovery and timing

```powershell
$run.field_discovery_matrix | Select-Object field_name,field_group,source_found,source_type,source_table_or_artifact,available_in_c28_rows,available_in_c42_artifact,available_in_database_or_repository,join_required,safe_for_selection,safe_for_diagnostic_only,field_classification,coverage_count,coverage_pct,missing_count,missing_pct,reason_code | Format-Table -AutoSize
$run.timing_and_leakage_audit | Select-Object field_name,as_of_date_rule,timing_safe,safe_for_selection,safe_for_diagnostic_only,field_classification,unsafe_reason | Format-Table -AutoSize
$run.join_feasibility_matrix | Select-Object field_name,source_type,source_table_or_artifact,required_join_keys_available,as_of_date_safe,coverage_count,coverage_pct,missing_count,missing_pct,safe_for_selection,field_classification,reason_code | Format-Table -AutoSize
```

## Cluster and readiness

```powershell
$run.warning_cluster_enrichment | Select-Object cluster_code,trade_month,selected_source_code,field_name,field_value_or_bucket,cluster_row_count,cluster_loss_count,cluster_avg_ret_net,cluster_win_rate,cluster_share,baseline_row_count,target_row_count,g16_row_count,g21_row_count,safe_for_selection,field_explains_warning_cluster,field_explanation_strength,field_can_support_future_refinement | Format-Table -AutoSize
$run.cluster_field_explanation_table | Select-Object field_name,field_classification,field_explains_warning_cluster,field_explanation_strength,field_can_support_future_refinement,reason_code,message | Format-Table -AutoSize
$run.refinement_readiness_assessment | Format-List
$run.guard_preservation_feasibility | Select-Object future_refinement_hypothesis,field_name,coverage_guard_feasible,branch_guard_feasible,g21_not_suppressed_total,months_covered_feasible,zero_pick_months_risk,min_selected_rows_risk,reason_code | Format-Table -AutoSize
```

## Decision and safety

```powershell
$run.c43_decision_summary | Format-List
$run.candidate_safety_audit | Select-Object candidate_code,review_layer,passed,reason_code,message,return_used_for_selection,future_path_used_for_selection,oos_data_used_for_tuning,direct_oos_proof_recommended,oos_proof_unlocked,production_ready | Format-Table -AutoSize
$run.not_evaluable_reasons | Select-Object validation_layer,validation_slice,reason_code,message | Format-Table -AutoSize
$run.diagnostics | Select-Object reason_code,message,fatal | Format-Table -AutoSize
Get-FileHash storage/app/watchlist/backtest/c43-pre-trade-field-expansion-diagnostic.json -Algorithm SHA1
```

Expected safety markers are `production_ready=false`, `oos_data_used_for_tuning=false`, `return_used_for_selection=false`, `future_path_used_for_selection=false`, `direct_oos_proof_recommended=false`, and `oos_proof_unlocked=false`.

## Final workspace evidence

```text
PHPUNIT_C43=PASS — OK (13 tests, 106 assertions)
FULL_WATCHLIST_PHPUNIT=PASS — OK (652 tests, 12966 assertions)
ARTISAN_C43_RUNTIME=COMPLETED
status=C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC_COMPLETED
artifact_hash=41a91ba0447dcf6c0493e1bb27bce6df08fd3490
file_sha1=27816E62CBE7278108D0BC43C4C3E3F91BC749D7
diagnostic_conclusion=C43_SAFE_PRE_TRADE_FIELDS_FOUND_FOR_C44_REFINEMENT
next_step_recommendation=C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION
production_ready=false
```
