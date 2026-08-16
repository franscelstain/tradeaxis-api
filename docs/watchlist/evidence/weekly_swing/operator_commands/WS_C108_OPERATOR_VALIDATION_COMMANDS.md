# WS_C108_OPERATOR_VALIDATION_COMMANDS

C108 is weekly swing watchlist non-live rehearsal handoff audit archive review.
C108 locks C107 weekly swing watchlist non-live rehearsal handoff closure seal review as source.
E02 is primary non-live rehearsal handoff audit archived candidate.
B01 is backup non-live rehearsal handoff audit archived candidate.
A01 remains comparator-only.

## Required Operator Reading

C108 validates C107 artifact hash and file SHA1.
C108 validates C107 weekly swing watchlist non-live rehearsal handoff closure seal state.
C108 requires --operator-approved.
C108 requires non-empty --approval-reference.
C108 confirms no temporary negative test artifact remains.
C108 archives weekly swing watchlist non-live rehearsal handoff audit trail only.
C108 archives handoff audit trail for E02 and B01 only.
C108 creates artifact-only non-live rehearsal handoff audit archive manifest.
C108 does not wire activated catalog to PLAN/CONFIRM live default runtime.
C108 does not deploy live production.
C108 does not mutate PLAN/CONFIRM.
C108 does not change PLAN/CONFIRM output.
C108 does not activate pilot runtime.
C108 does not activate shadow runtime.
C108 does not activate runtime bridge.
C108 does not activate weekly swing watchlist runtime.
C108 does not create weekly swing live output.
C108 does not generate official weekly swing recommendation.
C108 does not publish weekly swing output.
C108 keeps production_ready=false.
C108 keeps production_catalog_runtime_wired=false.
C108 keeps controlled_opt_in_runtime_bridge_active=false.
C108 keeps controlled_parallel_run_active=false.
C108 keeps controlled_rollout_active=false.
C108 keeps weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_persisted_to_live_runtime=false.
C108 keeps handoff_audit_archive_context_persisted_to_live_runtime=false.
C108 keeps production_deployment_allowed=false.
C108 keeps production_deployment_executed=false.
C108 keeps plan_confirm_mutation_allowed=false.
C108 keeps plan_confirm_mutated=false.
C108 keeps plan_confirm_runtime_reads_activated_catalog=false.
C108 keeps live_plan_confirm_rollout_allowed=false.
C108 keeps live_plan_confirm_rollout_executed=false.
C108 keeps pilot_runtime_active=false.
C108 keeps shadow_runtime_active=false.
C108 keeps runtime_bridge_active=false.
C108 keeps weekly_swing_watchlist_runtime_active=false.
C108 keeps weekly_swing_watchlist_plan_confirm_mutation_allowed=false.
C108 keeps weekly_swing_watchlist_live_output_enabled=false.
C108 keeps weekly_swing_watchlist_official_output_generated=false.
C108 keeps weekly_swing_watchlist_official_output_published=false.
C108 keeps weekly_swing_watchlist_live_recommendation_generated=false.
C108 weekly swing watchlist non-live rehearsal handoff audit archive review means continue to C109 weekly swing watchlist non-live rehearsal handoff audit archive completion review only.
C108 handoff audit archive record is not production deployment.
C108 handoff audit archive record is not PLAN/CONFIRM live rollout.
C108 handoff audit archive record is not runtime bridge activation.
C108 handoff audit archive record is not weekly swing live output.

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c108-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-review `
  --c107-artifact=storage/app/watchlist/backtest/c107-weekly-swing-watchlist-non-live-rehearsal-handoff-closure-seal-review.json `
  --expected-c107-hash=dd9edfc84044eeaa78f83b3fe4980e86ad9be62f `
  --expected-c107-file-sha1=002EAEC0989CA23C7CE713345AEA7CAE8C6622E8 `
  --approval-reference=C108_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c108-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected passing status:

```text
C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP
```

## Negative Approval Gate

Without --operator-approved:

```powershell
php artisan watchlist:backtest-c108-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-review `
  --c107-artifact=storage/app/watchlist/backtest/c107-weekly-swing-watchlist-non-live-rehearsal-handoff-closure-seal-review.json `
  --expected-c107-hash=dd9edfc84044eeaa78f83b3fe4980e86ad9be62f `
  --expected-c107-file-sha1=002EAEC0989CA23C7CE713345AEA7CAE8C6622E8 `
  --approval-reference=C108_OPERATOR_APPROVED_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c108-no-operator-approval-test.json `
  --overwrite
```

Without --approval-reference:

```powershell
php artisan watchlist:backtest-c108-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-review `
  --c107-artifact=storage/app/watchlist/backtest/c107-weekly-swing-watchlist-non-live-rehearsal-handoff-closure-seal-review.json `
  --expected-c107-hash=dd9edfc84044eeaa78f83b3fe4980e86ad9be62f `
  --expected-c107-file-sha1=002EAEC0989CA23C7CE713345AEA7CAE8C6622E8 `
  --output=storage/app/watchlist/backtest/c108-no-approval-reference-test.json `
  --operator-approved `
  --overwrite
```

Expected rejected status:

```text
C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Remove negative artifacts after the gate check:

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/c108-no-operator-approval-test.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/c108-no-approval-reference-test.json -ErrorAction SilentlyContinue
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
vendor/bin/phpunit --filter 'WatchlistBacktestC108'
vendor/bin/phpunit tests/Unit/Watchlist
```

## Runtime Evidence

Runtime evidence appended after local validation.

```text
C108_FOCUSED_PHPUNIT=OK (69 tests, 364 assertions)
C108_FULL_WATCHLIST_PHPUNIT_POST_C108=OK (2435 tests, 28894 assertions)
C108_RUNTIME_STATUS=C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP
C108_RUNTIME_REASON_CODE=C108_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_REVIEW_PASSED_AUDIT_ARCHIVED_PRIMARY_AND_BACKUP
C108_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c108-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-review.json
C108_ARTIFACT_HASH=e7b6f6f94a40d1fe825bc0224b686d11e7510e94
C108_ARTIFACT_FILE_SHA1=591BF25C2A1E7678B2C9335ECBEF1938BDAF990C
C108_SOURCE_LOCK=C107
EXPECTED_C107_HASH=dd9edfc84044eeaa78f83b3fe4980e86ad9be62f
ACTUAL_C107_HASH=dd9edfc84044eeaa78f83b3fe4980e86ad9be62f
C107_HASH_MATCH=1
EXPECTED_C107_FILE_SHA1=002EAEC0989CA23C7CE713345AEA7CAE8C6622E8
ACTUAL_C107_FILE_SHA1=002EAEC0989CA23C7CE713345AEA7CAE8C6622E8
C107_FILE_SHA1_MATCH=1
C108_NEGATIVE_APPROVAL_GATE=PASS_REJECTED_OPERATOR_APPROVAL_MISSING
C108_TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_OUTPUT
C108_SAFETY_BOUNDARY=NON_LIVE_ARTIFACT_ONLY_HANDOFF_AUDIT_ARCHIVED_PRODUCTION_DISABLED_PLAN_CONFIRM_UNCHANGED_WEEKLY_LIVE_OUTPUT_DISABLED
C108_NEXT_RECOMMENDATION=C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW
```
