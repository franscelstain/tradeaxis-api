# WS_C88_OPERATOR_VALIDATION_COMMANDS

C88 is controlled limited runtime opt-in pilot / shadow rollout post-activation GO decision finalization review.
C88 starts from locked C87 final evidence.
C87 post-activation operator go/no-go review recorded GO for primary + backup.
E02 is primary finalized post-activation GO candidate.
B01 is backup finalized post-activation GO candidate.
A01 is comparator-only and cannot be promoted.
C88 validates C87 artifact hash and file SHA1.
C88 validates C87 readiness through nested next_readiness_decision.* path.
C88 validates C87 -> C60 lineage.
C88 requires --operator-approved.
C88 requires non-empty --approval-reference.
C88 finalizes post-activation GO decision only.
C88 does not redesign.
C88 does not retune.
C88 does not run parameter search.
C88 does not use OOS to rerank.
C88 does not use finalized GO to rerank.
C88 does not use finalized GO to deploy.
C88 does not change candidate scope.
C88 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C88 does not deploy live production.
C88 does not mutate PLAN/CONFIRM.
C88 does not change PLAN/CONFIRM output.
C88 keeps production_catalog_runtime_wired=false.
C88 keeps controlled_opt_in_runtime_bridge_active=false.
C88 keeps controlled_parallel_run_active=false.
C88 keeps controlled_rollout_active=false.
C88 keeps post_activation_go_decision_finalization_context_persisted_to_live_runtime=false.
C88 keeps production_deployment_allowed=false.
C88 keeps production_deployment_executed=false.
C88 keeps plan_confirm_mutation_allowed=false.
C88 keeps plan_confirm_mutated=false.
C88 keeps plan_confirm_runtime_reads_activated_catalog=false.
C88 keeps live_plan_confirm_rollout_allowed=false.
C88 keeps live_plan_confirm_rollout_executed=false.
C88 finalized post-activation GO means continue to C89 post-activation completion boundary review only.
C88 finalized post-activation GO record is not production deployment.
C88 finalized post-activation GO record is not PLAN/CONFIRM live rollout.
C88 finalized post-activation GO record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC88"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review `
  --c87-artifact=storage/app/watchlist/backtest/c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review.json `
  --expected-c87-hash=4c319158e1e90bc7e491636361551ed212848c5d `
  --expected-c87-file-sha1=EBEA22AD5E07792D0D5EE6F71A317966EFF546D8 `
  --approval-reference=C88_OPERATOR_APPROVED_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c87_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.post_activation_go_decision_finalization_decision | Format-List
$run.post_activation_go_decision_finalization_candidate_scorecard | Format-List
$run.post_activation_go_decision_finalization_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review `
  --c87-artifact=storage/app/watchlist/backtest/c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review.json `
  --expected-c87-hash=4c319158e1e90bc7e491636361551ed212848c5d `
  --expected-c87-file-sha1=EBEA22AD5E07792D0D5EE6F71A317966EFF546D8 `
  --approval-reference=C88_OPERATOR_APPROVED_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c88-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review `
  --c87-artifact=storage/app/watchlist/backtest/c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review.json `
  --expected-c87-hash=4c319158e1e90bc7e491636361551ed212848c5d `
  --expected-c87-file-sha1=EBEA22AD5E07792D0D5EE6F71A317966EFF546D8 `
  --output=storage/app/watchlist/backtest/c88-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c88-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c88-no-approval-reference-test.json
```
