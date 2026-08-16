# C136 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c136-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review `
  --c135-artifact=storage/app/watchlist/backtest/c135-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --expected-c135-hash=a1573ce8ba1543ce8a98c08c17eefe519e4ca710 `
  --expected-c135-file-sha1=B283F81F0F10AD0CB46BE3C1BFF2A4ABFA27B1A2 `
  --approval-reference=C136_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c136-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c136-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review `
  --output=storage/app/watchlist/backtest/c136-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c136-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --output=storage/app/watchlist/backtest/c136-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c136-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review `
  --operator-approved `
  --approval-reference=C136_OPERATOR_APPROVED_BUT_GO_FINALIZATION_NOT_CONFIRMED_TEST `
  --output=storage/app/watchlist/backtest/c136-no-go-finalization-test.json `
  --overwrite
```

Expected rejection:

```text
C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_NOT_CONFIRMED
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c136-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=38eee6c7216fd94421c65be129ba50c4a93fd1d1
POSITIVE_RUNTIME_FILE_SHA1=1B395D673F04AE8A7FD62527259DA2CFBA8244AF
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C136=OK (41 tests, 214 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C136=OK (4655 tests, 37991 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_GO_DECISION_FINALIZATION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_WITHOUT_GO_DECISION_FINALIZATION_CONFIRMATION_STATUS=C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C136_TEST_ARTIFACTS_REMAINING
GO_DECISION_FINALIZED=1
GO_DECISION_FINALIZATION_CONFIRMED=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW
```
