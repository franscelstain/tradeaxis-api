# WS_C92_OPERATOR_VALIDATION_COMMANDS

C92 is controlled limited runtime opt-in pilot / shadow rollout post-activation handoff completion boundary review.
C92 starts from locked C91 final evidence.
C91 finalized the post-activation handoff package for primary + backup.
E02 is primary post-activation handoff completion boundary cleared candidate.
B01 is backup post-activation handoff completion boundary cleared candidate.
A01 is comparator-only and cannot be promoted.
C92 validates C91 artifact hash and file SHA1.
C92 validates C91 readiness through nested next_readiness_decision.* path.
C92 validates C91 -> C60 lineage.
C92 requires --operator-approved.
C92 requires non-empty --approval-reference.
C92 clears post-activation handoff completion boundary only.
C92 does not redesign.
C92 does not retune.
C92 does not run parameter search.
C92 does not use OOS to rerank.
C92 does not use handoff completion boundary evidence to rerank.
C92 does not use handoff completion boundary evidence to deploy.
C92 does not change candidate scope.
C92 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C92 does not deploy live production.
C92 does not mutate PLAN/CONFIRM.
C92 does not change PLAN/CONFIRM output.
C92 keeps production_ready=false.
C92 keeps production_catalog_runtime_wired=false.
C92 keeps controlled_opt_in_runtime_bridge_active=false.
C92 keeps controlled_parallel_run_active=false.
C92 keeps controlled_rollout_active=false.
C92 keeps post_activation_handoff_completion_boundary_context_persisted_to_live_runtime=false.
C92 keeps production_deployment_allowed=false.
C92 keeps production_deployment_executed=false.
C92 keeps plan_confirm_mutation_allowed=false.
C92 keeps plan_confirm_mutated=false.
C92 keeps plan_confirm_runtime_reads_activated_catalog=false.
C92 keeps live_plan_confirm_rollout_allowed=false.
C92 keeps live_plan_confirm_rollout_executed=false.
C92 post-activation handoff completion boundary means continue to C93 post-activation handoff closure seal review only.
C92 post-activation handoff completion boundary record is not production deployment.
C92 post-activation handoff completion boundary record is not PLAN/CONFIRM live rollout.
C92 post-activation handoff completion boundary record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC92"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review `
  --c91-artifact=storage/app/watchlist/backtest/c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review.json `
  --expected-c91-hash=17731873369cf69b5083b2f80b15101de71851f2 `
  --expected-c91-file-sha1=D6306D0EB132FEEA5535B99AD4A4BA9099D80DF6 `
  --approval-reference=C92_OPERATOR_APPROVED_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.artifact_hash
$run.expected_c91_hash
$run.actual_c91_hash
$run.c91_hash_match
$run.expected_c91_file_sha1
$run.actual_c91_file_sha1
$run.c91_file_sha1_match
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
$run.post_activation_handoff_completion_boundary_context_persisted_to_live_runtime
$run.production_deployment_allowed
$run.production_deployment_executed
$run.plan_confirm_mutation_allowed
$run.plan_confirm_mutated
$run.plan_confirm_runtime_reads_activated_catalog
$run.live_plan_confirm_rollout_allowed
$run.live_plan_confirm_rollout_executed
$run.c92_readiness_decision | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review `
  --c91-artifact=storage/app/watchlist/backtest/c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review.json `
  --expected-c91-hash=17731873369cf69b5083b2f80b15101de71851f2 `
  --expected-c91-file-sha1=D6306D0EB132FEEA5535B99AD4A4BA9099D80DF6 `
  --approval-reference=C92_OPERATOR_APPROVED_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c92-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review `
  --c91-artifact=storage/app/watchlist/backtest/c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review.json `
  --expected-c91-hash=17731873369cf69b5083b2f80b15101de71851f2 `
  --expected-c91-file-sha1=D6306D0EB132FEEA5535B99AD4A4BA9099D80DF6 `
  --output=storage/app/watchlist/backtest/c92-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c92-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item storage/app/watchlist/backtest/c92-no-approval-reference-test.json -ErrorAction SilentlyContinue
Get-ChildItem storage/app/watchlist/backtest -Filter "*no-*-test.json"
```

Expected cleanup result:

```text
No output
```

## Manual Local Evidence To Return

```text
FOCUSED_PHPUNIT_C92=OK (35 tests, 175 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C92=OK (1507 tests, 23740 assertions)
C92_RUNTIME_STATUS=C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
C92_RUNTIME_REASON_CODE=C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
C92_ARTIFACT_HASH=21ea44188d303fb3208d1d1bff864ee86aa247e5
C92_FILE_SHA1=81B5F1502258E1419BAA7E302BCB6CBABE49A822
C91_HASH_MATCH=1
C91_FILE_SHA1_MATCH=1
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
```

## Final Operator Evidence Recorded — 2026-06-27

Final operator validation has been returned and recorded for C92. This record is documentation-only and does not modify C60-C91 artifacts, C92 runtime logic, production configuration, runtime bridge state, controlled rollout state, or PLAN/CONFIRM behavior.

```text
FOCUSED_PHPUNIT_C92=OK (35 tests, 175 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C92=OK (1507 tests, 23740 assertions)
C92_RUNTIME_STATUS=C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
C92_RUNTIME_REASON_CODE=C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
C92_ARTIFACT_HASH=21ea44188d303fb3208d1d1bff864ee86aa247e5
C92_FILE_SHA1=81B5F1502258E1419BAA7E302BCB6CBABE49A822
C91_HASH_MATCH=1
C91_FILE_SHA1_MATCH=1
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
NEXT_RECOMMENDATION=C93_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_CLOSURE_SEAL_REVIEW
```

C92 manual validation is complete. C92 remains non-live, non-mutating, non-production, and PLAN/CONFIRM unchanged.
