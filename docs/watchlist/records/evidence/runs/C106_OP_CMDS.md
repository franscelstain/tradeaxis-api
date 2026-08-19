# WS_C106_OPERATOR_VALIDATION_COMMANDS

C106 is weekly swing watchlist non-live rehearsal handoff completion boundary review.
C106 locks C105 weekly swing watchlist non-live rehearsal handoff finalization review as source.
E02 is primary non-live rehearsal handoff completion boundary cleared candidate.
B01 is backup non-live rehearsal handoff completion boundary cleared candidate.
A01 remains comparator-only.

## Required Operator Reading

C106 validates C105 artifact hash and file SHA1.
C106 validates C105 weekly swing watchlist non-live rehearsal handoff finalization state.
C106 requires --operator-approved.
C106 requires non-empty --approval-reference.
C106 confirms no temporary negative test artifact remains.
C106 clears weekly swing watchlist non-live rehearsal handoff completion boundary only.
C106 clears handoff completion boundary for E02 and B01 only.
C106 creates artifact-only non-live rehearsal handoff completion boundary manifest.
C106 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C106 does not deploy live production.
C106 does not mutate PLAN/CONFIRM.
C106 does not change PLAN/CONFIRM output.
C106 does not activate pilot runtime.
C106 does not activate shadow runtime.
C106 does not activate runtime bridge.
C106 does not activate weekly swing watchlist runtime.
C106 does not create weekly swing live output.
C106 does not generate official weekly swing recommendation.
C106 does not publish weekly swing output.
C106 keeps production_ready=false.
C106 keeps production_catalog_runtime_wired=false.
C106 keeps controlled_opt_in_runtime_bridge_active=false.
C106 keeps controlled_parallel_run_active=false.
C106 keeps controlled_rollout_active=false.
C106 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_context_persisted_to_live_runtime=false.
C106 keeps handoff_completion_boundary_context_persisted_to_live_runtime=false.
C106 keeps production_deployment_allowed=false.
C106 keeps production_deployment_executed=false.
C106 keeps plan_confirm_mutation_allowed=false.
C106 keeps plan_confirm_mutated=false.
C106 keeps plan_confirm_runtime_reads_activated_catalog=false.
C106 keeps live_plan_confirm_rollout_allowed=false.
C106 keeps live_plan_confirm_rollout_executed=false.
C106 keeps pilot_runtime_active=false.
C106 keeps shadow_runtime_active=false.
C106 keeps runtime_bridge_active=false.
C106 keeps weekly_swing_watchlist_runtime_active=false.
C106 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.
C106 keeps weekly_swing_watchlist_live_output_enabled=false.
C106 keeps weekly_swing_watchlist_official_output_generated=false.
C106 keeps weekly_swing_watchlist_official_output_published=false.
C106 keeps weekly_swing_watchlist_live_recommendation_generated=false.
C106 weekly swing watchlist non-live rehearsal handoff completion boundary review means continue to C107 weekly swing watchlist non-live rehearsal handoff closure seal review only.
C106 handoff completion boundary record is not production deployment.
C106 handoff completion boundary record is not PLAN/CONFIRM live rollout.
C106 handoff completion boundary record is not runtime bridge activation.
C106 handoff completion boundary record is not weekly swing live output.

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c106-weekly-swing-watchlist-non-live-rehearsal-handoff-completion-boundary-review `
  --c105-artifact=storage/app/watchlist/backtest/c105-weekly-swing-watchlist-non-live-rehearsal-handoff-finalization-review.json `
  --expected-c105-hash=dd53320a0cdaaa2d0c19773a331baa3cae6e29eb `
  --expected-c105-file-sha1=E2DA749D416094BCE061A38CD6A24C9E34F753CA `
  --approval-reference=C106_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c106-weekly-swing-watchlist-non-live-rehearsal-handoff-completion-boundary-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected passing status:

```text
C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
```

## Negative Approval Gate

Without --operator-approved:

