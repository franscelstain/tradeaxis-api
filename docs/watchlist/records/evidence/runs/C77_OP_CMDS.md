# WS_C77_OPERATOR_VALIDATION_COMMANDS

C77 is controlled runtime opt-in pilot / shadow rollout execution review.
C77 starts from locked C76 final evidence.
C76 controlled pilot/shadow preparation review passed primary + backup.
E02 is primary controlled pilot/shadow execution review candidate.
B01 is backup controlled pilot/shadow execution review candidate.
A01 is comparator-only and cannot be promoted.
C77 validates C76 artifact hash and file SHA1.
C77 validates C76 readiness through nested next_readiness_decision.* path.
C77 validates C76 -> C60 lineage.
C77 requires --operator-approved.
C77 requires non-empty --approval-reference.
C77 does not redesign.
C77 does not retune.
C77 does not run parameter search.
C77 does not use OOS to rerank.
C77 does not use parallel-run delta to rerank.
C77 does not use controlled wiring result to rerank.
C77 does not use pilot/shadow preparation result to rerank.
C77 does not use pilot/shadow execution result to rerank.
C77 does not change candidate scope.
C77 may create controlled runtime opt-in pilot execution review proof.
C77 may create controlled shadow rollout execution review proof.
C77 may create explicit controlled pilot/shadow execution context proof.
C77 may create rollback/emergency disable proof.
C77 may create next-session readiness decision.
C77 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C77 does not deploy live production.
C77 does not mutate PLAN/CONFIRM.
C77 does not change PLAN/CONFIRM output.
C77 keeps production_catalog_runtime_wired=false.
C77 keeps controlled_opt_in_runtime_bridge_active=false.
C77 keeps controlled_parallel_run_active=false.
C77 keeps controlled_rollout_active=false.
C77 keeps controlled_pilot_execution_context_persisted_to_live_runtime=false.
C77 keeps controlled_shadow_execution_context_persisted_to_live_runtime=false.
C77 keeps production_deployment_allowed=false.
C77 keeps production_deployment_executed=false.
C77 keeps plan_confirm_mutation_allowed=false.
C77 keeps plan_confirm_mutated=false.
C77 keeps plan_confirm_runtime_reads_activated_catalog=false.
C77 keeps live_plan_confirm_rollout_allowed=false.
C77 keeps live_plan_confirm_rollout_executed=false.
C77 carries bad-month risk as documented risk.
C77 carries weak-regime risk as documented risk.
C77 carries source-bias/shared-core risk as documented risk.
C65 cleanup note remains non-blocking.
C77 may only recommend C78 controlled limited runtime opt-in pilot / shadow rollout observation review if all execution review gates pass.
C77 pass is not full production deployment.
C77 pass is not PLAN/CONFIRM live rollout.
C77 pass is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC77"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review `
  --c76-artifact=storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json `
  --expected-c76-hash=40f1bc516ddbb127ab6f62433059cb99ff2ae2de `
  --expected-c76-file-sha1=115929AD40A739E9BE1D5A1A58DAA4FECB394ACD `
  --approval-reference=C77_OPERATOR_APPROVED_EXECUTION_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c76_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.controlled_pilot_shadow_execution_decision | Format-List
$run.controlled_pilot_execution_context_summary | Format-List
$run.controlled_shadow_execution_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.feature_flag_operator_approval_kill_switch_validation_summary | Format-List
$run.rollback_and_emergency_disable_review_summary | Format-List
$run.c76_proof_carry_forward_validation_summary | Format-List
$run.controlled_pilot_shadow_execution_governance_summary | Format-List
$run.fallback_behavior_controlled_pilot_shadow_execution_validation_summary | Format-List
$run.baseline_plan_confirm_non_mutation_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review `
  --c76-artifact=storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json `
  --expected-c76-hash=40f1bc516ddbb127ab6f62433059cb99ff2ae2de `
  --expected-c76-file-sha1=115929AD40A739E9BE1D5A1A58DAA4FECB394ACD `
  --approval-reference=C77_OPERATOR_APPROVED_EXECUTION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c77-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review `
  --c76-artifact=storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json `
  --expected-c76-hash=40f1bc516ddbb127ab6f62433059cb99ff2ae2de `
  --expected-c76-file-sha1=115929AD40A739E9BE1D5A1A58DAA4FECB394ACD `
  --output=storage/app/watchlist/backtest/c77-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c77-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c77-no-approval-reference-test.json
```

## Final Operator Result Evidence — 2026-06-27

Operator validation results recorded for C77:

```text
FOCUSED_PHPUNIT=OK (20 tests, 233 assertions)
FULL_WATCHLIST_PHPUNIT=OK (1303 tests, 21569 assertions)
RUNTIME_STATUS=C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
RUNTIME_REASON_CODE=C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
ARTIFACT_HASH=d827547d6d40a73785d4c2409b2913f60db42115
ARTIFACT_FILE_SHA1=8C296276DD4D278206366953F975AFD5F7E328DE
EXPECTED_C76_HASH=40f1bc516ddbb127ab6f62433059cb99ff2ae2de
ACTUAL_C76_HASH=40f1bc516ddbb127ab6f62433059cb99ff2ae2de
C76_HASH_MATCH=1
EXPECTED_C76_FILE_SHA1=115929AD40A739E9BE1D5A1A58DAA4FECB394ACD
ACTUAL_C76_FILE_SHA1=115929AD40A739E9BE1D5A1A58DAA4FECB394ACD
C76_FILE_SHA1_MATCH=1
NEGATIVE_WITHOUT_OPERATOR_APPROVED=C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C77_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=PASS_NO_NO_TEST_JSON_REMAINING
NEXT_RECOMMENDATION=C78_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OBSERVATION_REVIEW
```

The negative approval artifacts were temporary operator-validation artifacts only. They were removed after inspection. The final C77 artifact remains `storage/app/watchlist/backtest/c77-controlled-runtime-opt-in-pilot-or-shadow-rollout-execution-review.json`.
