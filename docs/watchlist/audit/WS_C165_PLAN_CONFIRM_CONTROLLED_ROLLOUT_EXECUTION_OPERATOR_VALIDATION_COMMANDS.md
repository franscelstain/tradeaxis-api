# WS C165 PLAN/CONFIRM Controlled Rollout Execution Validation Commands

## Positive Run

```powershell
php artisan watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-execution `
  --operator-approved `
  --controlled-rollout-execution-confirmed `
  --c165-boundary-locked-confirmed `
  --activated-catalog-read-confirmed `
  --plan-confirm-controlled-mutation-confirmed `
  --controlled-rollout-only-confirmed `
  --kill-switch-confirmed `
  --rollback-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C165_OPERATOR_APPROVED_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION `
  --overwrite
```

Expected status:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_PASSED_CONTROLLED_ROLLOUT_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP
```

## Negative Gates

Missing operator approval:

```powershell
php artisan watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-execution `
  --controlled-rollout-execution-confirmed `
  --c165-boundary-locked-confirmed `
  --activated-catalog-read-confirmed `
  --plan-confirm-controlled-mutation-confirmed `
  --controlled-rollout-only-confirmed `
  --kill-switch-confirmed `
  --rollback-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C165_NEGATIVE_MISSING_OPERATOR_APPROVAL `
  --output=storage/app/watchlist/backtest/.tmp-c165-controlled-rollout-execution-missing-approval-command-probe.json `
  --rollout-state-output=storage/app/watchlist/runtime/.tmp-c165-controlled-rollout-state-missing-approval-command-probe.json `
  --overwrite
```

Expected status:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING
```

Missing activated-catalog read confirmation:

```powershell
php artisan watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-execution `
  --operator-approved `
  --controlled-rollout-execution-confirmed `
  --c165-boundary-locked-confirmed `
  --plan-confirm-controlled-mutation-confirmed `
  --controlled-rollout-only-confirmed `
  --kill-switch-confirmed `
  --rollback-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C165_NEGATIVE_MISSING_CATALOG_READ_CONFIRMATION `
  --output=storage/app/watchlist/backtest/.tmp-c165-controlled-rollout-execution-missing-catalog-read-command-probe.json `
  --rollout-state-output=storage/app/watchlist/runtime/.tmp-c165-controlled-rollout-state-missing-catalog-read-command-probe.json `
  --overwrite
```

Expected status:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION_REJECTED_ACTIVATED_CATALOG_READ_CONFIRMATION_MISSING
```

Negative runs must not create a rollout-state artifact.

## Safety Expectations

```text
CONTROLLED_ROLLOUT_EXECUTED=1
CONTROLLED_ROLLOUT_ONLY=1
UNRESTRICTED_ROLLOUT_ALLOWED=0
PLAN_CONFIRM_MUTATED=1
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=1
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=1
PRODUCTION_CONFIG_MUTATED=0
KILL_SWITCH_CONFIRMED=1
ROLLBACK_CONFIRMED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
NEXT_RECOMMENDATION=C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW
```
