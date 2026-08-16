# C158 Operator Validation Commands

## Positive Runtime Command

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review `
  --c157-artifact=storage/app/watchlist/backtest/c157-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-go-decision-finalization-review.json `
  --expected-c157-hash=36f8aadb64d1994bde030efcfec985c7fd0df411 `
  --expected-c157-file-sha1=E3B40E1080F3C3CCE5E39E0A660E38937F25A68B `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review.json `
  --operator-approved `
  --publication-boundary-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --overwrite `
  --progress
```

## Negative Gates

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review `
  --approval-reference=C158_NEGATIVE_MISSING_OPERATOR `
  --publication-boundary-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-no-operator.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review `
  --operator-approved `
  --approval-reference=C158_NEGATIVE_MISSING_BOUNDARY_CONFIRMATION `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-missing-boundary-confirmation.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_BOUNDARY_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review `
  --operator-approved `
  --approval-reference=C158_NEGATIVE_MISSING_CONTROLLED_ONLY_CONFIRMATION `
  --publication-boundary-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-missing-controlled-only-confirmation.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ONLY_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review `
  --expected-c157-hash=bad-c157-hash `
  --operator-approved `
  --approval-reference=C158_NEGATIVE_C157_LOCK `
  --publication-boundary-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-c157-lock.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_ARTIFACT_LOCK_MISMATCH
```

## Cleanup

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-no-operator.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-missing-boundary-confirmation.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-missing-controlled-only-confirmation.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-c157-lock.json -ErrorAction SilentlyContinue
```

## Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_PASSED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=f17826dd8eb388491be7ef94d18600647dbccc85
POSITIVE_RUNTIME_FILE_SHA1=B61A0522835494811E3306ABDFE37639D5ED56C8
TOPIC_CODE=C158_CONTROLLED_OUTPUT_PUBLICATION
TOPIC_STAGE=BOUNDARY_REVIEW
NEXT_RECOMMENDATION=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C158_BOUNDARY=OK (28 tests, 119 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_PUBLICATION_BOUNDARY_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_PUBLICATION_BOUNDARY_CONFIRMATION_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_PUBLICATION_BOUNDARY_CONFIRMATION_MISSING
NEGATIVE_MISSING_CONTROLLED_PUBLICATION_ONLY_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_CONTROLLED_PUBLICATION_ONLY_CONFIRMATION_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ONLY_CONFIRMATION_MISSING
NEGATIVE_C157_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_C157_ARTIFACT_LOCK_MISMATCH_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_BOUNDARY_REVIEW_REJECTED_C157_ARTIFACT_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C158_TEST_ARTIFACTS_REMAINING
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_GENERATED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_CONTROLLED_PUBLICATION_ALLOWED_NEXT=1
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
```

## C158 Go Decision Finalization Positive Runtime Command

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review `
  --c158-operator-artifact=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review.json `
  --expected-c158-operator-hash=14fc284651d7d5f07d1941300b382c2d7071fea3 `
  --expected-c158-operator-file-sha1=66EDD8CC51F5C5F9C29889354A94A01FC0501B21 `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --controlled-publication-finalization-confirmed `
  --free-publication-locked-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review.json `
  --overwrite `
  --progress
```

## C158 Go Decision Finalization Negative Gates

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review `
  --go-decision-finalization-confirmed `
  --controlled-publication-finalization-confirmed `
  --free-publication-locked-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-finalization-missing-approval.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review `
  --operator-approved `
  --controlled-publication-finalization-confirmed `
  --free-publication-locked-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-finalization-missing-go-finalization.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --free-publication-locked-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-finalization-missing-controlled-finalization.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_FINALIZATION_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --controlled-publication-finalization-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-finalization-missing-free-lock.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --controlled-publication-finalization-confirmed `
  --free-publication-locked-confirmed `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-finalization-missing-plan-confirm.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review `
  --expected-c158-operator-hash=bad-c158-operator-hash `
  --operator-approved `
  --go-decision-finalization-confirmed `
  --controlled-publication-finalization-confirmed `
  --free-publication-locked-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-finalization-operator-lock-mismatch.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C158_OPERATOR_GO_NO_GO_ARTIFACT_LOCK_MISMATCH
```

## C158 Go Decision Finalization Cleanup

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-finalization-missing-approval.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-finalization-missing-go-finalization.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-finalization-missing-controlled-finalization.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-finalization-missing-free-lock.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-finalization-missing-plan-confirm.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-finalization-operator-lock-mismatch.json -ErrorAction SilentlyContinue
```

## C158 Go Decision Finalization Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_PASSED_GO_FINALIZED_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-go-decision-finalization-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=d8e4bfc3f906f3bc613f9aae1e03a27a67f9241b
POSITIVE_RUNTIME_FILE_SHA1=D732BDF92A76DC25434C2DECC539CD26181C8F21
TOPIC_CODE=C158_CONTROLLED_OUTPUT_PUBLICATION
TOPIC_STAGE=GO_DECISION_FINALIZATION_REVIEW
OPERATOR_DECISION=GO
GO_DECISION_FINALIZED=1
NEXT_RECOMMENDATION=C159_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_POST_PUBLICATION_OBSERVATION_REVIEW
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C158_GO_DECISION_FINALIZATION=OK (34 tests, 132 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_GO_DECISION_FINALIZATION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_GO_DECISION_FINALIZATION_CONFIRMATION_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_GO_DECISION_FINALIZATION_CONFIRMATION_MISSING
NEGATIVE_MISSING_CONTROLLED_PUBLICATION_FINALIZATION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_CONTROLLED_PUBLICATION_FINALIZATION_CONFIRMATION_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_CONTROLLED_PUBLICATION_FINALIZATION_CONFIRMATION_MISSING
NEGATIVE_MISSING_FREE_PUBLICATION_LOCK_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_FREE_PUBLICATION_LOCK_CONFIRMATION_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_FREE_PUBLICATION_LOCK_CONFIRMATION_MISSING
NEGATIVE_MISSING_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_PLAN_CONFIRM_UNCHANGED_CONFIRMATION_MISSING
NEGATIVE_C158_OPERATOR_GO_NO_GO_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_C158_OPERATOR_GO_NO_GO_ARTIFACT_LOCK_MISMATCH_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW_REJECTED_C158_OPERATOR_GO_NO_GO_ARTIFACT_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C158_GO_DECISION_FINALIZATION_TEST_ARTIFACTS_REMAINING
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
```

## C158 Operator Go/No-Go Positive Runtime Command

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review `
  --c158-result-review-artifact=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review.json `
  --expected-c158-result-review-hash=2912bf54b34ee23b4413a179072d3e670f92e719 `
  --expected-c158-result-review-file-sha1=C601A8598D83D61FB84F0AAB3DED9AD8E36AD59B `
  --operator-approved `
  --operator-decision=GO `
  --operator-decision-confirmed `
  --decision-reason="Operator approves C158 controlled output publication result for same-topic go decision finalization." `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_GO `
  --output=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review.json `
  --overwrite `
  --progress
```

## C158 Operator Go/No-Go Negative Gates

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review `
  --operator-decision=GO `
  --operator-decision-confirmed `
  --decision-reason="Operator gate validation rejects missing approval." `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_GO `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-operator-missing-approval.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review `
  --operator-approved `
  --operator-decision=MAYBE `
  --operator-decision-confirmed `
  --decision-reason="Operator gate validation rejects invalid decision." `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_GO `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-operator-invalid-decision.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review `
  --operator-approved `
  --operator-decision=GO `
  --decision-reason="Operator gate validation rejects unconfirmed decision." `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_GO `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-operator-unconfirmed-decision.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review `
  --operator-approved `
  --operator-decision=GO `
  --operator-decision-confirmed `
  --decision-reason= `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_GO `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-operator-missing-reason.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review `
  --expected-c158-result-review-hash=bad-c158-result-review-hash `
  --operator-approved `
  --operator-decision=GO `
  --operator-decision-confirmed `
  --decision-reason="Operator gate validation rejects source lock mismatch." `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_GO `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-operator-result-review-lock-mismatch.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH
```

## C158 Operator Go/No-Go Cleanup

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-operator-missing-approval.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-operator-invalid-decision.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-operator-unconfirmed-decision.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-operator-missing-reason.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-operator-result-review-lock-mismatch.json -ErrorAction SilentlyContinue
```

## C158 Operator Go/No-Go Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO_PRIMARY_AND_BACKUP_READY_FOR_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-operator-go-no-go-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=14fc284651d7d5f07d1941300b382c2d7071fea3
POSITIVE_RUNTIME_FILE_SHA1=66EDD8CC51F5C5F9C29889354A94A01FC0501B21
TOPIC_CODE=C158_CONTROLLED_OUTPUT_PUBLICATION
TOPIC_STAGE=OPERATOR_GO_NO_GO_REVIEW
OPERATOR_DECISION=GO
NEXT_RECOMMENDATION=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_GO_DECISION_FINALIZATION_REVIEW
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C158_OPERATOR_GO_NO_GO=OK (26 tests, 125 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_INVALID_OPERATOR_DECISION_EXIT_CODE=1
NEGATIVE_INVALID_OPERATOR_DECISION_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_INVALID
NEGATIVE_UNCONFIRMED_OPERATOR_DECISION_EXIT_CODE=1
NEGATIVE_UNCONFIRMED_OPERATOR_DECISION_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_NOT_CONFIRMED
NEGATIVE_MISSING_OPERATOR_DECISION_REASON_EXIT_CODE=1
NEGATIVE_MISSING_OPERATOR_DECISION_REASON_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_OPERATOR_DECISION_REASON_MISSING
NEGATIVE_C158_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_C158_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW_REJECTED_C158_RESULT_REVIEW_ARTIFACT_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C158_OPERATOR_GO_NO_GO_TEST_ARTIFACTS_REMAINING
WEEKLY_SWING_WATCHLIST_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEWED=1
WEEKLY_SWING_WATCHLIST_CONTROLLED_OUTPUT_PUBLICATION_EXECUTED=1
WEEKLY_SWING_WATCHLIST_CONTROLLED_OUTPUT_PUBLISHED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
```

## C158 Result Review Positive Runtime Command

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review `
  --c158-execution-artifact=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution.json `
  --expected-c158-execution-hash=fec3b624eb3e912b1302165b1def8fe0a4669a87 `
  --expected-c158-execution-file-sha1=242830E193C2D54A4C7A233A68D04F90412AEE7D `
  --controlled-publication=storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json `
  --expected-controlled-publication-hash=df064c7290ff4c3bfd0c7a8412d39299049c01d5 `
  --expected-controlled-publication-file-sha1=D87AB8CD1564BE8B266B8A68011470272D49EE60 `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review.json `
  --operator-approved `
  --result-review-confirmed `
  --controlled-publication-result-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --overwrite `
  --progress
```

## C158 Result Review Negative Gates

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review `
  --result-review-confirmed `
  --controlled-publication-result-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_NEGATIVE_RESULT_MISSING_OPERATOR `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-result-no-operator.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review `
  --operator-approved `
  --controlled-publication-result-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_NEGATIVE_RESULT_MISSING_CONFIRMATION `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-result-missing-confirmation.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review `
  --expected-controlled-publication-hash=bad-controlled-publication-hash `
  --operator-approved `
  --result-review-confirmed `
  --controlled-publication-result-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_NEGATIVE_RESULT_PUBLICATION_LOCK `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-result-publication-lock.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review `
  --expected-c158-execution-hash=bad-c158-execution-hash `
  --operator-approved `
  --result-review-confirmed `
  --controlled-publication-result-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_NEGATIVE_RESULT_EXECUTION_LOCK `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-result-execution-lock.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_ARTIFACT_LOCK_MISMATCH
```

## C158 Result Review Cleanup

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-result-no-operator.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-result-missing-confirmation.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-result-publication-lock.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-result-execution-lock.json -ErrorAction SilentlyContinue
```

## C158 Result Review Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_PASSED_READY_FOR_OPERATOR_GO_NO_GO_REVIEW_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review.json
POSITIVE_RUNTIME_ARTIFACT_HASH=2912bf54b34ee23b4413a179072d3e670f92e719
POSITIVE_RUNTIME_FILE_SHA1=C601A8598D83D61FB84F0AAB3DED9AD8E36AD59B
CONTROLLED_PUBLICATION_ARTIFACT=storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json
CONTROLLED_PUBLICATION_HASH=df064c7290ff4c3bfd0c7a8412d39299049c01d5
CONTROLLED_PUBLICATION_FILE_SHA1=D87AB8CD1564BE8B266B8A68011470272D49EE60
CONTROLLED_PUBLICATION_RECORD_COUNT=2
TOPIC_CODE=C158_CONTROLLED_OUTPUT_PUBLICATION
TOPIC_STAGE=RESULT_REVIEW
NEXT_RECOMMENDATION=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_OPERATOR_GO_NO_GO_REVIEW
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C158_RESULT_REVIEW=OK (23 tests, 108 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_RESULT_REVIEW_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_RESULT_REVIEW_CONFIRMATION_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_RESULT_REVIEW_CONFIRMATION_MISSING
NEGATIVE_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_CONTROLLED_PUBLICATION_ARTIFACT_LOCK_MISMATCH
NEGATIVE_C158_EXECUTION_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_C158_EXECUTION_ARTIFACT_LOCK_MISMATCH_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_REJECTED_C158_EXECUTION_ARTIFACT_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C158_RESULT_REVIEW_TEST_ARTIFACTS_REMAINING
WEEKLY_SWING_WATCHLIST_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEWED=1
WEEKLY_SWING_WATCHLIST_CONTROLLED_OUTPUT_PUBLICATION_EXECUTED=1
WEEKLY_SWING_WATCHLIST_CONTROLLED_OUTPUT_PUBLISHED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
```

## C158 Execution Positive Runtime Command

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution `
  --c158-boundary-artifact=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review.json `
  --expected-c158-boundary-hash=f17826dd8eb388491be7ef94d18600647dbccc85 `
  --expected-c158-boundary-file-sha1=B61A0522835494811E3306ABDFE37639D5ED56C8 `
  --controlled-output=storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json `
  --expected-controlled-output-hash=a1ca6b200993e4c70c7dccfa62ace43ffb2f7c4e `
  --expected-controlled-output-file-sha1=AFCA465B7567AFA37034388B257F5F5808B17E5F `
  --controlled-publication=storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json `
  --approval-reference=C158_OPERATOR_APPROVED_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_CONTROLLED_ONLY `
  --output=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution.json `
  --operator-approved `
  --controlled-publication-execution-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --overwrite `
  --progress
```

## C158 Execution Negative Gates

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution `
  --controlled-publication-execution-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_NEGATIVE_MISSING_OPERATOR `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-execution-no-operator.json `
  --controlled-publication=storage/app/watchlist/output/.tmp-c158-validation-execution-no-operator-publication.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution `
  --operator-approved `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_NEGATIVE_MISSING_EXECUTION_CONFIRMATION `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-execution-missing-confirmation.json `
  --controlled-publication=storage/app/watchlist/output/.tmp-c158-validation-execution-missing-confirmation-publication.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_EXECUTION_CONFIRMATION_MISSING
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution `
  --expected-controlled-output-hash=bad-controlled-output-hash `
  --operator-approved `
  --controlled-publication-execution-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_NEGATIVE_CONTROLLED_OUTPUT_LOCK `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-execution-controlled-output-lock.json `
  --controlled-publication=storage/app/watchlist/output/.tmp-c158-validation-execution-controlled-output-lock-publication.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH
```

```powershell
php artisan watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution `
  --expected-c158-boundary-hash=bad-c158-boundary-hash `
  --operator-approved `
  --controlled-publication-execution-confirmed `
  --controlled-publication-only-confirmed `
  --plan-confirm-unchanged-confirmed `
  --approval-reference=C158_NEGATIVE_C158_BOUNDARY_LOCK `
  --output=storage/app/watchlist/backtest/.tmp-c158-validation-execution-boundary-lock.json `
  --controlled-publication=storage/app/watchlist/output/.tmp-c158-validation-execution-boundary-lock-publication.json `
  --overwrite
```

Expected rejection:

```text
C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_ARTIFACT_LOCK_MISMATCH
```

## C158 Execution Cleanup

```powershell
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-execution-no-operator.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-execution-missing-confirmation.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-execution-controlled-output-lock.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/backtest/.tmp-c158-validation-execution-boundary-lock.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/output/.tmp-c158-validation-execution-no-operator-publication.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/output/.tmp-c158-validation-execution-missing-confirmation-publication.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/output/.tmp-c158-validation-execution-controlled-output-lock-publication.json -ErrorAction SilentlyContinue
Remove-Item -LiteralPath storage/app/watchlist/output/.tmp-c158-validation-execution-boundary-lock-publication.json -ErrorAction SilentlyContinue
```

## C158 Execution Observed Runtime Evidence

```text
POSITIVE_RUNTIME_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_PASSED_CONTROLLED_PUBLICATION_EXECUTED_READY_FOR_RESULT_REVIEW_PRIMARY_AND_BACKUP
POSITIVE_RUNTIME_ARTIFACT=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution.json
POSITIVE_RUNTIME_ARTIFACT_HASH=fec3b624eb3e912b1302165b1def8fe0a4669a87
POSITIVE_RUNTIME_FILE_SHA1=242830E193C2D54A4C7A233A68D04F90412AEE7D
CONTROLLED_PUBLICATION_ARTIFACT=storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json
CONTROLLED_PUBLICATION_HASH=df064c7290ff4c3bfd0c7a8412d39299049c01d5
CONTROLLED_PUBLICATION_FILE_SHA1=D87AB8CD1564BE8B266B8A68011470272D49EE60
CONTROLLED_PUBLICATION_RECORD_COUNT=2
TOPIC_CODE=C158_CONTROLLED_OUTPUT_PUBLICATION
TOPIC_STAGE=EXECUTION
NEXT_RECOMMENDATION=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW
POSITIVE_RUNTIME_EXIT_CODE=0
FOCUSED_PHPUNIT_C158_EXECUTION=OK (24 tests, 128 assertions)
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_EXIT_CODE=1
NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_OPERATOR_APPROVAL_MISSING
NEGATIVE_MISSING_CONTROLLED_PUBLICATION_EXECUTION_CONFIRMATION_EXIT_CODE=1
NEGATIVE_MISSING_CONTROLLED_PUBLICATION_EXECUTION_CONFIRMATION_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_PUBLICATION_EXECUTION_CONFIRMATION_MISSING
NEGATIVE_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_CONTROLLED_OUTPUT_ARTIFACT_LOCK_MISMATCH
NEGATIVE_C158_BOUNDARY_ARTIFACT_LOCK_MISMATCH_EXIT_CODE=1
NEGATIVE_C158_BOUNDARY_ARTIFACT_LOCK_MISMATCH_STATUS=C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_REJECTED_C158_BOUNDARY_ARTIFACT_LOCK_MISMATCH
TEMPORARY_NEGATIVE_ARTIFACT_CLEANUP=NO_C158_EXECUTION_TEST_ARTIFACTS_REMAINING
WEEKLY_SWING_WATCHLIST_CONTROLLED_OUTPUT_PUBLICATION_EXECUTED=1
WEEKLY_SWING_WATCHLIST_CONTROLLED_OUTPUT_PUBLISHED=1
WEEKLY_SWING_WATCHLIST_OFFICIAL_OUTPUT_PUBLISHED=0
WEEKLY_SWING_WATCHLIST_PUBLICATION_ALLOWED=0
WEEKLY_SWING_WATCHLIST_UNRESTRICTED_PUBLICATION_ALLOWED=0
PLAN_CONFIRM_MUTATION_ALLOWED=0
PLAN_CONFIRM_MUTATED=0
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=0
```
