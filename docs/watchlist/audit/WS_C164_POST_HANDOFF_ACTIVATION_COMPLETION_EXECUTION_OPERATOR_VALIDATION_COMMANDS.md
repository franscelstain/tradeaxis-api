# WS C164 Post-Handoff Activation Completion Execution Operator Validation Commands

## Positive Run

```powershell
php artisan watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-execution `
  --operator-approved `
  --completion-execution-confirmed `
  --c164-boundary-cleared-confirmed `
  --post-handoff-activation-completion-boundary-confirmed `
  --controlled-completion-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C164_OPERATOR_APPROVED_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_ONLY `
  --overwrite
```

Expected status:

```text
C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_PASSED_COMPLETION_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP
```

## Required Locks

```text
C164_BOUNDARY_ARTIFACT_HASH=997bb3cc6f5565da92438a2afaca441bb50977b4
C164_BOUNDARY_FILE_SHA1=2EBE74B5E40E53C60456A4110DF41A29B1D3E1A6
CONTROLLED_COMPLETION_HASH=e9862d9e7738d0558f107d978f329f97f14b3520
CONTROLLED_COMPLETION_FILE_SHA1=AB9FC9F714339B78D68132222AC8C398BE7EE1B3
```

## Negative Gates

Missing operator approval:

```powershell
php artisan watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-execution `
  --completion-execution-confirmed `
  --c164-boundary-cleared-confirmed `
  --post-handoff-activation-completion-boundary-confirmed `
  --controlled-completion-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C164_NEGATIVE_MISSING_OPERATOR `
  --output=storage/app/watchlist/backtest/.tmp-c164-completion-execution-missing-operator.json `
  --overwrite
```

Expected status:

```text
C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING
```

Missing completion execution confirmation:

```powershell
php artisan watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-execution `
  --operator-approved `
  --c164-boundary-cleared-confirmed `
  --post-handoff-activation-completion-boundary-confirmed `
  --controlled-completion-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C164_NEGATIVE_MISSING_EXECUTION_CONFIRMATION `
  --output=storage/app/watchlist/backtest/.tmp-c164-completion-execution-missing-execution-confirmation.json `
  --overwrite
```

Expected status:

```text
C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_REJECTED_COMPLETION_EXECUTION_CONFIRMATION_MISSING
```

## Safety Expectations

```text
WATCHLIST_FUNCTION_USED=CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION
PRIMARY_AND_BACKUP_ONLY=1
A01_REMAINS_COMPARATOR_ONLY=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=0
NEXT_RECOMMENDATION=C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW
```
