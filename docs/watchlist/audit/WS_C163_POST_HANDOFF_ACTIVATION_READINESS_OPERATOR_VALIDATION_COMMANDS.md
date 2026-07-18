# C163 Post-Handoff Activation Readiness Operator Validation Commands

Run the positive C163 activation readiness gate:

```bash
php artisan watchlist:backtest-c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-readiness-review \
  --operator-approved \
  --post-handoff-activation-readiness-confirmed \
  --c163-post-handoff-boundary-complete-confirmed \
  --post-handoff-boundary-confirmed \
  --plan-confirm-unchanged-confirmed \
  --no-live-plan-confirm-rollout-confirmed \
  --free-publication-locked-confirmed \
  --approval-reference=C163_OPERATOR_APPROVED_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW \
  --overwrite \
  --progress
```

Verify implementation and artifact:

```bash
php -l app/Application/Watchlist/Services/WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationReadinessReviewService.php
php -l app/Console/Commands/Watchlist/RunBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationReadinessReviewCommand.php
php -l tests/Unit/Watchlist/WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationReadinessReviewTest.php
vendor/bin/phpunit tests/Unit/Watchlist/WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationReadinessReviewTest.php
powershell -Command "(Get-FileHash -Algorithm SHA1 -Path 'storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-readiness-review.json').Hash"
```

Expected positive result:

```text
STATUS=C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_PASSED_READY_FOR_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW_PRIMARY_AND_BACKUP
C163_ARTIFACT_HASH=2ade4f45972d1675eb2be1c222bc688d0c454b3b
C163_FILE_SHA1=17BA06C16DC071B38643D8F502C2D22808725A72
NEXT_RECOMMENDATION=C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_APPROVAL_REVIEW
```

Expected negative gates:

```text
MISSING_OPERATOR_APPROVAL=C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
MISSING_POST_HANDOFF_ACTIVATION_READINESS_CONFIRMATION=C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_POST_HANDOFF_ACTIVATION_READINESS_CONFIRMATION_MISSING
C163_POST_HANDOFF_BOUNDARY_HASH_MISMATCH=C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_READINESS_REVIEW_REJECTED_C163_POST_HANDOFF_BOUNDARY_ARTIFACT_LOCK_MISMATCH
```

Do not use C163 activation readiness output as free publication approval, unrestricted publication approval, PLAN/CONFIRM mutation approval, or live rollout approval.
