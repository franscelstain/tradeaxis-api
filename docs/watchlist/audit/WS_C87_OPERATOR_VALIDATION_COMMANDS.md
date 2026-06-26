# WS_C87_OPERATOR_VALIDATION_COMMANDS

C87 is controlled limited runtime opt-in pilot / shadow rollout post-activation operator go/no-go review.
C87 starts from locked C86 final evidence.
C86 post-activation observation result review passed result review for primary + backup.
E02 is primary post-activation operator GO candidate.
B01 is backup post-activation operator GO candidate.
A01 is comparator-only and cannot be promoted.
C87 validates C86 artifact hash and file SHA1.
C87 validates C86 readiness through nested next_readiness_decision.* path.
C87 validates C86 -> C60 lineage.
C87 requires --operator-approved.
C87 requires non-empty --approval-reference.
C87 records post-activation operator GO/NO-GO only.
C87 does not redesign.
C87 does not retune.
C87 does not run parameter search.
C87 does not use OOS to rerank.
C87 does not use operator GO to rerank.
C87 does not use operator GO to deploy.
C87 does not change candidate scope.
C87 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C87 does not deploy live production.
C87 does not mutate PLAN/CONFIRM.
C87 does not change PLAN/CONFIRM output.
C87 keeps production_catalog_runtime_wired=false.
C87 keeps controlled_opt_in_runtime_bridge_active=false.
C87 keeps controlled_parallel_run_active=false.
C87 keeps controlled_rollout_active=false.
C87 keeps post_activation_operator_go_no_go_context_persisted_to_live_runtime=false.
C87 keeps production_deployment_allowed=false.
C87 keeps production_deployment_executed=false.
C87 keeps plan_confirm_mutation_allowed=false.
C87 keeps plan_confirm_mutated=false.
C87 keeps plan_confirm_runtime_reads_activated_catalog=false.
C87 keeps live_plan_confirm_rollout_allowed=false.
C87 keeps live_plan_confirm_rollout_executed=false.
C87 post-activation operator GO means continue to C88 post-activation go decision finalization review only.
C87 post-activation operator GO record is not production deployment.
C87 post-activation operator GO record is not PLAN/CONFIRM live rollout.
C87 post-activation operator GO record is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC87"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime

```powershell
php artisan watchlist:backtest-c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review `
  --c86-artifact=storage/app/watchlist/backtest/c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review.json `
  --expected-c86-hash=2ec7b0acddcf0ed09d1988c555cc32165e6c972f `
  --expected-c86-file-sha1=D0F261827F286FFE502927D7C3704D7A79B4FD6E `
  --approval-reference=C87_OPERATOR_APPROVED_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review.json `
  --overwrite `
  --progress
```

Expected runtime status and reason code:

```text
C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
```

## Artifact Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review.json | ConvertFrom-Json
$run.status
$run.reason_code
$run.source_artifact_locks | Format-List
$run.c86_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.post_activation_operator_go_no_go_decision | Format-List
$run.post_activation_operator_go_no_go_candidate_scorecard | Format-List
$run.post_activation_operator_go_no_go_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.progress_summary | Format-List
$run.planned_next_summary | Format-List
$run.next_readiness_decision | Format-List
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review.json -Algorithm SHA1
```

## Negative Approval Checks

Without `--operator-approved`:

```powershell
php artisan watchlist:backtest-c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review `
  --c86-artifact=storage/app/watchlist/backtest/c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review.json `
  --expected-c86-hash=2ec7b0acddcf0ed09d1988c555cc32165e6c972f `
  --expected-c86-file-sha1=D0F261827F286FFE502927D7C3704D7A79B4FD6E `
  --approval-reference=C87_OPERATOR_APPROVED_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c87-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Without `--approval-reference`:

```powershell
php artisan watchlist:backtest-c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review `
  --c86-artifact=storage/app/watchlist/backtest/c86-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-observation-result-review.json `
  --expected-c86-hash=2ec7b0acddcf0ed09d1988c555cc32165e6c972f `
  --expected-c86-file-sha1=D0F261827F286FFE502927D7C3704D7A79B4FD6E `
  --output=storage/app/watchlist/backtest/c87-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary negative artifacts after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c87-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c87-no-approval-reference-test.json
```

## Final Operator Result Evidence — 2026-06-27

Operator validation results recorded for C87:

```text
FOCUSED_PHPUNIT=OK (12 tests, 138 assertions)
FULL_WATCHLIST_PHPUNIT=OK (1424 tests, 23011 assertions)
RUNTIME_STATUS=C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
RUNTIME_REASON_CODE=C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
ARTIFACT_HASH=4c319158e1e90bc7e491636361551ed212848c5d
ARTIFACT_FILE_SHA1=EBEA22AD5E07792D0D5EE6F71A317966EFF546D8
EXPECTED_C86_HASH=2ec7b0acddcf0ed09d1988c555cc32165e6c972f
ACTUAL_C86_HASH=2ec7b0acddcf0ed09d1988c555cc32165e6c972f
C86_HASH_MATCH=1
EXPECTED_C86_FILE_SHA1=D0F261827F286FFE502927D7C3704D7A79B4FD6E
ACTUAL_C86_FILE_SHA1=D0F261827F286FFE502927D7C3704D7A79B4FD6E
C86_FILE_SHA1_MATCH=1
NEGATIVE_WITHOUT_OPERATOR_APPROVED=C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C87_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=PASS_NO_NO_TEST_JSON_REMAINING
NEXT_RECOMMENDATION=C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW
```

The negative approval artifacts were temporary operator-validation artifacts only. They were removed after inspection. The final C87 artifact remains `storage/app/watchlist/backtest/c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review.json`.
