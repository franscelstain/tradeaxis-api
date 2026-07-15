# C146 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c146-weekly-swing-watchlist-production-live-runtime-activation-execution-review `
  --c145-artifact=storage/app/watchlist/backtest/c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json `
  --expected-c145-hash=abdca67093a73670414ea0691792a5fe8f028ac5 `
  --expected-c145-file-sha1=6CA397B20E075F21E7A2BD7870E74FF3E95BF460 `
  --approval-reference=C146_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c146-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json `
  --operator-approved `
  --production-live-runtime-activation-execution-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c146-weekly-swing-watchlist-production-live-runtime-activation-execution-review `
  --c145-artifact=storage/app/watchlist/backtest/c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json `
  --expected-c145-hash=abdca67093a73670414ea0691792a5fe8f028ac5 `
  --expected-c145-file-sha1=6CA397B20E075F21E7A2BD7870E74FF3E95BF460 `
  --approval-reference=C146_NO_OPERATOR_TEST `
  --output=storage/app/watchlist/backtest/c146-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C146_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c146-weekly-swing-watchlist-production-live-runtime-activation-execution-review `
  --c145-artifact=storage/app/watchlist/backtest/c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json `
  --expected-c145-hash=abdca67093a73670414ea0691792a5fe8f028ac5 `
  --expected-c145-file-sha1=6CA397B20E075F21E7A2BD7870E74FF3E95BF460 `
  --operator-approved `
  --production-live-runtime-activation-execution-confirmed `
  --output=storage/app/watchlist/backtest/c146-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C146_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c146-weekly-swing-watchlist-production-live-runtime-activation-execution-review `
  --c145-artifact=storage/app/watchlist/backtest/c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json `
  --expected-c145-hash=abdca67093a73670414ea0691792a5fe8f028ac5 `
  --expected-c145-file-sha1=6CA397B20E075F21E7A2BD7870E74FF3E95BF460 `
  --approval-reference=C146_OPERATOR_APPROVED_BUT_ACTIVATION_EXECUTION_NOT_CONFIRMED_TEST `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c146-no-execution-confirmation-test.json `
  --overwrite
```

Expected rejection:

```text
C146_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_ACTIVATION_EXECUTION_NOT_CONFIRMED
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C146_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_PASSED_READY_FOR_ACTIVATION_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c146-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=ff6549aa99b2488ce52184dd818190b124e480ce
POSITIVE_RUNTIME_FILE_SHA1=1291AADFB2CC7691D868AD86604731C2F6F5D9F2
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C146=OK (70 tests, 224 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C146=OK (5191 tests, 40221 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C146_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C146_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_ACTIVATION_EXECUTION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_WITHOUT_ACTIVATION_EXECUTION_CONFIRMATION_STATUS=C146_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW_REJECTED_ACTIVATION_EXECUTION_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C146_TEST_ARTIFACTS_REMAINING
ACTIVATION_AUTHORIZED=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C147_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW
```
