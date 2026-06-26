# WS_C79_OPERATOR_VALIDATION_COMMANDS

C79 is controlled limited runtime opt-in pilot / shadow rollout observation result review.
C79 starts from locked C78 final evidence.
C78 controlled limited pilot/shadow observation review passed primary + backup.
E02 is primary controlled limited observation result review candidate.
B01 is backup controlled limited observation result review candidate.
A01 is comparator-only and cannot be promoted.
C79 validates C78 artifact hash and file SHA1.
C79 validates C78 readiness through nested next_readiness_decision.* path.
C79 validates C78 -> C60 lineage.
C79 requires --operator-approved.
C79 requires non-empty --approval-reference.
C79 does not redesign.
C79 does not retune.
C79 does not run parameter search.
C79 does not use OOS to rerank.
C79 does not use parallel-run delta to rerank.
C79 does not use controlled wiring result to rerank.
C79 does not use pilot/shadow observation result to rerank.
C79 does not change candidate scope.
C79 may create controlled limited runtime opt-in pilot observation result review proof.
C79 may create controlled limited shadow rollout observation result review proof.
C79 may create explicit controlled limited pilot/shadow observation result context proof.
C79 may create progress summary.
C79 may create planned next summary.
C79 may create next-session readiness decision.
C79 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C79 does not deploy live production.
C79 does not mutate PLAN/CONFIRM.
C79 does not change PLAN/CONFIRM output.
C79 keeps production_catalog_runtime_wired=false.
C79 keeps controlled_opt_in_runtime_bridge_active=false.
C79 keeps controlled_parallel_run_active=false.
C79 keeps controlled_rollout_active=false.
C79 keeps controlled_limited_pilot_observation_result_context_persisted_to_live_runtime=false.
C79 keeps controlled_limited_shadow_observation_result_context_persisted_to_live_runtime=false.
C79 keeps production_deployment_allowed=false.
C79 keeps production_deployment_executed=false.
C79 keeps plan_confirm_mutation_allowed=false.
C79 keeps plan_confirm_mutated=false.
C79 keeps plan_confirm_runtime_reads_activated_catalog=false.
C79 keeps live_plan_confirm_rollout_allowed=false.
C79 keeps live_plan_confirm_rollout_executed=false.
C79 may only recommend C80 controlled limited runtime opt-in pilot / shadow rollout operator go/no-go review if all observation result review gates pass.
C79 pass is not full production deployment.
C79 pass is not PLAN/CONFIRM live rollout.
C79 pass is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC79"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review `
  --c78-artifact=storage/app/watchlist/backtest/c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review.json `
  --expected-c78-hash=989826f1620bea4592e3543d4908670192fab7f0 `
  --expected-c78-file-sha1=6C6EE121EB7B5F86E19532D24115139F5915CBF3 `
  --approval-reference=C79_OPERATOR_APPROVED_OBSERVATION_RESULT_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW_PASSED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c78_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.controlled_limited_pilot_shadow_observation_result_decision | Format-List
$run.controlled_limited_pilot_observation_result_context_summary | Format-List
$run.controlled_limited_shadow_observation_result_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.feature_flag_operator_approval_kill_switch_validation_summary | Format-List
$run.rollback_and_emergency_disable_review_summary | Format-List
$run.c78_proof_carry_forward_validation_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review `
  --c78-artifact=storage/app/watchlist/backtest/c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review.json `
  --expected-c78-hash=989826f1620bea4592e3543d4908670192fab7f0 `
  --expected-c78-file-sha1=6C6EE121EB7B5F86E19532D24115139F5915CBF3 `
  --approval-reference=C79_OPERATOR_APPROVED_OBSERVATION_RESULT_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c79-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review `
  --c78-artifact=storage/app/watchlist/backtest/c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review.json `
  --expected-c78-hash=989826f1620bea4592e3543d4908670192fab7f0 `
  --expected-c78-file-sha1=6C6EE121EB7B5F86E19532D24115139F5915CBF3 `
  --output=storage/app/watchlist/backtest/c79-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c79-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c79-no-approval-reference-test.json
```

## Final Operator Result Evidence — 2026-06-27

Operator validation results recorded for C79:

```text
FOCUSED_PHPUNIT=OK (12 tests, 145 assertions)
FULL_WATCHLIST_PHPUNIT=OK (1328 tests, 21865 assertions)
RUNTIME_STATUS=C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW_PASSED_PRIMARY_AND_BACKUP
RUNTIME_REASON_CODE=C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW_PASSED_PRIMARY_AND_BACKUP
ARTIFACT_HASH=0ad7924e75a4627475600567fc6f6ad839a83961
ARTIFACT_FILE_SHA1=94A900AFD592C2756E2D8165B043F25191F1ACAF
EXPECTED_C78_HASH=989826f1620bea4592e3543d4908670192fab7f0
ACTUAL_C78_HASH=989826f1620bea4592e3543d4908670192fab7f0
C78_HASH_MATCH=1
EXPECTED_C78_FILE_SHA1=6C6EE121EB7B5F86E19532D24115139F5915CBF3
ACTUAL_C78_FILE_SHA1=6C6EE121EB7B5F86E19532D24115139F5915CBF3
C78_FILE_SHA1_MATCH=1
NEGATIVE_WITHOUT_OPERATOR_APPROVED=C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=PASS_NO_NO_TEST_JSON_REMAINING
NEXT_RECOMMENDATION=C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW
```

The negative approval artifacts were temporary operator-validation artifacts only. They were removed after inspection. The final C79 artifact remains `storage/app/watchlist/backtest/c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review.json`.
