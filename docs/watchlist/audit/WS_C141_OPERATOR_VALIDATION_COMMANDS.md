# C141 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c141-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review `
  --c140-artifact=storage/app/watchlist/backtest/c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json `
  --expected-c140-hash=e1a428c007dbe40d438e34a15c74d57a58cf5449 `
  --expected-c140-file-sha1=91EA2C44BB6E8742F55203589BFCFB7E1088DD6B `
  --approval-reference=C141_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c141-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c141-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review `
  --c140-artifact=storage/app/watchlist/backtest/c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json `
  --expected-c140-hash=e1a428c007dbe40d438e34a15c74d57a58cf5449 `
  --expected-c140-file-sha1=91EA2C44BB6E8742F55203589BFCFB7E1088DD6B `
  --output=storage/app/watchlist/backtest/c141-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c141-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review `
  --c140-artifact=storage/app/watchlist/backtest/c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json `
  --expected-c140-hash=e1a428c007dbe40d438e34a15c74d57a58cf5449 `
  --expected-c140-file-sha1=91EA2C44BB6E8742F55203589BFCFB7E1088DD6B `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c141-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c141-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=ea7c4be969c2faf9e4990a135503829b8f6d6518
POSITIVE_RUNTIME_FILE_SHA1=D9102B54D8719B40266AC8D4E9A0DF5B5BA5EB74
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C141=OK (44 tests, 197 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C141=OK (4874 tests, 39004 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C141_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C141_TEST_ARTIFACTS_REMAINING
ACTIVATION_AUTHORIZED=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW_PASS=1
READY_FOR_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C142_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW
```
