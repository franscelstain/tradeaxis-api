# C142 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --c141-artifact=storage/app/watchlist/backtest/c141-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --expected-c141-hash=ea7c4be969c2faf9e4990a135503829b8f6d6518 `
  --expected-c141-file-sha1=D9102B54D8719B40266AC8D4E9A0DF5B5BA5EB74 `
  --approval-reference=C142_OPERATOR_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --operator-approved `
  --operator-go-decision-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --c141-artifact=storage/app/watchlist/backtest/c141-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --expected-c141-hash=ea7c4be969c2faf9e4990a135503829b8f6d6518 `
  --expected-c141-file-sha1=D9102B54D8719B40266AC8D4E9A0DF5B5BA5EB74 `
  --output=storage/app/watchlist/backtest/c142-no-operator-test.json `
  --overwrite
```

Expected rejection:

```text
C142_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --c141-artifact=storage/app/watchlist/backtest/c141-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --expected-c141-hash=ea7c4be969c2faf9e4990a135503829b8f6d6518 `
  --expected-c141-file-sha1=D9102B54D8719B40266AC8D4E9A0DF5B5BA5EB74 `
  --operator-approved `
  --operator-go-decision-confirmed `
  --output=storage/app/watchlist/backtest/c142-no-reference-test.json `
  --overwrite
```

Expected rejection:

```text
C142_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --c141-artifact=storage/app/watchlist/backtest/c141-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --expected-c141-hash=ea7c4be969c2faf9e4990a135503829b8f6d6518 `
  --expected-c141-file-sha1=D9102B54D8719B40266AC8D4E9A0DF5B5BA5EB74 `
  --approval-reference=C142_OPERATOR_APPROVED_BUT_GO_DECISION_NOT_CONFIRMED_TEST `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c142-no-go-confirmation-test.json `
  --overwrite
```

Expected rejection:

```text
C142_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C142_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=18821ce6df6043bd31ba2d8add49062c6c811e3e
POSITIVE_RUNTIME_FILE_SHA1=3D82D0647F20144FA98F46AA800D2777E33F7880
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C142=OK (48 tests, 217 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C142=OK (4922 tests, 39221 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C142_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_EXIT_CODE=1
NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C142_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_WITHOUT_GO_DECISION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_WITHOUT_GO_DECISION_CONFIRMATION_STATUS=C142_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_GO_DECISION_NOT_CONFIRMED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C142_TEST_ARTIFACTS_REMAINING
OPERATOR_GO_DECISION=GO
OPERATOR_GO_DECISION_CONFIRMED=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C143_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW
```
