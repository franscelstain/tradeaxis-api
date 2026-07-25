# WS C28 Operator Validation Commands

## C170 Correction

The prior ready-for-C29 expectation is superseded. The corrected service must report:

```text
lookahead_violation_count=1575
future_derived_route_count=1575
execution_time_route_availability_pass=0
candidate_failure_reason_codes=LOOKAHEAD_OR_PATH_SAFETY_WEAK,FUTURE_DERIVED_BUCKET_ROUTE_NOT_EXECUTABLE
c28_revised_candidate_ready=0
c29_oos_proof_recommended=0
```

Use `storage/app/watchlist/backtest/c170-c28-g05-execution-route-revalidation.json` as the correction artifact. Do not use the historical C28 artifact to start OOS.

C28 validation is complete for source, PHPUnit, focused runtime, and all-param runtime.

C28 remains IS-only. Do not run OOS, do not create a C28 catalog, and do not change canonical execution rules.

## Final C28 Status

```text
C28_RULE_REVISION_TIEBREAK_SOURCE_IMPLEMENTED=true
PHPUNIT_C28=PASS
C28_FOCUSED_RUNTIME_PASS=true
C28_ALL_PARAM_RUNTIME_PASS=true
C28_REVISED_CANDIDATE_READY=true
C28_C29_OOS_PROOF_RECOMMENDED=true
C28_CATALOG_IMPLEMENTATION_DEFERRED=true
C28_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## PHPUnit Validation

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC28"
```

Result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

.....                                                               5 / 5 (100%)

Time: 00:00.301, Memory: 20.00 MB

OK (5 tests, 90 assertions)
```

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

435 / 435 (100%)

Time: 00:11.834, Memory: 350.00 MB

OK (435 tests, 10768 assertions)
```

## Focused C28 Runtime Command

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c28-rule-revision-tiebreak-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --max-params=3 `
  --max-picks=400 `
  --input-c27-artifact=storage/app/watchlist/backtest/c27-catalog-candidate-raw-ohlc-validation-all-param.json `
  --output=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-focused.json `
  --overwrite
```

Focused result:

```text
status=PASS
reason_code=WS_BT_C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC_READY
scope=IS_ONLY_RULE_REVISION_TIEBREAK_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-focused.json
artifact_hash=94805cfba218fab4baae0a0e25f427f688acb924
diagnostic_profile_count=10
profile_scope=ALL_DEFAULT_MAX_PARAMS_3_MAX_PICKS_400
candidate_profile_code=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY
evaluated_picks_count=395
c27_input_artifact_hash=9bae5ed7227615d64765738b1ff83fa8b9232769
raw_ohlc_validation_pass=1
candidate_avg_ret_net=0.0059334029913666
candidate_median_ret_net=0.006514657980456
candidate_p25_ret_net=-0.0059687717758005
candidate_avg_delta_vs_r09=0.0053832775199967
candidate_param_pass_count=3
candidate_param_fail_count=0
candidate_month_pass_count=26
candidate_month_fail_count=1
candidate_bucket_pass_count=3
candidate_bucket_fail_count=0
c28_revised_candidate_ready=0
c29_oos_proof_recommended=0
oos_executed=0
production_ready=0
```

## All-Param C28 Runtime Command

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c28-rule-revision-tiebreak-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --candidate-profile-code=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY `
  --input-c27-artifact=storage/app/watchlist/backtest/c27-catalog-candidate-raw-ohlc-validation-all-param.json `
  --output=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json `
  --overwrite
```

All-param result:

```text
status=PASS
reason_code=WS_BT_C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC_READY
scope=IS_ONLY_RULE_REVISION_TIEBREAK_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
artifact_hash=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
diagnostic_profile_count=10
profile_scope=ALL_DEFAULT
candidate_profile_code=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY
evaluated_picks_count=1575
c27_input_artifact_hash=9bae5ed7227615d64765738b1ff83fa8b9232769
raw_ohlc_validation_pass=1
raw_r09_avg_ret_net=-0.00021743307264814
raw_r09_median_ret_net=-0.00049987503124219
raw_r09_p25_ret_net=-0.021244659600659
raw_g21_avg_ret_net=0.0010363616567251
raw_g21_median_ret_net=0.010022550739163
raw_g21_p25_ret_net=-0.0038892584821657
candidate_avg_ret_net=0.0061941599395967
candidate_median_ret_net=0.0058664259927798
candidate_p25_ret_net=-0.0065973510332174
candidate_win_rate=0.58603174603175
candidate_avg_delta_vs_r09=0.0064115930122448
candidate_median_delta_vs_r09=0.006366301024022
candidate_p25_delta_vs_r09=0.014647308567441
candidate_param_pass_count=12
candidate_param_fail_count=0
candidate_month_pass_count=27
candidate_month_fail_count=0
candidate_bucket_pass_count=3
candidate_bucket_fail_count=0
lookahead_violation_count=0
c28_revised_candidate_ready=1
c29_oos_proof_recommended=1
c28_catalog_code=NOT_CREATED
oos_executed=0
production_ready=0
```

## Final Interpretation

```text
C28_REVISED_CANDIDATE_READY=true
C28_PRIMARY_PROFILE=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY
C28_C29_OOS_PROOF_RECOMMENDED=true
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C29_OOS_PROOF_WITH_C28_ARTIFACT_HASH_LOCK
```
