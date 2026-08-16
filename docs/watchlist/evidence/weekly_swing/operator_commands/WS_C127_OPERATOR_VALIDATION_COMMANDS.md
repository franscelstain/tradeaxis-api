# WS_C127_OPERATOR_VALIDATION_COMMANDS

Commands for C127 weekly swing watchlist controlled runtime wiring handoff audit archive completion review.

C127 is artifact-only, review-only, non-live, non-mutating.
C127 does not activate production runtime wiring, live rollout, runtime bridge, pilot runtime, shadow runtime, PLAN/CONFIRM mutation, official weekly swing output, or live recommendation generation.

## Positive Runtime

```powershell
php artisan watchlist:backtest-c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review `
  --c126-artifact=storage/app/watchlist/backtest/c126-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-review.json `
  --expected-c126-hash=3f990d65414dd754ac4cd7a257ade44d52c89b67 `
  --expected-c126-file-sha1=16B4F020A06459B46CD5ECDAAEDAC1DC2829561E `
  --approval-reference=C127_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review.json `
  --operator-approved `
  --handoff-audit-archive-completion-confirmed `
  --overwrite `
  --progress
```

Expected positive status:

```text
C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_READY_PRIMARY_AND_BACKUP
```

## Negative Gate: Missing Operator Approval

```powershell
php artisan watchlist:backtest-c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review `
  --c126-artifact=storage/app/watchlist/backtest/c126-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-review.json `
  --expected-c126-hash=3f990d65414dd754ac4cd7a257ade44d52c89b67 `
  --expected-c126-file-sha1=16B4F020A06459B46CD5ECDAAEDAC1DC2829561E `
  --approval-reference=C127_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c127-no-operator-test.json `
  --handoff-audit-archive-completion-confirmed `
  --overwrite
```

Expected rejection:

```text
C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary output after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c127-no-operator-test.json
```

## Negative Gate: Missing Approval Reference

```powershell
php artisan watchlist:backtest-c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review `
  --c126-artifact=storage/app/watchlist/backtest/c126-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-review.json `
  --expected-c126-hash=3f990d65414dd754ac4cd7a257ade44d52c89b67 `
  --expected-c126-file-sha1=16B4F020A06459B46CD5ECDAAEDAC1DC2829561E `
  --output=storage/app/watchlist/backtest/c127-no-approval-reference-test.json `
  --operator-approved `
  --handoff-audit-archive-completion-confirmed `
  --overwrite
```

Expected rejection:

```text
C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove temporary output after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c127-no-approval-reference-test.json
```

## Negative Gate: Missing Handoff Audit Archive Completion Confirmation

```powershell
php artisan watchlist:backtest-c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review `
  --c126-artifact=storage/app/watchlist/backtest/c126-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-review.json `
  --expected-c126-hash=3f990d65414dd754ac4cd7a257ade44d52c89b67 `
  --expected-c126-file-sha1=16B4F020A06459B46CD5ECDAAEDAC1DC2829561E `
  --approval-reference=C127_OPERATOR_APPROVED_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c127-no-audit-archive-completion-confirmation-test.json `
  --operator-approved `
  --overwrite
```

Expected rejection:

```text
C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_AUDIT_ARCHIVE_COMPLETION_NOT_CONFIRMED
```

Remove temporary output after inspection:

```powershell
Remove-Item storage/app/watchlist/backtest/c127-no-audit-archive-completion-confirmation-test.json
```

## PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC127"
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime Evidence

Runtime evidence after local validation.

```text
C127_FOCUSED_PHPUNIT=OK (89 tests, 365 assertions)
C127_FULL_WATCHLIST_PHPUNIT_POST_C127=OK (4261 tests, 36066 assertions)
C127_RUNTIME_STATUS=C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED_AUDIT_ARCHIVE_COMPLETION_READY_PRIMARY_AND_BACKUP
C127_ARTIFACT_HASH=fc9d9204da55658d1416e24bd9be20381a1bbc54
C127_FILE_SHA1=6AE20CACBA644E8863FEA16FD4003BE1C775DA54
EXPECTED_C126_HASH=3f990d65414dd754ac4cd7a257ade44d52c89b67
EXPECTED_C126_FILE_SHA1=16B4F020A06459B46CD5ECDAAEDAC1DC2829561E
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE=C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_HANDOFF_AUDIT_ARCHIVE_COMPLETION_CONFIRMATION=C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_REJECTED_AUDIT_ARCHIVE_COMPLETION_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C127_TEST_ARTIFACTS_REMAINING
NEXT_RECOMMENDATION=C128_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW
```
