# WS_C101_OPERATOR_VALIDATION_COMMANDS

C101 is weekly swing watchlist non-live rehearsal operator go/no-go review.
C101 starts from locked C100 weekly swing watchlist non-live rehearsal result review evidence.
C100 reviewed the artifact-only non-live rehearsal result for primary + backup.
E02 is primary non-live rehearsal operator GO candidate.
B01 is backup non-live rehearsal operator GO candidate.
A01 is comparator-only and cannot be promoted.
C101 validates C100 artifact hash and file SHA1.
C101 validates C100 weekly swing watchlist non-live rehearsal result review state.
C101 validates C100 next recommendation to C101.
C101 requires --operator-approved.
C101 requires non-empty --approval-reference.
C101 confirms no temporary negative test artifact remains.
C101 records weekly swing watchlist non-live rehearsal operator go/no-go review only.
C101 records operator GO for E02 and B01 only.
C101 creates artifact-only non-live rehearsal operator go/no-go manifest.
C101 does not redesign.
C101 does not retune.
C101 does not run parameter search.
C101 does not run OOS rerank.
C101 does not rebuild signal quality.
C101 does not use operator GO evidence to rerank.
C101 does not use operator GO evidence to select.
C101 does not use operator GO evidence to deploy.
C101 does not change candidate scope.
C101 does not promote A01.
C101 does not change scoring logic.
C101 does not change catalog selection.
C101 does not change runtime selection.
C101 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C101 does not deploy live production.
C101 does not mutate PLAN/CONFIRM.
C101 does not change PLAN/CONFIRM output.
C101 does not activate pilot runtime.
C101 does not activate shadow runtime.
C101 does not activate runtime bridge.
C101 does not activate weekly swing watchlist runtime.
C101 does not create weekly swing live output.
C101 does not generate official weekly swing recommendation.
C101 does not publish weekly swing output.
C101 keeps production_ready=false.
C101 keeps production_catalog_runtime_wired=false.
C101 keeps controlled_opt_in_runtime_bridge_active=false.
C101 keeps controlled_parallel_run_active=false.
C101 keeps controlled_rollout_active=false.
C101 keeps weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime=false.
C101 keeps weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime=false.
C101 keeps weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime=false.
C101 keeps weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime=false.
C101 keeps operator_go_no_go_context_persisted_to_live_runtime=false.
C101 keeps production_deployment_allowed=false.
C101 keeps production_deployment_executed=false.
C101 keeps plan_confirm_mutation_allowed=false.
C101 keeps plan_confirm_mutated=false.
C101 keeps plan_confirm_runtime_reads_activated_catalog=false.
C101 keeps live_plan_confirm_rollout_allowed=false.
C101 keeps live_plan_confirm_rollout_executed=false.
C101 keeps pilot_runtime_active=false.
C101 keeps shadow_runtime_active=false.
C101 keeps runtime_bridge_active=false.
C101 keeps weekly_swing_watchlist_runtime_active=false.
C101 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.
C101 keeps weekly_swing_watchlist_live_output_enabled=false.
C101 keeps weekly_swing_watchlist_official_output_generated=false.
C101 keeps weekly_swing_watchlist_official_output_published=false.
C101 keeps weekly_swing_watchlist_live_recommendation_generated=false.
C101 pass records artifact-only non-live operator GO for primary and backup and can only recommend `C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW`; it is not full production deployment, not PLAN/CONFIRM live rollout, not runtime bridge activation, and not weekly swing live output.
C101 weekly swing watchlist non-live rehearsal operator go/no-go review means continue to C102 weekly swing watchlist non-live rehearsal go decision finalization review only.
C101 GO is not production deployment.
C101 GO is not PLAN/CONFIRM live rollout.
C101 GO is not runtime bridge activation.
C101 GO is not weekly swing live output.

## Focused PHPUnit C101

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC101"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Positive Runtime C101

