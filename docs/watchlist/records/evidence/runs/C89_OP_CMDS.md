# WS_C89_OPERATOR_VALIDATION_COMMANDS

C89 is controlled limited runtime opt-in pilot / shadow rollout post-activation completion boundary review.
C89 starts from locked C88 final evidence.
C88 finalized post-activation GO for primary + backup.
E02 is primary post-activation completion boundary candidate.
B01 is backup post-activation completion boundary candidate.
A01 is comparator-only and cannot be promoted.
C89 validates C88 artifact hash and file SHA1.
C89 validates C88 readiness through nested next_readiness_decision.* path.
C89 validates C88 -> C60 lineage.
C89 requires --operator-approved.
C89 requires non-empty --approval-reference.
C89 clears post-activation completion boundary only.
C89 does not redesign.
C89 does not retune.
C89 does not run parameter search.
C89 does not use OOS to rerank.
C89 does not use completion boundary evidence to rerank.
C89 does not use completion boundary evidence to deploy.
C89 does not change candidate scope.
C89 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C89 does not deploy live production.
C89 does not mutate PLAN/CONFIRM.
C89 does not change PLAN/CONFIRM output.
C89 keeps production_catalog_runtime_wired=false.
C89 keeps controlled_opt_in_runtime_bridge_active=false.
C89 keeps controlled_parallel_run_active=false.
C89 keeps controlled_rollout_active=false.
C89 keeps post_activation_completion_boundary_context_persisted_to_live_runtime=false.
C89 keeps production_deployment_allowed=false.
C89 keeps production_deployment_executed=false.
C89 keeps plan_confirm_mutation_allowed=false.
C89 keeps plan_confirm_mutated=false.
C89 keeps plan_confirm_runtime_reads_activated_catalog=false.
C89 keeps live_plan_confirm_rollout_allowed=false.
C89 keeps live_plan_confirm_rollout_executed=false.
C89 post-activation completion boundary means continue to C90 post-activation handoff readiness review only.
C89 post-activation completion boundary record is not production deployment.
C89 post-activation completion boundary record is not PLAN/CONFIRM live rollout.
C89 post-activation completion boundary record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC89"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c89-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-completion-boundary-review `
  --c88-artifact=storage/app/watchlist/backtest/c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review.json `
  --expected-c88-hash=f0f296e4e3e608780c9a2095acff7f70cf61e7bb `
  --expected-c88-file-sha1=9CB05635B380E32FE3E9AABFD65262E5754BEAE2 `
  --approval-reference=C89_OPERATOR_APPROVED_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c89-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-completion-boundary-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c89-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-completion-boundary-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c88_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.post_activation_completion_boundary_decision | Format-List
$run.post_activation_completion_boundary_candidate_scorecard | Format-List
$run.post_activation_completion_boundary_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c89-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-completion-boundary-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c89-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-completion-boundary-review `
  --c88-artifact=storage/app/watchlist/backtest/c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review.json `
  --expected-c88-hash=f0f296e4e3e608780c9a2095acff7f70cf61e7bb `
  --expected-c88-file-sha1=9CB05635B380E32FE3E9AABFD65262E5754BEAE2 `
  --approval-reference=C89_OPERATOR_APPROVED_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c89-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c89-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-completion-boundary-review `
  --c88-artifact=storage/app/watchlist/backtest/c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review.json `
  --expected-c88-hash=f0f296e4e3e608780c9a2095acff7f70cf61e7bb `
  --expected-c88-file-sha1=9CB05635B380E32FE3E9AABFD65262E5754BEAE2 `
  --output=storage/app/watchlist/backtest/c89-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c89-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c89-no-approval-reference-test.json
```

## Final Operator Result Evidence — 2026-06-27

Operator validation results recorded for C89:

```text
FOCUSED_PHPUNIT=OK (12 tests, 138 assertions)
FULL_WATCHLIST_PHPUNIT=OK (1448 tests, 23286 assertions)
RUNTIME_STATUS=C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
RUNTIME_REASON_CODE=C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
ARTIFACT_HASH=11ce5f21fcc027171d8073babc51212565859631
ARTIFACT_FILE_SHA1=1D709D0D06F465F1F2033D4FD15DA489A5245C78
EXPECTED_C88_HASH=f0f296e4e3e608780c9a2095acff7f70cf61e7bb
ACTUAL_C88_HASH=f0f296e4e3e608780c9a2095acff7f70cf61e7bb
C88_HASH_MATCH=1
EXPECTED_C88_FILE_SHA1=9CB05635B380E32FE3E9AABFD65262E5754BEAE2
ACTUAL_C88_FILE_SHA1=9CB05635B380E32FE3E9AABFD65262E5754BEAE2
C88_FILE_SHA1_MATCH=1
NEGATIVE_WITHOUT_OPERATOR_APPROVED=C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C89_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=PASS_NO_NO_TEST_JSON_REMAINING
NEXT_RECOMMENDATION=C90_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_READINESS_REVIEW
```

The negative approval artifacts were temporary operator-validation artifacts only. They were removed after inspection. The final C89 artifact remains `storage/app/watchlist/backtest/c89-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-completion-boundary-review.json`.
