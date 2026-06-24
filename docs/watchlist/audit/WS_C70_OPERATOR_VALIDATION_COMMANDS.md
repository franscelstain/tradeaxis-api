# WS_C70_OPERATOR_VALIDATION_COMMANDS

C70 is controlled production deployment execution review.
C70 starts from locked C69 final evidence.
E02 is primary controlled deployment execution candidate.
B01 is backup controlled deployment execution candidate.
A01 is comparator-only and cannot be promoted.

C70 validates C69 artifact hash and file SHA1.
C70 validates C69 readiness through nested `c70_readiness_decision.*` path.
C70 validates C69 → C60 lineage.
C70 does not redesign.
C70 does not retune.
C70 does not run parameter search.
C70 does not use OOS to rerank.
C70 does not change candidate scope.
C70 does not wire activated catalog to PLAN/CONFIRM live.
C70 does not deploy live production.
C70 does not mutate PLAN/CONFIRM.
C70 does not change PLAN/CONFIRM output.
C70 keeps `production_catalog_runtime_wired=false`.
C70 keeps `production_deployment_allowed=false`.
C70 keeps `production_deployment_executed=false`.
C70 keeps `plan_confirm_mutation_allowed=false`.
C70 keeps `plan_confirm_mutated=false`.
C70 keeps `plan_confirm_runtime_reads_activated_catalog=false`.
C70 keeps `live_plan_confirm_rollout_allowed=false`.
C70 keeps `live_plan_confirm_rollout_executed=false`.
C70 carries bad-month risk as documented risk.
C70 carries weak-regime risk as documented risk.
C70 carries source-bias/shared-core risk as documented risk.
C65 cleanup note remains non-blocking.
C70 pass is not full production deployment.
C70 pass is not PLAN/CONFIRM rollout.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC70"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c70-production-deployment-execution-review `
  --c69-artifact=storage/app/watchlist/backtest/c69-production-deployment-prep-or-bridge-review.json `
  --expected-c69-hash=477a279a1f35cfafb811f5984e7a329f72d3f08e `
  --expected-c69-file-sha1=82BAF5F192AF0C4680303F7A0409D0EA446A8192 `
  --c68-artifact=storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json `
  --expected-c68-hash=54145854758e22115e4b65a297e4c157d94c638d `
  --expected-c68-file-sha1=209E3225F37015DA348EC2DA9A0D6A3FFCC6E4F7 `
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
  --output=storage/app/watchlist/backtest/c70-production-deployment-execution-review.json `
  --overwrite `
  --progress
```

## Inspect

```powershell
$run = Get-Content storage/app/watchlist/backtest/c70-production-deployment-execution-review.json | ConvertFrom-Json
$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.production_deployment_execution_review_executed
$run.production_deployment_execution_review_pass
$run.controlled_production_deployment_execution_review_allowed
$run.controlled_production_deployment_execution_review_pass
$run.production_catalog_runtime_wired
$run.production_deployment_allowed
$run.production_deployment_executed
$run.plan_confirm_mutation_allowed
$run.plan_confirm_mutated
$run.plan_confirm_runtime_reads_activated_catalog
$run.live_plan_confirm_rollout_allowed
$run.live_plan_confirm_rollout_executed
$run.c71_readiness_decision | Format-List
$run.production_deployment_execution_candidate_scorecard | Format-Table -AutoSize
Get-FileHash storage/app/watchlist/backtest/c70-production-deployment-execution-review.json -Algorithm SHA1
```

## Final Operator Evidence — 2026-06-24

The final operator run used `tradeaxis-api_C70.zip` as source of truth.

Expected final markers already observed by operator:

```text
ROOT_ALIGNMENT_NOTE_FILE_PRESENT=false
OLD_C69_LOCK_REFERENCES_PRESENT=false
PHPUNIT_C70=PASS: OK (22 tests, 254 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (1141 tests, 19903 assertions)
C70_RUNTIME=COMPLETED
C70_FINAL_STATUS=C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
C70_REASON_CODE=C70_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
C70_ARTIFACT_HASH=d148bfa0e277387a4d2a1348904117bc8772bce2
C70_FILE_SHA1=436657CCA085C88B425A2BD402AD425C810D477B
C69_ARTIFACT_HASH=477a279a1f35cfafb811f5984e7a329f72d3f08e
C69_FILE_SHA1=82BAF5F192AF0C4680303F7A0409D0EA446A8192
C69_HASH_MATCH=true
C69_FILE_SHA1_MATCH=true
PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASS=true
CONTROLLED_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_ALLOWED=true
CONTROLLED_PRODUCTION_DEPLOYMENT_EXECUTION_REVIEW_PASS=true
PRODUCTION_READY=false
PRODUCTION_CATALOG_RUNTIME_WIRED=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
PRODUCTION_DEPLOYMENT_EXECUTED=false
PLAN_CONFIRM_MUTATION_ALLOWED=false
PLAN_CONFIRM_MUTATED=false
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=false
LIVE_PLAN_CONFIRM_ROLLOUT_ALLOWED=false
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=false
C71_CANDIDATE_READY_FOR_C71_COUNT=2
C71_RECOMMENDATION=C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION
```

Final operator artifact hash command:

```powershell
Get-FileHash storage/app/watchlist/backtest/c70-production-deployment-execution-review.json -Algorithm SHA1
```

Expected final operator SHA1:

```text
436657CCA085C88B425A2BD402AD425C810D477B
```
