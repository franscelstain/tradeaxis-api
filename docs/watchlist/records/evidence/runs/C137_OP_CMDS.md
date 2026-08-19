# C137 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review `
  --c136-artifact=storage/app/watchlist/backtest/c136-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json `
  --expected-c136-hash=38eee6c7216fd94421c65be129ba50c4a93fd1d1 `
  --expected-c136-file-sha1=1B395D673F04AE8A7FD62527259DA2CFBA8244AF `
  --approval-reference=C137_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json `
  --operator-approved `
  --pre-activation-boundary-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review `
  --output=storage/app/watchlist/backtest/c137-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review `
  --operator-approved `
  --pre-activation-boundary-confirmed `
  --output=storage/app/watchlist/backtest/c137-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review `
  --operator-approved `
  --approval-reference=C137_OPERATOR_APPROVED_BUT_BOUNDARY_NOT_CONFIRMED_TEST `
  --output=storage/app/watchlist/backtest/c137-no-boundary-confirmation-test.json `
  --overwrite
```

Expected rejection:

```text
C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_PRE_ACTIVATION_BOUNDARY_NOT_CONFIRMED
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=da4f273d8b60a5cc07e0950a59a8673ac9ad8e1d
POSITIVE_RUNTIME_FILE_SHA1=F1599D92D69EBC4AB820B61CB8C0F421A9C7EFB9
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C137=OK (43 tests, 221 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C137=OK (4698 tests, 38212 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_PRE_ACTIVATION_BOUNDARY_CONFIRMATION_EXIT_CODE=1
NEGATIVE_WITHOUT_PRE_ACTIVATION_BOUNDARY_CONFIRMATION_STATUS=C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_REJECTED_PRE_ACTIVATION_BOUNDARY_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C137_TEST_ARTIFACTS_REMAINING
PRE_ACTIVATION_BOUNDARY_CLEARED=1
ACTIVATION_AUTHORIZED=0
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C138_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW
```
