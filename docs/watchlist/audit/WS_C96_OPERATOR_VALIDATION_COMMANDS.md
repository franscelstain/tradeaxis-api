# WS_C96_OPERATOR_VALIDATION_COMMANDS

C96 is controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive closure seal review.
C96 starts from locked C95 audit archive completion evidence.
C95 completed the post-activation audit archive evidence for primary + backup.
E02 is primary post-activation audit archive closure sealed candidate.
B01 is backup post-activation audit archive closure sealed candidate.
A01 is comparator-only and cannot be promoted.
C96 validates C95 artifact hash and file SHA1.
C96 validates C95 audit archive completion state.
C96 validates C95 next recommendation to C96.
C96 requires --operator-approved.
C96 requires non-empty --approval-reference.
C96 confirms no temporary negative test artifact remains.
C96 records post-activation audit archive closure seal only.
C96 seals post-activation audit archive closure evidence only.
C96 does not redesign.
C96 does not retune.
C96 does not run parameter search.
C96 does not run OOS rerank.
C96 does not use audit archive closure seal evidence to rerank.
C96 does not use audit archive closure seal evidence to deploy.
C96 does not change candidate scope.
C96 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C96 does not deploy live production.
C96 does not mutate PLAN/CONFIRM.
C96 does not change PLAN/CONFIRM output.
C96 keeps production_ready=false.
C96 keeps production_catalog_runtime_wired=false.
C96 keeps controlled_opt_in_runtime_bridge_active=false.
C96 keeps controlled_parallel_run_active=false.
C96 keeps controlled_rollout_active=false.
C96 keeps post_activation_audit_archive_context_persisted_to_live_runtime=false.
C96 keeps post_activation_audit_archive_completion_context_persisted_to_live_runtime=false.
C96 keeps post_activation_audit_archive_closure_seal_context_persisted_to_live_runtime=false.
C96 keeps production_deployment_allowed=false.
C96 keeps production_deployment_executed=false.
C96 keeps plan_confirm_mutation_allowed=false.
C96 keeps plan_confirm_mutated=false.
C96 keeps plan_confirm_runtime_reads_activated_catalog=false.
C96 keeps live_plan_confirm_rollout_allowed=false.
C96 keeps live_plan_confirm_rollout_executed=false.
C96 keeps pilot_runtime_active=false.
C96 keeps shadow_runtime_active=false.
C96 keeps runtime_bridge_active=false.
C96 post-activation audit archive closure seal means continue to C97 audit archive finalization review only.
C96 post-activation audit archive closure seal record is not production deployment.
C96 post-activation audit archive closure seal record is not PLAN/CONFIRM live rollout.
C96 post-activation audit archive closure seal record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC96"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review `
  --c95-artifact=storage/app/watchlist/backtest/c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review.json `
  --expected-c95-hash=a8923e58e35126741226eab29cc07c88a2a721f8 `
  --expected-c95-file-sha1=AEF14CC999F8050DADC8E451E9116C59FD1C2534 `
  --approval-reference=C96_OPERATOR_APPROVED_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_CLOSURE_SEALED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review.json | ConvertFrom-Json
$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.expected_c95_hash
$run.actual_c95_hash
$run.c95_hash_match
$run.expected_c95_file_sha1
$run.actual_c95_file_sha1
$run.c95_file_sha1_match
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
$run.post_activation_audit_archive_closure_seal_context_persisted_to_live_runtime
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
$run.c96_readiness_decision | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review `
  --c95-artifact=storage/app/watchlist/backtest/c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review.json `
  --expected-c95-hash=a8923e58e35126741226eab29cc07c88a2a721f8 `
  --expected-c95-file-sha1=AEF14CC999F8050DADC8E451E9116C59FD1C2534 `
  --approval-reference=C96_OPERATOR_APPROVED_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c96-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review `
  --c95-artifact=storage/app/watchlist/backtest/c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review.json `
  --expected-c95-hash=a8923e58e35126741226eab29cc07c88a2a721f8 `
  --expected-c95-file-sha1=AEF14CC999F8050DADC8E451E9116C59FD1C2534 `
  --output=storage/app/watchlist/backtest/c96-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c96-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item storage/app/watchlist/backtest/c96-no-approval-reference-test.json -ErrorAction SilentlyContinue
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
FOCUSED_PHPUNIT_C96=OK (49 tests, 236 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C96=OK (1697 tests, 24683 assertions)
C96_RUNTIME_STATUS=C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_CLOSURE_SEALED_PRIMARY_AND_BACKUP
C96_RUNTIME_REASON_CODE=C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_CLOSURE_SEALED_PRIMARY_AND_BACKUP
C96_ARTIFACT_HASH=970152d11467ea83c80eca83081d6ae81beec38b
C96_FILE_SHA1=CCD6B92B52745B928C48BF349BC7004E755B1EB6
C95_HASH_MATCH=1
C95_FILE_SHA1_MATCH=1
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
NEXT_RECOMMENDATION=C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW
```
