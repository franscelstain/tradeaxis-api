# C144 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review `
  --c143-artifact=storage/app/watchlist/backtest/c143-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json `
  --expected-c143-hash=804b6020e73e24e7dac0a9ecbbe116ff5ee95808 `
  --expected-c143-file-sha1=F0645B69E7F22C1FACEEA235ED0256777558752F `
  --approval-reference=C144_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json `
  --operator-approved `
  --pre-activation-boundary-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review `
  --c143-artifact=storage/app/watchlist/backtest/c143-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json `
  --expected-c143-hash=804b6020e73e24e7dac0a9ecbbe116ff5ee95808 `
  --expected-c143-file-sha1=F0645B69E7F22C1FACEEA235ED0256777558752F `
  --output=storage/app/watchlist/backtest/c144-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C144_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review `
  --c143-artifact=storage/app/watchlist/backtest/c143-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json `
  --expected-c143-hash=804b6020e73e24e7dac0a9ecbbe116ff5ee95808 `
  --expected-c143-file-sha1=F0645B69E7F22C1FACEEA235ED0256777558752F `
  --operator-approved `
  --pre-activation-boundary-confirmed `
  --output=storage/app/watchlist/backtest/c144-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C144_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review `
  --c143-artifact=storage/app/watchlist/backtest/c143-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json `
  --expected-c143-hash=804b6020e73e24e7dac0a9ecbbe116ff5ee95808 `
  --expected-c143-file-sha1=F0645B69E7F22C1FACEEA235ED0256777558752F `
  --approval-reference=C144_OPERATOR_APPROVED_BUT_PRE_ACTIVATION_BOUNDARY_NOT_CONFIRMED_TEST `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c144-no-boundary-confirmation-test.json `
  --overwrite
```

Expected rejection:

```text
C144_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_PRE_ACTIVATION_BOUNDARY_NOT_CONFIRMED
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C144_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=68d5bb7d096b09d1defa3a655313ff0a7f658e84
POSITIVE_RUNTIME_FILE_SHA1=FBC618728E9A8B49A5FBD5CE273EF2159705C816
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C144=OK (67 tests, 260 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C144=OK (5052 tests, 39728 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C144_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C144_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_PRE_ACTIVATION_BOUNDARY_CONFIRMATION_EXIT_CODE=1
NEGATIVE_WITHOUT_PRE_ACTIVATION_BOUNDARY_CONFIRMATION_STATUS=C144_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_PRE_ACTIVATION_BOUNDARY_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C144_TEST_ARTIFACTS_REMAINING
PRE_ACTIVATION_BOUNDARY_CONFIRMED=1
PRE_ACTIVATION_BOUNDARY_CLEARED=1
ACTIVATION_AUTHORIZED=0
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW
```
