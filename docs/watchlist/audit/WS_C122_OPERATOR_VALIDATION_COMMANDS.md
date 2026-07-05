# WS_C122_OPERATOR_VALIDATION_COMMANDS

## Positive Runtime

```powershell
php artisan watchlist:backtest-c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review `
  --c121-artifact=storage/app/watchlist/backtest/c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review.json `
  --expected-c121-hash=54c19fc3235d62f07b3d57b3faac96f09afeb616 `
  --expected-c121-file-sha1=AF4AF4C557F57D1435AC226311E8F49E509C4BA8 `
  --approval-reference=C122_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review.json `
  --operator-approved `
  --handoff-readiness-confirmed `
  --overwrite `
  --progress
```

Expected result:

```text
C122_RUNTIME_STATUS=C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP
C122_RUNTIME_REASON_CODE=C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP
C122_ARTIFACT_HASH=0edfa166bfa8f195db6dfd09f318b6e0515cc5c7
C122_FILE_SHA1=FF830FE04623A636F86E514120575BD57A98EEB4
HANDOFF_READY=1
HANDOFF_READINESS_CONFIRMED=1
HANDOFF_READINESS_GO_DECISION=HANDOFF_READY_GO
NEXT_RECOMMENDATION=C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW
C121_HASH_MATCH=1
C121_FILE_SHA1_MATCH=1
C121_CONVERT_FROM_JSON_PASS=1
C121_LOCK_VALID=1
C121_COMPLETION_BOUNDARY_VALID=1
PRODUCTION_READY=0
PRODUCTION_RUNTIME_WIRING_ALLOWED=0
PRODUCTION_RUNTIME_WIRING_EXECUTED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
RUNTIME_BRIDGE_ACTIVE=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_CONTEXT_PERSISTED_TO_LIVE_RUNTIME=0
```

## Focused Tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC122"
```

Observed result:

```text
OK (104 tests, 351 assertions)
```

## Full Watchlist Tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Observed result:

```text
OK (3854 tests, 34345 assertions)
```

## Negative Gate - Missing Operator Approval

```powershell
php artisan watchlist:backtest-c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review `
  --c121-artifact=storage/app/watchlist/backtest/c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review.json `
  --expected-c121-hash=54c19fc3235d62f07b3d57b3faac96f09afeb616 `
  --expected-c121-file-sha1=AF4AF4C557F57D1435AC226311E8F49E509C4BA8 `
  --approval-reference=C122_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c122-no-operator-approval-test.json `
  --handoff-readiness-confirmed `
  --overwrite
```

Expected rejection:

```text
C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing Approval Reference

```powershell
php artisan watchlist:backtest-c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review `
  --c121-artifact=storage/app/watchlist/backtest/c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review.json `
  --expected-c121-hash=54c19fc3235d62f07b3d57b3faac96f09afeb616 `
  --expected-c121-file-sha1=AF4AF4C557F57D1435AC226311E8F49E509C4BA8 `
  --output=storage/app/watchlist/backtest/c122-no-approval-reference-test.json `
  --operator-approved `
  --handoff-readiness-confirmed `
  --overwrite
```

Expected rejection:

```text
C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate - Missing Handoff Readiness Confirmation

```powershell
php artisan watchlist:backtest-c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review `
  --c121-artifact=storage/app/watchlist/backtest/c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review.json `
  --expected-c121-hash=54c19fc3235d62f07b3d57b3faac96f09afeb616 `
  --expected-c121-file-sha1=AF4AF4C557F57D1435AC226311E8F49E509C4BA8 `
  --approval-reference=C122_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c122-no-handoff-readiness-confirmation-test.json `
  --operator-approved `
  --overwrite
```

Expected rejection:

```text
C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_REJECTED_HANDOFF_READINESS_NOT_CONFIRMED
```

## Boundary

C122 validates C121 artifact hash and file SHA1.
C122 validates C121 controlled runtime wiring completion boundary for handoff readiness review only.
C122 is controlled runtime wiring handoff readiness review only.
C122 creates controlled runtime wiring handoff readiness manifest as artifact-only.
C122 handoff readiness review means continue to C123 controlled runtime wiring handoff finalization review only.
C122 handoff readiness record is not an official weekly swing stock recommendation.
