# WS C68 Operator Validation Commands

C68 is production catalog activation execution review.
C68 starts from locked C67 final evidence.
E02 is primary activation execution candidate.
B01 is backup activation execution candidate.
A01 is comparator-only and cannot be promoted.
C68 does not redesign.
C68 does not retune.
C68 does not use OOS to rerank.
C68 may create controlled activation execution artifact/record.
C68 does not wire activated catalog to PLAN/CONFIRM.
C68 does not deploy production.
C68 does not mutate PLAN/CONFIRM.
bad-month risk remains documented.
weak-regime risk remains documented.
C68 pass is not production deployment.
C68 pass is not PLAN/CONFIRM rollout.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC68"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c68-production-catalog-activation-execution-review `
  --c67-artifact=storage/app/watchlist/backtest/c67-production-catalog-activation-review.json `
  --expected-c67-hash=5e3ba8ac20c810a36a7928ad1f201c82143ac72f `
  --expected-c67-file-sha1=CB98A7B5B4B5F0CCCEDEF0C7B5BDC8CB3FE940E6 `
  --c66-artifact=storage/app/watchlist/backtest/c66-production-lock-review.json `
  --expected-c66-hash=9ef0c2eed94f2ac9e6e8e348e93774c563f8e6d4 `
  --expected-c66-file-sha1=11936FC807140E9B0A18FD00B543B03C8AE2950C `
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
  --output=storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json `
  --overwrite `
  --progress
```

## Inspect

```powershell
$run = Get-Content storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.production_catalog_activation_execution_review_executed
$run.production_catalog_activation_execution_review_pass
$run.production_catalog_lock_allowed
$run.production_catalog_activation_allowed
$run.production_catalog_activation_execution_allowed
$run.production_catalog_activation_execution_performed
$run.production_catalog_activated
$run.production_catalog_runtime_wired
$run.production_deployment_allowed
$run.production_deployment_executed
$run.plan_confirm_mutation_allowed
$run.plan_confirm_mutated
$run.plan_confirm_runtime_reads_activated_catalog

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c67_lock_validation_summary | Format-List
$run.c66_lineage_validation_summary | Format-List
$run.c65_lineage_validation_summary | Format-List
$run.c64_lineage_validation_summary | Format-List
$run.c63_lineage_validation_summary | Format-List
$run.c62_lineage_validation_summary | Format-List
$run.c61_lineage_validation_summary | Format-List
$run.c60_lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.production_catalog_activation_execution_decision | Format-List
$run.production_catalog_activation_record | Format-List
$run.c69_readiness_decision | Format-List
$run.failure_attribution_summary | Format-List
$run.production_activation_execution_mutation_safety_summary | Format-List
$run.c65_cleanup_note_summary | Format-List

$run.production_catalog_activation_execution_candidate_scorecard |
  Select-Object `
    candidate_code,
    c68_role,
    parent_candidate_code,
    production_catalog_activation_execution_review_pass,
    candidate_active_in_production_catalog,
    candidate_ready_for_deployment_prep_review,
    production_catalog_activation_execution_allowed,
    production_catalog_activation_execution_performed,
    production_catalog_activated,
    production_catalog_runtime_wired,
    production_deployment_allowed,
    production_deployment_executed,
    plan_confirm_mutation_allowed,
    plan_confirm_mutated,
    bad_month_governance_pass,
    weak_regime_governance_pass,
    concentration_governance_pass,
    loss_cluster_governance_pass,
    rolling_governance_pass,
    source_bias_governance_pass,
    shared_core_governance_pass,
    safety_and_leakage_governance_pass,
    activation_mutation_safety_pass,
    deployment_non_execution_pass,
    plan_confirm_non_mutation_pass,
    failure_reason_codes |
  Format-Table -AutoSize

$run.bad_month_activation_execution_review_results | Format-List
$run.weak_regime_activation_execution_review_results | Format-List
$run.concentration_loss_cluster_governance_summary | Format-List
$run.rolling_month_dependency_governance_summary | Format-List
$run.source_bias_shared_core_governance_summary | Format-List
$run.documentation_governance_summary | Format-List
```

## Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json -Algorithm SHA1
```

---

## Final Operator Evidence

The final operator validation completed successfully.

```text
PHPUNIT_C68=PASS
PHPUNIT_C68_RESULT=OK (22 tests, 241 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (1093 tests, 19331 assertions)
C68_RUNTIME=COMPLETED
C68_FINAL_STATUS=C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
C68_REASON_CODE=C68_PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
C68_ARTIFACT_HASH=54145854758e22115e4b65a297e4c157d94c638d
C68_FILE_SHA1=209E3225F37015DA348EC2DA9A0D6A3FFCC6E4F7
NEXT_STEP_RECOMMENDATION=C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW
```

Final activation boundary:

```text
PRODUCTION_CATALOG_ACTIVATION_EXECUTION_REVIEW_PASS=true
PRODUCTION_CATALOG_ACTIVATION_EXECUTION_PERFORMED=true
PRODUCTION_CATALOG_ACTIVATED=true
PRODUCTION_CATALOG_RUNTIME_WIRED=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
PRODUCTION_DEPLOYMENT_EXECUTED=false
PLAN_CONFIRM_MUTATION_ALLOWED=false
PLAN_CONFIRM_MUTATED=false
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=false
```

Final candidate result:

```text
E02_PRIMARY_ACTIVATION_EXECUTION_PASS=true
B01_BACKUP_ACTIVATION_EXECUTION_PASS=true
A01_COMPARATOR_ONLY=true
A01_PROMOTED=false
```

Final operator decision: C68 passed. This is controlled activation artifact/record only, not production deployment and not PLAN/CONFIRM rollout.

