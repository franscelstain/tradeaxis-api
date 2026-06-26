# WS_C90_OPERATOR_VALIDATION_COMMANDS

C90 is controlled limited runtime opt-in pilot / shadow rollout post-activation handoff readiness review.
C90 starts from locked C89 final evidence.
C89 cleared the post-activation completion boundary for primary + backup.
E02 is primary post-activation handoff ready candidate.
B01 is backup post-activation handoff ready candidate.
A01 is comparator-only and cannot be promoted.
C90 validates C89 artifact hash and file SHA1.
C90 validates C89 readiness through nested next_readiness_decision.* path.
C90 validates C89 -> C60 lineage.
C90 requires --operator-approved.
C90 requires non-empty --approval-reference.
C90 marks post-activation handoff package ready only.
C90 does not redesign.
C90 does not retune.
C90 does not run parameter search.
C90 does not use OOS to rerank.
C90 does not use handoff readiness evidence to rerank.
C90 does not use handoff readiness evidence to deploy.
C90 does not change candidate scope.
C90 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C90 does not deploy live production.
C90 does not mutate PLAN/CONFIRM.
C90 does not change PLAN/CONFIRM output.
C90 keeps production_catalog_runtime_wired=false.
C90 keeps controlled_opt_in_runtime_bridge_active=false.
C90 keeps controlled_parallel_run_active=false.
C90 keeps controlled_rollout_active=false.
C90 keeps post_activation_handoff_readiness_context_persisted_to_live_runtime=false.
C90 keeps production_deployment_allowed=false.
C90 keeps production_deployment_executed=false.
C90 keeps plan_confirm_mutation_allowed=false.
C90 keeps plan_confirm_mutated=false.
C90 keeps plan_confirm_runtime_reads_activated_catalog=false.
C90 keeps live_plan_confirm_rollout_allowed=false.
C90 keeps live_plan_confirm_rollout_executed=false.
C90 post-activation handoff readiness means continue to C91 post-activation handoff finalization review only.
C90 post-activation handoff readiness record is not production deployment.
C90 post-activation handoff readiness record is not PLAN/CONFIRM live rollout.
C90 post-activation handoff readiness record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC90"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review `
  --c89-artifact=storage/app/watchlist/backtest/c89-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-completion-boundary-review.json `
  --expected-c89-hash=11ce5f21fcc027171d8073babc51212565859631 `
  --expected-c89-file-sha1=1D709D0D06F465F1F2033D4FD15DA489A5245C78 `
  --approval-reference=C90_OPERATOR_APPROVED_POST_ACTIVATION_HANDOFF_READINESS_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C90_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c89_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.post_activation_handoff_readiness_decision | Format-List
$run.post_activation_handoff_readiness_candidate_scorecard | Format-List
$run.post_activation_handoff_readiness_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review `
  --c89-artifact=storage/app/watchlist/backtest/c89-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-completion-boundary-review.json `
  --expected-c89-hash=11ce5f21fcc027171d8073babc51212565859631 `
  --expected-c89-file-sha1=1D709D0D06F465F1F2033D4FD15DA489A5245C78 `
  --approval-reference=C90_OPERATOR_APPROVED_POST_ACTIVATION_HANDOFF_READINESS_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c90-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review `
  --c89-artifact=storage/app/watchlist/backtest/c89-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-completion-boundary-review.json `
  --expected-c89-hash=11ce5f21fcc027171d8073babc51212565859631 `
  --expected-c89-file-sha1=1D709D0D06F465F1F2033D4FD15DA489A5245C78 `
  --output=storage/app/watchlist/backtest/c90-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C90_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c90-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c90-no-approval-reference-test.json
```
