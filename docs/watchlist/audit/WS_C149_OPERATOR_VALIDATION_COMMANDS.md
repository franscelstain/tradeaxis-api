# C149 Operator Validation Commands

## Positive GO Runtime Command

```powershell
php artisan watchlist:backtest-c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --c148-artifact=storage/app/watchlist/backtest/c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --expected-c148-hash=d5420447a0b5994791e51f65318dcc46c75ec156 `
  --expected-c148-file-sha1=9EF227B2B7944B2406D15235DC6C84264466B81F `
  --approval-reference=C149_OPERATOR_GO_APPROVED_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_ONLY `
  --operator-decision=GO `
  --decision-reason="Operator GO recorded from locked C148 observation result evidence to move into C150 final activation execution target while keeping C149 non-live and non-mutating." `
  --output=storage/app/watchlist/backtest/c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json `
  --operator-approved `
  --operator-decision-confirmed `
  --overwrite `
  --progress
```

## Branch Validation Commands

```powershell
php artisan watchlist:backtest-c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --c148-artifact=storage/app/watchlist/backtest/c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --expected-c148-hash=d5420447a0b5994791e51f65318dcc46c75ec156 `
  --expected-c148-file-sha1=9EF227B2B7944B2406D15235DC6C84264466B81F `
  --approval-reference=C149_HOLD_DECISION_BRANCH_VALIDATION `
  --operator-decision=HOLD `
  --decision-reason="Operator HOLD branch validation without opening C150." `
  --output=storage/app/watchlist/backtest/c149-hold-decision-branch.json `
  --operator-approved `
  --operator-decision-confirmed `
  --overwrite
```

Expected status:

```text
C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_PRODUCTION_LIVE_RUNTIME_ACTIVATION_DEFERRED
```

```powershell
php artisan watchlist:backtest-c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --c148-artifact=storage/app/watchlist/backtest/c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --expected-c148-hash=d5420447a0b5994791e51f65318dcc46c75ec156 `
  --expected-c148-file-sha1=9EF227B2B7944B2406D15235DC6C84264466B81F `
  --approval-reference=C149_NO_GO_DECISION_BRANCH_VALIDATION `
  --operator-decision=NO_GO `
  --decision-reason="Operator NO_GO branch validation closes activation without opening C150." `
  --output=storage/app/watchlist/backtest/c149-no-go-decision-branch.json `
  --operator-approved `
  --operator-decision-confirmed `
  --overwrite
```

Expected status:

```text
C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_PRODUCTION_LIVE_RUNTIME_ACTIVATION_STOPPED
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --c148-artifact=storage/app/watchlist/backtest/c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --expected-c148-hash=d5420447a0b5994791e51f65318dcc46c75ec156 `
  --expected-c148-file-sha1=9EF227B2B7944B2406D15235DC6C84264466B81F `
  --approval-reference=C149_NO_OPERATOR_TEST `
  --operator-decision=GO `
  --decision-reason="Negative missing operator approval test." `
  --output=storage/app/watchlist/backtest/c149-no-operator-test.json `
  --operator-decision-confirmed `
  --overwrite
```

Expected rejection:

```text
C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review `
  --c148-artifact=storage/app/watchlist/backtest/c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json `
  --expected-c148-hash=d5420447a0b5994791e51f65318dcc46c75ec156 `
  --expected-c148-file-sha1=9EF227B2B7944B2406D15235DC6C84264466B81F `
  --approval-reference=C149_INVALID_DECISION_TEST `
  --operator-decision=MAYBE `
  --decision-reason="Negative invalid decision test." `
  --output=storage/app/watchlist/backtest/c149-invalid-decision-test.json `
  --operator-approved `
  --operator-decision-confirmed `
  --overwrite
```

Expected rejection:

```text
C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_C150_FINAL_ACTIVATION_EXECUTION
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=311898597454a6a1984f4ed84473ad52ba6859fb
POSITIVE_RUNTIME_FILE_SHA1=3B14776D36FBC922782B332BDC55CE90B50188E5
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C149=OK (35 tests, 224 assertions)
FULL_WATCHLIST_PHPUNIT_POST_C149=OK (5371 tests, 40934 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_INVALID_OPERATOR_DECISION_EXIT_CODE=1
NEGATIVE_INVALID_OPERATOR_DECISION_STATUS=C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID
HOLD_BRANCH_EXIT_CODE=0
HOLD_BRANCH_STATUS=C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD_PRODUCTION_LIVE_RUNTIME_ACTIVATION_DEFERRED
NO_GO_BRANCH_EXIT_CODE=0
NO_GO_BRANCH_STATUS=C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO_PRODUCTION_LIVE_RUNTIME_ACTIVATION_STOPPED
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C149_TEST_ARTIFACTS_REMAINING
OPERATOR_DECISION=GO
READY_FOR_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION_ALLOWED_NEXT=1
PRODUCTION_LIVE_RUNTIME_ACTIVATION_EXECUTED=0
RUNTIME_BRIDGE_ACTIVE=0
PLAN_CONFIRM_MUTATED=0
WEEKLY_SWING_WATCHLIST_LIVE_OUTPUT_ENABLED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=0
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=0
NEXT_RECOMMENDATION=C150_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_FINAL_EXECUTION
```
