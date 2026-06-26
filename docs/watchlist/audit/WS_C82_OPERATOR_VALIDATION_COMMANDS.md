# WS_C82_OPERATOR_VALIDATION_COMMANDS

C82 is controlled limited runtime opt-in pilot / shadow rollout pre-activation boundary review.
C82 starts from locked C81 final evidence.
C81 GO decision finalization review passed finalized GO for primary + backup.
E02 is primary pre-activation boundary-cleared candidate.
B01 is backup pre-activation boundary-cleared candidate.
A01 is comparator-only and cannot be promoted.
C82 validates C81 artifact hash and file SHA1.
C82 validates C81 readiness through nested next_readiness_decision.* path.
C82 validates C81 -> C60 lineage.
C82 requires --operator-approved.
C82 requires non-empty --approval-reference.
C82 clears pre-activation boundary only.
C82 does not authorize activation.
C82 does not redesign.
C82 does not retune.
C82 does not run parameter search.
C82 does not use OOS to rerank.
C82 does not use boundary clearance to rerank.
C82 does not use boundary clearance to deploy.
C82 does not change candidate scope.
C82 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C82 does not deploy live production.
C82 does not mutate PLAN/CONFIRM.
C82 does not change PLAN/CONFIRM output.
C82 keeps activation_authorized=false.
C82 keeps production_catalog_runtime_wired=false.
C82 keeps controlled_opt_in_runtime_bridge_active=false.
C82 keeps controlled_parallel_run_active=false.
C82 keeps controlled_rollout_active=false.
C82 keeps pre_activation_boundary_context_persisted_to_live_runtime=false.
C82 keeps production_deployment_allowed=false.
C82 keeps production_deployment_executed=false.
C82 keeps plan_confirm_mutation_allowed=false.
C82 keeps plan_confirm_mutated=false.
C82 keeps plan_confirm_runtime_reads_activated_catalog=false.
C82 keeps live_plan_confirm_rollout_allowed=false.
C82 keeps live_plan_confirm_rollout_executed=false.
C82 boundary clearance means continue to C83 activation authorization review only.
C82 boundary clearance is not activation authorization.
C82 boundary clearance is not production deployment.
C82 boundary clearance is not PLAN/CONFIRM live rollout.
C82 boundary clearance is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC82"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review `
  --c81-artifact=storage/app/watchlist/backtest/c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review.json `
  --expected-c81-hash=45e1abfb6ba0ddc6ddf2b0494527cf8706172f18 `
  --expected-c81-file-sha1=588753D1F62EBCDB318A5969ACE4165CD83D98BD `
  --approval-reference=C82_OPERATOR_APPROVED_PRE_ACTIVATION_BOUNDARY_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c81_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.pre_activation_boundary_decision | Format-List
$run.pre_activation_boundary_candidate_scorecard | Format-List
$run.pre_activation_boundary_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review `
  --c81-artifact=storage/app/watchlist/backtest/c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review.json `
  --expected-c81-hash=45e1abfb6ba0ddc6ddf2b0494527cf8706172f18 `
  --expected-c81-file-sha1=588753D1F62EBCDB318A5969ACE4165CD83D98BD `
  --approval-reference=C82_OPERATOR_APPROVED_PRE_ACTIVATION_BOUNDARY_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c82-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c82-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-pre-activation-boundary-review `
  --c81-artifact=storage/app/watchlist/backtest/c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review.json `
  --expected-c81-hash=45e1abfb6ba0ddc6ddf2b0494527cf8706172f18 `
  --expected-c81-file-sha1=588753D1F62EBCDB318A5969ACE4165CD83D98BD `
  --output=storage/app/watchlist/backtest/c82-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c82-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c82-no-approval-reference-test.json
```
