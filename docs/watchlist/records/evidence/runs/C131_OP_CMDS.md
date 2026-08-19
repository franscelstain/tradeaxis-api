# C131 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c131-weekly-swing-watchlist-production-live-runtime-activation-approval-review `
  --c130-artifact=storage/app/watchlist/backtest/c130-weekly-swing-watchlist-production-live-runtime-activation-readiness-review.json `
  --expected-c130-hash=b4c4d48a672a953fee5fc5e79459817c34863775 `
  --expected-c130-file-sha1=B244D23169FA9B01B473382398BE7C847A0C2794 `
  --approval-reference=C131_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c131-weekly-swing-watchlist-production-live-runtime-activation-approval-review.json `
  --operator-approved `
  --production-live-runtime-activation-approval-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c131-weekly-swing-watchlist-production-live-runtime-activation-approval-review --overwrite
```

Expected rejection:

```text
C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c131-weekly-swing-watchlist-production-live-runtime-activation-approval-review `
  --operator-approved `
  --approval-reference=C131_NEGATIVE_NO_ACTIVATION_APPROVAL_CONFIRMATION `
  --overwrite
```

Expected rejection:

```text
C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_ACTIVATION_APPROVAL_NOT_CONFIRMED
```

## Runtime Evidence

```text
POSITIVE_RUNTIME_COMMAND=OK
C131_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c131-weekly-swing-watchlist-production-live-runtime-activation-approval-review.json
C131_RUNTIME_STATUS=C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_PASSED_READY_FOR_ACTIVATION_EXECUTION_REVIEW_PRIMARY_AND_BACKUP
C131_ARTIFACT_HASH=b585d9df32751e811f2b11038e71acb730d694b5
C131_FILE_SHA1=C493DA15314B5AD070FC6D236AD90BB73B046AD8
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_ACTIVATION_APPROVAL_CONFIRMATION=C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_REJECTED_ACTIVATION_APPROVAL_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C131_TEST_ARTIFACTS_REMAINING
FOCUSED_PHPUNIT_C131=OK (26 tests, 147 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C131=OK (4499 tests, 37053 assertions)
NEXT_RECOMMENDATION=C132_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTION_REVIEW
```
