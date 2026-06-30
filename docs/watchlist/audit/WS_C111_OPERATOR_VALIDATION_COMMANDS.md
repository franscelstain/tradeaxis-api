# WS_C111_OPERATOR_VALIDATION_COMMANDS

C111 is weekly swing watchlist non-live rehearsal handoff audit archive final closure review.
C111 locks C110 weekly swing watchlist non-live rehearsal handoff audit archive completion seal review as source.
E02 is primary non-live rehearsal handoff audit archive final closure candidate.
B01 is backup non-live rehearsal handoff audit archive final closure candidate.
A01 remains comparator-only.

## Required Operator Reading

C111 validates C110 artifact hash and file SHA1.
C111 validates C110 weekly swing watchlist non-live rehearsal handoff audit archive completion seal state.
C111 validates C104-C110 handoff lineage is carried forward as final-closed.
C111 requires --operator-approved.
C111 requires non-empty --approval-reference.
C111 confirms no temporary negative test artifact remains.
C111 final closes weekly swing watchlist non-live rehearsal handoff audit archive only.
C111 marks handoff audit archive final closed for E02 and B01 only.
C111 keeps A01 comparator-only and does not promote A01.
C111 creates artifact-only non-live rehearsal handoff audit archive final closure manifest.
C111 does not run OOS rerank.
C111 does not rebuild signal quality.
C111 does not change candidate selection.
C111 does not rerank candidate.
C111 does not retune strategy.
C111 does not change scoring logic.
C111 does not change catalog selection.
C111 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C111 does not deploy live production.
C111 does not mutate PLAN/CONFIRM.
C111 does not change PLAN/CONFIRM output.
C111 does not activate controlled rollout.
C111 does not activate pilot runtime.
C111 does not activate shadow runtime.
C111 does not activate runtime bridge.
C111 does not activate weekly swing watchlist runtime.
C111 does not create weekly swing live output.
C111 does not generate official weekly swing recommendation.
C111 does not publish weekly swing output.
C111 keeps production_ready=false.
C111 keeps production_catalog_runtime_wired=false.
C111 keeps controlled_opt_in_runtime_bridge_active=false.
C111 keeps controlled_parallel_run_active=false.
C111 keeps controlled_rollout_active=false.
C111 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_persisted_to_live_runtime=false.
C111 keeps handoff_audit_archive_context_persisted_to_live_runtime=false.
C111 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_persisted_to_live_runtime=false.
C111 keeps handoff_audit_archive_completion_context_persisted_to_live_runtime=false.
C111 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_seal_context_persisted_to_live_runtime=false.
C111 keeps handoff_audit_archive_completion_seal_context_persisted_to_live_runtime=false.
C111 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_context_persisted_to_live_runtime=false.
C111 keeps handoff_audit_archive_final_closure_context_persisted_to_live_runtime=false.
C111 keeps production_deployment_allowed=false.
C111 keeps production_deployment_executed=false.
C111 keeps plan_confirm_mutation_allowed=false.
C111 keeps plan_confirm_mutated=false.
C111 keeps plan_confirm_runtime_reads_activated_catalog=false.
C111 keeps live_plan_confirm_rollout_allowed=false.
C111 keeps live_plan_confirm_rollout_executed=false.
C111 keeps pilot_runtime_active=false.
C111 keeps shadow_runtime_active=false.
C111 keeps runtime_bridge_active=false.
C111 keeps weekly_swing_watchlist_runtime_active=false.
C111 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.
C111 keeps weekly_swing_watchlist_live_output_enabled=false.
C111 keeps weekly_swing_watchlist_official_output_generated=false.
C111 keeps weekly_swing_watchlist_official_output_published=false.
C111 keeps weekly_swing_watchlist_live_recommendation_generated=false.
C111 weekly swing watchlist non-live rehearsal handoff audit archive final closure review means the non-live audit archive package is closed; it is not a production deployment or live rollout.
C111 handoff audit archive final closure record is not production deployment.
C111 handoff audit archive final closure record is not PLAN/CONFIRM live rollout.
C111 handoff audit archive final closure record is not runtime bridge activation.
C111 handoff audit archive final closure record is not weekly swing live output.
C111 handoff audit archive final closure record is not official weekly swing stock recommendation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC111"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review `
  --c110-artifact=storage/app/watchlist/backtest/c110-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-completion-seal-review.json `
  --expected-c110-hash=17352f926bcf9138be62c9f43a81551f89de0cc7 `
  --expected-c110-file-sha1=407DB31435BF42C48FD0C7419B7BEBCA138DB127 `
  --approval-reference=C111_OPERATOR_APPROVED_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected passing status:

```text
C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.expected_c110_hash
$run.actual_c110_hash
$run.c110_hash_match
$run.expected_c110_file_sha1
$run.actual_c110_file_sha1
$run.c110_file_sha1_match
$run.handoff_audit_archive_final_closed
$run.audit_archive_final_closed
$run.final_closure_manifest_created
$run.primary_candidate_handoff_audit_archive_final_closed
$run.backup_candidate_handoff_audit_archive_final_closed
$run.comparator_candidate_handoff_audit_archive_final_closed
$run.temporary_negative_artifacts_remaining
$run.temporary_negative_artifact_cleanup_confirmed
$run.temporary_negative_artifact_paths
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
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
$run.handoff_audit_archive_final_closure_context_persisted_to_live_runtime
$run.c111_readiness_decision | Format-List
$run.next_readiness_decision | Format-List
$run.weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_final_closure_manifest | Format-List
```

Expected key values:

```text
c110_hash_match=1
c110_file_sha1_match=1
handoff_audit_archive_final_closed=1
audit_archive_final_closed=1
final_closure_manifest_created=1
primary_candidate_handoff_audit_archive_final_closed=1
backup_candidate_handoff_audit_archive_final_closed=1
comparator_candidate_handoff_audit_archive_final_closed=0
temporary_negative_artifacts_remaining=0
temporary_negative_artifact_cleanup_confirmed=1
temporary_negative_artifact_paths=[]
production_ready=0
production_catalog_runtime_wired=0
controlled_opt_in_runtime_bridge_active=0
controlled_parallel_run_active=0
controlled_rollout_active=0
production_deployment_allowed=0
production_deployment_executed=0
plan_confirm_mutation_allowed=0
plan_confirm_mutated=0
plan_confirm_runtime_reads_activated_catalog=0
live_plan_confirm_rollout_allowed=0
live_plan_confirm_rollout_executed=0
pilot_runtime_active=0
shadow_runtime_active=0
runtime_bridge_active=0
weekly_swing_watchlist_runtime_active=0
weekly_swing_watchlist_plan_confirm_mutation_allowed=0
weekly_swing_watchlist_live_output_enabled=0
weekly_swing_watchlist_official_output_generated=0
weekly_swing_watchlist_official_output_published=0
weekly_swing_watchlist_live_recommendation_generated=0
handoff_audit_archive_final_closure_context_persisted_to_live_runtime=0
next_step_recommendation=NO_NEXT_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED
```

## C111 File SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json -Algorithm SHA1
```

