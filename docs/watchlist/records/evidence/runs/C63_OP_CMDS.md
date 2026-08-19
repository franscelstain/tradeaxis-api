# WS C63 Operator Validation Commands

Run C63 focused PHPUnit:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC63"
```

Run full Watchlist PHPUnit:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```


Before runtime, optional C62 readiness check:

```powershell
$c62 = Get-Content storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json | ConvertFrom-Json
$c62.c63_readiness_decision | Format-List
```

Expected nested readiness:

```text
candidate_ready_for_c63_count=2
c63_recommendation=C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY
```

Run C63 runtime:

```powershell
php artisan watchlist:backtest-c63-pre-oos-unlock-review-is-only `
  --c62-artifact=storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json `
  --expected-c62-hash=d3a089b9b986838764d517682035d76e0bb4112d `
  --expected-c62-file-sha1=8DF1649BC72233D119581A802F9E41BA9BEBF12E `
  --c61-artifact=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json `
  --expected-c61-hash=40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8 `
  --expected-c61-file-sha1=DEA3C807813DE81DB6776AB2C441C945D4E98EC6 `
  --c60-artifact=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json `
  --expected-c60-hash=25a32ee9c4cb77ecc29103c86a1abf0826aea705 `
  --expected-c60-file-sha1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c63-pre-oos-unlock-review-is-only.json `
  --overwrite `
  --progress
```

Inspect top-level result:

```powershell
$run = Get-Content storage/app/watchlist/backtest/c63-pre-oos-unlock-review-is-only.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.production_ready
$run.direct_oos_proof_recommended
$run.oos_proof_unlocked
$run.pre_oos_unlocked

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c62_lock_validation_summary | Format-List
$run.c61_lineage_validation_summary | Format-List
$run.c60_lineage_validation_summary | Format-List
$run.c62_decision_replay_summary | Format-List
$run.unlock_hierarchy_summary | Format-List
$run.pre_oos_unlock_decision | Format-List
$run.c64_readiness_decision | Format-List
```

Inspect unlock candidate scorecard:

```powershell
$run.unlock_candidate_scorecard |
  Select-Object `
    candidate_code,
    c62_review_role,
    c63_unlock_review_role,
    parent_candidate_code,
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
    bad_month_risk_level,
    bad_month_risk_acceptable_for_unlock,
    weak_regime_pick_count,
    weak_regime_avg_ret_net,
    weak_regime_median_ret_net,
    weak_regime_win_rate,
    weak_regime_month_coverage,
    weak_regime_unlock_ready,
    rolling_unlock_ready,
    loo_unlock_ready,
    single_month_dependency_detected,
    concentration_unlock_ready,
    loss_cluster_unlock_ready,
    shared_core_unlock_ready,
    source_bias_unlock_ready,
    parent_diversity_sufficient,
    safety_and_leakage_unlock_pass,
    pre_oos_unlock_review_pass,
    candidate_ready_for_c64,
    failure_reason_codes |
  Format-Table -AutoSize
```

Inspect bad-month and weak-regime reviews:

```powershell
$run.bad_month_unlock_review_results |
  Select-Object `
    candidate_code,
    worst_month,
    worst_month_pick_count,
    worst_month_win_rate,
    worst_month_avg_ret_net,
    worst_month_regime,
    zero_win_month_count,
    bad_month_risk_level,
    bad_month_risk_acceptable_for_unlock,
    bad_month_unlock_decision,
    failure_reason_codes |
  Format-Table -AutoSize

$run.weak_regime_unlock_review_results |
  Select-Object `
    candidate_code,
    weakest_regime,
    weak_regime_expected_name,
    weak_regime_pick_count,
    weak_regime_month_coverage,
    weak_regime_avg_ret_net,
    weak_regime_median_ret_net,
    weak_regime_win_rate,
    weak_regime_branch_count,
    weak_regime_bucket_count,
    weak_regime_ticker_count,
    weak_regime_unlock_ready,
    weak_regime_unlock_risk_level,
    sample_collapse_detected,
    weak_regime_improved_vs_c60,
    weak_regime_improved_vs_c59,
    weak_regime_improved_vs_c58,
    failure_reason_codes |
  Format-Table -AutoSize
```

