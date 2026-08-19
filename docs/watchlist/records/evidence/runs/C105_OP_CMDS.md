# WS C105 Operator Validation Commands

Date: 2026-06-30

Positive runtime command:

```powershell
php artisan watchlist:backtest-c105-weekly-swing-watchlist-non-live-rehearsal-handoff-finalization-review --c104-artifact=storage/app/watchlist/backtest/c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review.json --expected-c104-hash=9949422cda0ff224c7b441cdd0dd02bfb6c694a4 --expected-c104-file-sha1=08F7A41BDB04E4B40562C855230FDC170E8A2335 --approval-reference=C105_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW_ONLY --output=storage/app/watchlist/backtest/c105-weekly-swing-watchlist-non-live-rehearsal-handoff-finalization-review.json --operator-approved --overwrite --progress
```

Expected positive status:

```text
C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW_PASSED_HANDOFF_FINALIZED_PRIMARY_AND_BACKUP
```

Negative approval gate commands:

```powershell
php artisan watchlist:backtest-c105-weekly-swing-watchlist-non-live-rehearsal-handoff-finalization-review --c104-artifact=storage/app/watchlist/backtest/c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review.json --expected-c104-hash=9949422cda0ff224c7b441cdd0dd02bfb6c694a4 --expected-c104-file-sha1=08F7A41BDB04E4B40562C855230FDC170E8A2335 --approval-reference=C105_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW_ONLY --output=storage/app/watchlist/backtest/c105-no-operator-approval-test.json --overwrite
php artisan watchlist:backtest-c105-weekly-swing-watchlist-non-live-rehearsal-handoff-finalization-review --c104-artifact=storage/app/watchlist/backtest/c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review.json --expected-c104-hash=9949422cda0ff224c7b441cdd0dd02bfb6c694a4 --expected-c104-file-sha1=08F7A41BDB04E4B40562C855230FDC170E8A2335 --approval-reference= --output=storage/app/watchlist/backtest/c105-no-approval-reference-test.json --operator-approved --overwrite
```

Expected negative status:

```text
C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Temporary negative artifact cleanup:

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/c105-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c105-no-approval-reference-test.json -ErrorAction SilentlyContinue
Get-ChildItem storage/app/watchlist/backtest -Filter "*no-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*missing-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*mismatch-*-test.json"
Get-ChildItem storage/app/watchlist/backtest -Filter "*negative-*-test.json"
```

C105 validates C104 artifact hash and file SHA1.
C105 validates C104 weekly swing watchlist non-live rehearsal handoff readiness state.
C105 requires --operator-approved.
C105 requires non-empty --approval-reference.
C105 confirms no temporary negative test artifact remains.
C105 finalizes weekly swing watchlist non-live rehearsal handoff package only.
C105 finalizes handoff for E02 and B01 only.
C105 creates artifact-only non-live rehearsal handoff finalization manifest.
C105 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C105 does not deploy live production.
C105 does not mutate PLAN/CONFIRM.
C105 does not change PLAN/CONFIRM output.
C105 does not activate pilot runtime.
C105 does not activate shadow runtime.
C105 does not activate runtime bridge.
C105 does not activate weekly swing watchlist runtime.
C105 does not create weekly swing live output.
C105 does not generate official weekly swing recommendation.
C105 does not publish weekly swing output.
C105 keeps production_ready=false.
C105 keeps production_catalog_runtime_wired=false.
C105 keeps controlled_opt_in_runtime_bridge_active=false.
C105 keeps controlled_parallel_run_active=false.
C105 keeps controlled_rollout_active=false.
C105 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_context_persisted_to_live_runtime=false.
C105 keeps handoff_finalization_context_persisted_to_live_runtime=false.
C105 keeps production_deployment_allowed=false.
C105 keeps production_deployment_executed=false.
C105 keeps plan_confirm_mutation_allowed=false.
C105 keeps plan_confirm_mutated=false.
C105 keeps plan_confirm_runtime_reads_activated_catalog=false.
C105 keeps live_plan_confirm_rollout_allowed=false.
C105 keeps live_plan_confirm_rollout_executed=false.
C105 keeps pilot_runtime_active=false.
C105 keeps shadow_runtime_active=false.
C105 keeps runtime_bridge_active=false.
C105 keeps weekly_swing_watchlist_runtime_active=false.
C105 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.
C105 keeps weekly_swing_watchlist_live_output_enabled=false.
C105 keeps weekly_swing_watchlist_official_output_generated=false.
C105 keeps weekly_swing_watchlist_official_output_published=false.
C105 keeps weekly_swing_watchlist_live_recommendation_generated=false.
C105 weekly swing watchlist non-live rehearsal handoff finalization review means continue to C106 weekly swing watchlist non-live rehearsal handoff completion boundary review only.
C105 handoff finalization record is not production deployment.
C105 handoff finalization record is not PLAN/CONFIRM live rollout.
C105 handoff finalization record is not runtime bridge activation.
C105 handoff finalization record is not weekly swing live output.

Artifact path:

```text
storage/app/watchlist/backtest/c105-weekly-swing-watchlist-non-live-rehearsal-handoff-finalization-review.json
```
