# WS_C81_OPERATOR_VALIDATION_COMMANDS

C81 is controlled limited runtime opt-in pilot / shadow rollout GO decision finalization review.
C81 starts from locked C80 final evidence.
C80 operator go/no-go review passed GO for primary + backup.
E02 is primary finalized GO candidate.
B01 is backup finalized GO candidate.
A01 is comparator-only and cannot be promoted.
C81 validates C80 artifact hash and file SHA1.
C81 validates C80 readiness through nested next_readiness_decision.* path.
C81 validates C80 -> C60 lineage.
C81 requires --operator-approved.
C81 requires non-empty --approval-reference.
C81 finalizes GO decision only.
C81 does not redesign.
C81 does not retune.
C81 does not run parameter search.
C81 does not use OOS to rerank.
C81 does not use finalized GO to rerank.
C81 does not use finalized GO to deploy.
C81 does not change candidate scope.
C81 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C81 does not deploy live production.
C81 does not mutate PLAN/CONFIRM.
C81 does not change PLAN/CONFIRM output.
C81 keeps production_catalog_runtime_wired=false.
C81 keeps controlled_opt_in_runtime_bridge_active=false.
C81 keeps controlled_parallel_run_active=false.
C81 keeps controlled_rollout_active=false.
C81 keeps go_decision_finalization_context_persisted_to_live_runtime=false.
C81 keeps production_deployment_allowed=false.
C81 keeps production_deployment_executed=false.
C81 keeps plan_confirm_mutation_allowed=false.
C81 keeps plan_confirm_mutated=false.
C81 keeps plan_confirm_runtime_reads_activated_catalog=false.
C81 keeps live_plan_confirm_rollout_allowed=false.
C81 keeps live_plan_confirm_rollout_executed=false.
C81 finalized GO means continue to C82 pre-activation boundary review only.
C81 finalized GO is not production deployment.
C81 finalized GO is not PLAN/CONFIRM live rollout.
C81 finalized GO is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC81"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review `
  --c80-artifact=storage/app/watchlist/backtest/c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review.json `
  --expected-c80-hash=76270e9ebce21b101629de62aa48262d1d1a6492 `
  --expected-c80-file-sha1=BD51FF78572E886E38D72BC2AA2FFA23A9D2C619 `
  --approval-reference=C81_OPERATOR_APPROVED_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c80_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.go_decision_finalization_decision | Format-List
$run.go_decision_finalization_candidate_scorecard | Format-List
$run.go_decision_finalization_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review `
  --c80-artifact=storage/app/watchlist/backtest/c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review.json `
  --expected-c80-hash=76270e9ebce21b101629de62aa48262d1d1a6492 `
  --expected-c80-file-sha1=BD51FF78572E886E38D72BC2AA2FFA23A9D2C619 `
  --approval-reference=C81_OPERATOR_APPROVED_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c81-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review `
  --c80-artifact=storage/app/watchlist/backtest/c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review.json `
  --expected-c80-hash=76270e9ebce21b101629de62aa48262d1d1a6492 `
  --expected-c80-file-sha1=BD51FF78572E886E38D72BC2AA2FFA23A9D2C619 `
  --output=storage/app/watchlist/backtest/c81-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c81-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c81-no-approval-reference-test.json
```

## Final Operator Result Evidence — 2026-06-27

Operator validation results recorded for C81:

```text
FOCUSED_PHPUNIT=OK (12 tests, 141 assertions)
FULL_WATCHLIST_PHPUNIT=OK (1352 tests, 22145 assertions)
RUNTIME_STATUS=C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
RUNTIME_REASON_CODE=C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
ARTIFACT_HASH=45e1abfb6ba0ddc6ddf2b0494527cf8706172f18
ARTIFACT_FILE_SHA1=588753D1F62EBCDB318A5969ACE4165CD83D98BD
EXPECTED_C80_HASH=76270e9ebce21b101629de62aa48262d1d1a6492
ACTUAL_C80_HASH=76270e9ebce21b101629de62aa48262d1d1a6492
C80_HASH_MATCH=1
EXPECTED_C80_FILE_SHA1=BD51FF78572E886E38D72BC2AA2FFA23A9D2C619
ACTUAL_C80_FILE_SHA1=BD51FF78572E886E38D72BC2AA2FFA23A9D2C619
C80_FILE_SHA1_MATCH=1
NEGATIVE_WITHOUT_OPERATOR_APPROVED=C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C81_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=PASS_NO_NO_TEST_JSON_REMAINING
NEXT_RECOMMENDATION=C82_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PRE_ACTIVATION_BOUNDARY_REVIEW
```

The negative approval artifacts were temporary operator-validation artifacts only. They were removed after inspection. The final C81 artifact remains `storage/app/watchlist/backtest/c81-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-go-decision-finalization-review.json`.