```powershell
php artisan watchlist:backtest-c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review `
  --c100-artifact=storage/app/watchlist/backtest/c100-weekly-swing-watchlist-non-live-rehearsal-result-review.json `
  --expected-c100-hash=3b4467db23914686eea465ecf11601e7dfd3a9e6 `
  --expected-c100-file-sha1=E66CD7902FBE0454BFC30CED7695020E925B597E `
  --approval-reference=C101_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
reason_code=C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
next_step_recommendation=C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.expected_c100_hash
$run.actual_c100_hash
$run.c100_hash_match
$run.expected_c100_file_sha1
$run.actual_c100_file_sha1
$run.c100_file_sha1_match
$run.temporary_negative_artifacts_remaining
$run.temporary_negative_artifact_cleanup_confirmed
$run.temporary_negative_artifact_paths
$run.operator_go_decision
$run.operator_go_decision_confirmed
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
$run.c101_operator_go_no_go_decision | Format-List
$run.next_readiness_decision | Format-List
$run.weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_manifest | Format-List
```

## C101 File SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review.json -Algorithm SHA1
```

## Negative Approval Test - Without Operator Approval

```powershell
php artisan watchlist:backtest-c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review `
  --c100-artifact=storage/app/watchlist/backtest/c100-weekly-swing-watchlist-non-live-rehearsal-result-review.json `
  --expected-c100-hash=3b4467db23914686eea465ecf11601e7dfd3a9e6 `
  --expected-c100-file-sha1=E66CD7902FBE0454BFC30CED7695020E925B597E `
  --approval-reference=C101_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c101-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Expected:

```text
status=C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Approval Test - Without Approval Reference

```powershell
php artisan watchlist:backtest-c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review `
  --c100-artifact=storage/app/watchlist/backtest/c100-weekly-swing-watchlist-non-live-rehearsal-result-review.json `
  --expected-c100-hash=3b4467db23914686eea465ecf11601e7dfd3a9e6 `
  --expected-c100-file-sha1=E66CD7902FBE0454BFC30CED7695020E925B597E `
  --output=storage/app/watchlist/backtest/c101-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected:

```text
status=C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
reason_code=C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Cleanup Negative Temporary Artifacts

```powershell
Remove-Item storage/app/watchlist/backtest/c101-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item storage/app/watchlist/backtest/c101-no-approval-reference-test.json -ErrorAction SilentlyContinue

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
FOCUSED_PHPUNIT_C101=OK (64 tests, 374 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C101=OK (1984 tests, 26355 assertions)
C101_RUNTIME_STATUS=C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
C101_RUNTIME_REASON_CODE=C101_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
C101_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review.json
C101_ARTIFACT_HASH=f8a339760d94d230e184dc6f6b3016731ba72379
C101_ARTIFACT_FILE_SHA1=B12CF95D02172659B51B215E567D0B31C6F891F7
C101_SOURCE_LOCK=C100
EXPECTED_C100_HASH=3b4467db23914686eea465ecf11601e7dfd3a9e6
ACTUAL_C100_HASH=3b4467db23914686eea465ecf11601e7dfd3a9e6
C100_HASH_MATCH=1
EXPECTED_C100_FILE_SHA1=E66CD7902FBE0454BFC30CED7695020E925B597E
ACTUAL_C100_FILE_SHA1=E66CD7902FBE0454BFC30CED7695020E925B597E
C100_FILE_SHA1_MATCH=1
OPERATOR_GO_DECISION=GO
OPERATOR_GO_DECISION_CONFIRMED=1
C101_NEGATIVE_APPROVAL_GATE=PASS_REJECTED_OPERATOR_APPROVAL_MISSING
C101_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_OUTPUT
C101_SAFETY_BOUNDARY=NON_LIVE_ARTIFACT_ONLY_OPERATOR_GO_RECORDED_PRODUCTION_DISABLED_PLAN_CONFIRM_UNCHANGED_WEEKLY_LIVE_OUTPUT_DISABLED
C101_NEXT_RECOMMENDATION=C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW
```
