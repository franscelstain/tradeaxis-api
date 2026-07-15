# C143 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c143-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review `
  --c142-artifact=storage/app/watchlist/backtest/c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --expected-c142-hash=18821ce6df6043bd31ba2d8add49062c6c811e3e `
  --expected-c142-file-sha1=3D82D0647F20144FA98F46AA800D2777E33F7880 `
  --approval-reference=C143_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c143-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c143-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review `
  --c142-artifact=storage/app/watchlist/backtest/c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --expected-c142-hash=18821ce6df6043bd31ba2d8add49062c6c811e3e `
  --expected-c142-file-sha1=3D82D0647F20144FA98F46AA800D2777E33F7880 `
  --output=storage/app/watchlist/backtest/c143-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C143_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c143-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review `
  --c142-artifact=storage/app/watchlist/backtest/c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --expected-c142-hash=18821ce6df6043bd31ba2d8add49062c6c811e3e `
  --expected-c142-file-sha1=3D82D0647F20144FA98F46AA800D2777E33F7880 `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --output=storage/app/watchlist/backtest/c143-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C143_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c143-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review `
  --c142-artifact=storage/app/watchlist/backtest/c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --expected-c142-hash=18821ce6df6043bd31ba2d8add49062c6c811e3e `
  --expected-c142-file-sha1=3D82D0647F20144FA98F46AA800D2777E33F7880 `
  --approval-reference=C143_OPERATOR_APPROVED_BUT_GO_FINALIZATION_NOT_CONFIRMED_TEST `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c143-no-go-finalization-test.json `
  --overwrite
```

Expected rejection:

```text
C143_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_NOT_CONFIRMED
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C143_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_FINALIZED_GO_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c143-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=804b6020e73e24e7dac0a9ecbbe116ff5ee95808
POSITIVE_RUNTIME_FILE_SHA1=F0645B69E7F22C1FACEEA235ED0256777558752F
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C143=OK (63 tests, 247 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C143=OK (4985 tests, 39468 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C143_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C143_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_GO_DECISION_FINALIZATION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_WITHOUT_GO_DECISION_FINALIZATION_CONFIRMATION_STATUS=C143_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C143_TEST_ARTIFACTS_REMAINING
OPERATOR_GO_DECISION=GO
OPERATOR_GO_DECISION_CONFIRMED=1
GO_DECISION_FINALIZED=1
GO_DECISION_FINALIZATION_CONFIRMED=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C144_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW
```
