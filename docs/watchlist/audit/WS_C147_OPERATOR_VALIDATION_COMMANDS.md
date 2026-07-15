# C147 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c147-weekly-swing-watchlist-production-live-runtime-activation-observation-review `
  --c146-artifact=storage/app/watchlist/backtest/c146-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json `
  --expected-c146-hash=ff6549aa99b2488ce52184dd818190b124e480ce `
  --expected-c146-file-sha1=1291AADFB2CC7691D868AD86604731C2F6F5D9F2 `
  --approval-reference=C147_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c147-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c147-weekly-swing-watchlist-production-live-runtime-activation-observation-review `
  --c146-artifact=storage/app/watchlist/backtest/c146-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json `
  --expected-c146-hash=ff6549aa99b2488ce52184dd818190b124e480ce `
  --expected-c146-file-sha1=1291AADFB2CC7691D868AD86604731C2F6F5D9F2 `
  --approval-reference=C147_NO_OPERATOR_TEST `
  --output=storage/app/watchlist/backtest/c147-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C147_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c147-weekly-swing-watchlist-production-live-runtime-activation-observation-review `
  --c146-artifact=storage/app/watchlist/backtest/c146-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json `
  --expected-c146-hash=ff6549aa99b2488ce52184dd818190b124e480ce `
  --expected-c146-file-sha1=1291AADFB2CC7691D868AD86604731C2F6F5D9F2 `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c147-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C147_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C147_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_PASSED_READY_FOR_ACTIVATION_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c147-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=42bbc885078b0557d49b38a7377444969ad171c2
POSITIVE_RUNTIME_FILE_SHA1=A1CFE8CC09856A552156AC9365EDF55F9D41A5BD
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C147=OK (70 tests, 237 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C147=OK (5261 tests, 40458 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C147_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C147_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C147_TEST_ARTIFACTS_REMAINING
ACTIVATION_AUTHORIZED=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C148_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_RESULT_REVIEW
```
