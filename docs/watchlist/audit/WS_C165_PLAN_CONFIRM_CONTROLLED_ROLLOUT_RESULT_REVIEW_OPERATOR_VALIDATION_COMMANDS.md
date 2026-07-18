# C165 PLAN/CONFIRM Controlled Rollout Result Review Operator Validation Commands

## Positive Review

```powershell
php artisan watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-result-review `
  --operator-approved `
  --result-review-confirmed `
  --controlled-rollout-execution-result-confirmed `
  --rollout-state-locked-confirmed `
  --controlled-rollout-only-confirmed `
  --candidate-scope-confirmed `
  --kill-switch-confirmed `
  --rollback-confirmed `
  --production-config-unchanged-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C165_OPERATOR_APPROVED_CONTROLLED_ROLLOUT_RESULT_REVIEW `
  --overwrite `
  --progress
```

Expected status:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_PASSED_READY_FOR_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP
```

## Source Lock Verification

```powershell
$execution = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-execution.json'
$state = 'storage/app/watchlist/runtime/c165-weekly-swing-watchlist-plan-confirm-controlled-rollout-state.json'

(Get-Content -Raw $execution | ConvertFrom-Json).artifact_hash
(Get-FileHash $execution -Algorithm SHA1).Hash
(Get-Content -Raw $state | ConvertFrom-Json).rollout_state_hash
(Get-FileHash $state -Algorithm SHA1).Hash
```

Expected values:

```text
73dc9758d1baad52e7a8e56f6e0058e99b9f71f7
10B76E055119D1A9049F2D9EBA858E1B71A552BE
3a8350955f6a1396f5225af3fddcfa31fa622904
4B58D3A17B56136CF02BE1635FB2F16F12831722
```

## Negative Controls

Omit `--operator-approved` and verify rejection:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Omit `--rollout-state-locked-confirmed` and verify rejection:

```text
C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_RESULT_REVIEW_REJECTED_ROLLOUT_STATE_LOCK_CONFIRMATION_MISSING
```

These negative paths must not change the execution artifact, rollout state, production configuration, or publication state.
