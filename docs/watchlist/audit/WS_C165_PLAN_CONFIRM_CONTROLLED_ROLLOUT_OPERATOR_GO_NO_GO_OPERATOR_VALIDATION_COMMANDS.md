# C165 PLAN/CONFIRM Controlled Rollout Operator GO/NO-GO Validation Commands

## Official GO Decision

```powershell
php artisan watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-operator-go-no-go-review `
  --operator-approved `
  --operator-decision-confirmed `
  --operator-decision=GO `
  --decision-reason="C165 controlled rollout result is stable for same-topic GO decision finalization." `
  --approval-reference=C165_OPERATOR_APPROVED_CONTROLLED_ROLLOUT_GO `
  --result-review-locked-confirmed `
  --controlled-rollout-result-confirmed `
  --controlled-rollout-only-confirmed `
  --candidate-scope-confirmed `
  --kill-switch-confirmed `
  --rollback-confirmed `
  --production-config-unchanged-confirmed `
  --free-publication-locked-confirmed `
  --overwrite `
  --progress
```

Expected status:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_GO_DECISION_FINALIZATION_REVIEW
```

## Alternative Completed Decisions

`--operator-decision=NO_GO` must complete with progression stopped and must not open finalization:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_CONTROLLED_ROLLOUT_PROGRESSION_STOPPED
```

`--operator-decision=HOLD` must complete with progression deferred and must not open finalization:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_CONTROLLED_ROLLOUT_PROGRESSION_DEFERRED
```

## Lock Verification

```powershell
$source = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-result-review.json'
$operator = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-operator-go-no-go-review.json'

(Get-Content -Raw $source | ConvertFrom-Json).artifact_hash
(Get-FileHash $source -Algorithm SHA1).Hash
(Get-Content -Raw $operator | ConvertFrom-Json).artifact_hash
(Get-FileHash $operator -Algorithm SHA1).Hash
```

Expected values:

```text
a30b5b0eeab344e0d0283cb4164fd2a27b234802
664A639A2C8338F407BB0B34B9648733A0F6C94E
48cd9784bb9df5ceef8b47ca970996398d104f54
5457B6DDA328EF4FD1B0157E5857968D01965381
```

Missing approval, decision confirmation, decision reason, source lock confirmation, controlled-only confirmation, kill switch, rollback, production-config guard, or free-publication lock must be rejected.
