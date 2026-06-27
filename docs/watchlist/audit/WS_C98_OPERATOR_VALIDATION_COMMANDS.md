# WS_C98_OPERATOR_VALIDATION_COMMANDS

C98 is weekly swing watchlist non-live rehearsal review.
C98 starts from locked C97 audit archive finalization evidence.
C97 finalized the post-activation audit archive package for primary + backup.
E02 is primary non-live rehearsal candidate.
B01 is backup non-live rehearsal candidate.
A01 is comparator-only and cannot be promoted.
C98 validates C97 artifact hash and file SHA1.
C98 validates C97 audit archive finalization state.
C98 validates C97 next recommendation to C98.
C98 requires --operator-approved.
C98 requires non-empty --approval-reference.
C98 confirms no temporary negative test artifact remains.
C98 records weekly swing watchlist non-live rehearsal review only.
C98 creates artifact-only non-live rehearsal manifest.
C98 does not redesign.
C98 does not retune.
C98 does not run parameter search.
C98 does not run OOS rerank.
C98 does not rebuild signal quality.
C98 does not use rehearsal evidence to rerank.
C98 does not use rehearsal evidence to select.
C98 does not use rehearsal evidence to deploy.
C98 does not change candidate scope.
C98 does not promote A01.
C98 does not change scoring logic.
C98 does not change catalog selection.
C98 does not change runtime selection.
C98 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C98 does not deploy live production.
C98 does not mutate PLAN/CONFIRM.
C98 does not change PLAN/CONFIRM output.
C98 does not activate pilot runtime.
C98 does not activate shadow runtime.
C98 does not activate runtime bridge.
C98 does not activate weekly swing watchlist runtime.
C98 does not create weekly swing live output.
C98 does not generate official weekly swing recommendation.
C98 does not publish weekly swing output.
C98 keeps production_ready=false.
C98 keeps production_catalog_runtime_wired=false.
C98 keeps controlled_opt_in_runtime_bridge_active=false.
C98 keeps controlled_parallel_run_active=false.
C98 keeps controlled_rollout_active=false.
C98 keeps weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime=false.
C98 keeps production_deployment_allowed=false.
C98 keeps production_deployment_executed=false.
C98 keeps plan_confirm_mutation_allowed=false.
C98 keeps plan_confirm_mutated=false.
C98 keeps plan_confirm_runtime_reads_activated_catalog=false.
C98 keeps live_plan_confirm_rollout_allowed=false.
C98 keeps live_plan_confirm_rollout_executed=false.
C98 keeps pilot_runtime_active=false.
C98 keeps shadow_runtime_active=false.
C98 keeps runtime_bridge_active=false.
C98 keeps weekly_swing_watchlist_runtime_active=false.
C98 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.
C98 keeps weekly_swing_watchlist_live_output_enabled=false.
C98 keeps weekly_swing_watchlist_official_output_generated=false.
C98 keeps weekly_swing_watchlist_official_output_published=false.
C98 keeps weekly_swing_watchlist_live_recommendation_generated=false.
C98 pass records artifact-only non-live rehearsal readiness for primary and backup and can only recommend `C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW`; it is not full production deployment, not PLAN/CONFIRM live rollout, not runtime bridge activation, and not weekly swing live output.
C98 weekly swing watchlist non-live rehearsal review means continue to C99 weekly swing watchlist non-live rehearsal execution review only.
C98 weekly swing watchlist non-live rehearsal review is not production deployment.
C98 weekly swing watchlist non-live rehearsal review is not PLAN/CONFIRM live rollout.
C98 weekly swing watchlist non-live rehearsal review is not runtime bridge activation.
C98 weekly swing watchlist non-live rehearsal review is not weekly swing live output.

## Focused PHPUnit C98

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC98"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Positive Runtime C98

