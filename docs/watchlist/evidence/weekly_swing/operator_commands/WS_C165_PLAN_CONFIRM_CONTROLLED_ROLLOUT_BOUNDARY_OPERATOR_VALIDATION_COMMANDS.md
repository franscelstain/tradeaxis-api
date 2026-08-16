# WS C165 PLAN/CONFIRM Controlled Rollout Boundary Validation Commands

## Positive Run

```powershell
php artisan watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-boundary-review `
  --operator-approved `
  --controlled-rollout-boundary-confirmed `
  --c164-finalization-locked-confirmed `
  --controlled-rollout-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-rollout-executed-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C165_OPERATOR_APPROVED_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW `
  --overwrite
```

Expected status:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_ROLLOUT_EXECUTION_PRIMARY_AND_BACKUP
```

## Required Locks

```text
C164_FINALIZATION_ARTIFACT_HASH=63c7512cb6d395bc6268dae385a10ae703e4aa3d
C164_FINALIZATION_FILE_SHA1=9CA9F2F36F15F17C15301E9F119C303088EDD163
```

## Negative Gates

Missing operator approval:

```powershell
php artisan watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-boundary-review `
  --controlled-rollout-boundary-confirmed `
  --c164-finalization-locked-confirmed `
  --controlled-rollout-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-rollout-executed-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C165_NEGATIVE_MISSING_OPERATOR_APPROVAL `
  --output=storage/app/watchlist/backtest/.tmp-c165-controlled-rollout-boundary-missing-approval-command-probe.json `
  --overwrite
```

Expected status:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Missing boundary confirmation:

```powershell
php artisan watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-boundary-review `
  --operator-approved `
  --c164-finalization-locked-confirmed `
  --controlled-rollout-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-rollout-executed-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C165_NEGATIVE_MISSING_BOUNDARY_CONFIRMATION `
  --output=storage/app/watchlist/backtest/.tmp-c165-controlled-rollout-boundary-missing-confirmation-command-probe.json `
  --overwrite
```

Expected status:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_REJECTED_CONTROLLED_ROLLOUT_BOUNDARY_CONFIRMATION_MISSING
```

## Safety Expectations

```text
CONTROLLED_ROLLOUT_BOUNDARY_OPEN=1
CONTROLLED_ROLLOUT_EXECUTION_ALLOWED_NEXT=1
WATCHLIST_FUNCTION_USED=CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION
PRIMARY_AND_BACKUP_ONLY=1
A01_REMAINS_COMPARATOR_ONLY=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
LIVE_PLAN_CONFIRM_ROLLOUT_ALLOWED=0
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=0
NEXT_RECOMMENDATION=C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_EXECUTION
```
