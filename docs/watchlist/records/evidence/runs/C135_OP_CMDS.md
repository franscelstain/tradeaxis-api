# C135 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c135-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --c134-artifact=storage/app/watchlist/backtest/c134-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --expected-c134-hash=ada066cc599d749e050b5efd61073ccad1e64b74 `
  --expected-c134-file-sha1=AE7C013A1B5CC0DFC5968C4FC99B2E1DDFF88F3E `
  --approval-reference=C135_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c135-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --operator-approved `
  --operator-go-decision-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c135-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --output=storage/app/watchlist/backtest/c135-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c135-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --operator-approved `
  --operator-go-decision-confirmed `
  --output=storage/app/watchlist/backtest/c135-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c135-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --operator-approved `
  --approval-reference=C135_OPERATOR_APPROVED_BUT_GO_DECISION_NOT_CONFIRMED_TEST `
  --output=storage/app/watchlist/backtest/c135-no-go-decision-test.json `
  --overwrite
```

Expected rejection:

```text
C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c135-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=a1573ce8ba1543ce8a98c08c17eefe519e4ca710
POSITIVE_RUNTIME_FILE_SHA1=B283F81F0F10AD0CB46BE3C1BFF2A4ABFA27B1A2
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C135=OK (30 tests, 192 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C135=OK (4614 tests, 37777 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_GO_DECISION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_WITHOUT_GO_DECISION_CONFIRMATION_STATUS=C135_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C135_TEST_ARTIFACTS_REMAINING
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW
```
