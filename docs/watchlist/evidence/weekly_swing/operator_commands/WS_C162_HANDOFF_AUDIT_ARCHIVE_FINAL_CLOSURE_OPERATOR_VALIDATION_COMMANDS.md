# C162 Handoff Audit Archive Final Closure Operator Validation Commands

## C162 PLAN/CONFIRM Completion Handoff Audit Archive Final Closure Positive Runtime

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-final-closure-review `
  --operator-approved `
  --handoff-audit-archive-final-closure-confirmed `
  --c162-handoff-audit-archive-completion-seal-complete-confirmed `
  --handoff-audit-archive-completion-sealed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C162_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW `
  --overwrite `
  --progress
```

Expected pass:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP_HANDOFF_AUDIT_ARCHIVE_CHAIN_CLOSED
```

## C162 PLAN/CONFIRM Completion Handoff Audit Archive Final Closure Negative Gates

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-final-closure-review `
  --approval-reference=C162_NEGATIVE_MISSING_OPERATOR_APPROVAL `
  --handoff-audit-archive-final-closure-confirmed `
  --c162-handoff-audit-archive-completion-seal-complete-confirmed `
  --handoff-audit-archive-completion-sealed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-audit-archive-final-closure-negative-missing-operator-approval.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-final-closure-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_MISSING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE `
  --c162-handoff-audit-archive-completion-seal-complete-confirmed `
  --handoff-audit-archive-completion-sealed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-audit-archive-final-closure-negative-missing-final-closure.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-final-closure-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_MISSING_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_COMPLETE `
  --handoff-audit-archive-final-closure-confirmed `
  --handoff-audit-archive-completion-sealed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-audit-archive-final-closure-negative-missing-completion-seal-complete.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_COMPLETE_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-final-closure-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_HASH_MISMATCH `
  --handoff-audit-archive-final-closure-confirmed `
  --c162-handoff-audit-archive-completion-seal-complete-confirmed `
  --handoff-audit-archive-completion-sealed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --expected-c162-handoff-audit-archive-completion-seal-hash=bad-hash `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-audit-archive-final-closure-negative-c162-completion-seal-hash-mismatch.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_ARTIFACT_LOCK_MISMATCH
```

## C162 PLAN/CONFIRM Completion Handoff Audit Archive Final Closure Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED_AUDIT_ARCHIVE_FINAL_CLOSED_PRIMARY_AND_BACKUP_HANDOFF_AUDIT_ARCHIVE_CHAIN_CLOSED
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-final-closure-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=4de6d670e5e6d6990dd618e0e818e57a7f79716e
POSITIVE_RUNTIME_FILE_SHA1=97E9057EE0E7A71BC7F74B019F16FE1D251A3157
SOURCE_COMPLETION_SEAL_ARTIFACT=storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-completion-seal-review.json
SOURCE_COMPLETION_SEAL_ARTIFACT_HASH=91f8d60c73a56567346092a89f35eae5c5dee855
SOURCE_COMPLETION_SEAL_FILE_SHA1=0F125CFDC57A66A07DB71055E7227E63C29AFBA3
TOPIC_CODE=C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE
TOPIC_STAGE=PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW
NEXT_RECOMMENDATION=NO_NEXT_C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_REVIEW_REQUIRED
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C162_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW=OK (25 tests, 110 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_APPROVAL_REFERENCE=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_CONFIRMATION_MISSING
NEGATIVE_MISSING_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_COMPLETE_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_COMPLETE_CONFIRMATION_MISSING
NEGATIVE_MISSING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEALED_CONFIRMATION_MISSING
NEGATIVE_MISSING_PLAN_CONFIRM_UNCHANGED_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING
NEGATIVE_MISSING_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING
NEGATIVE_MISSING_FREE_PUBLICATION_LOCK_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING
NEGATIVE_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_ARTIFACT_LOCK_MISMATCH=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_ARTIFACT_LOCK_MISMATCH
NEGATIVE_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_FILE_SHA1_LOCK_MISMATCH=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_FILE_SHA1_LOCK_MISMATCH
NEGATIVE_TEMPORARY_ARTIFACT_REMAINS=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_REJECTED_TEMPORARY_NEGATIVE_ARTIFACT_REMAINS
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
LIVE_PLAN_CONFIRM_ROLLOUT_ALLOWED=0
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=0
FREE_PUBLICATION_ALLOWED=0
UNRESTRICTED_PUBLICATION_ALLOWED=0
```