Inspect concentration and loss-cluster reviews:

```powershell
$run.concentration_unlock_review_results |
  Select-Object `
    candidate_code,
    max_ticker_share,
    max_sector_share,
    max_bucket_share,
    max_branch_share,
    max_month_share,
    weak_regime_max_ticker_share,
    weak_regime_max_sector_share,
    weak_regime_max_bucket_share,
    weak_regime_max_branch_share,
    weak_regime_unique_ticker_count,
    weak_regime_unique_sector_count,
    weak_regime_unique_bucket_count,
    weak_regime_unique_branch_count,
    concentration_unlock_ready,
    improved_or_retained_vs_c60,
    failure_reason_codes |
  Format-Table -AutoSize

$run.loss_cluster_unlock_review_results |
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
    loss_cluster_unlock_ready,
    loss_cluster_improved_or_retained_vs_c60,
    failure_reason_codes |
  Format-Table -AutoSize
```

Inspect remaining summaries:

```powershell
$run.rolling_unlock_review_summary | Format-List
$run.loo_unlock_review_summary | Format-List
$run.shared_core_unlock_review_summary | Format-List
$run.source_bias_unlock_review_summary | Format-List
$run.safety_and_leakage_unlock_audit_summary | Format-List
```

Hash C63 artifact:

```powershell
Get-FileHash storage/app/watchlist/backtest/c63-pre-oos-unlock-review-is-only.json -Algorithm SHA1
```

Acceptance markers:

```text
PHPUNIT_C63=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C63_RUNTIME=COMPLETED_OR_REJECTED_WITH_VALID_BLOCKER
C62_HASH_MATCH=true
C62_FILE_SHA1_MATCH=true
C61_HASH_MATCH=true
C61_FILE_SHA1_MATCH=true
C60_HASH_MATCH=true
C60_FILE_SHA1_MATCH=true
OOS_ROWS_REQUESTED=0
FUTURE_LOOKUP_DETECTED=false
RETURN_FIELDS_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
OOS_RETURN_USED_FOR_SELECTION=false
PRODUCTION_CATALOG_CREATED=false
PLAN_CONFIRM_MUTATED=false
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
PRE_OOS_UNLOCKED=false
```

C63 approval means only `ready for C64 pre-OOS/OOS proof execution review`. It is not OOS-proven and not production-ready.


---

## Final Operator Validation Result

Final operator validation completed:

```text
PHPUNIT_C63=PASS OK (29 tests, 183 assertions)
FULL_WATCHLIST_PHPUNIT=PASS OK (929 tests, 18281 assertions)
C63_RUNTIME=COMPLETED
C63_STATUS=C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP
C63_REASON_CODE=C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP
C63_ARTIFACT_HASH=e98f1386928b36ee367728ceeec4de4344e1f3be
C63_FILE_SHA1=24C7EE585A165DA41E8FC22538A68145247C68B4
NEXT_STEP_RECOMMENDATION=C64_PRE_OOS_OR_OOS_PROOF_EXECUTION
```

Expected final safety markers:

```text
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
PRE_OOS_UNLOCKED=false
OOS_ROWS_REQUESTED=0
OOS_RETURN_USED_FOR_SELECTION=false
FUTURE_LOOKUP_DETECTED=false
ASOF_SAFE=true
PRODUCTION_CATALOG_CREATED=false
PLAN_CONFIRM_MUTATED=false
```

Expected final hierarchy markers:

```text
PRIMARY_UNLOCK_CANDIDATE=C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE
BACKUP_UNLOCK_CANDIDATE=C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
COMPARATOR_ONLY=C61_A01_B01_WEAK_REGIME_QUALITY_FIRST
REJECTED_CANDIDATES=[]
CANDIDATE_READY_FOR_C64_COUNT=2
C64_RECOMMENDATION=C64_PRE_OOS_OR_OOS_PROOF_EXECUTION
```

Final C63 interpretation: accepted as IS-only pre-OOS unlock review. C63 recommends C64 review execution only and does not claim OOS proof or production readiness.
