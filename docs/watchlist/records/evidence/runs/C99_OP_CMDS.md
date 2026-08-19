# WS_C99_OPERATOR_VALIDATION_COMMANDS

C99 is weekly swing watchlist non-live rehearsal execution review.
C99 starts from locked C98 weekly swing watchlist non-live rehearsal readiness evidence.
C98 prepared the artifact-only non-live rehearsal package for primary + backup.
E02 is primary non-live rehearsal execution candidate.
B01 is backup non-live rehearsal execution candidate.
A01 is comparator-only and cannot be promoted.
C99 validates C98 artifact hash and file SHA1.
C99 validates C98 weekly swing watchlist non-live rehearsal ready state.
C99 validates C98 next recommendation to C99.
C99 requires --operator-approved.
C99 requires non-empty --approval-reference.
C99 confirms no temporary negative test artifact remains.
C99 records weekly swing watchlist non-live rehearsal execution review only.
C99 creates artifact-only non-live rehearsal execution manifest.
C99 does not redesign.
C99 does not retune.
C99 does not run parameter search.
C99 does not run OOS rerank.
C99 does not rebuild signal quality.
C99 does not use rehearsal execution evidence to rerank.
C99 does not use rehearsal execution evidence to select.
C99 does not use rehearsal execution evidence to deploy.
C99 does not change candidate scope.
C99 does not promote A01.
C99 does not change scoring logic.
C99 does not change catalog selection.
C99 does not change runtime selection.
C99 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C99 does not deploy live production.
C99 does not mutate PLAN/CONFIRM.
C99 does not change PLAN/CONFIRM output.
C99 does not activate pilot runtime.
C99 does not activate shadow runtime.
C99 does not activate runtime bridge.
C99 does not activate weekly swing watchlist runtime.
C99 does not create weekly swing live output.
C99 does not generate official weekly swing recommendation.
C99 does not publish weekly swing output.
C99 keeps production_ready=false.
C99 keeps production_catalog_runtime_wired=false.
C99 keeps controlled_opt_in_runtime_bridge_active=false.
C99 keeps controlled_parallel_run_active=false.
C99 keeps controlled_rollout_active=false.
C99 keeps weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime=false.
C99 keeps weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime=false.
C99 keeps production_deployment_allowed=false.
C99 keeps production_deployment_executed=false.
C99 keeps plan_confirm_mutation_allowed=false.
C99 keeps plan_confirm_mutated=false.
C99 keeps plan_confirm_runtime_reads_activated_catalog=false.
C99 keeps live_plan_confirm_rollout_allowed=false.
C99 keeps live_plan_confirm_rollout_executed=false.
C99 keeps pilot_runtime_active=false.
C99 keeps shadow_runtime_active=false.
C99 keeps runtime_bridge_active=false.
C99 keeps weekly_swing_watchlist_runtime_active=false.
C99 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.
C99 keeps weekly_swing_watchlist_live_output_enabled=false.
C99 keeps weekly_swing_watchlist_official_output_generated=false.
C99 keeps weekly_swing_watchlist_official_output_published=false.
C99 keeps weekly_swing_watchlist_live_recommendation_generated=false.
C99 pass records artifact-only non-live rehearsal execution for primary and backup and can only recommend `C100_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_RESULT_REVIEW`; it is not full production deployment, not PLAN/CONFIRM live rollout, not runtime bridge activation, and not weekly swing live output.
C99 weekly swing watchlist non-live rehearsal execution review means continue to C100 weekly swing watchlist non-live rehearsal result review only.
C99 weekly swing watchlist non-live rehearsal execution review is not production deployment.
C99 weekly swing watchlist non-live rehearsal execution review is not PLAN/CONFIRM live rollout.
C99 weekly swing watchlist non-live rehearsal execution review is not runtime bridge activation.
C99 weekly swing watchlist non-live rehearsal execution review is not weekly swing live output.

## Focused PHPUnit C99

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC99"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Positive Runtime C99

