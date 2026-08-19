# WS_C119_OPERATOR_VALIDATION_COMMANDS

## Positive Runtime

```powershell
php artisan watchlist:backtest-c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review `
  --c118-artifact=storage/app/watchlist/backtest/c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review.json `
  --expected-c118-hash=fff0b2461783386f897971a55621e265f4f1498f `
  --expected-c118-file-sha1=1D81849D13F815900D56FE450BF69991904EA760 `
  --approval-reference=C119_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review.json `
  --operator-approved `
  --operator-go-decision-confirmed `
  --overwrite `
  --progress
```

Expected result:

```text
C119_RUNTIME_STATUS=C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
C119_RUNTIME_REASON_CODE=C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
OPERATOR_GO_DECISION=GO
OPERATOR_GO_DECISION_CONFIRMED=1
NEXT_RECOMMENDATION=C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW
C118_HASH_MATCH=1
C118_FILE_SHA1_MATCH=1
C118_CONVERT_FROM_JSON_PASS=1
C118_OBSERVATION_RESULT_REVIEW_VALID=1
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
CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
```

## Focused Tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC119"
```

## Negative Gate - Missing Operator Approval

```powershell
php artisan watchlist:backtest-c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review `
  --c118-artifact=storage/app/watchlist/backtest/c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review.json `
  --expected-c118-hash=fff0b2461783386f897971a55621e265f4f1498f `
  --expected-c118-file-sha1=1D81849D13F815900D56FE450BF69991904EA760 `
  --approval-reference=C119_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c119-no-operator-approval-test.json `
  --operator-go-decision-confirmed `
  --overwrite
```

Expected rejection:

```text
C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing Approval Reference

```powershell
php artisan watchlist:backtest-c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review `
  --c118-artifact=storage/app/watchlist/backtest/c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review.json `
  --expected-c118-hash=fff0b2461783386f897971a55621e265f4f1498f `
  --expected-c118-file-sha1=1D81849D13F815900D56FE450BF69991904EA760 `
  --output=storage/app/watchlist/backtest/c119-no-approval-reference-test.json `
  --operator-approved `
  --operator-go-decision-confirmed `
  --overwrite
```

Expected rejection:

```text
C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing GO Decision Confirmation

```powershell
php artisan watchlist:backtest-c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review `
  --c118-artifact=storage/app/watchlist/backtest/c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review.json `
  --expected-c118-hash=fff0b2461783386f897971a55621e265f4f1498f `
  --expected-c118-file-sha1=1D81849D13F815900D56FE450BF69991904EA760 `
  --approval-reference=C119_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c119-no-go-decision-confirmation-test.json `
  --operator-approved `
  --overwrite
```

Expected rejection:

```text
C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED
```

## Cleanup Verification

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/c119-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c119-no-approval-reference-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c119-no-go-decision-confirmation-test.json -ErrorAction SilentlyContinue
Get-ChildItem storage/app/watchlist/backtest -Filter "*test.json" | Where-Object { $_.Name -like "c119-*" }
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

C119 validates C118 artifact hash and file SHA1.
C119 validates C118 controlled runtime wiring observation result review for operator go/no-go review only.
C119 confirms C118 ConvertFrom-Json compatibility.
C119 is controlled runtime wiring operator go/no-go review only.
C119 records operator_go_decision=GO as artifact-only evidence.
C119 requires --operator-approved.
C119 requires non-empty --approval-reference.
C119 requires --operator-go-decision-confirmed.
C119 creates controlled runtime wiring operator go/no-go manifest as artifact-only.
C119 creates controlled runtime wiring operator go/no-go checklist as artifact-only.
C119 operator go/no-go review means proceed to C120 controlled runtime wiring GO decision finalization review only.
C119 operator go/no-go record is not an official weekly swing stock recommendation.