```powershell
php artisan watchlist:backtest-c98-weekly-swing-watchlist-non-live-rehearsal-review `
  --c97-artifact=storage/app/watchlist/backtest/c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review.json `
  --expected-c97-hash=5898b6eaa0b537006ba249339c21b5038c8cb6fc `
  --expected-c97-file-sha1=620FF85234701FD72FC40BB661F068308751C2E4 `
  --approval-reference=C98_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c98-weekly-swing-watchlist-non-live-rehearsal-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_PASSED_NON_LIVE_REHEARSAL_READY_PRIMARY_AND_BACKUP
reason_code=C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_PASSED_NON_LIVE_REHEARSAL_READY_PRIMARY_AND_BACKUP
next_step_recommendation=C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c98-weekly-swing-watchlist-non-live-rehearsal-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.expected_c97_hash
$run.actual_c97_hash
$run.c97_hash_match
$run.expected_c97_file_sha1
$run.actual_c97_file_sha1
$run.c97_file_sha1_match
$run.temporary_negative_artifacts_remaining
$run.temporary_negative_artifact_cleanup_confirmed
$run.temporary_negative_artifact_paths
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
$run.weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime
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
$run.weekly_swing_watchlist_official_output_generated
$run.weekly_swing_watchlist_official_output_published
$run.weekly_swing_watchlist_live_recommendation_generated
$run.c98_readiness_decision | Format-List
$run.next_readiness_decision | Format-List
$run.weekly_swing_watchlist_non_live_rehearsal_manifest | Format-List
```

## C98 File SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c98-weekly-swing-watchlist-non-live-rehearsal-review.json -Algorithm SHA1
```

## Negative Approval Test - Without Operator Approval

```powershell
php artisan watchlist:backtest-c98-weekly-swing-watchlist-non-live-rehearsal-review `
  --c97-artifact=storage/app/watchlist/backtest/c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review.json `
  --expected-c97-hash=5898b6eaa0b537006ba249339c21b5038c8cb6fc `
  --expected-c97-file-sha1=620FF85234701FD72FC40BB661F068308751C2E4 `
  --approval-reference=C98_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c98-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Expected:

```text
status=C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Approval Test - Without Approval Reference

```powershell
php artisan watchlist:backtest-c98-weekly-swing-watchlist-non-live-rehearsal-review `
  --c97-artifact=storage/app/watchlist/backtest/c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review.json `
  --expected-c97-hash=5898b6eaa0b537006ba249339c21b5038c8cb6fc `
  --expected-c97-file-sha1=620FF85234701FD72FC40BB661F068308751C2E4 `
  --output=storage/app/watchlist/backtest/c98-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Cleanup Negative Temporary Artifacts

```powershell
Remove-Item storage/app/watchlist/backtest/c98-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item storage/app/watchlist/backtest/c98-no-approval-reference-test.json -ErrorAction SilentlyContinue

Get-ChildItem storage/app/watchlist/backtest -Filter "*no-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*missing-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*mismatch-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*negative-*-test.json"
```

Expected:

```text
No output
```

## Final Operator Evidence - 2026-06-28

```text
FOCUSED_PHPUNIT_C98=OK (53 tests, 328 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C98=OK (1805 tests, 25305 assertions)
C98_RUNTIME_STATUS=C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_PASSED_NON_LIVE_REHEARSAL_READY_PRIMARY_AND_BACKUP
C98_RUNTIME_REASON_CODE=C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_PASSED_NON_LIVE_REHEARSAL_READY_PRIMARY_AND_BACKUP
C98_ARTIFACT_HASH=269eb05141a2acf28925fdef51df9263955b0143
C98_FILE_SHA1=762BAFFCFCB104E10C9D8C6F6CCBD4E990766702
C97_HASH_MATCH=1
C97_FILE_SHA1_MATCH=1
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
WEEKLY_SWING_WATCHLIST_REHEARSAL_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
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
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW
```

C98 final operator validation passed. This is weekly swing watchlist non-live rehearsal review only and does not activate production, runtime bridge, pilot/shadow runtime, controlled rollout, PLAN/CONFIRM mutation, official weekly swing recommendation, or weekly swing live output.
