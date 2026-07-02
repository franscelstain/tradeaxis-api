# WS_C115_OPERATOR_VALIDATION_COMMANDS

C115 is PR-03 / C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW.
C115 validates C114 production runtime wiring readiness review and records controlled runtime wiring execution approval review only.
C115 does not execute production runtime wiring, deploy production, create weekly live output, or mutate PLAN/CONFIRM.

## Required Operator Reading

C115 validates C114 artifact hash and file SHA1.
C115 validates C114 production runtime wiring readiness review for execution approval review only.
C115 confirms C114 ConvertFrom-Json compatibility.
C115 keeps C111 terminal/final-closed for the non-live handoff audit archive chain.
C115 keeps C112 as a separate post-C111 production phase transition gate.
C115 keeps C113 as production readiness review only.
C115 keeps C114 as runtime wiring readiness review only.
C115 is not runtime wiring execution.
C115 is not production deployment.
C115 does not mutate PLAN/CONFIRM.
C115 requires --operator-approved.
C115 requires non-empty --approval-reference.
C115 confirms no temporary negative test artifact remains.
C115 creates controlled runtime wiring execution approval review manifest as artifact-only.
C115 creates controlled runtime wiring execution approval checklist as artifact-only.
C115 keeps A01 comparator-only and does not promote A01.
C115 does not execute production runtime wiring.
C115 does not wire production runtime.
C115 does not activate runtime bridge.
C115 does not create weekly swing live output.
C115 does not generate official weekly swing recommendation.
C115 does not publish weekly swing output.
C115 keeps production_ready=false.
C115 keeps production_catalog_runtime_wired=false.
C115 keeps production_runtime_wiring_allowed=false.
C115 keeps production_runtime_wiring_executed=false.
C115 keeps controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime=false.
C115 keeps controlled_runtime_wiring_execution_context_persisted_to_live_runtime=false.
C115 execution approval review means proceed to C116 controlled runtime wiring execution review only.
C115 execution approval record is not an official weekly swing stock recommendation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC115"
```

Expected:

```text
FOCUSED_PHPUNIT_C115=OK (109 tests, 422 assertions)
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected:

```text
FULL_WATCHLIST_PHPUNIT_POST_C115=OK (3048 tests, 31552 assertions)
```

## Positive Runtime

```powershell
php artisan watchlist:backtest-c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review `
  --c114-artifact=storage/app/watchlist/backtest/c114-weekly-swing-watchlist-production-runtime-wiring-readiness-review.json `
  --expected-c114-hash=f66f44216218ae5360e7920ef20f0ff051f8f987 `
  --expected-c114-file-sha1=51590143E73A77EB33F6ED67065CAE6ADF30D778 `
  --approval-reference=C115_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW_PRIMARY_AND_BACKUP
reason_code=C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW_PRIMARY_AND_BACKUP
next_step_recommendation=C116_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review.json | ConvertFrom-Json

$run.run_code
$run.phase_label
$run.status
$run.reason_code
$run.artifact_hash

$run.expected_c114_hash
$run.actual_c114_hash
$run.c114_hash_match
$run.expected_c114_file_sha1
$run.actual_c114_file_sha1
$run.c114_file_sha1_match
$run.c114_convert_from_json_pass
$run.c114_runtime_wiring_readiness_valid

$run.c111_final_closure_valid
$run.c111_non_live_audit_archive_terminal
$run.c112_not_audit_archive_continuation
$run.c112_does_not_reopen_c111_final_closure
$run.c113_production_readiness_valid

$run.temporary_negative_artifacts_remaining
$run.temporary_negative_artifact_cleanup_confirmed
$run.temporary_negative_artifact_paths

$run.production_ready
$run.production_catalog_runtime_wired
$run.production_runtime_wiring_allowed
$run.production_runtime_wiring_executed
$run.runtime_bridge_active
$run.controlled_rollout_active
$run.plan_confirm_mutation_allowed
$run.plan_confirm_mutated
$run.weekly_swing_watchlist_live_output_enabled
$run.weekly_swing_watchlist_official_output_generated
$run.weekly_swing_watchlist_official_output_published
$run.weekly_swing_watchlist_live_recommendation_generated
$run.controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime
$run.controlled_runtime_wiring_execution_context_persisted_to_live_runtime

$run.c115_execution_approval_decision | Format-List
$run.next_execution_decision | Format-List
$run.weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_review_manifest | Format-List
$run.weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_checklist | Format-List
```

