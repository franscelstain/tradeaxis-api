# C166 PLAN/CONFIRM Controlled Rollout Post-Rollout Observation Operator GO/NO-GO Validation Commands

## Official GO Decision

```powershell
php artisan watchlist:backtest-c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-operator-go-no-go-review `
  --operator-approved `
  --operator-decision=GO `
  --operator-decision-confirmed `
  --decision-reason="C166 control-plane observation result is valid for same-topic GO decision finalization without market-metric inference." `
  --approval-reference=C166_OPERATOR_APPROVED_POST_ROLLOUT_OBSERVATION_GO `
  --result-review-locked-confirmed `
  --post-rollout-observation-result-confirmed `
  --control-plane-result-confirmed `
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
C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_GO_DECISION_FINALIZATION_REVIEW
```

`--operator-decision=NO_GO` must produce a completed stop decision. `--operator-decision=HOLD` must produce a completed deferred decision. Neither may open finalization.

## Lock Verification

```powershell
$result = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-result-review.json'
$operator = 'storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-operator-go-no-go-review.json'

(Get-Content -Raw $result | ConvertFrom-Json).artifact_hash
(Get-FileHash $result -Algorithm SHA1).Hash
(Get-Content -Raw $operator | ConvertFrom-Json).artifact_hash
(Get-FileHash $operator -Algorithm SHA1).Hash
```

Expected values:

```text
1dbd61b08afb2d45918cc66a16c782983cfd6666
2555E1C7612C066FBF60342D0235AE399CB23253
20b00b9c2c53e33eee4f1501e8fddc7c8c379dda
3158EDB0120527909C12A557C36C2EC28C91B209
```

The operator gate must reject altered locks, incomplete result evidence, invalid or unconfirmed decisions, empty reasons, missing safety confirmations, candidate/function scope changes, market-metric inference, new runtime action, configuration mutation, or free publication.
