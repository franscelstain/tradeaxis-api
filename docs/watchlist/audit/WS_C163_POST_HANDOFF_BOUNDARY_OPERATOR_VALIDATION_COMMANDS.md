# C163 Post-Handoff Boundary Operator Validation Commands

Run the positive C163 boundary gate:

```bash
php artisan watchlist:backtest-c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-boundary-review \
  --operator-approved \
  --post-handoff-boundary-confirmed \
  --c162-handoff-audit-archive-chain-closed-confirmed \
  --c162-terminal-no-next-confirmed \
  --plan-confirm-unchanged-confirmed \
  --no-live-plan-confirm-rollout-confirmed \
  --free-publication-locked-confirmed \
  --approval-reference=C163_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW \
  --overwrite \
  --progress
```

Verify the source lock and output:

```bash
php -l app/Application/Watchlist/Services/WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewService.php
php -l app/Console/Commands/Watchlist/RunBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewCommand.php
php -l tests/Unit/Watchlist/WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewTest.php
vendor/bin/phpunit tests/Unit/Watchlist/WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewTest.php
powershell -Command "(Get-FileHash -Algorithm SHA1 -Path 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-boundary-review.json').Hash"
```

Expected positive result:

```text
STATUS=C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_PASSED_C162_HANDOFF_CLOSED_READY_FOR_POST_HANDOFF_ACTIVATION_READINESS_REVIEW
C163_ARTIFACT_HASH=e0cb142d4a075acefb89e5a6f0a367e090ec190d
C163_FILE_SHA1=986469AFAC7F1349A77F4FD1712AB2272CC6E37A
NEXT_RECOMMENDATION=C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW
```

Expected negative gates:

```text
MISSING_OPERATOR_APPROVAL=C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
MISSING_POST_HANDOFF_BOUNDARY_CONFIRMATION=C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_POST_HANDOFF_BOUNDARY_CONFIRMATION_MISSING
C162_FINAL_CLOSURE_HASH_MISMATCH=C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_REJECTED_C162_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_ARTIFACT_LOCK_MISMATCH
```

Do not use C163 post-handoff boundary output as free publication approval, unrestricted publication approval, PLAN/CONFIRM mutation approval, or live rollout approval.
