# WS C59 Operator Validation Commands

Run from project root.

## 1. Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC59"
```

## 2. Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## 3. Runtime

```powershell
php artisan watchlist:backtest-c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only `
  --c58-artifact=storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json `
  --expected-c58-hash=80d09de8053659bf01ce5b8b72d9e2d82cdf69dc `
  --expected-c58-file-sha1=FA6FE27604F6CDA664DCF90A251AF41672670700 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only.json `
  --progress
```

If rerun is intentional and the output already exists, add:

```powershell
  --overwrite
```

## 4. Inspect artifact

```powershell
$run = Get-Content storage/app/watchlist/backtest/c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only.json | ConvertFrom-Json

$run.database_dictionary_read_summary | Format-List
$run.c58_blocker_summary | Format-List
$run.candidate_generation_summary | Format-List

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
    rolling_validation_pass,
    loo_validation_pass,
    regime_robustness_validation_pass,
    concentration_validation_pass,
    loss_cluster_validation_pass,
    sample_recovery_pass,
    material_selection_difference_pass,
    anti_shared_core_pass,
    overall_is_redesign_pass,
    candidate_ready_for_c60,
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
$run.material_selection_difference_summary | Format-List
$run.anti_shared_core_summary | Format-List
$run.source_bias_validation_summary | Format-List
$run.c60_readiness_decision | Format-List
```

## 5. Hash artifact

```powershell
Get-FileHash storage/app/watchlist/backtest/c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only.json -Algorithm SHA1
```

## Expected acceptance checks

```text
PHPUNIT_C59=PASS
FULL_WATCHLIST_PHPUNIT=PASS
ARTISAN_C59_RUNTIME=COMPLETED
C58_HASH_MATCH=true
C58_FILE_SHA1_MATCH=true
DATABASE_DICTIONARY_READ_REQUIRED=true
DICTIONARY_MISSING_COVERAGE_DETECTED=false
ASOF_SAFE=true
FUTURE_LOOKUP_DETECTED=false
OOS_ROWS_REQUESTED=0
C57_REGIME_FULLY_EVALUABLE_RETAINED=true
LOSS_CLUSTER_METRICS_COMPUTED_FOR_ALL_CANDIDATES=true
CONCENTRATION_METRICS_COMPUTED_FOR_ALL_CANDIDATES=true
ROLLING_LOO_REGIME_REEVALUATED=true
SAMPLE_RECOVERY_REPORTED=true
ANTI_SHARED_CORE_REPORTED=true
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
NEXT_STEP_IS_EVIDENCE_BASED=true
```

If no candidate passes every IS gate, that is valid C59 evidence. Do not lower gates. Record the dominant blocker and continue IS-only.


## Final operator validation result

Operator validation completed successfully.

```text
PHPUNIT_C59=PASS OK (33 tests, 1101 assertions)
FULL_WATCHLIST_PHPUNIT=PASS OK (850 tests, 17498 assertions)
ARTISAN_C59_RUNTIME=COMPLETED
C59_STATUS=C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED
C59_REASON_CODE=C59_REGIME_ROBUSTNESS_GAP_REMAINS
C59_ARTIFACT_HASH=7ebd6f74bc90ffac358b410244d90b3c7c3c5456
C58_HASH_MATCH=true
C58_FILE_SHA1_MATCH=true
CANDIDATE_READY_FOR_C60_COUNT=0
ROLLING_VALIDATION_PASS_CANDIDATE_COUNT=5
CONCENTRATION_VALIDATION_PASS_CANDIDATE_COUNT=9
LOSS_CLUSTER_PASS_CANDIDATE_COUNT=5
LOO_VALIDATION_PASS_CANDIDATE_COUNT=2
REGIME_ROBUSTNESS_PASS_CANDIDATE_COUNT=0
SAMPLE_RECOVERY_PASS_CANDIDATE_COUNT=11
NEXT_STEP_RECOMMENDATION=C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
```

C59 final result is valid but non-promotable. Do not run OOS. Continue with C60 as IS-only.
