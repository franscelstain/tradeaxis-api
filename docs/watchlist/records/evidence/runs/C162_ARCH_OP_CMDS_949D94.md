# C162 Handoff Audit Archive Operator Validation Commands

## C162 PLAN/CONFIRM Completion Handoff Audit Archive Positive Runtime

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-review `
  --operator-approved `
  --handoff-audit-archive-confirmed `
  --c162-handoff-closure-seal-complete-confirmed `
  --handoff-closure-sealed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C162_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW `
  --overwrite `
  --progress
```

Expected pass:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW
```

## C162 PLAN/CONFIRM Completion Handoff Audit Archive Negative Gates

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-review `
  --approval-reference=C162_NEGATIVE_MISSING_OPERATOR_APPROVAL `
  --handoff-audit-archive-confirmed `
  --c162-handoff-closure-seal-complete-confirmed `
  --handoff-closure-sealed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-audit-archive-negative-missing-operator-approval.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_MISSING_HANDOFF_AUDIT_ARCHIVE `
  --c162-handoff-closure-seal-complete-confirmed `
  --handoff-closure-sealed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-audit-archive-negative-missing-handoff-audit-archive.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_HANDOFF_AUDIT_ARCHIVE_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_MISSING_C162_HANDOFF_CLOSURE_SEAL_COMPLETE `
  --handoff-audit-archive-confirmed `
  --handoff-closure-sealed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-audit-archive-negative-missing-c162-handoff-closure-seal-complete.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_COMPLETE_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_C162_HANDOFF_CLOSURE_SEAL_HASH_MISMATCH `
  --handoff-audit-archive-confirmed `
  --c162-handoff-closure-seal-complete-confirmed `
  --handoff-closure-sealed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --expected-c162-handoff-closure-seal-hash=bad-hash `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-audit-archive-negative-c162-handoff-closure-seal-hash-mismatch.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_ARTIFACT_LOCK_MISMATCH
```

## C162 PLAN/CONFIRM Completion Handoff Audit Archive Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=ad53366fea95f0fe89ea1643443f1254eb1acbd8
POSITIVE_RUNTIME_FILE_SHA1=6047605B700ABC36C0BB33CCD25D6087C869CE39
TOPIC_CODE=C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE
TOPIC_STAGE=PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW
NEXT_RECOMMENDATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW=OK (25 tests, 103 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_HANDOFF_AUDIT_ARCHIVE_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_HANDOFF_AUDIT_ARCHIVE_CONFIRMATION_MISSING
NEGATIVE_MISSING_C162_HANDOFF_CLOSURE_SEAL_COMPLETE_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_COMPLETE_CONFIRMATION_MISSING
NEGATIVE_MISSING_HANDOFF_CLOSURE_SEALED_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_HANDOFF_CLOSURE_SEALED_CONFIRMATION_MISSING
NEGATIVE_MISSING_PLAN_CONFIRM_UNCHANGED_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING
NEGATIVE_MISSING_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING
NEGATIVE_MISSING_FREE_PUBLICATION_LOCK_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING
NEGATIVE_C162_HANDOFF_CLOSURE_SEAL_ARTIFACT_LOCK_MISMATCH=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_ARTIFACT_LOCK_MISMATCH
NEGATIVE_C162_HANDOFF_CLOSURE_SEAL_FILE_SHA1_LOCK_MISMATCH=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_C162_HANDOFF_CLOSURE_SEAL_FILE_SHA1_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_TEST_ARTIFACTS_REMAINING
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
LIVE_PLAN_CONFIRM_ROLLOUT_ALLOWED=0
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=0
FREE_PUBLICATION_ALLOWED=0
UNRESTRICTED_PUBLICATION_ALLOWED=0
```
