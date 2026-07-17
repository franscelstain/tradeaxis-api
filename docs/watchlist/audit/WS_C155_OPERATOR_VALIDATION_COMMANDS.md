# C155 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review `
  --c154-artifact=storage/app/watchlist/backtest/c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution.json `
  --expected-c154-hash=cd321cbbbbc1fa3902da5928a61741e80c8bd437 `
  --expected-c154-file-sha1=82C8C90E04A7B7C5208BC37E40CAC8B02673CACB `
  --controlled-output=storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json `
  --expected-controlled-output-hash=a1ca6b200993e4c70c7dccfa62ace43ffb2f7c4e `
  --expected-controlled-output-file-sha1=AFCA465B7567AFA37034388B257F5F5808B17E5F `
  --approval-reference=C155_OPERATOR_APPROVED_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW `
  --output=storage/app/watchlist/backtest/c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review.json `
  --operator-approved `
  --result-review-confirmed `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review `
  --approval-reference=C155_NEGATIVE_MISSING_OPERATOR `
  --result-review-confirmed `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c155-validation-no-operator.json `
  --overwrite
```

Expected rejection:

```text
C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review `
  --operator-approved `
  --approval-reference=C155_NEGATIVE_MISSING_CONFIRMATION `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c155-validation-confirmation.json `
  --overwrite
```

Expected rejection:

```text
C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review `
  --expected-c154-hash=bad-c154-hash `
  --operator-approved `
  --approval-reference=C155_NEGATIVE_C154_LOCK `
  --result-review-confirmed `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c155-validation-c154-lock.json `
  --overwrite
```

Expected rejection:

```text
C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_ARTIFACT_LOCK_MISMATCH
```

```powershell
php artisan watchlist:backtest-c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review `
  --expected-controlled-output-hash=bad-controlled-output-hash `
  --operator-approved `
  --approval-reference=C155_NEGATIVE_CONTROLLED_OUTPUT_LOCK `
  --result-review-confirmed `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c155-validation-controlled-output-lock.json `
  --overwrite
```

Expected rejection:

```text
C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH
```

## Cleanup

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c155-validation-no-operator.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c155-validation-confirmation.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c155-validation-c154-lock.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c155-validation-controlled-output-lock.json -ErrorAction SilentlyContinue
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=6fa40eafa588299db84b465202ea060a310d0d12
POSITIVE_RUNTIME_FILE_SHA1=637A4D7EAE383CDCD8804040384367439847B16D
CONTROLLED_OUTPUT_HASH=a1ca6b200993e4c70c7dccfa62ace43ffb2f7c4e
CONTROLLED_OUTPUT_FILE_SHA1=AFCA465B7567AFA37034388B257F5F5808B17E5F
CONTROLLED_OUTPUT_RECORD_COUNT=2
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C155=OK (22 tests, 94 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_RESULT_REVIEW_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_RESULT_REVIEW_CONFIRMATION_STATUS=C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING
NEGATIVE_C154_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_C154_ARTIFACT_LOCK_MISMATCH_STATUS=C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_C154_ARTIFACT_LOCK_MISMATCH
NEGATIVE_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH_STATUS=C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_REJECTED_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C155_TEST_ARTIFACTS_REMAINING
WEEKLY_SWING_WATCHLIST_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEWED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=1
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
NEXT_RECOMMENDATION=C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW
```
