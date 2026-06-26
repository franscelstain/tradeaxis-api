# WS_C84_OPERATOR_VALIDATION_COMMANDS

C84 is controlled limited runtime opt-in pilot / shadow rollout activation execution review.
C84 starts from locked C83 final evidence.
C83 activation authorization review passed authorization for primary + backup.
E02 is primary activation execution candidate.
B01 is backup activation execution candidate.
A01 is comparator-only and cannot be promoted.
C84 validates C83 artifact hash and file SHA1.
C84 validates C83 readiness through nested next_readiness_decision.* path.
C84 validates C83 -> C60 lineage.
C84 requires --operator-approved.
C84 requires non-empty --approval-reference.
C84 creates controlled activation execution record only.
C84 does not redesign.
C84 does not retune.
C84 does not run parameter search.
C84 does not use OOS to rerank.
C84 does not use activation execution to rerank.
C84 does not use activation execution to deploy.
C84 does not change candidate scope.
C84 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C84 does not deploy live production.
C84 does not mutate PLAN/CONFIRM.
C84 does not change PLAN/CONFIRM output.
C84 keeps production_catalog_runtime_wired=false.
C84 keeps controlled_opt_in_runtime_bridge_active=false.
C84 keeps controlled_parallel_run_active=false.
C84 keeps controlled_rollout_active=false.
C84 keeps activation_execution_context_persisted_to_live_runtime=false.
C84 keeps production_deployment_allowed=false.
C84 keeps production_deployment_executed=false.
C84 keeps plan_confirm_mutation_allowed=false.
C84 keeps plan_confirm_mutated=false.
C84 keeps plan_confirm_runtime_reads_activated_catalog=false.
C84 keeps live_plan_confirm_rollout_allowed=false.
C84 keeps live_plan_confirm_rollout_executed=false.
C84 activation execution means continue to C85 post-activation observation review only.
C84 activation execution record is not production deployment.
C84 activation execution record is not PLAN/CONFIRM live rollout.
C84 activation execution record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC84"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review `
  --c83-artifact=storage/app/watchlist/backtest/c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review.json `
  --expected-c83-hash=2927dea9624be20ea493c9e449b57879e0ea5da7 `
  --expected-c83-file-sha1=E90EA61673FB7820988507670F547CD6F02D6A5F `
  --approval-reference=C84_OPERATOR_APPROVED_ACTIVATION_EXECUTION_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW_PASSED_EXECUTED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c83_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.activation_execution_decision | Format-List
$run.activation_execution_candidate_scorecard | Format-List
$run.activation_execution_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review `
  --c83-artifact=storage/app/watchlist/backtest/c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review.json `
  --expected-c83-hash=2927dea9624be20ea493c9e449b57879e0ea5da7 `
  --expected-c83-file-sha1=E90EA61673FB7820988507670F547CD6F02D6A5F `
  --approval-reference=C84_OPERATOR_APPROVED_ACTIVATION_EXECUTION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c84-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review `
  --c83-artifact=storage/app/watchlist/backtest/c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review.json `
  --expected-c83-hash=2927dea9624be20ea493c9e449b57879e0ea5da7 `
  --expected-c83-file-sha1=E90EA61673FB7820988507670F547CD6F02D6A5F `
  --output=storage/app/watchlist/backtest/c84-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C84_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c84-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c84-no-approval-reference-test.json
```
