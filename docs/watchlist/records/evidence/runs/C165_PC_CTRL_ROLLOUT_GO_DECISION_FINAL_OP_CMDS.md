# C165 PLAN/CONFIRM Controlled Rollout GO Decision Finalization Operator Validation Commands

## Official Finalization

```powershell
php artisan watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-go-decision-finalization-review `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --controlled-rollout-topic-closure-confirmed `
  --operator-go-locked-confirmed `
  --controlled-rollout-result-confirmed `
  --kill-switch-confirmed `
  --rollback-confirmed `
  --production-config-unchanged-confirmed `
  --free-publication-locked-confirmed `
  --post-rollout-observation-required-confirmed `
  --approval-reference=C165_OPERATOR_APPROVED_CONTROLLED_ROLLOUT_GO_FINALIZATION `
  --overwrite `
  --progress
```

Expected status:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_CONTROLLED_ROLLOUT_CLOSED_READY_FOR_POST_ROLLOUT_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP
```

## Lock Verification

```powershell
$operator = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-operator-go-no-go-review.json'
$final = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-go-decision-finalization-review.json'

(Get-Content -Raw $operator | ConvertFrom-Json).artifact_hash
(Get-FileHash $operator -Algorithm SHA1).Hash
(Get-Content -Raw $final | ConvertFrom-Json).artifact_hash
(Get-FileHash $final -Algorithm SHA1).Hash
```

Expected values:

```text
48cd9784bb9df5ceef8b47ca970996398d104f54
5457B6DDA328EF4FD1B0157E5857968D01965381
618a09a64ba295aee023edc8131452782e184a9f
8EBDA0F4267597ED04F7AB798A1B1A227ACE4B9A
```

Finalization must reject a `NO_GO`/`HOLD` source, an already-finalized source, missing closure confirmation, missing observation requirement, altered candidate/function scope, or any publication/configuration safety violation.
