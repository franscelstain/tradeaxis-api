# WS_C128_OPERATOR_VALIDATION_COMMANDS

Commands for C128 weekly swing watchlist controlled runtime wiring handoff audit archive completion seal review.

C128 is artifact-only, review-only, non-live, non-mutating.
C128 does not activate production runtime wiring, live rollout, runtime bridge, pilot runtime, shadow runtime, PLAN/CONFIRM mutation, official weekly swing output, or live recommendation generation.

## Positive Runtime

```powershell
php artisan watchlist:backtest-c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review `
  --c127-artifact=storage/app/watchlist/backtest/c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review.json `
  --expected-c127-hash=fc9d9204da55658d1416e24bd9be20381a1bbc54 `
  --expected-c127-file-sha1=6AE20CACBA644E8863FEA16FD4003BE1C775DA54 `
  --approval-reference=C128_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review.json `
  --operator-approved `
  --handoff-audit-archive-completion-seal-confirmed `
  --overwrite `
  --progress
```

Expected positive status:

```text
C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_SEALED_PRIMARY_AND_BACKUP
```

## Negative Gate: Missing Operator Approval

```powershell
php artisan watchlist:backtest-c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review `
  --c127-artifact=storage/app/watchlist/backtest/c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review.json `
  --expected-c127-hash=fc9d9204da55658d1416e24bd9be20381a1bbc54 `
  --expected-c127-file-sha1=6AE20CACBA644E8863FEA16FD4003BE1C775DA54 `
  --approval-reference=C128_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c128-no-operator-test.json `
  --handoff-audit-archive-completion-seal-confirmed `
  --overwrite
```

Expected rejection:

```text
C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate: Missing Approval Reference

```powershell
php artisan watchlist:backtest-c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review `
  --c127-artifact=storage/app/watchlist/backtest/c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review.json `
  --expected-c127-hash=fc9d9204da55658d1416e24bd9be20381a1bbc54 `
  --expected-c127-file-sha1=6AE20CACBA644E8863FEA16FD4003BE1C775DA54 `
  --output=storage/app/watchlist/backtest/c128-no-approval-reference-test.json `
  --operator-approved `
  --handoff-audit-archive-completion-seal-confirmed `
  --overwrite
```

Expected rejection:

```text
C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Gate: Missing Handoff Audit Archive Completion Seal Confirmation

```powershell
php artisan watchlist:backtest-c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review `
  --c127-artifact=storage/app/watchlist/backtest/c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review.json `
  --expected-c127-hash=fc9d9204da55658d1416e24bd9be20381a1bbc54 `
  --expected-c127-file-sha1=6AE20CACBA644E8863FEA16FD4003BE1C775DA54 `
  --approval-reference=C128_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c128-no-audit-archive-completion-seal-confirmation-test.json `
  --operator-approved `
  --overwrite
```

Expected rejection:

```text
C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_AUDIT_ARCHIVE_COMPLETION_SEAL_NOT_CONFIRMED
```

## PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC128"
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime Evidence

```text
C128_FOCUSED_PHPUNIT=OK (98 tests, 361 assertions)
C128_FULL_WATCHLIST_PHPUNIT_POST_C128=OK (4359 tests, 36427 assertions)
C128_RUNTIME_STATUS=C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_SEALED_PRIMARY_AND_BACKUP
C128_ARTIFACT_HASH=6ef4c4f7868f71fa3855c3db3a2e1372af201f68
C128_FILE_SHA1=33C094BFA0FF23952E68EB0E45A7C9AE092F9A82
EXPECTED_C127_HASH=fc9d9204da55658d1416e24bd9be20381a1bbc54
EXPECTED_C127_FILE_SHA1=6AE20CACBA644E8863FEA16FD4003BE1C775DA54
C127_HASH_MATCH=1
C127_FILE_SHA1_MATCH=1
C127_CONVERT_FROM_JSON_PASS=1
HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED=1
READY_FOR_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_CONFIRMATION=C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_REJECTED_AUDIT_ARCHIVE_COMPLETION_SEAL_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C128_TEST_ARTIFACTS_REMAINING
NEXT_RECOMMENDATION=C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW
```
