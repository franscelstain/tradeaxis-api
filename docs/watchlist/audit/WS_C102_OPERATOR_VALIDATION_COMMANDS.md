# WS_C102_OPERATOR_VALIDATION_COMMANDS

C102 is weekly swing watchlist non-live rehearsal GO decision finalization review.
C102 starts from locked C101 weekly swing watchlist non-live rehearsal operator GO/NO-GO evidence.
E02 is primary non-live rehearsal finalized GO candidate.
B01 is backup non-live rehearsal finalized GO candidate.
A01 is comparator-only and cannot be promoted.
C102 validates C101 artifact hash and file SHA1.
C102 validates C101 weekly swing watchlist non-live rehearsal operator GO/NO-GO state.
C102 validates C101 next recommendation to C102.
C102 requires --operator-approved.
C102 requires non-empty --approval-reference.
C102 confirms no temporary negative test artifact remains.
C102 records weekly swing watchlist non-live rehearsal GO decision finalization review only.
C102 records finalized GO for E02 and B01 only.
C102 creates artifact-only non-live rehearsal GO decision finalization manifest.
C102 does not redesign.
C102 does not retune.
C102 does not run parameter search.
C102 does not run OOS rerank.
C102 does not rebuild signal quality.
C102 does not use operator GO evidence to rerank.
C102 does not use operator GO evidence to select.
C102 does not use operator GO evidence to deploy.
C102 does not change candidate scope.
C102 does not promote A01.
C102 does not change scoring logic.
C102 does not change catalog selection.
C102 does not change runtime selection.
C102 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C102 does not deploy live production.
C102 does not mutate PLAN/CONFIRM.
C102 does not change PLAN/CONFIRM output.
C102 does not activate pilot runtime.
C102 does not activate shadow runtime.
C102 does not activate runtime bridge.
C102 does not activate weekly swing watchlist runtime.
C102 does not create weekly swing live output.
C102 does not generate official weekly swing recommendation.
C102 does not publish weekly swing output.
C102 keeps production_ready=false.
C102 keeps production_catalog_runtime_wired=false.
C102 keeps controlled_opt_in_runtime_bridge_active=false.
C102 keeps controlled_parallel_run_active=false.
C102 keeps controlled_rollout_active=false.
C102 keeps weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime=false.
C102 keeps weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime=false.
C102 keeps weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime=false.
C102 keeps weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime=false.
C102 keeps operator_go_no_go_context_persisted_to_live_runtime=false.
C102 keeps weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_persisted_to_live_runtime=false.
C102 keeps go_decision_finalization_context_persisted_to_live_runtime=false.
C102 keeps production_deployment_allowed=false.
C102 keeps production_deployment_executed=false.
C102 keeps plan_confirm_mutation_allowed=false.
C102 keeps plan_confirm_mutated=false.
C102 keeps plan_confirm_runtime_reads_activated_catalog=false.
C102 keeps live_plan_confirm_rollout_allowed=false.
C102 keeps live_plan_confirm_rollout_executed=false.
C102 keeps pilot_runtime_active=false.
C102 keeps shadow_runtime_active=false.
C102 keeps runtime_bridge_active=false.
C102 keeps weekly_swing_watchlist_runtime_active=false.
C102 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.
C102 keeps weekly_swing_watchlist_live_output_enabled=false.
C102 keeps weekly_swing_watchlist_official_output_generated=false.
C102 keeps weekly_swing_watchlist_official_output_published=false.
C102 keeps weekly_swing_watchlist_live_recommendation_generated=false.
C102 pass records artifact-only non-live finalized GO for primary and backup and can only recommend `C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW`; it is not full production deployment, not PLAN/CONFIRM live rollout, not runtime bridge activation, and not weekly swing live output.
C102 weekly swing watchlist non-live rehearsal GO decision finalization review means continue to C103 weekly swing watchlist non-live rehearsal completion boundary review only.
C102 GO is not production deployment.
C102 GO is not PLAN/CONFIRM live rollout.
C102 GO is not runtime bridge activation.
C102 GO is not weekly swing live output.

## Focused PHPUnit C102

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC102"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Positive Runtime C102

