# WS_C80_OPERATOR_VALIDATION_COMMANDS

C80 is controlled limited runtime opt-in pilot / shadow rollout operator go/no-go review.
C80 starts from locked C79 final evidence.
C79 controlled limited pilot/shadow observation result review passed primary + backup.
E02 is primary operator GO candidate.
B01 is backup operator GO candidate.
A01 is comparator-only and cannot be promoted.
C80 validates C79 artifact hash and file SHA1.
C80 validates C79 readiness through nested next_readiness_decision.* path.
C80 validates C79 -> C60 lineage.
C80 requires --operator-approved.
C80 requires non-empty --approval-reference.
C80 records operator GO/NO-GO only.
C80 does not redesign.
C80 does not retune.
C80 does not run parameter search.
C80 does not use OOS to rerank.
C80 does not use operator GO to rerank.
C80 does not use operator GO to deploy.
C80 does not change candidate scope.
C80 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C80 does not deploy live production.
C80 does not mutate PLAN/CONFIRM.
C80 does not change PLAN/CONFIRM output.
C80 keeps production_catalog_runtime_wired=false.
C80 keeps controlled_opt_in_runtime_bridge_active=false.
C80 keeps controlled_parallel_run_active=false.
C80 keeps controlled_rollout_active=false.
C80 keeps operator_go_no_go_context_persisted_to_live_runtime=false.
C80 keeps production_deployment_allowed=false.
C80 keeps production_deployment_executed=false.
C80 keeps plan_confirm_mutation_allowed=false.
C80 keeps plan_confirm_mutated=false.
C80 keeps plan_confirm_runtime_reads_activated_catalog=false.
C80 keeps live_plan_confirm_rollout_allowed=false.
C80 keeps live_plan_confirm_rollout_executed=false.
C80 GO means continue to C81 finalization review only.
C80 GO is not production deployment.
C80 GO is not PLAN/CONFIRM live rollout.
C80 GO is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC80"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review `
  --c79-artifact=storage/app/watchlist/backtest/c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review.json `
  --expected-c79-hash=0ad7924e75a4627475600567fc6f6ad839a83961 `
  --expected-c79-file-sha1=94A900AFD592C2756E2D8165B043F25191F1ACAF `
  --approval-reference=C80_OPERATOR_APPROVED_GO_NO_GO_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c79_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.operator_go_no_go_decision | Format-List
$run.operator_go_no_go_candidate_scorecard | Format-List
$run.operator_go_no_go_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review `
  --c79-artifact=storage/app/watchlist/backtest/c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review.json `
  --expected-c79-hash=0ad7924e75a4627475600567fc6f6ad839a83961 `
  --expected-c79-file-sha1=94A900AFD592C2756E2D8165B043F25191F1ACAF `
  --approval-reference=C80_OPERATOR_APPROVED_GO_NO_GO_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c80-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review `
  --c79-artifact=storage/app/watchlist/backtest/c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review.json `
  --expected-c79-hash=0ad7924e75a4627475600567fc6f6ad839a83961 `
  --expected-c79-file-sha1=94A900AFD592C2756E2D8165B043F25191F1ACAF `
  --output=storage/app/watchlist/backtest/c80-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c80-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c80-no-approval-reference-test.json
```

## Final Operator Result Evidence — 2026-06-27

Operator validation results recorded for C80:

```text
FOCUSED_PHPUNIT=OK (12 tests, 139 assertions)
FULL_WATCHLIST_PHPUNIT=OK (1340 tests, 22004 assertions)
RUNTIME_STATUS=C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
RUNTIME_REASON_CODE=C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
ARTIFACT_HASH=76270e9ebce21b101629de62aa48262d1d1a6492
ARTIFACT_FILE_SHA1=BD51FF78572E886E38D72BC2AA2FFA23A9D2C619
EXPECTED_C79_HASH=0ad7924e75a4627475600567fc6f6ad839a83961
ACTUAL_C79_HASH=0ad7924e75a4627475600567fc6f6ad839a83961
C79_HASH_MATCH=1
EXPECTED_C79_FILE_SHA1=94A900AFD592C2756E2D8165B043F25191F1ACAF
ACTUAL_C79_FILE_SHA1=94A900AFD592C2756E2D8165B043F25191F1ACAF
C79_FILE_SHA1_MATCH=1
NEGATIVE_WITHOUT_OPERATOR_APPROVED=C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=PASS_NO_NO_TEST_JSON_REMAINING
NEXT_RECOMMENDATION=C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW
```

The negative approval artifacts were temporary operator-validation artifacts only. They were removed after inspection. The final C80 artifact remains `storage/app/watchlist/backtest/c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review.json`.
