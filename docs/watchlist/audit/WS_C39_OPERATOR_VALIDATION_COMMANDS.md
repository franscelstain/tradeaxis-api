# WS C39 Operator Validation Commands

Run these commands from the repository root:

```text
D:\Laravel\watchlist\tradeaxis-api
```

## PHPUnit

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC39"
```

Expected result:

```text
OK (17 tests, 174 assertions)
```

Full Watchlist suite:

```text
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected result:

```text
OK (593 tests, 12464 assertions)
```

## Runtime

```text
php artisan watchlist:backtest-c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards --c38-artifact=storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json --expected-c38-hash=7fe69c9ee9797615df676b0fe0c7378b452da429 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json --progress --overwrite
```

Observed result:

```text
status=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED
artifact_path=storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json
artifact_hash=504aaa061054ed2771ed08294d8a0570f08e18db
production_ready=0
expected_c38_hash=7fe69c9ee9797615df676b0fe0c7378b452da429
actual_c38_hash=7fe69c9ee9797615df676b0fe0c7378b452da429
c38_hash_match=1
c38_status=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED
c38_diagnostic_conclusion=C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
diagnostic_conclusion=C39_GUARDED_IS_CANDIDATE_FORMED
next_step_recommendation=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE
source_c38_c37_anti_overfit_result=FAIL
source_c38_zero_pick_months=2023-03
source_c38_evidence=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
c39_candidate_formed=1
c39_best_is_candidate_code=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
c39_best_candidate_requires_C40_validation=1
c39_candidate_with_all_guards_count=1
c39_best_candidate_top_branch_share=0.79374624173181
c39_best_candidate_zero_pick_month_count=0
```

## Artifact Hash Check

```text
Get-FileHash -Algorithm SHA1 storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json
```

Observed result:

```text
B08233211E335C982E327D6A0C638428B906BFC9
```

## Boundary Checks

C39 is valid only if all of the following remain true:

```text
C38_ARTIFACT_HASH_LOCK=true
IS_ONLY_CANDIDATE_FORMATION=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
CANDIDATE_REQUIRES_C40_VALIDATION=true
production_ready=false
```
