# C150 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution `
  --c149-artifact=storage/app/watchlist/backtest/c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --expected-c149-hash=311898597454a6a1984f4ed84473ad52ba6859fb `
  --expected-c149-file-sha1=3B14776D36FBC922782B332BDC55CE90B50188E5 `
  --activation-reference=C150_OPERATOR_APPROVED_FINAL_RUNTIME_ACTIVATION_EXECUTION `
  --output=storage/app/watchlist/backtest/c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution.json `
  --runtime-state=storage/app/watchlist/runtime/weekly-swing-watchlist-production-live-runtime-activation-state.json `
  --operator-approved `
  --enable-runtime-bridge `
  --enable-live-output `
  --confirm-rollback `
  --confirm-kill-switch `
  --overwrite `
  --overwrite-runtime-state `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution `
  --c149-artifact=storage/app/watchlist/backtest/c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --expected-c149-hash=311898597454a6a1984f4ed84473ad52ba6859fb `
  --expected-c149-file-sha1=3B14776D36FBC922782B332BDC55CE90B50188E5 `
  --activation-reference=C150_NO_OPERATOR_TEST `
  --output=storage/app/watchlist/backtest/c150-no-operator-test.json `
  --runtime-state=storage/app/watchlist/runtime/c150-no-operator-test-state.json `
  --enable-runtime-bridge `
  --enable-live-output `
  --confirm-rollback `
  --confirm-kill-switch `
  --overwrite `
  --overwrite-runtime-state
```

Expected rejection:

```text
C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution `
  --c149-artifact=storage/app/watchlist/backtest/c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --expected-c149-hash=311898597454a6a1984f4ed84473ad52ba6859fb `
  --expected-c149-file-sha1=3B14776D36FBC922782B332BDC55CE90B50188E5 `
  --activation-reference=C150_MISSING_ENABLEMENT_TEST `
  --output=storage/app/watchlist/backtest/c150-missing-enable-test.json `
  --runtime-state=storage/app/watchlist/runtime/c150-missing-enable-test-state.json `
  --operator-approved `
  --enable-live-output `
  --confirm-rollback `
  --confirm-kill-switch `
  --overwrite `
  --overwrite-runtime-state
```

Expected rejection:

```text
C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_EXPLICIT_RUNTIME_ENABLEMENT_MISSING
```

```powershell
php artisan watchlist:backtest-c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution `
  --c149-artifact=storage/app/watchlist/backtest/c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --expected-c149-hash=311898597454a6a1984f4ed84473ad52ba6859fb `
  --expected-c149-file-sha1=3B14776D36FBC922782B332BDC55CE90B50188E5 `
  --activation-reference=C150_MISSING_ROLLBACK_TEST `
  --output=storage/app/watchlist/backtest/c150-missing-rollback-test.json `
  --runtime-state=storage/app/watchlist/runtime/c150-missing-rollback-test-state.json `
  --operator-approved `
  --enable-runtime-bridge `
  --enable-live-output `
  --confirm-kill-switch `
  --overwrite `
  --overwrite-runtime-state
```

Expected rejection:

```text
C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_ROLLBACK_OR_KILL_SWITCH_CONFIRMATION_MISSING
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_PASSED_LIVE_RUNTIME_BRIDGE_ACTIVE_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c150-weekly-swing-watchlist-production-live-runtime-activation-final-execution.json
POSITIVE_RUNTIME_ARTIFACT_HASH=0b3b5e57011d8d98fcd38c004fb8d94fb33ca9ad
POSITIVE_RUNTIME_FILE_SHA1=E25A4E0DF40F9E01E6B3270F2AE2C5FF1CF0A500
POSITIVE_RUNTIME_STATE=storage/app/watchlist/runtime/weekly-swing-watchlist-production-live-runtime-activation-state.json
POSITIVE_RUNTIME_STATE_HASH=00cb935a8252efe340d5f6ec6ea6966d9645cff7
POSITIVE_RUNTIME_STATE_FILE_SHA1=17E41FFC5C6EE00CCCB4DF555A22EF192F2FCCF4
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C150=OK (27 tests, 109 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C150=OK (5398 tests, 41043 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_RUNTIME_ENABLEMENT_EXIT_CODE=1
NEGATIVE_MISSING_RUNTIME_ENABLEMENT_STATUS=C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_EXPLICIT_RUNTIME_ENABLEMENT_MISSING
NEGATIVE_MISSING_ROLLBACK_OR_KILL_SWITCH_EXIT_CODE=1
NEGATIVE_MISSING_ROLLBACK_OR_KILL_SWITCH_STATUS=C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_REJECTED_ROLLBACK_OR_KILL_SWITCH_CONFIRMATION_MISSING
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C150_TEST_ARTIFACTS_REMAINING
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=1
PRODUCTION_READY=1
PRODUCTION_CATALOG_RUNTIME_WIRED=1
PRODUCTION_RUNTIME_WIRING_EXECUTED=1
RUNTIME_BRIDGE_ACTIVE=1
WEEKLY_SWING_WATCHLIST_RUNTIME_ACTIVE=1
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=1
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATION_ALLOWED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
NEXT_RECOMMENDATION=C151_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_POST_EXECUTION_OBSERVATION_REVIEW
```
