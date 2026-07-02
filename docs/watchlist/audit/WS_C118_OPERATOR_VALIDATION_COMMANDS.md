# WS_C118_OPERATOR_VALIDATION_COMMANDS

## Positive Runtime

```powershell
php artisan watchlist:backtest-c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review `
  --c117-artifact=storage/app/watchlist/backtest/c117-weekly-swing-watchlist-controlled-runtime-wiring-observation-review.json `
  --expected-c117-hash=5a41862b964e1c56547ad40e50dbaa95dd0bd6ea `
  --expected-c117-file-sha1=78A8F6BA18AC378ED74B98ADF9179FC9A7F49084 `
  --approval-reference=C118_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected result:

```text
C118_RUNTIME_STATUS=C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP
C118_RUNTIME_REASON_CODE=C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP
NEXT_RECOMMENDATION=C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW
C117_HASH_MATCH=1
C117_FILE_SHA1_MATCH=1
C117_CONVERT_FROM_JSON_PASS=1
C117_OBSERVATION_REVIEW_VALID=1
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
CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
```

## Focused Tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC118"
```

## Negative Gate - Missing Operator Approval

```powershell
php artisan watchlist:backtest-c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review `
  --c117-artifact=storage/app/watchlist/backtest/c117-weekly-swing-watchlist-controlled-runtime-wiring-observation-review.json `
  --expected-c117-hash=5a41862b964e1c56547ad40e50dbaa95dd0bd6ea `
  --expected-c117-file-sha1=78A8F6BA18AC378ED74B98ADF9179FC9A7F49084 `
  --approval-reference=C118_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c118-no-operator-approval-test.json `
  --overwrite
```

Expected rejection:

```text
C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing Approval Reference

```powershell
php artisan watchlist:backtest-c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review `
  --c117-artifact=storage/app/watchlist/backtest/c117-weekly-swing-watchlist-controlled-runtime-wiring-observation-review.json `
  --expected-c117-hash=5a41862b964e1c56547ad40e50dbaa95dd0bd6ea `
  --expected-c117-file-sha1=78A8F6BA18AC378ED74B98ADF9179FC9A7F49084 `
  --output=storage/app/watchlist/backtest/c118-no-approval-reference-test.json `
  --operator-approved `
  --overwrite
```

Expected rejection:

```text
C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Cleanup Verification

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/c118-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c118-no-approval-reference-test.json -ErrorAction SilentlyContinue
Get-ChildItem storage/app/watchlist/backtest -Filter "*test.json" | Where-Object { $_.Name -like "c118-*" }
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

C118 validates C117 artifact hash and file SHA1.
C118 validates C117 controlled runtime wiring observation review for observation result review only.
C118 confirms C117 ConvertFrom-Json compatibility.
C118 is controlled runtime wiring observation result review only.
C118 requires --operator-approved.
C118 requires non-empty --approval-reference.
C118 creates controlled runtime wiring observation result review manifest as artifact-only.
C118 creates controlled runtime wiring observation result review checklist as artifact-only.
C118 observation result review means proceed to C119 controlled runtime wiring operator go/no-go review only.
C118 observation result review record is not an official weekly swing stock recommendation.
