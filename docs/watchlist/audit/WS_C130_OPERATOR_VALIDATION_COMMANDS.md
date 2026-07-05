# C130 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c130-weekly-swing-watchlist-production-live-runtime-activation-readiness-review `
  --c129-artifact=storage/app/watchlist/backtest/c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review.json `
  --expected-c129-hash=39b7a16acf266f9b8853d275ff8dff3ef582f716 `
  --expected-c129-file-sha1=BA9AE12F4111AED9DC973BF1EA1BAE9181844E9E `
  --approval-reference=C130_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c130-weekly-swing-watchlist-production-live-runtime-activation-readiness-review.json `
  --operator-approved `
  --production-live-runtime-activation-readiness-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c130-weekly-swing-watchlist-production-live-runtime-activation-readiness-review --overwrite
```

Expected rejection:

```text
C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c130-weekly-swing-watchlist-production-live-runtime-activation-readiness-review `
  --operator-approved `
  --approval-reference=C130_NEGATIVE_NO_READINESS_CONFIRMATION `
  --overwrite
```

Expected rejection:

```text
C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_ACTIVATION_READINESS_NOT_CONFIRMED
```

## Runtime Evidence

```text
POSITIVE_RUNTIME_COMMAND=OK
C130_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c130-weekly-swing-watchlist-production-live-runtime-activation-readiness-review.json
C130_RUNTIME_STATUS=C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_PASSED_READY_FOR_ACTIVATION_APPROVAL_REVIEW_PRIMARY_AND_BACKUP
C130_ARTIFACT_HASH=b4c4d48a672a953fee5fc5e79459817c34863775
C130_FILE_SHA1=B244D23169FA9B01B473382398BE7C847A0C2794
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_ACTIVATION_READINESS_CONFIRMATION=C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_REJECTED_ACTIVATION_READINESS_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C130_TEST_ARTIFACTS_REMAINING
FOCUSED_PHPUNIT_C130=OK (24 tests, 139 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C130=OK (4473 tests, 36906 assertions)
NEXT_RECOMMENDATION=C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW
```
