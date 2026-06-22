# WS C64 Operator Validation Commands

Status: `FINAL_OPERATOR_VALIDATED`

Run C64 focused PHPUnit:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC64"
```

Run full Watchlist PHPUnit:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Run C64 runtime:

```powershell
php artisan watchlist:backtest-c64-pre-oos-or-oos-proof-execution `
  --c63-artifact=storage/app/watchlist/backtest/c63-pre-oos-unlock-review-is-only.json `
  --expected-c63-hash=e98f1386928b36ee367728ceeec4de4344e1f3be `
  --expected-c63-file-sha1=24C7EE585A165DA41E8FC22538A68145247C68B4 `
  --c62-artifact=storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json `
  --expected-c62-hash=d3a089b9b986838764d517682035d76e0bb4112d `
  --expected-c62-file-sha1=8DF1649BC72233D119581A802F9E41BA9BEBF12E `
  --c61-artifact=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json `
  --expected-c61-hash=40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8 `
  --expected-c61-file-sha1=DEA3C807813DE81DB6776AB2C441C945D4E98EC6 `
  --c60-artifact=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json `
  --expected-c60-hash=25a32ee9c4cb77ecc29103c86a1abf0826aea705 `
  --expected-c60-file-sha1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F `
  --is-from=2023-01-02 `
  --is-to=2025-05-21 `
  --oos-from=2025-05-22 `
  --oos-to=2026-05-29 `
  --output=storage/app/watchlist/backtest/c64-pre-oos-or-oos-proof-execution.json `
  --overwrite `
  --progress
```

Inspect top-level result:

```powershell
$run = Get-Content storage/app/watchlist/backtest/c64-pre-oos-or-oos-proof-execution.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.production_ready
$run.oos_proof_executed
$run.oos_proof_pass
$run.direct_oos_proof_recommended
$run.oos_proof_unlocked
$run.pre_oos_unlocked

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c63_lock_validation_summary | Format-List
$run.c62_lineage_validation_summary | Format-List
$run.c61_lineage_validation_summary | Format-List
$run.c60_lineage_validation_summary | Format-List
$run.selection_freeze_summary | Format-List
$run.oos_period_summary | Format-List
$run.oos_proof_decision | Format-List
$run.c65_readiness_decision | Format-List
$run.failure_attribution_summary | Format-List
```

Inspect OOS candidate scorecards:

```powershell
$run.oos_proof_candidate_scorecard |
  Select-Object `
    candidate_code,
    c64_oos_role,
    parent_candidate_code,
    oos_evaluated_picks_count,
    oos_trading_days_covered,
    oos_first_trade_date,
    oos_last_trade_date,
    oos_avg_ret_net,
    oos_median_ret_net,
    oos_win_rate,
    oos_month_count,
    oos_month_win_rate_min,
    oos_bad_month_count,
    oos_zero_win_month_count,
    oos_worst_month,
    oos_worst_month_pick_count,
    oos_worst_month_win_rate,
    oos_worst_month_avg_ret_net,
    oos_worst_month_regime,
    oos_weak_regime_pick_count,
    oos_weak_regime_avg_ret_net,
    oos_weak_regime_median_ret_net,
    oos_weak_regime_win_rate,
    oos_weak_regime_month_coverage,
    oos_concentration_validation_pass,
    oos_loss_cluster_validation_pass,
    oos_rolling_validation_pass,
    oos_bad_month_validation_pass,
    oos_weak_regime_validation_pass,
    oos_source_bias_validation_pass,
    oos_shared_core_validation_pass,
    oos_safety_and_leakage_pass,
    oos_proof_pass,
    candidate_ready_for_c65,
    failure_reason_codes |
  Format-Table -AutoSize
```

Inspect review tracks:

```powershell
$run.oos_bad_month_review_results | Format-List
$run.oos_weak_regime_review_results | Format-List
$run.oos_concentration_review_results | Format-List
$run.oos_loss_cluster_review_results | Format-List
$run.oos_rolling_review_summary | Format-List
$run.oos_month_dependency_review_summary | Format-List
$run.oos_shared_core_review_summary | Format-List
$run.oos_source_bias_review_summary | Format-List
$run.oos_safety_and_leakage_audit_summary | Format-List
```

Hash artifact:

```powershell
Get-FileHash storage/app/watchlist/backtest/c64-pre-oos-or-oos-proof-execution.json -Algorithm SHA1
```

Expected invariant regardless pass/fail:

```text
production_ready=false
direct_oos_proof_recommended=false
oos_proof_unlocked=false
pre_oos_unlocked=false
A01 remains comparator-only
No production catalog created
No PLAN/CONFIRM mutation
No retuning after OOS
No selection change after OOS
```


---

## Final Operator Validation Result

C64 operator validation completed successfully.

```text
PHPUNIT_C64=PASS OK (67 tests, 190 assertions)
FULL_WATCHLIST_PHPUNIT=PASS OK (996 tests, 18471 assertions)
C64_RUNTIME=COMPLETED
C64_STATUS=C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP
C64_REASON_CODE=C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP
C64_ARTIFACT_HASH=767d860956e0f27eeedccdc30f73aa1d0e5a415b
C64_FILE_SHA1=032C7BA7435799D83CC06EEDBC463A9AF2B123B3
OOS_PROOF_EXECUTED=true
OOS_PROOF_PASS=true
OOS_PASS_SCOPE=PRIMARY_AND_BACKUP
CANDIDATE_READY_FOR_C65_COUNT=2
C65_RECOMMENDATION=C65_PRODUCTION_PRE_LOCK_REVIEW
PRODUCTION_READY=false
```

Final candidate result:

```text
E02_PRIMARY_READY_FOR_C65=true
B01_BACKUP_READY_FOR_C65=true
A01_COMPARATOR_ONLY_READY_FOR_C65=false
A01_FAILURE_REASON_CODES={C64_A01_REMAINS_COMPARATOR_ONLY}
```

Final safety result:

```text
SELECTION_FROZEN_BEFORE_OOS=true
OOS_READ_BEFORE_SELECTION_FREEZE=false
SELECTION_CHANGED_AFTER_OOS=false
PARAMETER_CHANGED_AFTER_OOS=false
FUTURE_LOOKUP_DETECTED=false
ASOF_SAFE=true
LATEST_SHORTCUT_USED=false
MAX_DATE_SHORTCUT_USED=false
PRODUCTION_CATALOG_CREATED=false
PLAN_CONFIRM_MUTATED=false
OOS_SAFETY_AND_LEAKAGE_PASS=true
```

C64 validation is accepted. The only next step is `C65_PRODUCTION_PRE_LOCK_REVIEW`; C64 remains `production_ready=false`.
