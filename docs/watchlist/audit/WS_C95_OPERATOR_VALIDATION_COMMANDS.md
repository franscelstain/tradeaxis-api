# WS_C95_OPERATOR_VALIDATION_COMMANDS

C95 is controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive completion review.
C95 starts from locked C94 audit archive evidence.
C94 archived the post-activation audit evidence for primary + backup.
E02 is primary post-activation audit archive completed candidate.
B01 is backup post-activation audit archive completed candidate.
A01 is comparator-only and cannot be promoted.
C95 validates C94 artifact hash and file SHA1.
C95 validates C94 audit archive state.
C95 validates C94 next recommendation to C95.
C95 requires --operator-approved.
C95 requires non-empty --approval-reference.
C95 confirms no temporary negative test artifact remains.
C95 records post-activation audit archive completion only.
C95 completes post-activation audit archive evidence only.
C95 does not redesign.
C95 does not retune.
C95 does not run parameter search.
C95 does not run OOS rerank.
C95 does not use audit archive completion evidence to rerank.
C95 does not use audit archive completion evidence to deploy.
C95 does not change candidate scope.
C95 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C95 does not deploy live production.
C95 does not mutate PLAN/CONFIRM.
C95 does not change PLAN/CONFIRM output.
C95 keeps production_ready=false.
C95 keeps production_catalog_runtime_wired=false.
C95 keeps controlled_opt_in_runtime_bridge_active=false.
C95 keeps controlled_parallel_run_active=false.
C95 keeps controlled_rollout_active=false.
C95 keeps post_activation_audit_archive_context_persisted_to_live_runtime=false.
C95 keeps post_activation_audit_archive_completion_context_persisted_to_live_runtime=false.
C95 keeps production_deployment_allowed=false.
C95 keeps production_deployment_executed=false.
C95 keeps plan_confirm_mutation_allowed=false.
C95 keeps plan_confirm_mutated=false.
C95 keeps plan_confirm_runtime_reads_activated_catalog=false.
C95 keeps live_plan_confirm_rollout_allowed=false.
C95 keeps live_plan_confirm_rollout_executed=false.
C95 keeps pilot_runtime_active=false.
C95 keeps shadow_runtime_active=false.
C95 keeps runtime_bridge_active=false.
C95 post-activation audit archive completion means continue to C96 audit archive closure seal review only.
C95 post-activation audit archive completion record is not production deployment.
C95 post-activation audit archive completion record is not PLAN/CONFIRM live rollout.
C95 post-activation audit archive completion record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC95"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review `
  --c94-artifact=storage/app/watchlist/backtest/c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review.json `
  --expected-c94-hash=2a17baceb2e899f93fd1d658bd6a7b020ef9b252 `
  --expected-c94-file-sha1=0D81162ED0DF53DC434B2131E34106F7203119D6 `
  --approval-reference=C95_OPERATOR_APPROVED_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review.json | ConvertFrom-Json
$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.expected_c94_hash
$run.actual_c94_hash
$run.c94_hash_match
$run.expected_c94_file_sha1
$run.actual_c94_file_sha1
$run.c94_file_sha1_match
$run.temporary_negative_artifacts_remaining
$run.temporary_negative_artifact_cleanup_confirmed
$run.temporary_negative_artifact_paths
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
$run.post_activation_audit_archive_context_persisted_to_live_runtime
$run.post_activation_audit_archive_completion_context_persisted_to_live_runtime
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
$run.c95_readiness_decision | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review `
  --c94-artifact=storage/app/watchlist/backtest/c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review.json `
  --expected-c94-hash=2a17baceb2e899f93fd1d658bd6a7b020ef9b252 `
  --expected-c94-file-sha1=0D81162ED0DF53DC434B2131E34106F7203119D6 `
  --approval-reference=C95_OPERATOR_APPROVED_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c95-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review `
  --c94-artifact=storage/app/watchlist/backtest/c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review.json `
  --expected-c94-hash=2a17baceb2e899f93fd1d658bd6a7b020ef9b252 `
  --expected-c94-file-sha1=0D81162ED0DF53DC434B2131E34106F7203119D6 `
  --output=storage/app/watchlist/backtest/c95-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c95-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item storage/app/watchlist/backtest/c95-no-approval-reference-test.json -ErrorAction SilentlyContinue
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
FOCUSED_PHPUNIT_C95=OK (48 tests, 230 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C95=OK (1648 tests, 24447 assertions)
C95_RUNTIME_STATUS=C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETED_PRIMARY_AND_BACKUP
C95_RUNTIME_REASON_CODE=C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETED_PRIMARY_AND_BACKUP
C95_ARTIFACT_HASH=a8923e58e35126741226eab29cc07c88a2a721f8
C95_FILE_SHA1=AEF14CC999F8050DADC8E451E9116C59FD1C2534
C94_HASH_MATCH=1
C94_FILE_SHA1_MATCH=1
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
NEXT_RECOMMENDATION=C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW
```