```powershell
php artisan watchlist:backtest-c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review `
  --c101-artifact=storage/app/watchlist/backtest/c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review.json `
  --expected-c101-hash=f8a339760d94d230e184dc6f6b3016731ba72379 `
  --expected-c101-file-sha1=B12CF95D02172659B51B215E567D0B31C6F891F7 `
  --approval-reference=C102_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
reason_code=C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
next_step_recommendation=C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.expected_c101_hash
$run.actual_c101_hash
$run.c101_hash_match
$run.expected_c101_file_sha1
$run.actual_c101_file_sha1
$run.c101_file_sha1_match
$run.temporary_negative_artifacts_remaining
$run.temporary_negative_artifact_cleanup_confirmed
$run.temporary_negative_artifact_paths
$run.operator_go_decision
$run.operator_go_decision_confirmed
$run.go_decision_finalized
$run.go_decision_finalization_confirmed
$run.primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized
$run.backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized
$run.comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
$run.weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime
$run.weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime
$run.weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime
$run.weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime
$run.operator_go_no_go_context_persisted_to_live_runtime
$run.weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_persisted_to_live_runtime
$run.go_decision_finalization_context_persisted_to_live_runtime
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
$run.c102_go_decision_finalization_decision | Format-List
$run.next_readiness_decision | Format-List
$run.weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_manifest | Format-List
```

## C102 File SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review.json -Algorithm SHA1
```

## Negative Approval Test - Without Operator Approval

```powershell
php artisan watchlist:backtest-c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review `
  --c101-artifact=storage/app/watchlist/backtest/c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review.json `
  --expected-c101-hash=f8a339760d94d230e184dc6f6b3016731ba72379 `
  --expected-c101-file-sha1=B12CF95D02172659B51B215E567D0B31C6F891F7 `
  --approval-reference=C102_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c102-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Expected:

```text
status=C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Approval Test - Without Approval Reference

```powershell
php artisan watchlist:backtest-c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review `
  --c101-artifact=storage/app/watchlist/backtest/c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review.json `
  --expected-c101-hash=f8a339760d94d230e184dc6f6b3016731ba72379 `
  --expected-c101-file-sha1=B12CF95D02172659B51B215E567D0B31C6F891F7 `
  --output=storage/app/watchlist/backtest/c102-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Cleanup Negative Temporary Artifacts

```powershell
Remove-Item storage/app/watchlist/backtest/c102-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item storage/app/watchlist/backtest/c102-no-approval-reference-test.json -ErrorAction SilentlyContinue

Get-ChildItem storage/app/watchlist/backtest -Filter "*no-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*missing-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*mismatch-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*negative-*-test.json"
```

Expected:

```text
No output
```

## Final Operator Evidence - 2026-06-29

```text
FOCUSED_PHPUNIT_C102=OK (61 tests, 384 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C102=OK (2045 tests, 26739 assertions)
C102_RUNTIME_STATUS=C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
C102_RUNTIME_REASON_CODE=C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
C102_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review.json
C102_ARTIFACT_HASH=e9e246048d14dcedda262a35fce9d52b64b052c0
C102_ARTIFACT_FILE_SHA1=DD731AFB11D2EA513EEF6795BF03D2F404670FB6
C102_SOURCE_LOCK=C101
EXPECTED_C101_HASH=f8a339760d94d230e184dc6f6b3016731ba72379
ACTUAL_C101_HASH=f8a339760d94d230e184dc6f6b3016731ba72379
C101_HASH_MATCH=1
EXPECTED_C101_FILE_SHA1=B12CF95D02172659B51B215E567D0B31C6F891F7
ACTUAL_C101_FILE_SHA1=B12CF95D02172659B51B215E567D0B31C6F891F7
C101_FILE_SHA1_MATCH=1
OPERATOR_GO_DECISION=GO
OPERATOR_GO_DECISION_CONFIRMED=1
GO_DECISION_FINALIZED=1
GO_DECISION_FINALIZATION_CONFIRMED=1
C102_NEGATIVE_APPROVAL_GATE=PASS_REJECTED_OPERATOR_APPROVAL_MISSING
C102_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_OUTPUT
C102_SAFETY_BOUNDARY=NON_LIVE_ARTIFACT_ONLY_FINALIZED_GO_RECORDED_PRODUCTION_DISABLED_PLAN_CONFIRM_UNCHANGED_WEEKLY_LIVE_OUTPUT_DISABLED
C102_NEXT_RECOMMENDATION=C103_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_COMPLETION_BOUNDARY_REVIEW
```
