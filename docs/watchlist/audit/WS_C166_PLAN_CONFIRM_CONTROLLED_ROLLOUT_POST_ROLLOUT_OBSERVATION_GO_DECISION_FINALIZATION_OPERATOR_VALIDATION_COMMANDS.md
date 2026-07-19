# C166 PLAN/CONFIRM Controlled Rollout Post-Rollout Observation GO Decision Finalization Validation Commands

## Official Finalization

```powershell
php artisan watchlist:backtest-c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-go-decision-finalization-review `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --post-rollout-observation-topic-closure-confirmed `
  --operator-go-locked-confirmed `
  --post-rollout-observation-result-confirmed `
  --control-plane-result-confirmed `
  --market-metrics-not-inferred-confirmed `
  --candidate-scope-confirmed `
  --kill-switch-confirmed `
  --rollback-confirmed `
  --production-config-unchanged-confirmed `
  --free-publication-locked-confirmed `
  --controlled-rollout-completion-boundary-required-confirmed `
  --approval-reference=C166_OPERATOR_APPROVED_POST_ROLLOUT_OBSERVATION_GO_FINALIZATION `
  --overwrite `
  --progress
```

Expected status:

```text
C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_ROLLOUT_OBSERVATION_CLOSED_READY_FOR_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP
```

## Lock Verification

```powershell
$operator = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-operator-go-no-go-review.json'
$final = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-go-decision-finalization-review.json'

(Get-Content -Raw $operator | ConvertFrom-Json).artifact_hash
(Get-FileHash $operator -Algorithm SHA1).Hash
(Get-Content -Raw $final | ConvertFrom-Json).artifact_hash
(Get-FileHash $final -Algorithm SHA1).Hash
```

Expected values:

```text
20b00b9c2c53e33eee4f1501e8fddc7c8c379dda
3158EDB0120527909C12A557C36C2EC28C91B209
299eb7f2978b8755351d28bb299249f0cb0d818f
3E2CF7C226756EFD9F3AADBDDCAE3BD133D174BA
```

Finalization must reject altered locks, `NO_GO` or `HOLD`, an already-finalized source, incomplete closure evidence, unavailable market-metric inference, changed candidate/function scope, safety-control loss, runtime action, configuration mutation, or free publication.
