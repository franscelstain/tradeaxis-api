# WS_C117_OPERATOR_VALIDATION_COMMANDS

## Positive Runtime

```powershell
php artisan watchlist:backtest-c117-weekly-swing-watchlist-controlled-runtime-wiring-observation-review `
  --c116-artifact=storage/app/watchlist/backtest/c116-weekly-swing-watchlist-controlled-runtime-wiring-execution-review.json `
  --expected-c116-hash=2f258cc4c6171a396f1cba3f118cd67a15ba55f0 `
  --expected-c116-file-sha1=288BA008CFDB19D73F1BBEBF4EEDFF667B7ABB60 `
  --approval-reference=C117_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c117-weekly-swing-watchlist-controlled-runtime-wiring-observation-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected result:

```text
C117_RUNTIME_STATUS=C117_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP
C117_RUNTIME_REASON_CODE=C117_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW_PASSED_READY_FOR_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP
NEXT_RECOMMENDATION=C118_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_RESULT_REVIEW
C116_HASH_MATCH=1
C116_FILE_SHA1_MATCH=1
C116_CONVERT_FROM_JSON_PASS=1
C116_EXECUTION_REVIEW_VALID=1
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
CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
```

## Focused Tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC117"
```

## Negative Gate - Missing Operator Approval

```powershell
php artisan watchlist:backtest-c117-weekly-swing-watchlist-controlled-runtime-wiring-observation-review `
  --c116-artifact=storage/app/watchlist/backtest/c116-weekly-swing-watchlist-controlled-runtime-wiring-execution-review.json `
  --expected-c116-hash=2f258cc4c6171a396f1cba3f118cd67a15ba55f0 `
  --expected-c116-file-sha1=288BA008CFDB19D73F1BBEBF4EEDFF667B7ABB60 `
  --approval-reference=C117_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c117-no-operator-approval-test.json `
  --overwrite
```

Expected rejection:

```text
C117_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing Approval Reference

```powershell
php artisan watchlist:backtest-c117-weekly-swing-watchlist-controlled-runtime-wiring-observation-review `
  --c116-artifact=storage/app/watchlist/backtest/c116-weekly-swing-watchlist-controlled-runtime-wiring-execution-review.json `
  --expected-c116-hash=2f258cc4c6171a396f1cba3f118cd67a15ba55f0 `
  --expected-c116-file-sha1=288BA008CFDB19D73F1BBEBF4EEDFF667B7ABB60 `
  --output=storage/app/watchlist/backtest/c117-no-approval-reference-test.json `
  --operator-approved `
  --overwrite
```

Expected rejection:

```text
C117_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Cleanup Verification

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/c117-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c117-no-approval-reference-test.json -ErrorAction SilentlyContinue
Get-ChildItem storage/app/watchlist/backtest -Filter "*test.json" | Where-Object { $_.Name -like "c117-*" }
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

C117 validates C116 artifact hash and file SHA1.
C117 validates C116 controlled runtime wiring execution review for observation review only.
C117 confirms C116 ConvertFrom-Json compatibility.
C117 is controlled runtime wiring observation review only.
C117 requires --operator-approved.
C117 requires non-empty --approval-reference.
C117 creates controlled runtime wiring observation review manifest as artifact-only.
C117 creates controlled runtime wiring observation review checklist as artifact-only.
C117 observation review means proceed to C118 controlled runtime wiring observation result review only.
C117 observation review record is not an official weekly swing stock recommendation.
