# C162 Operator Validation Commands

## C162 PLAN/CONFIRM Completion Handoff Readiness Positive Runtime

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-readiness-review `
  --operator-approved `
  --handoff-readiness-confirmed `
  --c161-topic-complete-confirmed `
  --plan-confirm-completion-closed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C162_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW `
  --overwrite `
  --progress
```

Expected pass:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_FINALIZATION_REVIEW
```

## C162 PLAN/CONFIRM Completion Handoff Readiness Negative Gates

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-readiness-review `
  --approval-reference=C162_NEGATIVE_MISSING_OPERATOR_APPROVAL `
  --handoff-readiness-confirmed `
  --c161-topic-complete-confirmed `
  --plan-confirm-completion-closed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-readiness-negative-missing-operator-approval.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-readiness-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_MISSING_HANDOFF_READINESS `
  --c161-topic-complete-confirmed `
  --plan-confirm-completion-closed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-readiness-negative-missing-handoff-readiness.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_HANDOFF_READINESS_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-readiness-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_MISSING_C161_TOPIC_COMPLETE `
  --handoff-readiness-confirmed `
  --plan-confirm-completion-closed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-readiness-negative-missing-c161-topic-complete.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_TOPIC_COMPLETE_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-readiness-review `
  --operator-approved `
  --approval-reference=C162_NEGATIVE_C161_FINALIZATION_HASH_MISMATCH `
  --handoff-readiness-confirmed `
  --c161-topic-complete-confirmed `
  --plan-confirm-completion-closed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --expected-c161-finalization-hash=bad-hash `
  --output=storage/app/watchlist/backtest/.tmp-runtime-c162-handoff-readiness-negative-c161-finalization-hash-mismatch.json `
  --overwrite
```

Expected rejection:

```text
C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_ARTIFACT_LOCK_MISMATCH
```

## C162 PLAN/CONFIRM Completion Handoff Readiness Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP_READY_FOR_HANDOFF_FINALIZATION_REVIEW
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-readiness-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=69a0d4384511782cd6e65eb25543275694a2b02a
POSITIVE_RUNTIME_FILE_SHA1=D48FF62967B413BA244AA502EE2F57F526AD2C10
TOPIC_CODE=C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS
TOPIC_STAGE=PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW
NEXT_RECOMMENDATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_FINALIZATION_REVIEW
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW=OK (32 tests, 130 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_HANDOFF_READINESS_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_HANDOFF_READINESS_CONFIRMATION_MISSING
NEGATIVE_MISSING_C161_TOPIC_COMPLETE_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_TOPIC_COMPLETE_CONFIRMATION_MISSING
NEGATIVE_MISSING_PLAN_CONFIRM_COMPLETION_CLOSED_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_PLAN_CONFIRM_COMPLETION_CLOSED_CONFIRMATION_MISSING
NEGATIVE_MISSING_PLAN_CONFIRM_UNCHANGED_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING
NEGATIVE_MISSING_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_NO_LIVE_PLAN_CONFIRM_ROLLOUT_CONFIRMATION_MISSING
NEGATIVE_MISSING_FREE_PUBLICATION_LOCK_CONFIRMATION=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING
NEGATIVE_C161_FINALIZATION_ARTIFACT_LOCK_MISMATCH=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_ARTIFACT_LOCK_MISMATCH
NEGATIVE_C161_FINALIZATION_FILE_SHA1_LOCK_MISMATCH=C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_REJECTED_C161_FINALIZATION_FILE_SHA1_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C162_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_TEST_ARTIFACTS_REMAINING
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
LIVE_PLAN_CONFIRM_ROLLOUT_ALLOWED=0
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=0
FREE_PUBLICATION_ALLOWED=0
UNRESTRICTED_PUBLICATION_ALLOWED=0
```
