# WS_C121_OPERATOR_VALIDATION_COMMANDS

## Positive Runtime

```powershell
php artisan watchlist:backtest-c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review `
  --c120-artifact=storage/app/watchlist/backtest/c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review.json `
  --expected-c120-hash=295ca48901a384ec36852fccbde970f62e393ff5 `
  --expected-c120-file-sha1=4FE363EC781E016B2A1729C29E4CD704527E2C2C `
  --approval-reference=C121_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review.json `
  --operator-approved `
  --completion-boundary-confirmed `
  --overwrite `
  --progress
```

Expected result:

```text
C121_RUNTIME_STATUS=C121_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
C121_RUNTIME_REASON_CODE=C121_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
COMPLETION_BOUNDARY_CLEARED=1
COMPLETION_BOUNDARY_CONFIRMED=1
BOUNDARY_GO_DECISION=BOUNDARY_CLEARED_GO
NEXT_RECOMMENDATION=C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW
C120_HASH_MATCH=1
C120_FILE_SHA1_MATCH=1
C120_CONVERT_FROM_JSON_PASS=1
C120_LOCK_VALID=1
C120_GO_DECISION_FINALIZATION_VALID=1
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
CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
```

## Focused Tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC121"
```

## Negative Gate - Missing Operator Approval

```powershell
php artisan watchlist:backtest-c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review `
  --c120-artifact=storage/app/watchlist/backtest/c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review.json `
  --expected-c120-hash=295ca48901a384ec36852fccbde970f62e393ff5 `
  --expected-c120-file-sha1=4FE363EC781E016B2A1729C29E4CD704527E2C2C `
  --approval-reference=C121_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c121-no-operator-approval-test.json `
  --completion-boundary-confirmed `
  --overwrite
```

Expected rejection:

```text
C121_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing Approval Reference

```powershell
php artisan watchlist:backtest-c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review `
  --c120-artifact=storage/app/watchlist/backtest/c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review.json `
  --expected-c120-hash=295ca48901a384ec36852fccbde970f62e393ff5 `
  --expected-c120-file-sha1=4FE363EC781E016B2A1729C29E4CD704527E2C2C `
  --output=storage/app/watchlist/backtest/c121-no-approval-reference-test.json `
  --operator-approved `
  --completion-boundary-confirmed `
  --overwrite
```

Expected rejection:

```text
C121_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing Completion Boundary Confirmation

```powershell
php artisan watchlist:backtest-c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review `
  --c120-artifact=storage/app/watchlist/backtest/c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review.json `
  --expected-c120-hash=295ca48901a384ec36852fccbde970f62e393ff5 `
  --expected-c120-file-sha1=4FE363EC781E016B2A1729C29E4CD704527E2C2C `
  --approval-reference=C121_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c121-no-completion-boundary-confirmation-test.json `
  --operator-approved `
  --overwrite
```

Expected rejection:

```text
C121_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW_REJECTED_COMPLETION_BOUNDARY_NOT_CONFIRMED
```

## Cleanup Verification

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/c121-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c121-no-approval-reference-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c121-no-completion-boundary-confirmation-test.json -ErrorAction SilentlyContinue
Get-ChildItem storage/app/watchlist/backtest -Filter "*test.json" | Where-Object { $_.Name -like "c121-*" }
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

C121 validates C120 artifact hash and file SHA1.
C121 validates C120 controlled runtime wiring GO decision finalization for completion boundary review only.
C121 confirms C120 ConvertFrom-Json compatibility.
C121 is controlled runtime wiring completion boundary review only.
C121 records completion_boundary_cleared=1 as artifact-only evidence.
C121 requires --operator-approved.
C121 requires non-empty --approval-reference.
C121 requires --completion-boundary-confirmed.
C121 creates controlled runtime wiring completion boundary manifest as artifact-only.
C121 creates controlled runtime wiring completion boundary checklist as artifact-only.
C121 completion boundary review means proceed to C122 controlled runtime wiring handoff readiness review only.
C121 completion boundary record is not an official weekly swing stock recommendation.

## Final Evidence - 2026-07-03

```text
FOCUSED_PHPUNIT_C121=OK (121 tests, 394 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C121=OK (3750 tests, 33994 assertions)
C121_RUNTIME_STATUS=C121_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
C121_RUNTIME_REASON_CODE=C121_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
C121_ARTIFACT_HASH=54c19fc3235d62f07b3d57b3faac96f09afeb616
C121_FILE_SHA1=AF4AF4C557F57D1435AC226311E8F49E509C4BA8
C120_HASH_MATCH=1
C120_FILE_SHA1_MATCH=1
C120_CONVERT_FROM_JSON_PASS=1
C120_LOCK_VALID=1
C120_GO_DECISION_FINALIZATION_VALID=1
COMPLETION_BOUNDARY_CLEARED=1
COMPLETION_BOUNDARY_CONFIRMED=1
BOUNDARY_GO_DECISION=BOUNDARY_CLEARED_GO
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_COMPLETION_BOUNDARY_CONFIRMATION=REJECTED_COMPLETION_BOUNDARY_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_OUTPUT
NEXT_RECOMMENDATION=C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW
```
