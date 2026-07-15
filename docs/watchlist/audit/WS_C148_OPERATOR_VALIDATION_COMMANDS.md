# C148 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review `
  --c147-artifact=storage/app/watchlist/backtest/c147-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json `
  --expected-c147-hash=42bbc885078b0557d49b38a7377444969ad171c2 `
  --expected-c147-file-sha1=A1CFE8CC09856A552156AC9365EDF55F9D41A5BD `
  --approval-reference=C148_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review `
  --c147-artifact=storage/app/watchlist/backtest/c147-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json `
  --expected-c147-hash=42bbc885078b0557d49b38a7377444969ad171c2 `
  --expected-c147-file-sha1=A1CFE8CC09856A552156AC9365EDF55F9D41A5BD `
  --approval-reference=C148_NO_OPERATOR_TEST `
  --output=storage/app/watchlist/backtest/c148-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review `
  --c147-artifact=storage/app/watchlist/backtest/c147-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json `
  --expected-c147-hash=42bbc885078b0557d49b38a7377444969ad171c2 `
  --expected-c147-file-sha1=A1CFE8CC09856A552156AC9365EDF55F9D41A5BD `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c148-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=d5420447a0b5994791e51f65318dcc46c75ec156
POSITIVE_RUNTIME_FILE_SHA1=9EF227B2B7944B2406D15235DC6C84264466B81F
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C148=OK (75 tests, 252 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C148=OK (5336 tests, 40710 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C148_TEST_ARTIFACTS_REMAINING
ACTIVATION_AUTHORIZED=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW
```
