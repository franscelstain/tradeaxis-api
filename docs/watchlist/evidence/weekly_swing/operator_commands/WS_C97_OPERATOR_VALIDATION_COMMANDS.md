# WS_C97_OPERATOR_VALIDATION_COMMANDS

C97 is controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive finalization review.
C97 starts from locked C96 audit archive closure seal evidence.
C96 sealed the post-activation audit archive closure evidence for primary + backup.
E02 is primary audit archive finalized candidate.
B01 is backup audit archive finalized candidate.
A01 is comparator-only and cannot be promoted.
C97 validates C96 artifact hash and file SHA1.
C97 validates C96 audit archive closure seal state.
C97 validates C96 next recommendation to C97.
C97 requires --operator-approved.
C97 requires non-empty --approval-reference.
C97 confirms no temporary negative test artifact remains.
C97 records audit archive finalization only.
C97 finalizes post-activation audit archive evidence only.
C97 does not redesign.
C97 does not retune.
C97 does not run parameter search.
C97 does not run OOS rerank.
C97 does not use audit archive finalization evidence to rerank.
C97 does not use audit archive finalization evidence to deploy.
C97 does not change candidate scope.
C97 does not promote A01.
C97 does not change scoring logic.
C97 does not change catalog selection.
C97 does not change runtime selection.
C97 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C97 does not deploy live production.
C97 does not mutate PLAN/CONFIRM.
C97 does not change PLAN/CONFIRM output.
C97 does not activate pilot runtime.
C97 does not activate shadow runtime.
C97 does not activate runtime bridge.
C97 does not activate weekly swing watchlist runtime.
C97 does not create weekly swing live output.
C97 keeps production_ready=false.
C97 keeps production_catalog_runtime_wired=false.
C97 keeps controlled_opt_in_runtime_bridge_active=false.
C97 keeps controlled_parallel_run_active=false.
C97 keeps controlled_rollout_active=false.
C97 keeps audit_archive_finalization_context_persisted_to_live_runtime=false.
C97 keeps production_deployment_allowed=false.
C97 keeps production_deployment_executed=false.
C97 keeps plan_confirm_mutation_allowed=false.
C97 keeps plan_confirm_mutated=false.
C97 keeps plan_confirm_runtime_reads_activated_catalog=false.
C97 keeps live_plan_confirm_rollout_allowed=false.
C97 keeps live_plan_confirm_rollout_executed=false.
C97 keeps pilot_runtime_active=false.
C97 keeps shadow_runtime_active=false.
C97 keeps runtime_bridge_active=false.
C97 keeps weekly_swing_watchlist_runtime_active=false.
C97 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.
C97 keeps weekly_swing_watchlist_live_output_enabled=false.
C97 pass records artifact-only audit archive finalization for primary and backup and can only recommend `C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW`; it is not full production deployment, not PLAN/CONFIRM live rollout, not runtime bridge activation, and not weekly swing live output.
C97 audit archive finalization means continue to C98 weekly swing watchlist non-live rehearsal review only.
C97 audit archive finalization record is not production deployment.
C97 audit archive finalization record is not PLAN/CONFIRM live rollout.
C97 audit archive finalization record is not runtime bridge activation.
C97 audit archive finalization record is not weekly swing live output.

## Focused PHPUnit C97

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC97"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Positive Runtime C97

```powershell
php artisan watchlist:backtest-c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review `
  --c96-artifact=storage/app/watchlist/backtest/c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review.json `
  --expected-c96-hash=970152d11467ea83c80eca83081d6ae81beec38b `
  --expected-c96-file-sha1=CCD6B92B52745B928C48BF349BC7004E755B1EB6 `
  --approval-reference=C97_OPERATOR_APPROVED_AUDIT_ARCHIVE_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_PASSED_AUDIT_ARCHIVE_FINALIZED_PRIMARY_AND_BACKUP
reason_code=C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_PASSED_AUDIT_ARCHIVE_FINALIZED_PRIMARY_AND_BACKUP
next_step_recommendation=C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.expected_c96_hash
$run.actual_c96_hash
$run.c96_hash_match
$run.expected_c96_file_sha1
$run.actual_c96_file_sha1
$run.c96_file_sha1_match
$run.temporary_negative_artifacts_remaining
$run.temporary_negative_artifact_cleanup_confirmed
$run.temporary_negative_artifact_paths
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
$run.audit_archive_finalization_context_persisted_to_live_runtime
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
$run.weekly_swing_watchlist_runtime_active
$run.weekly_swing_watchlist_plan_confirm_mutation_allowed
$run.weekly_swing_watchlist_live_output_enabled
$run.c97_readiness_decision | Format-List
$run.next_readiness_decision | Format-List
```

## C97 File SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review.json -Algorithm SHA1
```

## Negative Approval Test - Without Operator Approval

```powershell
php artisan watchlist:backtest-c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review `
  --c96-artifact=storage/app/watchlist/backtest/c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review.json `
  --expected-c96-hash=970152d11467ea83c80eca83081d6ae81beec38b `
  --expected-c96-file-sha1=CCD6B92B52745B928C48BF349BC7004E755B1EB6 `
  --approval-reference=C97_OPERATOR_APPROVED_AUDIT_ARCHIVE_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c97-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Expected:

```text
status=C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Approval Test - Without Approval Reference

```powershell
php artisan watchlist:backtest-c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review `
  --c96-artifact=storage/app/watchlist/backtest/c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review.json `
  --expected-c96-hash=970152d11467ea83c80eca83081d6ae81beec38b `
  --expected-c96-file-sha1=CCD6B92B52745B928C48BF349BC7004E755B1EB6 `
  --output=storage/app/watchlist/backtest/c97-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Cleanup Negative Temporary Artifacts

```powershell
Remove-Item storage/app/watchlist/backtest/c97-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item storage/app/watchlist/backtest/c97-no-approval-reference-test.json -ErrorAction SilentlyContinue

Get-ChildItem storage/app/watchlist/backtest -Filter "*no-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*missing-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*mismatch-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*negative-*-test.json"
```

Expected:

```text
No output
```

## Final Operator Evidence - 2026-06-27

```text
FOCUSED_PHPUNIT_C97=OK (55 tests, 294 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C97=OK (1752 tests, 24977 assertions)
C97_RUNTIME_STATUS=C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_PASSED_AUDIT_ARCHIVE_FINALIZED_PRIMARY_AND_BACKUP
C97_RUNTIME_REASON_CODE=C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_PASSED_AUDIT_ARCHIVE_FINALIZED_PRIMARY_AND_BACKUP
C97_ARTIFACT_HASH=5898b6eaa0b537006ba249339c21b5038c8cb6fc
C97_FILE_SHA1=620FF85234701FD72FC40BB661F068308751C2E4
C96_HASH_MATCH=1
C96_FILE_SHA1_MATCH=1
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
PILOT_RUNTIME_ACTIVE=0
SHADOW_RUNTIME_ACTIVE=0
RUNTIME_BRIDGE_ACTIVE=0
WEEKLY_SWING_WATCHLIST_RUNTIME_ACTIVE=0
WEEKLY_SWING_WATCHLIST_PLAN_CONFIRM_MUTATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
NEXT_RECOMMENDATION=C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW
```

C97 final operator validation passed. This is audit archive finalization only and does not activate production, runtime bridge, pilot/shadow runtime, PLAN/CONFIRM mutation, or weekly swing live output.

