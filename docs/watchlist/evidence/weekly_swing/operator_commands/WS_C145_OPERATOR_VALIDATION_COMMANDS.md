# C145 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review `
  --c144-artifact=storage/app/watchlist/backtest/c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json `
  --expected-c144-hash=68d5bb7d096b09d1defa3a655313ff0a7f658e84 `
  --expected-c144-file-sha1=FBC618728E9A8B49A5FBD5CE273EF2159705C816 `
  --approval-reference=C145_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json `
  --operator-approved `
  --activation-authorization-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review `
  --c144-artifact=storage/app/watchlist/backtest/c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json `
  --expected-c144-hash=68d5bb7d096b09d1defa3a655313ff0a7f658e84 `
  --expected-c144-file-sha1=FBC618728E9A8B49A5FBD5CE273EF2159705C816 `
  --output=storage/app/watchlist/backtest/c145-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review `
  --c144-artifact=storage/app/watchlist/backtest/c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json `
  --expected-c144-hash=68d5bb7d096b09d1defa3a655313ff0a7f658e84 `
  --expected-c144-file-sha1=FBC618728E9A8B49A5FBD5CE273EF2159705C816 `
  --operator-approved `
  --activation-authorization-confirmed `
  --output=storage/app/watchlist/backtest/c145-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review `
  --c144-artifact=storage/app/watchlist/backtest/c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json `
  --expected-c144-hash=68d5bb7d096b09d1defa3a655313ff0a7f658e84 `
  --expected-c144-file-sha1=FBC618728E9A8B49A5FBD5CE273EF2159705C816 `
  --approval-reference=C145_OPERATOR_APPROVED_BUT_ACTIVATION_AUTHORIZATION_NOT_CONFIRMED_TEST `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c145-no-authorization-confirmation-test.json `
  --overwrite
```

Expected rejection:

```text
C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_ACTIVATION_AUTHORIZATION_NOT_CONFIRMED
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_PASSED_AUTHORIZED_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=abdca67093a73670414ea0691792a5fe8f028ac5
POSITIVE_RUNTIME_FILE_SHA1=6CA397B20E075F21E7A2BD7870E74FF3E95BF460
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C145=OK (69 tests, 269 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C145=OK (5121 tests, 39997 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_ACTIVATION_AUTHORIZATION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_WITHOUT_ACTIVATION_AUTHORIZATION_CONFIRMATION_STATUS=C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_REJECTED_ACTIVATION_AUTHORIZATION_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C145_TEST_ARTIFACTS_REMAINING
ACTIVATION_AUTHORIZATION_CONFIRMED=1
ACTIVATION_AUTHORIZED=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C146_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW
```
