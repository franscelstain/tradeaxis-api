# C166 PLAN/CONFIRM Controlled Rollout Post-Rollout Observation Operator Validation Commands

## Official Observation

```powershell
php artisan watchlist:backtest-c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-review `
  --operator-approved `
  --post-rollout-observation-confirmed `
  --controlled-rollout-state-observation-confirmed `
  --observation-window-confirmed `
  --candidate-scope-confirmed `
  --kill-switch-confirmed `
  --rollback-confirmed `
  --production-config-unchanged-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C166_OPERATOR_APPROVED_POST_ROLLOUT_OBSERVATION `
  --overwrite `
  --progress
```

Expected status:

```text
C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_REVIEW_PASSED_CONTROLLED_ROLLOUT_OBSERVED_READY_FOR_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_PRIMARY_AND_BACKUP
```

## Lock Verification

```powershell
$final = 'storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-go-decision-finalization-review.json'
$state = 'storage/app/watchlist/runtime/c165-weekly-swing-watchlist-plan-confirm-controlled-rollout-state.json'
$observation = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-review.json'

(Get-Content -Raw $final | ConvertFrom-Json).artifact_hash
(Get-FileHash $final -Algorithm SHA1).Hash
(Get-Content -Raw $state | ConvertFrom-Json).artifact_hash
(Get-FileHash $state -Algorithm SHA1).Hash
(Get-Content -Raw $observation | ConvertFrom-Json).artifact_hash
(Get-FileHash $observation -Algorithm SHA1).Hash
```

Expected values:

```text
618a09a64ba295aee023edc8131452782e184a9f
8EBDA0F4267597ED04F7AB798A1B1A227ACE4B9A
3a8350955f6a1396f5225af3fddcfa31fa622904
4B58D3A17B56136CF02BE1635FB2F16F12831722
9ffec96e1a08e927c5ad14445d6e6d038528a7f2
D9AF66D1488F3BA14134820647E8C1A288C75525
```

The observation must reject altered source locks, inactive or unrestricted rollout state, changed candidate/function scope, A01 promotion, unavailable safety controls, configuration mutation, or free publication. A passing observation does not prove market performance; it authorizes only same-topic observation result review.
