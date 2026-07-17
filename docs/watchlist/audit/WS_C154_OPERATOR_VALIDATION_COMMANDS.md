# C154 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution `
  --c153-artifact=storage/app/watchlist/backtest/c153-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-boundary-review.json `
  --expected-c153-hash=51bdfbcbb34ce49a185122f0df932451fd914a78 `
  --expected-c153-file-sha1=9B8A640C6C7C9DD1947AB4C69706C76F44793B43 `
  --controlled-output=storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json `
  --approval-reference=C154_OPERATOR_APPROVED_CONTROLLED_OUTPUT_GENERATION_EXECUTION `
  --output=storage/app/watchlist/backtest/c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution.json `
  --operator-approved `
  --controlled-output-confirmed `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution `
  --approval-reference=C154_NEGATIVE_MISSING_OPERATOR `
  --controlled-output-confirmed `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c154-validation-no-operator.json `
  --controlled-output=storage/app/watchlist/output/.tmp-c154-validation-no-operator-controlled-output.json `
  --overwrite
```

Expected rejection:

```text
C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution `
  --operator-approved `
  --approval-reference=C154_NEGATIVE_MISSING_CONFIRMATION `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c154-validation-confirmation.json `
  --controlled-output=storage/app/watchlist/output/.tmp-c154-validation-confirmation-controlled-output.json `
  --overwrite
```

Expected rejection:

```text
C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution `
  --expected-c153-hash=bad-c153-hash `
  --operator-approved `
  --approval-reference=C154_NEGATIVE_LOCK_MISMATCH `
  --controlled-output-confirmed `
  --no-publication-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c154-validation-lock.json `
  --controlled-output=storage/app/watchlist/output/.tmp-c154-validation-lock-controlled-output.json `
  --overwrite
```

Expected rejection:

```text
C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_ARTIFACT_LOCK_MISMATCH
```

## Cleanup

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c154-validation-no-operator.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c154-validation-confirmation.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c154-validation-lock.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/output/.tmp-c154-validation-no-operator-controlled-output.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/output/.tmp-c154-validation-confirmation-controlled-output.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/output/.tmp-c154-validation-lock-controlled-output.json -ErrorAction SilentlyContinue
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_PASSED_CONTROLLED_OUTPUT_GENERATED_NOT_PUBLISHED_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution.json
POSITIVE_RUNTIME_ARTIFACT_HASH=cd321cbbbbc1fa3902da5928a61741e80c8bd437
POSITIVE_RUNTIME_FILE_SHA1=82C8C90E04A7B7C5208BC37E40CAC8B02673CACB
CONTROLLED_OUTPUT_ARTIFACT=storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json
CONTROLLED_OUTPUT_HASH=a1ca6b200993e4c70c7dccfa62ace43ffb2f7c4e
CONTROLLED_OUTPUT_FILE_SHA1=AFCA465B7567AFA37034388B257F5F5808B17E5F
CONTROLLED_OUTPUT_RECORD_COUNT=2
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C154=OK (33 tests, 107 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_CONTROLLED_OUTPUT_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_CONTROLLED_OUTPUT_CONFIRMATION_STATUS=C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_CONFIRMATION_MISSING
NEGATIVE_C153_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_C153_ARTIFACT_LOCK_MISMATCH_STATUS=C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_REJECTED_C153_ARTIFACT_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C154_TEST_ARTIFACTS_REMAINING
WEEKLY_SWING_WATCHLIST_CONTROLLED_OUTPUT_GENERATION_EXECUTED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=1
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
NEXT_RECOMMENDATION=C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW
```
