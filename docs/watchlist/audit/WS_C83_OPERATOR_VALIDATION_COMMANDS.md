# WS_C83_OPERATOR_VALIDATION_COMMANDS

C83 is controlled limited runtime opt-in pilot / shadow rollout activation authorization review.
C83 starts from locked C82 final evidence.
C82 pre-activation boundary review passed boundary clearance for primary + backup.
E02 is primary activation-authorized candidate.
B01 is backup activation-authorized candidate.
A01 is comparator-only and cannot be promoted.
C83 validates C82 artifact hash and file SHA1.
C83 validates C82 readiness through nested next_readiness_decision.* path.
C83 validates C82 -> C60 lineage.
C83 requires --operator-approved.
C83 requires non-empty --approval-reference.
C83 records activation authorization only.
C83 does not execute activation.
C83 does not redesign.
C83 does not retune.
C83 does not run parameter search.
C83 does not use OOS to rerank.
C83 does not use activation authorization to rerank.
C83 does not use activation authorization to deploy.
C83 does not change candidate scope.
C83 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C83 does not deploy live production.
C83 does not mutate PLAN/CONFIRM.
C83 does not change PLAN/CONFIRM output.
C83 keeps activation_executed=false.
C83 keeps production_catalog_runtime_wired=false.
C83 keeps controlled_opt_in_runtime_bridge_active=false.
C83 keeps controlled_parallel_run_active=false.
C83 keeps controlled_rollout_active=false.
C83 keeps activation_authorization_context_persisted_to_live_runtime=false.
C83 keeps production_deployment_allowed=false.
C83 keeps production_deployment_executed=false.
C83 keeps plan_confirm_mutation_allowed=false.
C83 keeps plan_confirm_mutated=false.
C83 keeps plan_confirm_runtime_reads_activated_catalog=false.
C83 keeps live_plan_confirm_rollout_allowed=false.
C83 keeps live_plan_confirm_rollout_executed=false.
C83 activation authorization means continue to C84 activation execution review only.
C83 activation authorization is not activation execution.
C83 activation authorization is not production deployment.
C83 activation authorization is not PLAN/CONFIRM live rollout.
C83 activation authorization is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC83"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review `
  --c82-artifact=storage/app/watchlist/backtest/c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review.json `
  --expected-c82-hash=1c78f08cc78abe4800cde96b892932ad6b8df725 `
  --expected-c82-file-sha1=24D91E58F7F9FAADE95F6DABF985F430C48C05E2 `
  --approval-reference=C83_OPERATOR_APPROVED_ACTIVATION_AUTHORIZATION_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_PASSED_AUTHORIZED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c82_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.activation_authorization_decision | Format-List
$run.activation_authorization_candidate_scorecard | Format-List
$run.activation_authorization_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review `
  --c82-artifact=storage/app/watchlist/backtest/c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review.json `
  --expected-c82-hash=1c78f08cc78abe4800cde96b892932ad6b8df725 `
  --expected-c82-file-sha1=24D91E58F7F9FAADE95F6DABF985F430C48C05E2 `
  --approval-reference=C83_OPERATOR_APPROVED_ACTIVATION_AUTHORIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c83-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c83-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-authorization-review `
  --c82-artifact=storage/app/watchlist/backtest/c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review.json `
  --expected-c82-hash=1c78f08cc78abe4800cde96b892932ad6b8df725 `
  --expected-c82-file-sha1=24D91E58F7F9FAADE95F6DABF985F430C48C05E2 `
  --output=storage/app/watchlist/backtest/c83-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C83_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c83-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c83-no-approval-reference-test.json
```
