# WS C67 Operator Validation Commands

C67 is production catalog activation review.
C67 pass is not live activation.
C67 pass is not live deployment.
C67 does not mutate PLAN/CONFIRM.
A01 remains comparator-only.
bad-month risk remains documented.
weak-regime risk remains documented.
activation execution is deferred to C68.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC67"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c67-production-catalog-activation-review `
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
  --output=storage/app/watchlist/backtest/c67-production-catalog-activation-review.json `
  --overwrite `
  --progress
```

## Inspect

```powershell
$run = Get-Content storage/app/watchlist/backtest/c67-production-catalog-activation-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.production_catalog_activation_review_executed
$run.production_catalog_activation_review_pass
$run.production_catalog_lock_allowed
$run.production_catalog_activation_allowed
$run.production_catalog_activation_execution_allowed
$run.production_deployment_allowed
$run.plan_confirm_mutation_allowed

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c66_lock_validation_summary | Format-List
$run.c65_lineage_validation_summary | Format-List
$run.c64_lineage_validation_summary | Format-List
$run.c63_lineage_validation_summary | Format-List
$run.c62_lineage_validation_summary | Format-List
$run.c61_lineage_validation_summary | Format-List
$run.c60_lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.production_catalog_activation_review_decision | Format-List
$run.c68_readiness_decision | Format-List
$run.failure_attribution_summary | Format-List
$run.production_activation_mutation_safety_summary | Format-List
$run.c65_cleanup_note_summary | Format-List

$run.production_catalog_activation_candidate_scorecard |
  Select-Object `
    candidate_code,
    c67_role,
    parent_candidate_code,
    production_catalog_activation_review_pass,
    candidate_ready_for_production_catalog_activation,
    production_catalog_lock_allowed,
    production_catalog_activation_allowed,
    production_catalog_activation_execution_allowed,
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
    production_mutation_safety_pass,
    plan_confirm_non_mutation_pass,
    production_catalog_activation_non_execution_pass,
    failure_reason_codes |
  Format-Table -AutoSize

$run.bad_month_activation_review_results | Format-List
$run.weak_regime_activation_review_results | Format-List
$run.concentration_loss_cluster_governance_summary | Format-List
$run.rolling_month_dependency_governance_summary | Format-List
$run.source_bias_shared_core_governance_summary | Format-List
$run.documentation_governance_summary | Format-List
```

## Artifact hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c67-production-catalog-activation-review.json -Algorithm SHA1
```
