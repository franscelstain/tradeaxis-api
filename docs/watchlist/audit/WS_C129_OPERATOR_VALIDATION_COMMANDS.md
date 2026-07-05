# WS_C129_OPERATOR_VALIDATION_COMMANDS

Commands for C129 weekly swing watchlist controlled runtime wiring handoff audit archive final closure review.

C129 is artifact-only, review-only, non-live, non-mutating.
C129 does not activate production runtime wiring, live rollout, runtime bridge, pilot runtime, shadow runtime, PLAN/CONFIRM mutation, official weekly swing output, or live recommendation generation.

## Positive Runtime

```powershell
php artisan watchlist:backtest-c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review `
  --c128-artifact=storage/app/watchlist/backtest/c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review.json `
  --expected-c128-hash=6ef4c4f7868f71fa3855c3db3a2e1372af201f68 `
  --expected-c128-file-sha1=33C094BFA0FF23952E68EB0E45A7C9AE092F9A82 `
  --approval-reference=C129_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review.json `
  --operator-approved `
  --handoff-audit-archive-final-closure-confirmed `
  --overwrite `
  --progress
```

Expected positive status:

```text
C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP
```

## Negative Gate: Missing Operator Approval

```powershell
php artisan watchlist:backtest-c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review `
  --c128-artifact=storage/app/watchlist/backtest/c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review.json `
  --expected-c128-hash=6ef4c4f7868f71fa3855c3db3a2e1372af201f68 `
  --expected-c128-file-sha1=33C094BFA0FF23952E68EB0E45A7C9AE092F9A82 `
  --approval-reference=C129_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c129-no-operator-test.json `
  --handoff-audit-archive-final-closure-confirmed `
  --overwrite
```

Expected rejection:

```text
C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate: Missing Approval Reference

```powershell
php artisan watchlist:backtest-c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review `
  --c128-artifact=storage/app/watchlist/backtest/c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review.json `
  --expected-c128-hash=6ef4c4f7868f71fa3855c3db3a2e1372af201f68 `
  --expected-c128-file-sha1=33C094BFA0FF23952E68EB0E45A7C9AE092F9A82 `
  --output=storage/app/watchlist/backtest/c129-no-approval-reference-test.json `
  --operator-approved `
  --handoff-audit-archive-final-closure-confirmed `
  --overwrite
```

Expected rejection:

```text
C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate: Missing Handoff Audit Archive Final Closure Confirmation

```powershell
php artisan watchlist:backtest-c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review `
  --c128-artifact=storage/app/watchlist/backtest/c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review.json `
  --expected-c128-hash=6ef4c4f7868f71fa3855c3db3a2e1372af201f68 `
  --expected-c128-file-sha1=33C094BFA0FF23952E68EB0E45A7C9AE092F9A82 `
  --approval-reference=C129_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c129-no-audit-archive-final-closure-confirmation-test.json `
  --operator-approved `
  --overwrite
```

Expected rejection:

```text
C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_AUDIT_ARCHIVE_FINAL_CLOSURE_NOT_CONFIRMED
```

## PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC129"
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime Evidence

```text
C129_FOCUSED_PHPUNIT=OK (90 tests, 340 assertions)
C129_FULL_WATCHLIST_PHPUNIT_POST_C129=OK (4449 tests, 36767 assertions)
C129_RUNTIME_STATUS=C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP
C129_ARTIFACT_HASH=39b7a16acf266f9b8853d275ff8dff3ef582f716
C129_FILE_SHA1=BA9AE12F4111AED9DC973BF1EA1BAE9181844E9E
EXPECTED_C128_HASH=6ef4c4f7868f71fa3855c3db3a2e1372af201f68
EXPECTED_C128_FILE_SHA1=33C094BFA0FF23952E68EB0E45A7C9AE092F9A82
C128_HASH_MATCH=1
C128_FILE_SHA1_MATCH=1
C128_CONVERT_FROM_JSON_PASS=1
HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSED=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_CONFIRMATION=C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_AUDIT_ARCHIVE_FINAL_CLOSURE_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C129_TEST_ARTIFACTS_REMAINING
NEXT_RECOMMENDATION=NO_NEXT_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED
```
