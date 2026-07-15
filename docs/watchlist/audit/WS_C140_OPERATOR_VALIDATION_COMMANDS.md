# C140 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review `
  --c139-artifact=storage/app/watchlist/backtest/c139-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json `
  --expected-c139-hash=2b2e648433b2bf1e502246d879e7c5e5d943fba7 `
  --expected-c139-file-sha1=EDE1BC52EFDCF750304E31BB04677FD63912D296 `
  --approval-reference=C140_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review `
  --c139-artifact=storage/app/watchlist/backtest/c139-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json `
  --expected-c139-hash=2b2e648433b2bf1e502246d879e7c5e5d943fba7 `
  --expected-c139-file-sha1=EDE1BC52EFDCF750304E31BB04677FD63912D296 `
  --output=storage/app/watchlist/backtest/c140-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C140_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review `
  --c139-artifact=storage/app/watchlist/backtest/c139-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json `
  --expected-c139-hash=2b2e648433b2bf1e502246d879e7c5e5d943fba7 `
  --expected-c139-file-sha1=EDE1BC52EFDCF750304E31BB04677FD63912D296 `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c140-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C140_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C140_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_PASSED_READY_FOR_ACTIVATION_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=e1a428c007dbe40d438e34a15c74d57a58cf5449
POSITIVE_RUNTIME_FILE_SHA1=91EA2C44BB6E8742F55203589BFCFB7E1088DD6B
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C140=OK (41 tests, 185 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C140=OK (4830 tests, 38807 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C140_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C140_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C140_TEST_ARTIFACTS_REMAINING
ACTIVATION_AUTHORIZED=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_PASS=1
READY_FOR_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW
```
