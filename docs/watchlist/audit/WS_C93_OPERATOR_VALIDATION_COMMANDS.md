# WS_C93_OPERATOR_VALIDATION_COMMANDS

C93 is controlled limited runtime opt-in pilot / shadow rollout post-activation handoff closure seal review.
C93 starts from locked C92 completion boundary evidence.
C92 cleared the post-activation handoff completion boundary for primary + backup.
E02 is primary post-activation handoff closure sealed candidate.
B01 is backup post-activation handoff closure sealed candidate.
A01 is comparator-only and cannot be promoted.
C93 validates C92 artifact hash and file SHA1.
C93 validates C92 completion boundary state.
C93 validates C92 next recommendation to C93.
C93 requires --operator-approved.
C93 requires non-empty --approval-reference.
C93 confirms no temporary negative test artifact remains.
C93 seals post-activation handoff closure only.
C93 does not redesign.
C93 does not retune.
C93 does not run parameter search.
C93 does not run OOS rerank.
C93 does not use closure seal evidence to rerank.
C93 does not use closure seal evidence to deploy.
C93 does not change candidate scope.
C93 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C93 does not deploy live production.
C93 does not mutate PLAN/CONFIRM.
C93 does not change PLAN/CONFIRM output.
C93 keeps production_ready=false.
C93 keeps production_catalog_runtime_wired=false.
C93 keeps controlled_opt_in_runtime_bridge_active=false.
C93 keeps controlled_parallel_run_active=false.
C93 keeps controlled_rollout_active=false.
C93 keeps post_activation_handoff_closure_seal_context_persisted_to_live_runtime=false.
C93 keeps production_deployment_allowed=false.
C93 keeps production_deployment_executed=false.
C93 keeps plan_confirm_mutation_allowed=false.
C93 keeps plan_confirm_mutated=false.
C93 keeps plan_confirm_runtime_reads_activated_catalog=false.
C93 keeps live_plan_confirm_rollout_allowed=false.
C93 keeps live_plan_confirm_rollout_executed=false.
C93 keeps pilot_runtime_active=false.
C93 keeps shadow_runtime_active=false.
C93 keeps runtime_bridge_active=false.
C93 post-activation handoff closure seal means continue to C94 post-activation audit archive review only.
C93 post-activation handoff closure seal record is not production deployment.
C93 post-activation handoff closure seal record is not PLAN/CONFIRM live rollout.
C93 post-activation handoff closure seal record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC93"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review `
  --c92-artifact=storage/app/watchlist/backtest/c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review.json `
  --expected-c92-hash=21ea44188d303fb3208d1d1bff864ee86aa247e5 `
  --expected-c92-file-sha1=81B5F1502258E1419BAA7E302BCB6CBABE49A822 `
  --approval-reference=C93_OPERATOR_APPROVED_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_PASSED_CLOSURE_SEALED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review.json | ConvertFrom-Json
$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.expected_c92_hash
$run.actual_c92_hash
$run.c92_hash_match
$run.expected_c92_file_sha1
$run.actual_c92_file_sha1
$run.c92_file_sha1_match
$run.temporary_negative_artifacts_remaining
$run.temporary_negative_artifact_cleanup_confirmed
$run.temporary_negative_artifact_paths
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
$run.post_activation_handoff_closure_seal_context_persisted_to_live_runtime
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
$run.c93_readiness_decision | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review `
  --c92-artifact=storage/app/watchlist/backtest/c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review.json `
  --expected-c92-hash=21ea44188d303fb3208d1d1bff864ee86aa247e5 `
  --expected-c92-file-sha1=81B5F1502258E1419BAA7E302BCB6CBABE49A822 `
  --approval-reference=C93_OPERATOR_APPROVED_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c93-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review `
  --c92-artifact=storage/app/watchlist/backtest/c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review.json `
  --expected-c92-hash=21ea44188d303fb3208d1d1bff864ee86aa247e5 `
  --expected-c92-file-sha1=81B5F1502258E1419BAA7E302BCB6CBABE49A822 `
  --output=storage/app/watchlist/backtest/c93-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c93-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item storage/app/watchlist/backtest/c93-no-approval-reference-test.json -ErrorAction SilentlyContinue
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
FOCUSED_PHPUNIT_C93=OK (48 tests, 255 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C93=OK (1555 tests, 23995 assertions)
C93_RUNTIME_STATUS=C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_PASSED_CLOSURE_SEALED_PRIMARY_AND_BACKUP
C93_RUNTIME_REASON_CODE=C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW_PASSED_CLOSURE_SEALED_PRIMARY_AND_BACKUP
C93_ARTIFACT_HASH=bd19ac672c30ea183fc46534acd6e976515c3453
C93_FILE_SHA1=F71799E201B9C71A79094D81AFF786FCACDF9E1D
C92_HASH_MATCH=1
C92_FILE_SHA1_MATCH=1
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
NEXT_RECOMMENDATION=C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW
```

## Final Implementation Evidence Recorded - 2026-06-27

Final C93 implementation validation has been recorded. This record is documentation-only and does not modify C60-C92 artifacts, C93 runtime logic, production configuration, runtime bridge state, controlled rollout state, or PLAN/CONFIRM behavior.
