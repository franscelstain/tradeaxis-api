# WS_C85_OPERATOR_VALIDATION_COMMANDS

C85 is controlled limited runtime opt-in pilot / shadow rollout post-activation observation review.
C85 starts from locked C84 final evidence.
C84 activation execution review created controlled activation execution record for primary + backup.
E02 is primary post-activation observation candidate.
B01 is backup post-activation observation candidate.
A01 is comparator-only and cannot be promoted.
C85 validates C84 artifact hash and file SHA1.
C85 validates C84 readiness through nested next_readiness_decision.* path.
C85 validates C84 -> C60 lineage.
C85 requires --operator-approved.
C85 requires non-empty --approval-reference.
C85 observes controlled activation execution record only.
C85 does not redesign.
C85 does not retune.
C85 does not run parameter search.
C85 does not use OOS to rerank.
C85 does not use post-activation observation to rerank.
C85 does not use post-activation observation to deploy.
C85 does not change candidate scope.
C85 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C85 does not deploy live production.
C85 does not mutate PLAN/CONFIRM.
C85 does not change PLAN/CONFIRM output.
C85 keeps production_catalog_runtime_wired=false.
C85 keeps controlled_opt_in_runtime_bridge_active=false.
C85 keeps controlled_parallel_run_active=false.
C85 keeps controlled_rollout_active=false.
C85 keeps post_activation_observation_context_persisted_to_live_runtime=false.
C85 keeps production_deployment_allowed=false.
C85 keeps production_deployment_executed=false.
C85 keeps plan_confirm_mutation_allowed=false.
C85 keeps plan_confirm_mutated=false.
C85 keeps plan_confirm_runtime_reads_activated_catalog=false.
C85 keeps live_plan_confirm_rollout_allowed=false.
C85 keeps live_plan_confirm_rollout_executed=false.
C85 post-activation observation means continue to C86 post-activation observation result review only.
C85 post-activation observation record is not production deployment.
C85 post-activation observation record is not PLAN/CONFIRM live rollout.
C85 post-activation observation record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC85"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review `
  --c84-artifact=storage/app/watchlist/backtest/c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review.json `
  --expected-c84-hash=54f39e02202b597c0e353cfec602215a1f41251b `
  --expected-c84-file-sha1=CEAF5D69D61D15A7220CA5A843DCF3CB1DDB5255 `
  --approval-reference=C85_OPERATOR_APPROVED_POST_ACTIVATION_OBSERVATION_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_PASSED_OBSERVED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c84_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.post_activation_observation_decision | Format-List
$run.post_activation_observation_candidate_scorecard | Format-List
$run.post_activation_observation_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review `
  --c84-artifact=storage/app/watchlist/backtest/c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review.json `
  --expected-c84-hash=54f39e02202b597c0e353cfec602215a1f41251b `
  --expected-c84-file-sha1=CEAF5D69D61D15A7220CA5A843DCF3CB1DDB5255 `
  --approval-reference=C85_OPERATOR_APPROVED_POST_ACTIVATION_OBSERVATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c85-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c85-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-review `
  --c84-artifact=storage/app/watchlist/backtest/c84-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-activation-execution-review.json `
  --expected-c84-hash=54f39e02202b597c0e353cfec602215a1f41251b `
  --expected-c84-file-sha1=CEAF5D69D61D15A7220CA5A843DCF3CB1DDB5255 `
  --output=storage/app/watchlist/backtest/c85-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C85_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c85-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c85-no-approval-reference-test.json
```
