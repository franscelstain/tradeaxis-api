# WS C41 Operator Validation Commands

Run these commands from the repository root:

```text
D:\Laravel\watchlist\tradeaxis-api
```

## PHPUnit

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC41"
```

Expected result:

```text
OK (18 tests, 123 assertions)
```

Full Watchlist suite:

```text
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected result:

```text
OK (627 tests, 12763 assertions)
```

## Runtime

```text
php artisan watchlist:backtest-c41-is-review-or-evidence-expansion-before-oos --c40-artifact=storage/app/watchlist/backtest/c40-is-validation-and-anti-overfit-check-for-c39-guarded-candidate.json --expected-c40-hash=0b40ee2464ed820d47ad0b83acbacd78b440d5bd --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c41-is-review-or-evidence-expansion-before-oos.json --progress --overwrite
```

Observed result:

```text
status=C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED
artifact_path=storage/app/watchlist/backtest/c41-is-review-or-evidence-expansion-before-oos.json
artifact_hash=fa3afd197cfe07d67d90edf87d69aec81310d791
production_ready=0
expected_c40_hash=0b40ee2464ed820d47ad0b83acbacd78b440d5bd
actual_c40_hash=0b40ee2464ed820d47ad0b83acbacd78b440d5bd
c40_hash_match=1
c40_status=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE_COMPLETED
c40_diagnostic_conclusion=C40_CANDIDATE_WARNING_REQUIRES_REVIEW_BEFORE_OOS
diagnostic_conclusion=C41_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
next_step_recommendation=C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_OR_GUARD_REFINEMENT
source_c40_target_candidate=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
source_c40_overall_anti_overfit_result=WARNING
source_c40_warning_layers=2
source_c40_failed_layers=0
source_c40_not_evaluable_layers=0
c41_warning_layer_count=2
c41_rolling_warning_windows=3
c41_non_bad_month_warning=1
c41_candidate_decision=C41_REQUIRES_EVIDENCE_EXPANSION_BEFORE_OOS
c41_direct_oos_proof_recommended=0
c41_oos_proof_unlocked=0
c41_evidence_requirements_count=5
```

## Artifact Hash Check

```text
Get-FileHash -Algorithm SHA1 storage/app/watchlist/backtest/c41-is-review-or-evidence-expansion-before-oos.json
```

Observed result:

```text
9B44AD084DBD7637E0794A8AF5085E3A846D9486
```

## Boundary Checks

C41 is valid only if all of the following remain true:

```text
C40_ARTIFACT_HASH_LOCK=true
IS_ONLY_REVIEW=true
EVIDENCE_EXPANSION_REVIEW_ONLY=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C41_CANDIDATE_RESELECTION=true
CANDIDATE_IS_NOT_PRODUCTION=true
production_ready=false
```
