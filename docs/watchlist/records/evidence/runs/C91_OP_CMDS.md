# WS_C91_OPERATOR_VALIDATION_COMMANDS

C91 is controlled limited runtime opt-in pilot / shadow rollout post-activation handoff finalization review.
C91 starts from locked C90 final evidence.
C90 marked the post-activation handoff package ready for primary + backup.
E02 is primary post-activation handoff finalized candidate.
B01 is backup post-activation handoff finalized candidate.
A01 is comparator-only and cannot be promoted.
C91 validates C90 artifact hash and file SHA1.
C91 validates C90 readiness through nested next_readiness_decision.* path.
C91 validates C90 -> C60 lineage.
C91 requires --operator-approved.
C91 requires non-empty --approval-reference.
C91 finalizes post-activation handoff package only.
C91 does not redesign.
C91 does not retune.
C91 does not run parameter search.
C91 does not use OOS to rerank.
C91 does not use handoff finalization evidence to rerank.
C91 does not use handoff finalization evidence to deploy.
C91 does not change candidate scope.
C91 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C91 does not deploy live production.
C91 does not mutate PLAN/CONFIRM.
C91 does not change PLAN/CONFIRM output.
C91 keeps production_catalog_runtime_wired=false.
C91 keeps controlled_opt_in_runtime_bridge_active=false.
C91 keeps controlled_parallel_run_active=false.
C91 keeps controlled_rollout_active=false.
C91 keeps post_activation_handoff_finalization_context_persisted_to_live_runtime=false.
C91 keeps production_deployment_allowed=false.
C91 keeps production_deployment_executed=false.
C91 keeps plan_confirm_mutation_allowed=false.
C91 keeps plan_confirm_mutated=false.
C91 keeps plan_confirm_runtime_reads_activated_catalog=false.
C91 keeps live_plan_confirm_rollout_allowed=false.
C91 keeps live_plan_confirm_rollout_executed=false.
C91 post-activation handoff finalization means continue to C92 post-activation handoff completion boundary review only.
C91 post-activation handoff finalization record is not production deployment.
C91 post-activation handoff finalization record is not PLAN/CONFIRM live rollout.
C91 post-activation handoff finalization record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC91"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review `
  --c90-artifact=storage/app/watchlist/backtest/c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review.json `
  --expected-c90-hash=a5e4bf444348c4d2e639ff1532ad2ac4b814d4af `
  --expected-c90-file-sha1=30E924E65D9BE18BA9C55E37869424879C3EB41F `
  --approval-reference=C91_OPERATOR_APPROVED_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c90_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.post_activation_handoff_finalization_decision | Format-List
$run.post_activation_handoff_finalization_candidate_scorecard | Format-List
$run.post_activation_handoff_finalization_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review `
  --c90-artifact=storage/app/watchlist/backtest/c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review.json `
  --expected-c90-hash=a5e4bf444348c4d2e639ff1532ad2ac4b814d4af `
  --expected-c90-file-sha1=30E924E65D9BE18BA9C55E37869424879C3EB41F `
  --approval-reference=C91_OPERATOR_APPROVED_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c91-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review `
  --c90-artifact=storage/app/watchlist/backtest/c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review.json `
  --expected-c90-hash=a5e4bf444348c4d2e639ff1532ad2ac4b814d4af `
  --expected-c90-file-sha1=30E924E65D9BE18BA9C55E37869424879C3EB41F `
  --output=storage/app/watchlist/backtest/c91-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c91-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c91-no-approval-reference-test.json
```

## Final Operator Result Evidence — 2026-06-27

Operator validation results recorded for C91:

```text
FOCUSED_PHPUNIT=OK (12 tests, 140 assertions)
FULL_WATCHLIST_PHPUNIT=OK (1472 tests, 23565 assertions)
RUNTIME_STATUS=C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP
RUNTIME_REASON_CODE=C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP
ARTIFACT_HASH=17731873369cf69b5083b2f80b15101de71851f2
ARTIFACT_FILE_SHA1=D6306D0EB132FEEA5535B99AD4A4BA9099D80DF6
EXPECTED_C90_HASH=a5e4bf444348c4d2e639ff1532ad2ac4b814d4af
ACTUAL_C90_HASH=a5e4bf444348c4d2e639ff1532ad2ac4b814d4af
C90_HASH_MATCH=1
EXPECTED_C90_FILE_SHA1=30E924E65D9BE18BA9C55E37869424879C3EB41F
ACTUAL_C90_FILE_SHA1=30E924E65D9BE18BA9C55E37869424879C3EB41F
C90_FILE_SHA1_MATCH=1
NEGATIVE_WITHOUT_OPERATOR_APPROVED=C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=PASS_NO_NO_TEST_JSON_REMAINING
NEXT_RECOMMENDATION=C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW
```

The negative approval artifacts were temporary operator-validation artifacts only. They were removed after inspection. The final C91 artifact remains `storage/app/watchlist/backtest/c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review.json`.
