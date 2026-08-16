# WS C164 Post-Handoff Activation Completion GO Decision Finalization Validation Commands

## Positive Run

```powershell
php artisan watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-go-decision-finalization-review `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --post-handoff-activation-completion-finalization-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C164_OPERATOR_APPROVED_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_CONTROLLED_EVIDENCE_ONLY `
  --overwrite
```

Expected status:

```text
C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_POST_HANDOFF_ACTIVATION_COMPLETION_CLOSED_READY_FOR_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP
```

## Required Locks

```text
C164_OPERATOR_ARTIFACT_HASH=df6957364fb3090d64ce767990fdab3964e2573d
C164_OPERATOR_FILE_SHA1=3F6C5BCD92864B89CDF2A974FD0C9F9367EDCD2C
CONTROLLED_COMPLETION_HASH=e9862d9e7738d0558f107d978f329f97f14b3520
CONTROLLED_COMPLETION_FILE_SHA1=AB9FC9F714339B78D68132222AC8C398BE7EE1B3
```

## Negative Gates

Missing operator approval:

```powershell
php artisan watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-go-decision-finalization-review `
  --go-decision-finalization-confirmed `
  --post-handoff-activation-completion-finalization-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C164_NEGATIVE_MISSING_OPERATOR_APPROVAL `
  --output=storage/app/watchlist/backtest/.tmp-c164-go-decision-finalization-missing-approval-command-probe.json `
  --overwrite
```

Expected status:

```text
C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Missing GO finalization confirmation:

```powershell
php artisan watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-go-decision-finalization-review `
  --operator-approved `
  --post-handoff-activation-completion-finalization-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C164_NEGATIVE_MISSING_GO_FINALIZATION_CONFIRMATION `
  --output=storage/app/watchlist/backtest/.tmp-c164-go-decision-finalization-missing-go-confirmation-command-probe.json `
  --overwrite
```

Expected status:

```text
C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING
```

## Safety Expectations

```text
WATCHLIST_FUNCTION_USED=CONTROLLED_WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION
PRIMARY_AND_BACKUP_ONLY=1
A01_REMAINS_COMPARATOR_ONLY=1
C164_TOPIC_COMPLETE_AFTER_FINALIZATION=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=0
NEXT_RECOMMENDATION=C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW
```
