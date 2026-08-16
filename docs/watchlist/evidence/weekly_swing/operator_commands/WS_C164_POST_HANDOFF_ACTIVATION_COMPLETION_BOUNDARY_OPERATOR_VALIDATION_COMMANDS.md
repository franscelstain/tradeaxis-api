# C164 Post-Handoff Activation Completion Boundary Operator Validation Commands

Use these commands to validate C164 completion boundary review from the locked C163 GO decision finalization artifact.

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review `
  --operator-approved `
  --completion-boundary-confirmed `
  --c163-topic-complete-confirmed `
  --post-handoff-activation-closed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C164_OPERATOR_APPROVED_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_ONLY `
  --overwrite
```

Expected status:

```text
C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_READY_FOR_COMPLETION_EXECUTION_PRIMARY_AND_BACKUP
```

## Required Source Lock

```text
C163_GO_DECISION_FINALIZATION_ARTIFACT=storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-go-decision-finalization-review.json
C163_GO_DECISION_FINALIZATION_ARTIFACT_HASH=e7a4e300eea57aa5f28a87e5cceb297fd92c195a
C163_GO_DECISION_FINALIZATION_FILE_SHA1=450DC99CAC858CBE08D4E2FB32BC4D9D2F1845B9
```

## Negative Gate Probes

```powershell
php artisan watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review `
  --completion-boundary-confirmed `
  --c163-topic-complete-confirmed `
  --post-handoff-activation-closed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C164_NEGATIVE_MISSING_OPERATOR `
  --output=storage/app/watchlist/backtest/.tmp-c164-probe-missing-operator.json `
  --overwrite

php artisan watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review `
  --operator-approved `
  --c163-topic-complete-confirmed `
  --post-handoff-activation-closed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C164_NEGATIVE_MISSING_BOUNDARY `
  --output=storage/app/watchlist/backtest/.tmp-c164-probe-missing-boundary.json `
  --overwrite

php artisan watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review `
  --operator-approved `
  --completion-boundary-confirmed `
  --post-handoff-activation-closed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C164_NEGATIVE_MISSING_C163_TOPIC `
  --output=storage/app/watchlist/backtest/.tmp-c164-probe-missing-c163-topic.json `
  --overwrite

php artisan watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review `
  --operator-approved `
  --completion-boundary-confirmed `
  --c163-topic-complete-confirmed `
  --post-handoff-activation-closed-confirmed `
  --plan-confirm-unchanged-confirmed `
  --no-live-plan-confirm-rollout-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C164_NEGATIVE_HASH_MISMATCH `
  --expected-c163-finalization-hash=bad-hash `
  --output=storage/app/watchlist/backtest/.tmp-c164-probe-hash-mismatch.json `
  --overwrite
```

Expected rejected statuses:

```text
NEGATIVE_WITHOUT_OPERATOR_APPROVAL=C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_COMPLETION_BOUNDARY_CONFIRMATION=C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_COMPLETION_BOUNDARY_CONFIRMATION_MISSING
NEGATIVE_MISSING_C163_TOPIC_COMPLETE_CONFIRMATION=C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_TOPIC_COMPLETE_CONFIRMATION_MISSING
NEGATIVE_C163_GO_DECISION_FINALIZATION_ARTIFACT_LOCK_MISMATCH=C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_REJECTED_C163_GO_DECISION_FINALIZATION_ARTIFACT_LOCK_MISMATCH
```

## Artifact Sanity Check

```powershell
$p = 'storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review.json'
$j = Get-Content $p -Raw | ConvertFrom-Json
'artifact_hash=' + $j.artifact_hash
'file_sha1=' + (Get-FileHash $p -Algorithm SHA1).Hash
'status=' + $j.status
'next=' + $j.next_step_recommendation
'published=' + $j.weekly_swing_watchlist_official_output_published
'publication_allowed=' + $j.weekly_swing_watchlist_publication_allowed
'unrestricted_publication_allowed=' + $j.weekly_swing_watchlist_unrestricted_publication_allowed
'plan_mutated=' + $j.plan_confirm_mutated
'live_rollout=' + $j.live_plan_confirm_rollout_executed
```

Expected:

```text
artifact_hash=997bb3cc6f5565da92438a2afaca441bb50977b4
file_sha1=2EBE74B5E40E53C60456A4110DF41A29B1D3E1A6
next=C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION
published=False
publication_allowed=False
unrestricted_publication_allowed=False
plan_mutated=False
live_rollout=False
```

Do not use C164 completion boundary output as free publication approval, unrestricted publication approval, PLAN/CONFIRM mutation approval, or live PLAN/CONFIRM rollout execution. It only clears the C164 completion boundary and opens C164 completion execution.
