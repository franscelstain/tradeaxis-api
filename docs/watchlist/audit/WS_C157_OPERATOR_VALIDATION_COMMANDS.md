# C157 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review `
  --c156-artifact=storage/app/watchlist/backtest/c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review.json `
  --expected-c156-hash=f36edcf84b291dd58119caf4e003c00ced404311 `
  --expected-c156-file-sha1=A7165F0FB30111B313783A1FD3DE77992BD39E99 `
  --approval-reference=C157_OPERATOR_APPROVED_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review.json `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review `
  --approval-reference=C157_NEGATIVE_MISSING_OPERATOR `
  --go-decision-finalization-confirmed `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c157-validation-no-operator.json `
  --overwrite
```

Expected rejection:

```text
C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review `
  --operator-approved `
  --approval-reference=C157_NEGATIVE_UNCONFIRMED_GO `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c157-validation-unconfirmed-go.json `
  --overwrite
```

Expected rejection:

```text
C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review `
  --operator-approved `
  --approval-reference=C157_NEGATIVE_NO_PUBLICATION_CONFIRMATION `
  --go-decision-finalization-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c157-validation-no-publication-confirmation.json `
  --overwrite
```

Expected rejection:

```text
C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_NO_PUBLICATION_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review `
  --expected-c156-hash=bad-c156-hash `
  --operator-approved `
  --approval-reference=C157_NEGATIVE_C156_LOCK `
  --go-decision-finalization-confirmed `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c157-validation-c156-lock.json `
  --overwrite
```

Expected rejection:

```text
C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_ARTIFACT_LOCK_MISMATCH
```

## Cleanup

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c157-validation-no-operator.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c157-validation-unconfirmed-go.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c157-validation-no-publication-confirmation.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c157-validation-c156-lock.json -ErrorAction SilentlyContinue
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=36f8aadb64d1994bde030efcfec985c7fd0df411
POSITIVE_RUNTIME_FILE_SHA1=E3B40E1080F3C3CCE5E39E0A660E38937F25A68B
OPERATOR_GO_DECISION=GO
GO_DECISION_FINALIZED=1
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C157=OK (32 tests, 133 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_GO_DECISION_FINALIZATION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_GO_DECISION_FINALIZATION_CONFIRMATION_STATUS=C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING
NEGATIVE_MISSING_NO_PUBLICATION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_NO_PUBLICATION_CONFIRMATION_STATUS=C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_NO_PUBLICATION_CONFIRMATION_MISSING
NEGATIVE_C156_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_C156_ARTIFACT_LOCK_MISMATCH_STATUS=C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C156_ARTIFACT_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C157_TEST_ARTIFACTS_REMAINING
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=1
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
NEXT_RECOMMENDATION=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW
```
