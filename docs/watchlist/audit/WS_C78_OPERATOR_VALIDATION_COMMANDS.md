# WS_C78_OPERATOR_VALIDATION_COMMANDS

C78 is controlled limited runtime opt-in pilot / shadow rollout observation review.
C78 starts from locked C77 final evidence.
C77 controlled pilot/shadow execution review passed primary + backup.
E02 is primary controlled limited observation review candidate.
B01 is backup controlled limited observation review candidate.
A01 is comparator-only and cannot be promoted.
C78 validates C77 artifact hash and file SHA1.
C78 validates C77 readiness through nested next_readiness_decision.* path.
C78 validates C77 -> C60 lineage.
C78 requires --operator-approved.
C78 requires non-empty --approval-reference.
C78 does not redesign.
C78 does not retune.
C78 does not run parameter search.
C78 does not use OOS to rerank.
C78 does not use parallel-run delta to rerank.
C78 does not use controlled wiring result to rerank.
C78 does not use pilot/shadow preparation result to rerank.
C78 does not use pilot/shadow execution result to rerank.
C78 does not use pilot/shadow observation result to rerank.
C78 does not change candidate scope.
C78 may create controlled limited runtime opt-in pilot observation review proof.
C78 may create controlled limited shadow rollout observation review proof.
C78 may create explicit controlled limited pilot/shadow observation context proof.
C78 may create rollback/emergency disable proof.
C78 may create next-session readiness decision.
C78 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C78 does not deploy live production.
C78 does not mutate PLAN/CONFIRM.
C78 does not change PLAN/CONFIRM output.
C78 keeps production_catalog_runtime_wired=false.
C78 keeps controlled_opt_in_runtime_bridge_active=false.
C78 keeps controlled_parallel_run_active=false.
C78 keeps controlled_rollout_active=false.
C78 keeps controlled_limited_pilot_observation_context_persisted_to_live_runtime=false.
C78 keeps controlled_limited_shadow_observation_context_persisted_to_live_runtime=false.
C78 keeps production_deployment_allowed=false.
C78 keeps production_deployment_executed=false.
C78 keeps plan_confirm_mutation_allowed=false.
C78 keeps plan_confirm_mutated=false.
C78 keeps plan_confirm_runtime_reads_activated_catalog=false.
C78 keeps live_plan_confirm_rollout_allowed=false.
C78 keeps live_plan_confirm_rollout_executed=false.
C78 carries bad-month risk as documented risk.
C78 carries weak-regime risk as documented risk.
C78 carries source-bias/shared-core risk as documented risk.
C65 cleanup note remains non-blocking.
C78 may only recommend C79 controlled limited runtime opt-in pilot / shadow rollout observation result review if all observation review gates pass.
C78 pass is not full production deployment.
C78 pass is not PLAN/CONFIRM live rollout.
C78 pass is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC78"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review `
  --c77-artifact=storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json `
  --expected-c77-hash=d827547d6d40a73785d4c2409b2913f60db42115 `
  --expected-c77-file-sha1=8C296276DD4D278206366953F975AFD5F7E328DE `
  --approval-reference=C78_OPERATOR_APPROVED_OBSERVATION_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c77_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.controlled_pilot_shadow_execution_decision | Format-List
$run.controlled_limited_pilot_shadow_observation_decision | Format-List
$run.controlled_limited_pilot_observation_context_summary | Format-List
$run.controlled_limited_shadow_observation_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.feature_flag_operator_approval_kill_switch_validation_summary | Format-List
$run.rollback_and_emergency_disable_review_summary | Format-List
$run.c77_proof_carry_forward_validation_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review `
  --c77-artifact=storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json `
  --expected-c77-hash=d827547d6d40a73785d4c2409b2913f60db42115 `
  --expected-c77-file-sha1=8C296276DD4D278206366953F975AFD5F7E328DE `
  --approval-reference=C78_OPERATOR_APPROVED_OBSERVATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c78-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review `
  --c77-artifact=storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json `
  --expected-c77-hash=d827547d6d40a73785d4c2409b2913f60db42115 `
  --expected-c77-file-sha1=8C296276DD4D278206366953F975AFD5F7E328DE `
  --output=storage/app/watchlist/backtest/c78-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c78-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c78-no-approval-reference-test.json
```

## Final Operator Result Evidence — 2026-06-27

Operator validation results recorded for C78:

```text
FOCUSED_PHPUNIT=OK (13 tests, 151 assertions)
FULL_WATCHLIST_PHPUNIT=OK (1316 tests, 21720 assertions)
RUNTIME_STATUS=C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP
RUNTIME_REASON_CODE=C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_PASSED_PRIMARY_AND_BACKUP
ARTIFACT_HASH=989826f1620bea4592e3543d4908670192fab7f0
ARTIFACT_FILE_SHA1=6C6EE121EB7B5F86E19532D24115139F5915CBF3
EXPECTED_C77_HASH=d827547d6d40a73785d4c2409b2913f60db42115
ACTUAL_C77_HASH=d827547d6d40a73785d4c2409b2913f60db42115
C77_HASH_MATCH=1
EXPECTED_C77_FILE_SHA1=8C296276DD4D278206366953F975AFD5F7E328DE
ACTUAL_C77_FILE_SHA1=8C296276DD4D278206366953F975AFD5F7E328DE
C77_FILE_SHA1_MATCH=1
NEGATIVE_WITHOUT_OPERATOR_APPROVED=C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=PASS_NO_NO_TEST_JSON_REMAINING
NEXT_RECOMMENDATION=C79_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_RESULT_REVIEW
```

The negative approval artifacts were temporary operator-validation artifacts only. They were removed after inspection. The final C78 artifact remains `storage/app/watchlist/backtest/c78-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-review.json`.
