# WS C62 Operator Validation Commands

Run C62 focused PHPUnit:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC62"
```

Run full Watchlist PHPUnit:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Run C62 runtime:

```powershell
php artisan watchlist:backtest-c62-pre-lock-review-for-c61-signal-quality-candidates-is-only `
  --c61-artifact=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json `
  --expected-c61-hash=40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8 `
  --expected-c61-file-sha1=DEA3C807813DE81DB6776AB2C441C945D4E98EC6 `
  --c60-artifact=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json `
  --expected-c60-hash=25a32ee9c4cb77ecc29103c86a1abf0826aea705 `
  --expected-c60-file-sha1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json `
  --overwrite `
  --progress
```

Inspect top-level result:

```powershell
$run = Get-Content storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.production_ready
$run.direct_oos_proof_recommended
$run.oos_proof_unlocked
$run.pre_oos_unlocked

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c61_lock_validation_summary | Format-List
$run.c60_lineage_validation_summary | Format-List
$run.c61_ready_candidate_summary | Format-List
$run.candidate_ranking_summary | Format-List
$run.pre_lock_decision | Format-List
$run.c63_readiness_decision | Format-List
```

Inspect candidate scorecard:

```powershell
$run.pre_lock_candidate_scorecard |
  Select-Object `
    candidate_code,
    source_c61_candidate_code,
    parent_candidate_code,
    pre_lock_review_role,
    lineage_track,
    evaluated_picks_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    month_win_rate_min,
    bad_month_count,
    zero_win_month_count,
    worst_month,
    worst_month_pick_count,
    worst_month_win_rate,
    worst_month_avg_ret_net,
    worst_month_regime,
    weak_regime_pick_count,
    weak_regime_avg_ret_net,
    weak_regime_median_ret_net,
    weak_regime_win_rate,
    weak_regime_month_coverage,
    weak_regime_survival_pass,
    regime_robustness_validation_pass,
    rolling_validation_pass,
    loo_validation_pass,
    single_month_dependency_detected,
    bad_month_exposure_pass,
    month_dependency_pass,
    regime_aware_concentration_pass,
    concentration_validation_pass,
    loss_cluster_validation_pass,
    sample_recovery_pass,
    weak_regime_sample_recovery_pass,
    material_selection_difference_pass,
    anti_shared_core_pass,
    source_bias_validation_pass,
    safety_and_leakage_pass,
    pre_lock_review_pass,
    candidate_ready_for_c63,
    failure_reason_codes |
  Format-Table -AutoSize
```

Inspect audits:

```powershell
$run.month_dependency_audit_results | Format-Table -AutoSize
$run.bad_month_exposure_audit_results | Format-Table -AutoSize
$run.weak_regime_survival_revalidation_results | Format-Table -AutoSize
$run.regime_aware_concentration_revalidation_results | Format-Table -AutoSize
$run.loss_cluster_retention_revalidation_results | Format-Table -AutoSize
$run.rolling_stability_recheck_summary | Format-List
$run.leave_one_month_out_recheck_summary | Format-List
$run.material_selection_difference_recheck_summary | Format-List
$run.anti_shared_core_recheck_summary | Format-List
$run.source_bias_validation_summary | Format-List
$run.safety_and_leakage_audit_summary | Format-List
```

Hash C62 artifact:

```powershell
Get-FileHash storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json -Algorithm SHA1
```

Acceptance markers:

```text
PHPUNIT_C62=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C62_RUNTIME=COMPLETED
C61_HASH_MATCH=true
C61_FILE_SHA1_MATCH=true
C60_HASH_MATCH=true
C60_FILE_SHA1_MATCH=true
OOS_ROWS_REQUESTED=0
FUTURE_LOOKUP_DETECTED=false
RETURN_FIELDS_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
OOS_RETURN_USED_FOR_SELECTION=false
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
PRE_OOS_UNLOCKED=false
```
