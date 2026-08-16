# WS C60 Operator Validation Commands

## Focus

`C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY`

C60 is IS-only. Do not run OOS proof.

## PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC60"
```

Then full Watchlist suite:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c60-regime-stress-and-loo-dependency-redesign-is-only `
  --c59-artifact=storage/app/watchlist/backtest/c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only.json `
  --expected-c59-hash=7ebd6f74bc90ffac358b410244d90b3c7c3c5456 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json `
  --overwrite `
  --progress
```

## Inspect Artifact

```powershell
$run = Get-Content storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json | ConvertFrom-Json

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c59_blocker_summary | Format-List
$run.c59_improvement_retention_summary | Format-List
$run.candidate_generation_summary | Format-List
$run.weak_regime_diagnostics | Format-List

$run.candidate_scorecard |
  Select-Object `
    candidate_code,
    parent_candidate_code,
    candidate_role,
    lineage_track,
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
    weak_regime_pick_count,
    weak_regime_avg_ret_net,
    weak_regime_median_ret_net,
    weak_regime_win_rate,
    weak_regime_month_coverage,
    weak_regime_branch_count,
    weak_regime_bucket_count,
    weak_regime_ticker_count,
    weak_regime_survival_pass,
    rolling_validation_pass,
    loo_validation_pass,
    regime_robustness_validation_pass,
    regime_aware_concentration_pass,
    concentration_validation_pass,
    loss_cluster_validation_pass,
    sample_recovery_pass,
    weak_regime_sample_recovery_pass,
    material_selection_difference_pass,
    anti_shared_core_pass,
    overall_is_redesign_pass,
    candidate_ready_for_c61,
    failure_reason_codes |
  Format-Table -AutoSize

$run.regime_stress_validation_results |
  Select-Object `
    candidate_code,
    weakest_regime,
    weak_regime_expected_name,
    weak_regime_pick_count,
    weak_regime_sample_floor_pass,
    weak_regime_month_coverage,
    weak_regime_month_coverage_pass,
    weak_regime_avg_ret_net,
    weak_regime_median_ret_net,
    weak_regime_win_rate,
    weak_regime_branch_count,
    weak_regime_bucket_count,
    weak_regime_ticker_count,
    weak_regime_concentration_pass,
    weak_regime_survival_pass,
    failure_reason_codes |
  Format-Table -AutoSize

$run.regime_aware_concentration_results |
  Select-Object `
    candidate_code,
    max_ticker_share,
    max_sector_share,
    max_bucket_share,
    max_branch_share,
    weak_regime_max_ticker_share,
    weak_regime_max_sector_share,
    weak_regime_max_bucket_share,
    weak_regime_max_branch_share,
    weak_regime_unique_ticker_count,
    weak_regime_unique_sector_count,
    weak_regime_unique_bucket_count,
    weak_regime_unique_branch_count,
    regime_aware_concentration_pass,
    failure_reason_codes |
  Format-Table -AutoSize

$run.loss_cluster_validation_results |
  Select-Object `
    candidate_code,
    loss_cluster_share,
    loss_cluster_count,
    loss_cluster_trade_count,
    loss_cluster_month_count,
    loss_cluster_branch_count,
    loss_cluster_bucket_count,
    loss_cluster_ticker_count,
    loss_cluster_pre_trade_guard_pass,
    loss_cluster_validation_pass,
    loss_cluster_improved_or_retained_vs_c59,
    failure_reason_codes |
  Format-Table -AutoSize

$run.concentration_dependency_validation_results |
  Select-Object `
    candidate_code,
    max_ticker_share,
    max_sector_share,
    max_bucket_share,
    max_branch_share,
    max_month_share,
    unique_ticker_count,
    unique_sector_count,
    unique_bucket_count,
    unique_branch_count,
    loss_cluster_share,
    concentration_validation_pass,
    failure_reason_codes |
  Format-Table -AutoSize

$run.rolling_validation_summary | Format-List
$run.leave_one_month_out_summary | Format-List
$run.regime_robustness_validation_summary | Format-List
$run.sample_recovery_summary | Format-List
$run.weak_regime_sample_recovery_summary | Format-List
$run.material_selection_difference_summary | Format-List
$run.anti_shared_core_summary | Format-List
$run.source_bias_validation_summary | Format-List
$run.c61_readiness_decision | Format-List
```

## Hash Artifact

```powershell
Get-FileHash storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json -Algorithm SHA1
```

## Acceptance Checks

Expected from the included generated artifact:

- `status=C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED`
- `reason_code=C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS`
- `c59_hash_match=true`
- `database_dictionary_read_summary.asof_safe=true`
- `database_dictionary_read_summary.future_lookup_detected=false`
- `database_dictionary_read_summary.oos_rows_requested=0`
- `production_ready=false`
- `c61_readiness_decision.direct_oos_proof_recommended=false`
- `c61_readiness_decision.oos_proof_unlocked=false`
- `c61_readiness_decision.candidate_ready_for_c61_count=0`
- `c61_readiness_decision.c61_recommendation=C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY`

## Final Operator Validation Evidence

Final evidence recorded after the top-level OOS/proof flags were added to the C60 artifact shape:

```text
PHPUNIT_C60=PASS OK (13 tests, 165 assertions)
FULL_WATCHLIST_PHPUNIT=PASS OK (863 tests, 17663 assertions)
C60_RUNTIME=COMPLETED
C60_STATUS=C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED
C60_REASON_CODE=C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS
C60_ARTIFACT_HASH=25a32ee9c4cb77ecc29103c86a1abf0826aea705
C60_FILE_SHA1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F
C59_HASH_MATCH=true
C61_CANDIDATE_READY_FOR_C61_COUNT=0
C61_REGIME_ROBUSTNESS_PASS_CANDIDATE_COUNT=0
C61_WEAK_REGIME_SURVIVAL_PASS_CANDIDATE_COUNT=0
C61_RECOMMENDATION=C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY
```

Top-level safety flags must inspect as:

```powershell
$run.production_ready
$run.direct_oos_proof_recommended
$run.oos_proof_unlocked
```

Expected:

```text
False
False
False
```

Final acceptance checks:

- `status=C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED`
- `reason_code=C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS`
- `artifact_hash=25a32ee9c4cb77ecc29103c86a1abf0826aea705`
- `Get-FileHash SHA1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F`
- `c59_hash_match=true`
- `database_dictionary_read_summary.asof_safe=true`
- `database_dictionary_read_summary.future_lookup_detected=false`
- `database_dictionary_read_summary.oos_rows_requested=0`
- `production_ready=false`
- `direct_oos_proof_recommended=false`
- `oos_proof_unlocked=false`
- `c61_readiness_decision.direct_oos_proof_recommended=false`
- `c61_readiness_decision.oos_proof_unlocked=false`
- `c61_readiness_decision.production_ready=false`
- `c61_readiness_decision.candidate_ready_for_c61_count=0`
- `c61_readiness_decision.regime_robustness_pass_candidate_count=0`
- `c61_readiness_decision.weak_regime_survival_pass_candidate_count=0`
- `c61_readiness_decision.c61_recommendation=C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY`
