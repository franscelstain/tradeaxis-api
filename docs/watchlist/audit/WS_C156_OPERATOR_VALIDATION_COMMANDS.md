# C156 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review `
  --c155-artifact=storage/app/watchlist/backtest/c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review.json `
  --expected-c155-hash=6fa40eafa588299db84b465202ea060a310d0d12 `
  --expected-c155-file-sha1=637A4D7EAE383CDCD8804040384367439847B16D `
  --approval-reference=C156_OPERATOR_GO_APPROVED_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_ONLY `
  --operator-decision=GO `
  --decision-reason="Operator records GO from locked C155 controlled output result review into C157 go decision finalization review target; publication and PLAN/CONFIRM remain locked." `
  --output=storage/app/watchlist/backtest/c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review.json `
  --operator-approved `
  --operator-decision-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review `
  --approval-reference=C156_NEGATIVE_MISSING_OPERATOR `
  --operator-decision=GO `
  --operator-decision-confirmed `
  --decision-reason="negative missing operator" `
  --output=storage/app/watchlist/backtest/.tmp-c156-validation-no-operator.json `
  --overwrite
```

Expected rejection:

```text
C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review `
  --operator-approved `
  --approval-reference=C156_NEGATIVE_INVALID_DECISION `
  --operator-decision=MAYBE `
  --operator-decision-confirmed `
  --decision-reason="negative invalid decision" `
  --output=storage/app/watchlist/backtest/.tmp-c156-validation-invalid-decision.json `
  --overwrite
```

Expected rejection:

```text
C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID
```

```powershell
php artisan watchlist:backtest-c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review `
  --operator-approved `
  --approval-reference=C156_NEGATIVE_UNCONFIRMED_DECISION `
  --operator-decision=GO `
  --decision-reason="negative unconfirmed decision" `
  --output=storage/app/watchlist/backtest/.tmp-c156-validation-unconfirmed.json `
  --overwrite
```

Expected rejection:

```text
C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED
```

```powershell
php artisan watchlist:backtest-c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review `
  --expected-c155-hash=bad-c155-hash `
  --operator-approved `
  --approval-reference=C156_NEGATIVE_C155_LOCK `
  --operator-decision=GO `
  --operator-decision-confirmed `
  --decision-reason="negative c155 lock mismatch" `
  --output=storage/app/watchlist/backtest/.tmp-c156-validation-c155-lock.json `
  --overwrite
```

Expected rejection:

```text
C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_ARTIFACT_LOCK_MISMATCH
```

## Cleanup

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c156-validation-no-operator.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c156-validation-invalid-decision.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c156-validation-unconfirmed.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c156-validation-c155-lock.json -ErrorAction SilentlyContinue
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c156-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-operator-go-no-go-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=f36edcf84b291dd58119caf4e003c00ced404311
POSITIVE_RUNTIME_FILE_SHA1=A7165F0FB30111B313783A1FD3DE77992BD39E99
OPERATOR_DECISION=GO
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C156=OK (26 tests, 139 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_INVALID_OPERATOR_DECISION_EXIT_CODE=1
NEGATIVE_INVALID_OPERATOR_DECISION_STATUS=C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID
NEGATIVE_UNCONFIRMED_OPERATOR_DECISION_EXIT_CODE=1
NEGATIVE_UNCONFIRMED_OPERATOR_DECISION_STATUS=C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED
NEGATIVE_C155_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_C155_ARTIFACT_LOCK_MISMATCH_STATUS=C156_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C155_ARTIFACT_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C156_TEST_ARTIFACTS_REMAINING
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_LIVE_RECOMMENDATION_GENERATED=1
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
NEXT_RECOMMENDATION=C157_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_GO_DECISION_FINALIZATION_REVIEW
```