```powershell
php artisan watchlist:backtest-c106-weekly-swing-watchlist-non-live-rehearsal-handoff-completion-boundary-review `
  --c105-artifact=storage/app/watchlist/backtest/c105-weekly-swing-watchlist-non-live-rehearsal-handoff-finalization-review.json `
  --expected-c105-hash=dd53320a0cdaaa2d0c19773a331baa3cae6e29eb `
  --expected-c105-file-sha1=E2DA749D416094BCE061A38CD6A24C9E34F753CA `
  --approval-reference=C106_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c106-no-operator-approval-test.json `
  --overwrite
```

Without --approval-reference:

```powershell
php artisan watchlist:backtest-c106-weekly-swing-watchlist-non-live-rehearsal-handoff-completion-boundary-review `
  --c105-artifact=storage/app/watchlist/backtest/c105-weekly-swing-watchlist-non-live-rehearsal-handoff-finalization-review.json `
  --expected-c105-hash=dd53320a0cdaaa2d0c19773a331baa3cae6e29eb `
  --expected-c105-file-sha1=E2DA749D416094BCE061A38CD6A24C9E34F753CA `
  --output=storage/app/watchlist/backtest/c106-no-approval-reference-test.json `
  --operator-approved `
  --overwrite
```

Expected rejected status:

```text
C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove negative artifacts after the gate check:

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/c106-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c106-no-approval-reference-test.json -ErrorAction SilentlyContinue
Get-ChildItem storage/app/watchlist/backtest -Filter '*no-*-test.json'
Get-ChildItem storage/app/watchlist/backtest -Filter '*missing-*-test.json'
Get-ChildItem storage/app/watchlist/backtest -Filter '*mismatch-*-test.json'
Get-ChildItem storage/app/watchlist/backtest -Filter '*negative-*-test.json'
```

Expected cleanup result:

```text
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_OUTPUT
```

## PHPUnit

```powershell
vendor/bin/phpunit --filter 'WatchlistBacktestC106'
vendor/bin/phpunit tests/Unit/Watchlist
```

## Runtime Evidence

Runtime evidence appended after local validation.

```text
C106_FOCUSED_PHPUNIT=OK (65 tests, 338 assertions)
C106_FULL_WATCHLIST_PHPUNIT_POST_C106=OK (2298 tests, 28181 assertions)
C106_RUNTIME_STATUS=C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
C106_RUNTIME_REASON_CODE=C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED_BOUNDARY_CLEARED_PRIMARY_AND_BACKUP
C106_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c106-weekly-swing-watchlist-non-live-rehearsal-handoff-completion-boundary-review.json
C106_ARTIFACT_HASH=49b2a80cbd714a62418bcf452776514df2ee19ea
C106_ARTIFACT_FILE_SHA1=B2AA8A78A7A320D8F616EAEE6AA5362B63FFA2BD
C106_SOURCE_LOCK=C105
EXPECTED_C105_HASH=dd53320a0cdaaa2d0c19773a331baa3cae6e29eb
ACTUAL_C105_HASH=dd53320a0cdaaa2d0c19773a331baa3cae6e29eb
C105_HASH_MATCH=1
EXPECTED_C105_FILE_SHA1=E2DA749D416094BCE061A38CD6A24C9E34F753CA
ACTUAL_C105_FILE_SHA1=E2DA749D416094BCE061A38CD6A24C9E34F753CA
C105_FILE_SHA1_MATCH=1
C106_NEGATIVE_APPROVAL_GATE=PASS_REJECTED_OPERATOR_APPROVAL_MISSING
C106_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_OUTPUT
C106_SAFETY_BOUNDARY=NON_LIVE_ARTIFACT_ONLY_HANDOFF_COMPLETION_BOUNDARY_CLEARED_PRODUCTION_DISABLED_PLAN_CONFIRM_UNCHANGED_WEEKLY_LIVE_OUTPUT_DISABLED
C106_NEXT_RECOMMENDATION=C107_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_CLOSURE_SEAL_REVIEW
```
