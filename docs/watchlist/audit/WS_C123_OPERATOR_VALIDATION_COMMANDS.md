# WS_C123_OPERATOR_VALIDATION_COMMANDS

## Positive Runtime

```powershell
php artisan watchlist:backtest-c123-weekly-swing-watchlist-controlled-runtime-wiring-handoff-finalization-review `
  --c122-artifact=storage/app/watchlist/backtest/c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review.json `
  --expected-c122-hash=0edfa166bfa8f195db6dfd09f318b6e0515cc5c7 `
  --expected-c122-file-sha1=FF830FE04623A636F86E514120575BD57A98EEB4 `
  --approval-reference=C123_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c123-weekly-swing-watchlist-controlled-runtime-wiring-handoff-finalization-review.json `
  --operator-approved `
  --handoff-finalization-confirmed `
  --overwrite `
  --progress
```

Expected result:

```text
C123_RUNTIME_STATUS=C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP
C123_RUNTIME_REASON_CODE=C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP
C123_ARTIFACT_HASH=802f76794be7b4478ece5e9587c7d5e8635ff88d
C123_FILE_SHA1=9880DB3FDDCBFBA7FA325E8956F523A850605B4D
HANDOFF_READY=1
HANDOFF_FINALIZED=1
HANDOFF_FINALIZATION_CONFIRMED=1
HANDOFF_FINALIZATION_GO_DECISION=HANDOFF_FINALIZED_GO
NEXT_RECOMMENDATION=C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW
C122_HASH_MATCH=1
C122_FILE_SHA1_MATCH=1
C122_CONVERT_FROM_JSON_PASS=1
PRODUCTION_READY=0
PRODUCTION_RUNTIME_WIRING_ALLOWED=0
PRODUCTION_RUNTIME_WIRING_EXECUTED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
RUNTIME_BRIDGE_ACTIVE=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
```

## Focused Tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC123"
```

Observed result:

```text
OK (69 tests, 357 assertions)
```

## Full Watchlist Tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Observed result:

```text
OK (3923 tests, 34702 assertions)
```

## Negative Gate - Missing Operator Approval

```powershell
php artisan watchlist:backtest-c123-weekly-swing-watchlist-controlled-runtime-wiring-handoff-finalization-review `
  --c122-artifact=storage/app/watchlist/backtest/c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review.json `
  --expected-c122-hash=0edfa166bfa8f195db6dfd09f318b6e0515cc5c7 `
  --expected-c122-file-sha1=FF830FE04623A636F86E514120575BD57A98EEB4 `
  --approval-reference=C123_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c123-no-operator-approval-test.json `
  --handoff-finalization-confirmed `
  --overwrite
```

Expected rejection:

```text
C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing Approval Reference

```powershell
php artisan watchlist:backtest-c123-weekly-swing-watchlist-controlled-runtime-wiring-handoff-finalization-review `
  --c122-artifact=storage/app/watchlist/backtest/c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review.json `
  --expected-c122-hash=0edfa166bfa8f195db6dfd09f318b6e0515cc5c7 `
  --expected-c122-file-sha1=FF830FE04623A636F86E514120575BD57A98EEB4 `
  --output=storage/app/watchlist/backtest/c123-no-approval-reference-test.json `
  --operator-approved `
  --handoff-finalization-confirmed `
  --overwrite
```

Expected rejection:

```text
C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing Handoff Finalization Confirmation

```powershell
php artisan watchlist:backtest-c123-weekly-swing-watchlist-controlled-runtime-wiring-handoff-finalization-review `
  --c122-artifact=storage/app/watchlist/backtest/c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review.json `
  --expected-c122-hash=0edfa166bfa8f195db6dfd09f318b6e0515cc5c7 `
  --expected-c122-file-sha1=FF830FE04623A636F86E514120575BD57A98EEB4 `
  --approval-reference=C123_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c123-no-handoff-finalization-confirmation-test.json `
  --operator-approved `
  --overwrite
```

Expected rejection:

```text
C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_REJECTED_HANDOFF_FINALIZATION_NOT_CONFIRMED
```

## Boundary

C123 validates C122 artifact hash and file SHA1.
C123 validates C122 weekly swing watchlist controlled runtime wiring handoff readiness state.
C123 is controlled runtime wiring handoff finalization review only.
C123 creates controlled runtime wiring handoff finalization manifest as artifact-only.
C123 handoff finalization review means continue to C124 controlled runtime wiring handoff completion boundary review only.
C123 handoff finalization record is not an official weekly swing stock recommendation.
