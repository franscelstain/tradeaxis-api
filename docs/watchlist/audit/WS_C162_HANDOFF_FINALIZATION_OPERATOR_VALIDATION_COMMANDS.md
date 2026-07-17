# C162 Operator Validation Commands

## C162 PLAN/CONFIRM Completion Handoff Finalization Positive Runtime

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-finalization-review `
  --operator-approved `
  --handoff-finalization-confirmed `
  --c162-handoff-readiness-complete-confirmed `
  --handoff-ready-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C162_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW `
  --overwrite `
  --progress
```

Expected pass:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_COMPLETION_BOUNDARY_REVIEW
```

## C162 PLAN/CONFIRM Completion Handoff Finalization Negative Gates

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-finalization-review `
  --approval-reference=C162_NEGATIVE_MISSING_OPERATOR_APPROVAL `
  --handoff-finalization-confirmed `
  --c162-handoff-readiness-complete-confirmed `
  --handoff-ready-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-finalization-negative-missing-operator-approval.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-finalization-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_MISSING_HANDOFF_FINALIZATION `
  --c162-handoff-readiness-complete-confirmed `
  --handoff-ready-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-finalization-negative-missing-handoff-finalization.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_HANDOFF_FINALIZATION_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-finalization-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_MISSING_C162_HANDOFF_READINESS_COMPLETE `
  --handoff-finalization-confirmed `
  --handoff-ready-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-finalization-negative-missing-c162-handoff-readiness-complete.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_COMPLETE_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-finalization-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_C162_HANDOFF_READINESS_HASH_MISMATCH `
  --handoff-finalization-confirmed `
  --c162-handoff-readiness-complete-confirmed `
  --handoff-ready-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --expected-c162-handoff-readiness-hash=bad-hash `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-finalization-negative-c162-handoff-readiness-hash-mismatch.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_ARTIFACT_LOCK_MISMATCH
```

## C162 PLAN/CONFIRM Completion Handoff Finalization Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_COMPLETION_BOUNDARY_REVIEW
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-finalization-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=59f78ba6da2c7302246a79e412c27e025ef545c3
POSITIVE_RUNTIME_FILE_SHA1=E7F8D7441F028E5498D4CC8DCC0E24E25FB47FCB
TOPIC_CODE=C162_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION
TOPIC_STAGE=PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW
NEXT_RECOMMENDATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_COMPLETION_BOUNDARY_REVIEW
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C162_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW=OK (28 tests, 127 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_HANDOFF_FINALIZATION_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_HANDOFF_FINALIZATION_CONFIRMATION_MISSING
NEGATIVE_MISSING_C162_HANDOFF_READINESS_COMPLETE_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_COMPLETE_CONFIRMATION_MISSING
NEGATIVE_MISSING_HANDOFF_READY_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_HANDOFF_READY_CONFIRMATION_MISSING
NEGATIVE_MISSING_PLAN_CONFIRM_UNCHANGED_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING
NEGATIVE_MISSING_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING
NEGATIVE_MISSING_FREE_PUBLICATION_LOCK_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING
NEGATIVE_C162_HANDOFF_READINESS_ARTIFACT_LOCK_MISMATCH=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_ARTIFACT_LOCK_MISMATCH
NEGATIVE_C162_HANDOFF_READINESS_FILE_SHA1_LOCK_MISMATCH=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW_REJECTED_C162_HANDOFF_READINESS_FILE_SHA1_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C162_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_TEST_ARTIFACTS_REMAINING
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
LIVE_PLAN_CONFIRM_ROLLOUT_ALLOWED=0
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=0
FREE_PUBLICATION_ALLOWED=0
UNRESTRICTED_PUBLICATION_ALLOWED=0
```
