# WS_C66_OPERATOR_VALIDATION_COMMANDS

C66 is production lock review only. C66 pass is not live deployment. It does not activate production catalog, does not deploy production, and does not mutate PLAN/CONFIRM.

## PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC66"
```

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c66-production-lock-review `
  --c65-artifact=storage/app/watchlist/backtest/c65-production-pre-lock-review.json `
  --expected-c65-hash=f08da5acc87ccbe0d88c39423c4321496230b01b `
  --expected-c65-file-sha1=115201C1F44C7C420ABA3251435F21B870EF9AE6 `
  --c64-artifact=storage/app/watchlist/backtest/c64-pre-oos-or-oos-proof-execution.json `
  --expected-c64-hash=767d860956e0f27eeedccdc30f73aa1d0e5a415b `
  --expected-c64-file-sha1=032C7BA7435799D83CC06EEDBC463A9AF2B123B3 `
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
  --output=storage/app/watchlist/backtest/c66-production-lock-review.json `
  --overwrite `
  --progress
```

## Inspect artifact

```powershell
$run = Get-Content storage/app/watchlist/backtest/c66-production-lock-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.production_lock_review_executed
$run.production_lock_review_pass
$run.production_catalog_lock_allowed
$run.production_catalog_activation_allowed
$run.production_deployment_allowed
$run.plan_confirm_mutation_allowed

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c65_lock_validation_summary | Format-List
$run.c64_lineage_validation_summary | Format-List
$run.c63_lineage_validation_summary | Format-List
$run.c62_lineage_validation_summary | Format-List
$run.c61_lineage_validation_summary | Format-List
$run.c60_lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.production_lock_decision | Format-List
$run.c67_readiness_decision | Format-List
$run.failure_attribution_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.c65_cleanup_note_summary | Format-List

$run.production_lock_candidate_scorecard |
  Select-Object `
    candidate_code,
    c66_role,
    parent_candidate_code,
    production_lock_review_pass,
    candidate_locked_for_production_catalog,
    production_catalog_lock_allowed,
    production_catalog_activation_allowed,
    production_deployment_allowed,
    plan_confirm_mutation_allowed,
    bad_month_governance_pass,
    weak_regime_governance_pass,
    concentration_governance_pass,
    loss_cluster_governance_pass,
    rolling_governance_pass,
    source_bias_governance_pass,
    shared_core_governance_pass,
    safety_and_leakage_governance_pass,
    plan_confirm_non_mutation_pass,
    production_catalog_activation_non_creation_pass,
    failure_reason_codes |
  Format-Table -AutoSize

$run.bad_month_governance_lock_review_results | Format-List
$run.weak_regime_governance_lock_review_results | Format-List
$run.concentration_loss_cluster_governance_summary | Format-List
$run.rolling_month_dependency_governance_summary | Format-List
$run.source_bias_shared_core_governance_summary | Format-List
$run.documentation_governance_summary | Format-List
```

## Hash artifact

```powershell
Get-FileHash storage/app/watchlist/backtest/c66-production-lock-review.json -Algorithm SHA1
```

Required interpretation: `production_catalog_lock_allowed=true` is only a locked decision artifact. `production_catalog_activation_allowed=false`, `production_deployment_allowed=false`, and `plan_confirm_mutation_allowed=false` must remain false. A01 remains comparator-only. bad-month risk remains documented. weak-regime risk remains documented.
---

## Final Operator Validation Result — C66

The final operator run completed successfully.

```text
FOCUSED_C66_PHPUNIT=PASS
FOCUSED_C66_PHPUNIT_RESULT=OK (28 tests, 214 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (1052 tests, 18878 assertions)
C66_RUNTIME=COMPLETED
C66_FINAL_STATUS=C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP
C66_REASON_CODE=C66_PRODUCTION_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP
C66_ARTIFACT_HASH=9ef0c2eed94f2ac9e6e8e348e93774c563f8e6d4
C66_FILE_SHA1=11936FC807140E9B0A18FD00B543B03C8AE2950C
PRODUCTION_LOCK_REVIEW_EXECUTED=true
PRODUCTION_LOCK_REVIEW_PASS=true
PRODUCTION_CATALOG_LOCK_ALLOWED=true
PRODUCTION_CATALOG_ACTIVATION_ALLOWED=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
PLAN_CONFIRM_MUTATION_ALLOWED=false
CANDIDATE_READY_FOR_C67_COUNT=2
C67_RECOMMENDATION=C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW
DOMINANT_BLOCKER=NONE
```

Required interpretation remains unchanged: `production_catalog_lock_allowed=true` is only a locked decision artifact. C66 does not activate production catalog, does not deploy production, and does not mutate PLAN/CONFIRM. The next governed step is `C67_PRODUCTION_CATALOG_ACTIVATION_REVIEW`.
