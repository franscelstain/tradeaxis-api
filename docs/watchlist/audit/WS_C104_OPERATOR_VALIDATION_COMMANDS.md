# WS C104 Operator Validation Commands

Date: 2026-06-30

Positive runtime command:

```powershell
php artisan watchlist:backtest-c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review --c103-artifact=storage/app/watchlist/backtest/c103-weekly-swing-watchlist-non-live-rehearsal-completion-boundary-review.json --expected-c103-hash=60954783fd524694581bd1b4cdb47a71bdcd7bcb --expected-c103-file-sha1=F61E6BAF148D974CEE483D45164E0D5F6BD51376 --approval-reference=C104_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_ONLY --output=storage/app/watchlist/backtest/c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review.json --operator-approved --overwrite --progress
```

Expected positive status:

```text
C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_PASSED_HANDOFF_READY_PRIMARY_AND_BACKUP
```

Negative approval gate commands:

```powershell
php artisan watchlist:backtest-c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review --c103-artifact=storage/app/watchlist/backtest/c103-weekly-swing-watchlist-non-live-rehearsal-completion-boundary-review.json --expected-c103-hash=60954783fd524694581bd1b4cdb47a71bdcd7bcb --expected-c103-file-sha1=F61E6BAF148D974CEE483D45164E0D5F6BD51376 --approval-reference=C104_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_ONLY --output=storage/app/watchlist/backtest/c104-no-operator-approval-test.json --overwrite
php artisan watchlist:backtest-c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review --c103-artifact=storage/app/watchlist/backtest/c103-weekly-swing-watchlist-non-live-rehearsal-completion-boundary-review.json --expected-c103-hash=60954783fd524694581bd1b4cdb47a71bdcd7bcb --expected-c103-file-sha1=F61E6BAF148D974CEE483D45164E0D5F6BD51376 --approval-reference= --output=storage/app/watchlist/backtest/c104-no-approval-reference-test.json --operator-approved --overwrite
```

Expected negative status:

```text
C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Temporary negative artifact cleanup:

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/c104-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c104-no-approval-reference-test.json -ErrorAction SilentlyContinue
Get-ChildItem storage/app/watchlist/backtest -Filter "*no-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*missing-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*mismatch-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*negative-*-test.json"
```

C104 validates C103 artifact hash and file SHA1.
C104 validates C103 weekly swing watchlist non-live rehearsal completion boundary cleared state.
C104 requires --operator-approved.
C104 requires non-empty --approval-reference.
C104 confirms no temporary negative test artifact remains.
C104 marks weekly swing watchlist non-live rehearsal handoff readiness only.
C104 marks handoff ready for E02 and B01 only.
C104 creates artifact-only non-live rehearsal handoff readiness manifest.
C104 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C104 does not deploy live production.
C104 does not mutate PLAN/CONFIRM.
C104 does not change PLAN/CONFIRM output.
C104 does not activate pilot runtime.
C104 does not activate shadow runtime.
C104 does not activate runtime bridge.
C104 does not activate weekly swing watchlist runtime.
C104 does not create weekly swing live output.
C104 does not generate official weekly swing recommendation.
C104 does not publish weekly swing output.
C104 keeps production_ready=false.
C104 keeps production_catalog_runtime_wired=false.
C104 keeps controlled_opt_in_runtime_bridge_active=false.
C104 keeps controlled_parallel_run_active=false.
C104 keeps controlled_rollout_active=false.
C104 keeps weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime=false.
C104 keeps weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime=false.
C104 keeps weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime=false.
C104 keeps weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime=false.
C104 keeps operator_go_no_go_context_persisted_to_live_runtime=false.
C104 keeps weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_persisted_to_live_runtime=false.
C104 keeps go_decision_finalization_context_persisted_to_live_runtime=false.
C104 keeps weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_persisted_to_live_runtime=false.
C104 keeps completion_boundary_context_persisted_to_live_runtime=false.
C104 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_context_persisted_to_live_runtime=false.
C104 keeps handoff_readiness_context_persisted_to_live_runtime=false.
C104 keeps production_deployment_allowed=false.
C104 keeps production_deployment_executed=false.
C104 keeps plan_confirm_mutation_allowed=false.
C104 keeps plan_confirm_mutated=false.
C104 keeps plan_confirm_runtime_reads_activated_catalog=false.
C104 keeps live_plan_confirm_rollout_allowed=false.
C104 keeps live_plan_confirm_rollout_executed=false.
C104 keeps pilot_runtime_active=false.
C104 keeps shadow_runtime_active=false.
C104 keeps runtime_bridge_active=false.
C104 keeps weekly_swing_watchlist_runtime_active=false.
C104 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.
C104 keeps weekly_swing_watchlist_live_output_enabled=false.
C104 keeps weekly_swing_watchlist_official_output_generated=false.
C104 keeps weekly_swing_watchlist_official_output_published=false.
C104 keeps weekly_swing_watchlist_live_recommendation_generated=false.
C104 weekly swing watchlist non-live rehearsal handoff readiness review means continue to C105 weekly swing watchlist non-live rehearsal handoff finalization review only.
C104 handoff readiness record is not production deployment.
C104 handoff readiness record is not PLAN/CONFIRM live rollout.
C104 handoff readiness record is not runtime bridge activation.
C104 handoff readiness record is not weekly swing live output.

Artifact path:

```text
storage/app/watchlist/backtest/c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review.json
```
