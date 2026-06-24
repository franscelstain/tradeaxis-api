# WS_C74_OPERATOR_VALIDATION_COMMANDS

C74 is controlled operator-reviewed rollout gate / deployment readiness review.

C74 starts from locked C73 final evidence.

C74 pass is not full production deployment.

C74 pass is not PLAN/CONFIRM live rollout.

C74 does not mutate PLAN/CONFIRM.

C74 does not change PLAN/CONFIRM output.

C74 keeps `production_catalog_runtime_wired=false`, `controlled_opt_in_runtime_bridge_active=false`, `controlled_parallel_run_active=false`, `controlled_rollout_active=false`, `production_deployment_allowed=false`, `production_deployment_executed=false`, `plan_confirm_mutation_allowed=false`, `plan_confirm_mutated=false`, `plan_confirm_runtime_reads_activated_catalog=false`, `live_plan_confirm_rollout_allowed=false`, and `live_plan_confirm_rollout_executed=false`.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC74"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review `
  --c73-artifact=storage/app/watchlist/backtest/c73-controlled-parallel-run-non-mutating-plan-confirm-bridge-validation.json `
  --expected-c73-hash=34f1f84a4261da7ce1cb9d17a1bf33dfb1458281 `
  --expected-c73-file-sha1=BF18CAA2654D5A7DE5419DE5DAF42E0B55D73CC9 `
  --output=storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json `
  --operator-reviewed `
  --overwrite `
  --progress
```

Expected runtime status if all C74 gates pass:

```text
C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_PASSED_PRIMARY_AND_BACKUP
```

Expected C75 recommendation if all C74 gates pass:

```text
C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW
```

## Inspect artifact

```powershell
$run = Get-Content storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.controlled_operator_reviewed_rollout_gate_validation_executed
$run.controlled_operator_reviewed_rollout_gate_validation_allowed
$run.controlled_operator_reviewed_rollout_gate_validation_pass
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
$run.production_deployment_allowed
$run.production_deployment_executed
$run.plan_confirm_mutation_allowed
$run.plan_confirm_mutated
$run.plan_confirm_runtime_reads_activated_catalog
$run.live_plan_confirm_rollout_allowed
$run.live_plan_confirm_rollout_executed
$run.source_artifact_locks | Format-List
$run.c73_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.controlled_operator_reviewed_rollout_gate_validation_decision | Format-List
$run.c75_readiness_decision | Format-List
$run.failure_attribution_summary | Format-List
```

## Artifact SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json -Algorithm SHA1
```

## Negative manual test: without operator review

```powershell
php artisan watchlist:backtest-c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review `
  --c73-artifact=storage/app/watchlist/backtest/c73-controlled-parallel-run-non-mutating-plan-confirm-bridge-validation.json `
  --expected-c73-hash=34f1f84a4261da7ce1cb9d17a1bf33dfb1458281 `
  --expected-c73-file-sha1=BF18CAA2654D5A7DE5419DE5DAF42E0B55D73CC9 `
  --output=storage/app/watchlist/backtest/c74-no-operator-review-test.json `
  --overwrite `
  --progress
```

Expected negative status:

```text
C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Cleanup:

```powershell
Remove-Item storage/app/watchlist/backtest/c74-no-operator-review-test.json
```

## Final operator results — 2026-06-24

```text
Focused PHPUnit C74:
OK (40 tests, 227 assertions)

Full Watchlist PHPUnit:
OK (1245 tests, 20920 assertions)

Runtime C74 status:
C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_PASSED_PRIMARY_AND_BACKUP

Runtime C74 reason_code:
C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_PASSED_PRIMARY_AND_BACKUP

Runtime C74 artifact_hash:
2e02737a212cf9043d5937f5354a3c31541dc22f

Runtime C74 file SHA1:
C7FCA9797AFF0B2B3CD4B37E587DC646F01C2187
```

Final negative manual test result:

```text
C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Final cleanup result:

```text
storage/app/watchlist/backtest/c74-no-operator-review-test.json removed
```

