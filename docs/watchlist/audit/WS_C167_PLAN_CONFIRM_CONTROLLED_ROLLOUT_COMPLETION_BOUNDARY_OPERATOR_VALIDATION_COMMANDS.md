# C167 PLAN/CONFIRM Controlled Rollout Completion Boundary Validation Commands

## Official Boundary

```powershell
php artisan watchlist:backtest-c167-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-completion-boundary-review `
  --operator-approved `
  --approval-reference=C167-CONTROLLED-ROLLOUT-COMPLETION-BOUNDARY-20260719 `
  --controlled-rollout-completion-boundary-confirmed `
  --c166-finalization-locked-confirmed `
  --controlled-rollout-evidence-chain-complete-confirmed `
  --completion-execution-required-confirmed `
  --market-metrics-not-inferred-confirmed `
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
C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_ROLLOUT_COMPLETION_EXECUTION_PRIMARY_AND_BACKUP
```

## Verification

```powershell
$source = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-go-decision-finalization-review.json'
$boundary = 'storage/app/watchlist/backtest/c167-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-completion-boundary-review.json'

(Get-Content -Raw $source | ConvertFrom-Json).artifact_hash
(Get-FileHash $source -Algorithm SHA1).Hash
(Get-Content -Raw $boundary | ConvertFrom-Json).artifact_hash
(Get-FileHash $boundary -Algorithm SHA1).Hash
.\vendor\bin\phpunit --filter C167
```

Expected locks and test result:

```text
299eb7f2978b8755351d28bb299249f0cb0d818f
3E2CF7C226756EFD9F3AADBDDCAE3BD133D174BA
5b1a5efc91cfc56b8b98cadb5802f275cf417394
075A32EBEF7CAF03B5671C9B7BF9BF85A24F8CEF
OK (8 tests, 55 assertions)
```

The boundary must reject altered C166 locks, incomplete operator confirmations, an incomplete C166 topic, changed candidate/function scope, inferred market metrics, new rollout or PLAN/CONFIRM actions, production-configuration mutation, and free or unrestricted publication.