Expected:

```text
c114_hash_match=1
c114_file_sha1_match=1
c114_convert_from_json_pass=1
c114_runtime_wiring_readiness_valid=1
c111_final_closure_valid=1
c111_non_live_audit_archive_terminal=1
c112_not_audit_archive_continuation=1
c112_does_not_reopen_c111_final_closure=1
c113_production_readiness_valid=1
temporary_negative_artifacts_remaining=0
temporary_negative_artifact_cleanup_confirmed=1
temporary_negative_artifact_paths=[]

production_ready=0
production_catalog_runtime_wired=0
production_runtime_wiring_allowed=0
production_runtime_wiring_executed=0
runtime_bridge_active=0
controlled_rollout_active=0
plan_confirm_mutation_allowed=0
plan_confirm_mutated=0
weekly_swing_watchlist_live_output_enabled=0
weekly_swing_watchlist_official_output_generated=0
weekly_swing_watchlist_official_output_published=0
weekly_swing_watchlist_live_recommendation_generated=0
controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime=0
controlled_runtime_wiring_execution_context_persisted_to_live_runtime=0
```

## C115 File SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review.json -Algorithm SHA1
```

Record:

```text
C115_FILE_SHA1=82E446F553FD0384FDD6E0BE25AE80F8E4FEA949
```

## Negative Approval Test - Without Operator Approval

```powershell
php artisan watchlist:backtest-c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review `
  --c114-artifact=storage/app/watchlist/backtest/c114-weekly-swing-watchlist-production-runtime-wiring-readiness-review.json `
  --expected-c114-hash=f66f44216218ae5360e7920ef20f0ff051f8f987 `
  --expected-c114-file-sha1=51590143E73A77EB33F6ED67065CAE6ADF30D778 `
  --approval-reference=C115_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c115-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Expected:

```text
status=C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Approval Test - Without Approval Reference

```powershell
php artisan watchlist:backtest-c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review `
  --c114-artifact=storage/app/watchlist/backtest/c114-weekly-swing-watchlist-production-runtime-wiring-readiness-review.json `
  --expected-c114-hash=f66f44216218ae5360e7920ef20f0ff051f8f987 `
  --expected-c114-file-sha1=51590143E73A77EB33F6ED67065CAE6ADF30D778 `
  --output=storage/app/watchlist/backtest/c115-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Cleanup Negative Temporary Artifacts

```powershell
Remove-Item storage/app/watchlist/backtest/c115-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item storage/app/watchlist/backtest/c115-no-approval-reference-test.json -ErrorAction SilentlyContinue

Get-ChildItem storage/app/watchlist/backtest -Filter "*no-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*missing-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*mismatch-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*negative-*-test.json"
```

Expected:

```text
No output
```

## Manual Local Evidence To Return

```text
FOCUSED_PHPUNIT_C115=OK (109 tests, 422 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C115=OK (3048 tests, 31552 assertions)
C115_RUNTIME_STATUS=C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW_PRIMARY_AND_BACKUP
C115_RUNTIME_REASON_CODE=C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW_PRIMARY_AND_BACKUP
C115_ARTIFACT_HASH=0e28d161447332d62df603edd7ba666b37e8dd04
C115_FILE_SHA1=82E446F553FD0384FDD6E0BE25AE80F8E4FEA949
C114_HASH_MATCH=1
C114_FILE_SHA1_MATCH=1
C114_CONVERT_FROM_JSON_PASS=1
C114_RUNTIME_WIRING_READINESS_VALID=1
TEMPORARY_NEGATIVE_ARTIFACTS_REMAINING=0
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP_CONFIRMED=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_OUTPUT
PRODUCTION_READY=0
PRODUCTION_CATALOG_RUNTIME_WIRED=0
PRODUCTION_RUNTIME_WIRING_ALLOWED=0
PRODUCTION_RUNTIME_WIRING_EXECUTED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
RUNTIME_BRIDGE_ACTIVE=0
CONTROLLED_ROLLOUT_ACTIVE=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
CONTROLLED_RUNTIME_WIRING_EXECUTION_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
NEXT_RECOMMENDATION=C116_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW
```