```powershell
php artisan watchlist:backtest-c99-weekly-swing-watchlist-non-live-rehearsal-execution-review `
  --c98-artifact=storage/app/watchlist/backtest/c98-weekly-swing-watchlist-non-live-rehearsal-review.json `
  --expected-c98-hash=269eb05141a2acf28925fdef51df9263955b0143 `
  --expected-c98-file-sha1=762BAFFCFCB104E10C9D8C6F6CCBD4E990766702 `
  --approval-reference=C99_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c99-weekly-swing-watchlist-non-live-rehearsal-execution-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_PASSED_NON_LIVE_REHEARSAL_EXECUTED_PRIMARY_AND_BACKUP
reason_code=C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_PASSED_NON_LIVE_REHEARSAL_EXECUTED_PRIMARY_AND_BACKUP
next_step_recommendation=C100_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_RESULT_REVIEW
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c99-weekly-swing-watchlist-non-live-rehearsal-execution-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.expected_c98_hash
$run.actual_c98_hash
$run.c98_hash_match
$run.expected_c98_file_sha1
$run.actual_c98_file_sha1
$run.c98_file_sha1_match
$run.temporary_negative_artifacts_remaining
$run.temporary_negative_artifact_cleanup_confirmed
$run.temporary_negative_artifact_paths
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
$run.weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime
$run.weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime
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
$run.c99_execution_decision | Format-List
$run.next_readiness_decision | Format-List
$run.weekly_swing_watchlist_non_live_rehearsal_execution_manifest | Format-List
```

## C99 File SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c99-weekly-swing-watchlist-non-live-rehearsal-execution-review.json -Algorithm SHA1
```

## Negative Approval Test - Without Operator Approval

```powershell
php artisan watchlist:backtest-c99-weekly-swing-watchlist-non-live-rehearsal-execution-review `
  --c98-artifact=storage/app/watchlist/backtest/c98-weekly-swing-watchlist-non-live-rehearsal-review.json `
  --expected-c98-hash=269eb05141a2acf28925fdef51df9263955b0143 `
  --expected-c98-file-sha1=762BAFFCFCB104E10C9D8C6F6CCBD4E990766702 `
  --approval-reference=C99_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c99-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Expected:

```text
status=C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Approval Test - Without Approval Reference

```powershell
php artisan watchlist:backtest-c99-weekly-swing-watchlist-non-live-rehearsal-execution-review `
  --c98-artifact=storage/app/watchlist/backtest/c98-weekly-swing-watchlist-non-live-rehearsal-review.json `
  --expected-c98-hash=269eb05141a2acf28925fdef51df9263955b0143 `
  --expected-c98-file-sha1=762BAFFCFCB104E10C9D8C6F6CCBD4E990766702 `
  --output=storage/app/watchlist/backtest/c99-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Cleanup Negative Temporary Artifacts

```powershell
Remove-Item storage/app/watchlist/backtest/c99-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item storage/app/watchlist/backtest/c99-no-approval-reference-test.json -ErrorAction SilentlyContinue

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
FOCUSED_PHPUNIT_C99=OK (56 tests, 333 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C99=OK (1861 tests, 25638 assertions)
C99_RUNTIME_STATUS=C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_PASSED_NON_LIVE_REHEARSAL_EXECUTED_PRIMARY_AND_BACKUP
C99_RUNTIME_REASON_CODE=C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_PASSED_NON_LIVE_REHEARSAL_EXECUTED_PRIMARY_AND_BACKUP
C99_ARTIFACT_HASH=33d63c80f88c00e704b54d923ac511492994d34c
C99_FILE_SHA1=0C43E16B1C6FB6338343DD12BCA5E04A43BDBB41
C98_HASH_MATCH=1
C98_FILE_SHA1_MATCH=1
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
WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
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
NEXT_RECOMMENDATION=C100_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_RESULT_REVIEW
```

C99 final operator validation passed. This is weekly swing watchlist non-live rehearsal execution review only and does not activate production, runtime bridge, pilot/shadow runtime, controlled rollout, PLAN/CONFIRM mutation, official weekly swing recommendation, or weekly swing live output.
