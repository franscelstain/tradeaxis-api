# WS_C120_OPERATOR_VALIDATION_COMMANDS

## Positive Runtime

```powershell
php artisan watchlist:backtest-c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review `
  --c119-artifact=storage/app/watchlist/backtest/c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review.json `
  --expected-c119-hash=132ebe9778dd6d8e04834ff6174bdeec10e2e8f5 `
  --expected-c119-file-sha1=8ED2AFFAB95C75099E9365A2D959154F67FF9044 `
  --approval-reference=C120_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review.json `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --overwrite `
  --progress
```

Expected result:

```text
C120_RUNTIME_STATUS=C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
C120_RUNTIME_REASON_CODE=C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
OPERATOR_GO_DECISION=GO
OPERATOR_GO_DECISION_CONFIRMED=1
GO_DECISION_FINALIZED=1
GO_DECISION_FINALIZATION_CONFIRMED=1
NEXT_RECOMMENDATION=C121_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW
C119_HASH_MATCH=1
C119_FILE_SHA1_MATCH=1
C119_CONVERT_FROM_JSON_PASS=1
C119_LOCK_VALID=1
C119_OPERATOR_GO_NO_GO_VALID=1
PRODUCTION_READY=0
PRODUCTION_CATALOG_RUNTIME_WIRED=0
PRODUCTION_RUNTIME_WIRING_ALLOWED=0
PRODUCTION_RUNTIME_WIRING_EXECUTED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
RUNTIME_BRIDGE_ACTIVE=0
CONTROLLED_ROLLOUT_ACTIVE=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
```

## Focused Tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC120"
```

## Negative Gate - Missing Operator Approval

```powershell
php artisan watchlist:backtest-c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review `
  --c119-artifact=storage/app/watchlist/backtest/c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review.json `
  --expected-c119-hash=132ebe9778dd6d8e04834ff6174bdeec10e2e8f5 `
  --expected-c119-file-sha1=8ED2AFFAB95C75099E9365A2D959154F67FF9044 `
  --approval-reference=C120_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c120-no-operator-approval-test.json `
  --go-decision-finalization-confirmed `
  --overwrite
```

Expected rejection:

```text
C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing Approval Reference

```powershell
php artisan watchlist:backtest-c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review `
  --c119-artifact=storage/app/watchlist/backtest/c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review.json `
  --expected-c119-hash=132ebe9778dd6d8e04834ff6174bdeec10e2e8f5 `
  --expected-c119-file-sha1=8ED2AFFAB95C75099E9365A2D959154F67FF9044 `
  --output=storage/app/watchlist/backtest/c120-no-approval-reference-test.json `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --overwrite
```

Expected rejection:

```text
C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing GO Decision Finalization Confirmation

```powershell
php artisan watchlist:backtest-c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review `
  --c119-artifact=storage/app/watchlist/backtest/c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review.json `
  --expected-c119-hash=132ebe9778dd6d8e04834ff6174bdeec10e2e8f5 `
  --expected-c119-file-sha1=8ED2AFFAB95C75099E9365A2D959154F67FF9044 `
  --approval-reference=C120_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c120-no-go-decision-finalization-test.json `
  --operator-approved `
  --overwrite
```

Expected rejection:

```text
C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_NOT_CONFIRMED
```

## Cleanup Verification

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/c120-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c120-no-approval-reference-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c120-no-go-decision-finalization-test.json -ErrorAction SilentlyContinue
Get-ChildItem storage/app/watchlist/backtest -Filter "*test.json" | Where-Object { $_.Name -like "c120-*" }
```

Expected cleanup:

```text
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_OUTPUT
```

## Full Watchlist Tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Boundary

C120 validates C119 artifact hash and file SHA1.
C120 validates C119 controlled runtime wiring operator go/no-go review for GO decision finalization review only.
C120 confirms C119 ConvertFrom-Json compatibility.
C120 is controlled runtime wiring GO decision finalization review only.
C120 records go_decision_finalized=1 as artifact-only evidence.
C120 requires --operator-approved.
C120 requires non-empty --approval-reference.
C120 requires --go-decision-finalization-confirmed.
C120 creates controlled runtime wiring GO decision finalization manifest as artifact-only.
C120 creates controlled runtime wiring GO decision finalization checklist as artifact-only.
C120 GO decision finalization means proceed to C121 controlled runtime wiring completion boundary review only.
C120 GO decision finalization record is not an official weekly swing stock recommendation.

## Final Evidence - 2026-07-03

```text
FOCUSED_PHPUNIT_C120=OK (109 tests, 375 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C120=OK (3629 tests, 33600 assertions)
C120_RUNTIME_STATUS=C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
C120_RUNTIME_REASON_CODE=C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
C120_ARTIFACT_HASH=295ca48901a384ec36852fccbde970f62e393ff5
C120_FILE_SHA1=4FE363EC781E016B2A1729C29E4CD704527E2C2C
C119_HASH_MATCH=1
C119_FILE_SHA1_MATCH=1
C119_CONVERT_FROM_JSON_PASS=1
C119_LOCK_VALID=1
C119_OPERATOR_GO_NO_GO_VALID=1
OPERATOR_GO_DECISION=GO
OPERATOR_GO_DECISION_CONFIRMED=1
GO_DECISION_FINALIZED=1
GO_DECISION_FINALIZATION_CONFIRMED=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_GO_DECISION_FINALIZATION_CONFIRMATION=REJECTED_GO_DECISION_FINALIZATION_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_OUTPUT
NEXT_RECOMMENDATION=C121_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW
```
