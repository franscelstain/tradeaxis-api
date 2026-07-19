# C166 PLAN/CONFIRM Controlled Rollout Post-Rollout Observation Result Review Operator Validation Commands

## Official Result Review

```powershell
php artisan watchlist:backtest-c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-result-review `
  --operator-approved `
  --result-review-confirmed `
  --post-rollout-observation-result-confirmed `
  --observation-artifact-locked-confirmed `
  --control-plane-snapshot-confirmed `
  --candidate-scope-confirmed `
  --kill-switch-confirmed `
  --rollback-confirmed `
  --production-config-unchanged-confirmed `
  --free-publication-locked-confirmed `
  --market-metrics-not-inferred-confirmed `
  --approval-reference=C166_OPERATOR_APPROVED_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW `
  --overwrite `
  --progress
```

Expected status:

```text
C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_RESULT_REVIEW_PASSED_READY_FOR_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP
```

## Lock Verification

```powershell
$observation = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-review.json'
$result = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-result-review.json'

(Get-Content -Raw $observation | ConvertFrom-Json).artifact_hash
(Get-FileHash $observation -Algorithm SHA1).Hash
(Get-Content -Raw $result | ConvertFrom-Json).artifact_hash
(Get-FileHash $result -Algorithm SHA1).Hash
```

Expected values:

```text
9ffec96e1a08e927c5ad14445d6e6d038528a7f2
D9AF66D1488F3BA14134820647E8C1A288C75525
1dbd61b08afb2d45918cc66a16c782983cfd6666
2555E1C7612C066FBF60342D0235AE399CB23253
```

The review must reject altered source locks, invalid status/phase/next-stage evidence, changed function or candidate scope, unavailable market-metric inference, safety-control loss, configuration mutation, new rollout activity, or free publication. A passing review authorizes only same-topic operator GO/NO-GO review.
