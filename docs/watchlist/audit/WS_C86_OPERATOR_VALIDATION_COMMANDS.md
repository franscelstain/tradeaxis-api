# WS_C86_OPERATOR_VALIDATION_COMMANDS

C86 is controlled limited runtime opt-in pilot / shadow rollout post-activation observation result review.
C86 starts from locked C85 final evidence.
C85 post-activation observation review passed observation for primary + backup.
E02 is primary post-activation observation result candidate.
B01 is backup post-activation observation result candidate.
A01 is comparator-only and cannot be promoted.
C86 validates C85 artifact hash and file SHA1.
C86 validates C85 readiness through nested next_readiness_decision.* path.
C86 validates C85 -> C60 lineage.
C86 requires --operator-approved.
C86 requires non-empty --approval-reference.
C86 reviews post-activation observation result only.
C86 does not redesign.
C86 does not retune.
C86 does not run parameter search.
C86 does not use OOS to rerank.
C86 does not use post-activation observation result to rerank.
C86 does not use post-activation observation result to deploy.
C86 does not change candidate scope.
C86 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C86 does not deploy live production.
C86 does not mutate PLAN/CONFIRM.
C86 does not change PLAN/CONFIRM output.
C86 keeps production_catalog_runtime_wired=false.
C86 keeps controlled_opt_in_runtime_bridge_active=false.
C86 keeps controlled_parallel_run_active=false.
C86 keeps controlled_rollout_active=false.
C86 keeps post_activation_observation_result_context_persisted_to_live_runtime=false.
C86 keeps production_deployment_allowed=false.
C86 keeps production_deployment_executed=false.
C86 keeps plan_confirm_mutation_allowed=false.
C86 keeps plan_confirm_mutated=false.
C86 keeps plan_confirm_runtime_reads_activated_catalog=false.
C86 keeps live_plan_confirm_rollout_allowed=false.
C86 keeps live_plan_confirm_rollout_executed=false.
C86 post-activation observation result means continue to C87 post-activation operator go/no-go review only.
C86 post-activation observation result record is not production deployment.
C86 post-activation observation result record is not PLAN/CONFIRM live rollout.
C86 post-activation observation result record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC86"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review `
  --c85-artifact=storage/app/watchlist/backtest/c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review.json `
  --expected-c85-hash=80aa0fc1a0ea662870c373706e8fc15b7bb03396 `
  --expected-c85-file-sha1=80C9596AC8AD714DE161BDA17AECE4734667E645 `
  --approval-reference=C86_OPERATOR_APPROVED_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_RESULT_REVIEWED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c85_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.post_activation_observation_result_decision | Format-List
$run.post_activation_observation_result_candidate_scorecard | Format-List
$run.post_activation_observation_result_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review `
  --c85-artifact=storage/app/watchlist/backtest/c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review.json `
  --expected-c85-hash=80aa0fc1a0ea662870c373706e8fc15b7bb03396 `
  --expected-c85-file-sha1=80C9596AC8AD714DE161BDA17AECE4734667E645 `
  --approval-reference=C86_OPERATOR_APPROVED_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c86-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review `
  --c85-artifact=storage/app/watchlist/backtest/c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review.json `
  --expected-c85-hash=80aa0fc1a0ea662870c373706e8fc15b7bb03396 `
  --expected-c85-file-sha1=80C9596AC8AD714DE161BDA17AECE4734667E645 `
  --output=storage/app/watchlist/backtest/c86-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C86_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c86-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c86-no-approval-reference-test.json
```
