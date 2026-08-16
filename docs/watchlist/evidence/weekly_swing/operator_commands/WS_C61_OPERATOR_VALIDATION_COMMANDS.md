# WS C61 Operator Validation Commands

## 1. PHPUnit C61

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC61"
```

Expected:

```text
OK
```

## 2. Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected:

```text
OK
```

## 3. Runtime

```powershell
php artisan watchlist:backtest-c61-signal-quality-rebuild-for-weak-regime-is-only `
  --c60-artifact=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json `
  --expected-c60-hash=25a32ee9c4cb77ecc29103c86a1abf0826aea705 `
  --expected-c60-file-sha1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json `
  --overwrite `
  --progress
```

Expected top-level runtime markers:

```text
status=C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED
production_ready=0
direct_oos_proof_recommended=0
oos_proof_unlocked=0
c60_hash_match=1
c60_file_sha1_match=1
```

## 4. Inspect Artifact

```powershell
$run = Get-Content storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.production_ready
$run.direct_oos_proof_recommended
$run.oos_proof_unlocked

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c60_blocker_summary | Format-List
$run.c60_improvement_retention_summary | Format-List
$run.weak_regime_signal_quality_diagnostics | Format-List
$run.candidate_generation_summary | Format-List
```

## 5. Candidate Scorecard

```powershell
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
    weak_regime_signal_quality_pass,
    weak_regime_market_confirmation_pass,
    weak_regime_sector_confirmation_pass,
    weak_regime_relative_strength_pass,
    weak_regime_volatility_risk_pass,
    weak_regime_liquidity_pass,
    weak_regime_entry_timing_quality_pass,
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
    candidate_ready_for_c62,
    failure_reason_codes |
  Format-Table -AutoSize
```

## 6. Weak-Regime Signal Quality

```powershell
$run.weak_regime_signal_quality_results |
  Select-Object `
    candidate_code,
    weakest_regime,
    weak_regime_expected_name,
    weak_regime_pick_count,
    weak_regime_quality_rank_coverage,
    weak_regime_quality_floor_pass,
    weak_regime_market_confirmation_pass,
    weak_regime_sector_confirmation_pass,
    weak_regime_relative_strength_pass,
    weak_regime_volatility_risk_pass,
    weak_regime_liquidity_pass,
    weak_regime_entry_timing_quality_pass,
    weak_regime_signal_quality_pass,
    failure_reason_codes |
  Format-Table -AutoSize
```

## 7. Regime Stress

```powershell
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
    weak_regime_signal_quality_pass,
    weak_regime_survival_pass,
    weak_regime_improved_vs_c60,
    weak_regime_improved_vs_c59,
    weak_regime_improved_vs_c58,
    failure_reason_codes |
  Format-Table -AutoSize
```

## 8. Retention Gates

```powershell
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
    improved_or_retained_vs_c60,
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
    loss_cluster_improved_or_retained_vs_c60,
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
```

## 9. Summary Sections

```powershell
$run.rolling_validation_summary | Format-List
$run.leave_one_month_out_summary | Format-List
$run.regime_robustness_validation_summary | Format-List
$run.sample_recovery_summary | Format-List
$run.weak_regime_sample_recovery_summary | Format-List
$run.material_selection_difference_summary | Format-List
$run.anti_shared_core_summary | Format-List
$run.source_bias_validation_summary | Format-List
$run.c62_readiness_decision | Format-List
```

## 10. Artifact SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json -Algorithm SHA1
```

## Acceptance Interpretation

C61 is accepted only if:

- C61 PHPUnit passes.
- Full Watchlist PHPUnit passes.
- Runtime artifact is generated.
- C60 artifact hash matches.
- C60 file SHA1 matches.
- Database dictionary rule is recorded.
- OOS rows requested is `0`.
- Future lookup is `false`.
- Return/future/OOS fields are not used for selection.
- Weak regime is not skipped.
- Replay comparator is not promoted.
- Production readiness remains false.
- Direct OOS proof remains locked.

If candidates are ready, they are ready only for C62/pre-lock review, not OOS proof.

---

## Final Operator Validation Result

C61 operator validation completed successfully.

```text
PHPUNIT_C61=PASS OK (15 tests, 206 assertions)
FULL_WATCHLIST_PHPUNIT=PASS OK (878 tests, 17872 assertions)
C61_RUNTIME=COMPLETED
C61_STATUS=C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED
C61_REASON_CODE=C61_WEAK_REGIME_SIGNAL_QUALITY_REBUILD_FOUND_C62_REVIEW_CANDIDATE
C61_ARTIFACT_HASH=40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8
C61_FILE_SHA1=DEA3C807813DE81DB6776AB2C441C945D4E98EC6
C60_HASH_MATCH=true
C60_FILE_SHA1_MATCH=true
C62_VALIDATION_COMPLETED=true
CANDIDATE_READY_FOR_C62_COUNT=3
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
PRODUCTION_READY=false
NEXT_STEP=C62_PRE_LOCK_REVIEW_FOR_C61_SIGNAL_QUALITY_CANDIDATES_IS_ONLY
```

Ready-for-C62 candidates:

```text
C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE
C61_A01_B01_WEAK_REGIME_QUALITY_FIRST
C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
```

Final interpretation:

- C61 is accepted as `DONE_OPERATOR_VALIDATED_NOT_PRODUCTION_READY`.
- The primary C62 candidate is `C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE`.
- `C61_A01_B01_WEAK_REGIME_QUALITY_FIRST` is the backup signal-quality comparator.
- `C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION` is the diversification/lineage comparator.
- All three are ready only for C62/pre-lock IS review.
- OOS remains locked.
- Production readiness remains false.
