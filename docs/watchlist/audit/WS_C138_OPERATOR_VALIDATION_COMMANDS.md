# C138 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c138-weekly-swing-watchlist-production-live-runtime-activation-authorization-review `
  --c137-artifact=storage/app/watchlist/backtest/c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json `
  --expected-c137-hash=da4f273d8b60a5cc07e0950a59a8673ac9ad8e1d `
  --expected-c137-file-sha1=F1599D92D69EBC4AB820B61CB8C0F421A9C7EFB9 `
  --approval-reference=C138_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c138-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json `
  --operator-approved `
  --activation-authorization-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c138-weekly-swing-watchlist-production-live-runtime-activation-authorization-review `
  --c137-artifact=storage/app/watchlist/backtest/c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json `
  --expected-c137-hash=da4f273d8b60a5cc07e0950a59a8673ac9ad8e1d `
  --expected-c137-file-sha1=F1599D92D69EBC4AB820B61CB8C0F421A9C7EFB9 `
  --activation-authorization-confirmed `
  --output=storage/app/watchlist/backtest/c138-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C138_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c138-weekly-swing-watchlist-production-live-runtime-activation-authorization-review `
  --c137-artifact=storage/app/watchlist/backtest/c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json `
  --expected-c137-hash=da4f273d8b60a5cc07e0950a59a8673ac9ad8e1d `
  --expected-c137-file-sha1=F1599D92D69EBC4AB820B61CB8C0F421A9C7EFB9 `
  --operator-approved `
  --activation-authorization-confirmed `
  --output=storage/app/watchlist/backtest/c138-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C138_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c138-weekly-swing-watchlist-production-live-runtime-activation-authorization-review `
  --c137-artifact=storage/app/watchlist/backtest/c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json `
  --expected-c137-hash=da4f273d8b60a5cc07e0950a59a8673ac9ad8e1d `
  --expected-c137-file-sha1=F1599D92D69EBC4AB820B61CB8C0F421A9C7EFB9 `
  --operator-approved `
  --approval-reference=C138_OPERATOR_APPROVED_BUT_AUTHORIZATION_NOT_CONFIRMED_TEST `
  --output=storage/app/watchlist/backtest/c138-no-authorization-confirmation-test.json `
  --overwrite
```

Expected rejection:

```text
C138_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_ACTIVATION_AUTHORIZATION_NOT_CONFIRMED
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C138_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_PASSED_AUTHORIZED_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c138-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=e3954d308b8540bbf7d10ce716848ee816383201
POSITIVE_RUNTIME_FILE_SHA1=1FDC5A1BCF18AD32204FCACCDE6EFDD3747D28D0
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C138=OK (46 tests, 230 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C138=OK (4744 tests, 38442 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C138_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C138_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_ACTIVATION_AUTHORIZATION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_WITHOUT_ACTIVATION_AUTHORIZATION_CONFIRMATION_STATUS=C138_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_ACTIVATION_AUTHORIZATION_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C138_TEST_ARTIFACTS_REMAINING
ACTIVATION_AUTHORIZED=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C139_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW
```
