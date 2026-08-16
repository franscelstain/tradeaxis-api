# WS C40 Operator Validation Commands

Run these commands from the repository root:

```text
D:\Laravel\watchlist\tradeaxis-api
```

## PHPUnit

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC40"
```

Expected result:

```text
OK (16 tests, 176 assertions)
```

Full Watchlist suite:

```text
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected result:

```text
OK (609 tests, 12640 assertions)
```

## Runtime

```text
php artisan watchlist:backtest-c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate --c39-artifact=storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json --expected-c39-hash=504aaa061054ed2771ed08294d8a0570f08e18db --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate.json --progress --overwrite
```

Observed result:

```text
status=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED
artifact_path=storage/app/watchlist/backtest/c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate.json
artifact_hash=0b40ee2464ed820d47ad0b83acbacd78b440d5bd
production_ready=0
expected_c39_hash=504aaa061054ed2771ed08294d8a0570f08e18db
actual_c39_hash=504aaa061054ed2771ed08294d8a0570f08e18db
c39_hash_match=1
c39_status=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED
c39_diagnostic_conclusion=C39_GUARDED_IS_CANDIDATE_FORMED
diagnostic_conclusion=C40_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS
next_step_recommendation=C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS
source_c39_best_candidate=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
source_c39_evidence=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
c40_overall_anti_overfit_result=WARNING
c40_passed_layers=7
c40_warning_layers=2
c40_failed_layers=0
c40_not_evaluable_layers=0
c40_candidate_decision=C40_CANDIDATE_REQUIRES_REVIEW_BEFORE_OOS
```

## Artifact Hash Check

```text
Get-FileHash -Algorithm SHA1 storage/app/watchlist/backtest/c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate.json
```

Observed result:

```text
306E01AD1274944991F1AFE6CFEBBDB3C0E06BFC
```

## Boundary Checks

C40 is valid only if all of the following remain true:

```text
C39_ARTIFACT_HASH_LOCK=true
IS_ONLY_VALIDATION=true
ANTI_OVERFIT_VALIDATION=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
production_ready=false
```
