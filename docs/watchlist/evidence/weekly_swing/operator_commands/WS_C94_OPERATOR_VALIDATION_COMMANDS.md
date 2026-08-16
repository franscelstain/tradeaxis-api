# WS_C94_OPERATOR_VALIDATION_COMMANDS

C94 is controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive review.
C94 starts from locked C93 closure seal evidence.
C93 sealed the post-activation handoff closure for primary + backup.
E02 is primary post-activation audit archived candidate.
B01 is backup post-activation audit archived candidate.
A01 is comparator-only and cannot be promoted.
C94 validates C93 artifact hash and file SHA1.
C94 validates C93 closure seal state.
C94 validates C93 next recommendation to C94.
C94 requires --operator-approved.
C94 requires non-empty --approval-reference.
C94 confirms no temporary negative test artifact remains.
C94 records post-activation audit archive only.
C94 archives post-activation audit evidence only.
C94 does not redesign.
C94 does not retune.
C94 does not run parameter search.
C94 does not run OOS rerank.
C94 does not use audit archive evidence to rerank.
C94 does not use audit archive evidence to deploy.
C94 does not change candidate scope.
C94 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C94 does not deploy live production.
C94 does not mutate PLAN/CONFIRM.
C94 does not change PLAN/CONFIRM output.
C94 keeps production_ready=false.
C94 keeps production_catalog_runtime_wired=false.
C94 keeps controlled_opt_in_runtime_bridge_active=false.
C94 keeps controlled_parallel_run_active=false.
C94 keeps controlled_rollout_active=false.
C94 keeps post_activation_audit_archive_context_persisted_to_live_runtime=false.
C94 keeps production_deployment_allowed=false.
C94 keeps production_deployment_executed=false.
C94 keeps plan_confirm_mutation_allowed=false.
C94 keeps plan_confirm_mutated=false.
C94 keeps plan_confirm_runtime_reads_activated_catalog=false.
C94 keeps live_plan_confirm_rollout_allowed=false.
C94 keeps live_plan_confirm_rollout_executed=false.
C94 keeps pilot_runtime_active=false.
C94 keeps shadow_runtime_active=false.
C94 keeps runtime_bridge_active=false.
C94 post-activation audit archive means continue to C95 audit archive completion review only.
C94 post-activation audit archive record is not production deployment.
C94 post-activation audit archive record is not PLAN/CONFIRM live rollout.
C94 post-activation audit archive record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC94"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review `
  --c93-artifact=storage/app/watchlist/backtest/c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review.json `
  --expected-c93-hash=bd19ac672c30ea183fc46534acd6e976515c3453 `
  --expected-c93-file-sha1=F71799E201B9C71A79094D81AFF786FCACDF9E1D `
  --approval-reference=C94_OPERATOR_APPROVED_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review.json | ConvertFrom-Json
$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.expected_c93_hash
$run.actual_c93_hash
$run.c93_hash_match
$run.expected_c93_file_sha1
$run.actual_c93_file_sha1
$run.c93_file_sha1_match
$run.temporary_negative_artifacts_remaining
$run.temporary_negative_artifact_cleanup_confirmed
$run.temporary_negative_artifact_paths
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
$run.post_activation_audit_archive_context_persisted_to_live_runtime
$run.production_deployment_allowed
$run.production_deployment_executed
$run.plan_confirm_mutation_allowed
$run.plan_confirm_mutated
$run.plan_confirm_runtime_reads_activated_catalog
$run.live_plan_confirm_rollout_allowed
$run.live_plan_confirm_rollout_executed
$run.pilot_runtime_active
$run.shadow_runtime_active
$run.runtime_bridge_active
$run.c94_readiness_decision | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review `
  --c93-artifact=storage/app/watchlist/backtest/c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review.json `
  --expected-c93-hash=bd19ac672c30ea183fc46534acd6e976515c3453 `
  --expected-c93-file-sha1=F71799E201B9C71A79094D81AFF786FCACDF9E1D `
  --approval-reference=C94_OPERATOR_APPROVED_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c94-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review `
  --c93-artifact=storage/app/watchlist/backtest/c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review.json `
  --expected-c93-hash=bd19ac672c30ea183fc46534acd6e976515c3453 `
  --expected-c93-file-sha1=F71799E201B9C71A79094D81AFF786FCACDF9E1D `
  --output=storage/app/watchlist/backtest/c94-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c94-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item storage/app/watchlist/backtest/c94-no-approval-reference-test.json -ErrorAction SilentlyContinue
Get-ChildItem storage/app/watchlist/backtest -Filter "*no-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*missing-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*mismatch-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*negative-*-test.json"
```

Expected cleanup result:

```text
No output
```

## Manual Local Evidence To Return

```text
FOCUSED_PHPUNIT_C94=OK (45 tests, 222 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C94=OK (1600 tests, 24217 assertions)
C94_RUNTIME_STATUS=C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP
C94_RUNTIME_REASON_CODE=C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP
C94_ARTIFACT_HASH=2a17baceb2e899f93fd1d658bd6a7b020ef9b252
C94_FILE_SHA1=0D81162ED0DF53DC434B2131E34106F7203119D6
C93_HASH_MATCH=1
C93_FILE_SHA1_MATCH=1
TEMPORARY_NEGATIVE_ARTIFACTS_REMAINING=0
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_CONFIRMED=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_OUTPUT
PRODUCTION_READY=0
PRODUCTION_CATALOG_RUNTIME_WIRED=0
CONTROLLED_OPT_IN_RUNTIME_BRIDGE_ACTIVE=0
CONTROLLED_PARALLEL_RUN_ACTIVE=0
CONTROLLED_ROLLOUT_ACTIVE=0
PRODUCTION_DEPLOYMENT_ALLOWED=0
PRODUCTION_DEPLOYMENT_EXECUTED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
LIVE_PLAN_CONFIRM_ROLLOUT_ALLOWED=0
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=0
NEXT_RECOMMENDATION=C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW
```