## Negative Approval Gate

Without --operator-approved:

```powershell
php artisan watchlist:backtest-c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review `
  --c110-artifact=storage/app/watchlist/backtest/c110-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-completion-seal-review.json `
  --expected-c110-hash=17352f926bcf9138be62c9f43a81551f89de0cc7 `
  --expected-c110-file-sha1=407DB31435BF42C48FD0C7419B7BEBCA138DB127 `
  --approval-reference=C111_OPERATOR_APPROVED_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c111-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without --approval-reference:

```powershell
php artisan watchlist:backtest-c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review `
  --c110-artifact=storage/app/watchlist/backtest/c110-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-completion-seal-review.json `
  --expected-c110-hash=17352f926bcf9138be62c9f43a81551f89de0cc7 `
  --expected-c110-file-sha1=407DB31435BF42C48FD0C7419B7BEBCA138DB127 `
  --output=storage/app/watchlist/backtest/c111-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected rejected status:

```text
C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove negative artifacts after the gate check:

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/c111-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c111-no-approval-reference-test.json -ErrorAction SilentlyContinue
Get-ChildItem storage/app/watchlist/backtest -Filter '*no-*-test.json'
Get-ChildItem storage/app/watchlist/backtest -Filter '*missing-*-test.json'
Get-ChildItem storage/app/watchlist/backtest -Filter '*mismatch-*-test.json'
Get-ChildItem storage/app/watchlist/backtest -Filter '*negative-*-test.json'
```

Expected cleanup result:

```text
No output
```

## C111 Final Operator Evidence Captured - 2026-06-30

```text
FOCUSED_PHPUNIT_C111=OK (92 tests, 427 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C111=OK (2685 tests, 30084 assertions)
RUNTIME_STATUS=C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP
RUNTIME_REASON_CODE=C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP
RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json
ARTIFACT_HASH=8f7c8b81eb401bfdd70f62f90779db63fc4af56d
ARTIFACT_FILE_SHA1=D58C10185970C9344F6EB3818A5A31C75C876842
SOURCE_LOCK=C110
EXPECTED_C110_HASH=17352f926bcf9138be62c9f43a81551f89de0cc7
ACTUAL_C110_HASH=17352f926bcf9138be62c9f43a81551f89de0cc7
C110_HASH_MATCH=1
EXPECTED_C110_FILE_SHA1=407DB31435BF42C48FD0C7419B7BEBCA138DB127
ACTUAL_C110_FILE_SHA1=407DB31435BF42C48FD0C7419B7BEBCA138DB127
C110_FILE_SHA1_MATCH=1
HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED=1
AUDIT_ARCHIVE_FINAL_CLOSED=1
FINAL_CLOSURE_MANIFEST_CREATED=1
PRIMARY_CANDIDATE_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED=1
BACKUP_CANDIDATE_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED=1
COMPARATOR_CANDIDATE_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED=0
A01_REMAINS_COMPARATOR_ONLY=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C111_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_OUTPUT
PRODUCTION_READY=0
PRODUCTION_CATALOG_RUNTIME_WIRED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
NEXT_RECOMMENDATION=NO_NEXT_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED
```

## C111/C112 Boundary Clarification For Operator - 2026-06-30

```text
C111_OPERATOR_INTERPRETATION=FINAL_CLOSE_NON_LIVE_HANDOFF_AUDIT_ARCHIVE
C111_NO_NEXT_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED=1
C112_OPERATOR_INTERPRETATION=SEPARATE_POST_C111_PRODUCTION_PHASE_TRANSITION_GATE_ONLY
C112_NOT_AUDIT_ARCHIVE_CONTINUATION=1
C112_DOES_NOT_REOPEN_C111_FINAL_CLOSURE=1
C112_PRODUCTION_READY=0
C112_PRODUCTION_RUNTIME_WIRING_ALLOWED=0
C112_PRODUCTION_DEPLOYMENT_ALLOWED=0
C112_PLAN_CONFIRM_MUTATION_ALLOWED=0
C112_WEEKLY_SWING_LIVE_OUTPUT_ENABLED=0
```

Operator evidence for C111 should be read as terminal closure for the non-live audit archive. Any C112/C113 step belongs to a separate production-readiness path and must remain separately approved, separately locked, and non-mutating until an explicit production gate allows otherwise.
