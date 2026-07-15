# C139 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c139-weekly-swing-watchlist-production-live-runtime-activation-execution-review `
  --c138-artifact=storage/app/watchlist/backtest/c138-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json `
  --expected-c138-hash=e3954d308b8540bbf7d10ce716848ee816383201 `
  --expected-c138-file-sha1=1FDC5A1BCF18AD32204FCACCDE6EFDD3747D28D0 `
  --approval-reference=C139_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c139-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json `
  --operator-approved `
  --production-live-runtime-activation-execution-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c139-weekly-swing-watchlist-production-live-runtime-activation-execution-review `
  --c138-artifact=storage/app/watchlist/backtest/c138-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json `
  --expected-c138-hash=e3954d308b8540bbf7d10ce716848ee816383201 `
  --expected-c138-file-sha1=1FDC5A1BCF18AD32204FCACCDE6EFDD3747D28D0 `
  --production-live-runtime-activation-execution-confirmed `
  --output=storage/app/watchlist/backtest/c139-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C139_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c139-weekly-swing-watchlist-production-live-runtime-activation-execution-review `
  --c138-artifact=storage/app/watchlist/backtest/c138-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json `
  --expected-c138-hash=e3954d308b8540bbf7d10ce716848ee816383201 `
  --expected-c138-file-sha1=1FDC5A1BCF18AD32204FCACCDE6EFDD3747D28D0 `
  --operator-approved `
  --production-live-runtime-activation-execution-confirmed `
  --output=storage/app/watchlist/backtest/c139-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C139_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c139-weekly-swing-watchlist-production-live-runtime-activation-execution-review `
  --c138-artifact=storage/app/watchlist/backtest/c138-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json `
  --expected-c138-hash=e3954d308b8540bbf7d10ce716848ee816383201 `
  --expected-c138-file-sha1=1FDC5A1BCF18AD32204FCACCDE6EFDD3747D28D0 `
  --operator-approved `
  --approval-reference=C139_OPERATOR_APPROVED_BUT_EXECUTION_NOT_CONFIRMED_TEST `
  --output=storage/app/watchlist/backtest/c139-no-execution-confirmation-test.json `
  --overwrite
```

Expected rejection:

```text
C139_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_ACTIVATION_EXECUTION_NOT_CONFIRMED
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C139_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_PASSED_READY_FOR_ACTIVATION_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c139-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=2b2e648433b2bf1e502246d879e7c5e5d943fba7
POSITIVE_RUNTIME_FILE_SHA1=EDE1BC52EFDCF750304E31BB04677FD63912D296
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C139=OK (45 tests, 180 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C139=OK (4789 tests, 38622 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C139_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C139_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_ACTIVATION_EXECUTION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_WITHOUT_ACTIVATION_EXECUTION_CONFIRMATION_STATUS=C139_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_ACTIVATION_EXECUTION_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C139_TEST_ARTIFACTS_REMAINING
ACTIVATION_AUTHORIZED=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_PASS=1
READY_FOR_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C140_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW
```
