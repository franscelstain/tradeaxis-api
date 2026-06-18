# WS C27 Operator Validation Commands

C27 validation is complete for source, PHPUnit, focused runtime, and all-param runtime.

C27 remains IS-only. Do not run OOS, do not create a C27 catalog, and do not change canonical execution rules.

## Final C27 Status

```text
C27_RAW_OHLC_VALIDATION_SOURCE_IMPLEMENTED=true
PHPUNIT_C27=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C27_FOCUSED_RUNTIME_PASS=true
C27_ALL_PARAM_RUNTIME_PASS=true
C27_RAW_OHLC_VALIDATION_PASS=true
C27_G21_RAW_CATALOG_CANDIDATE_READY=false
C27_C28_OOS_PROOF_RECOMMENDED=false
C27_CATALOG_IMPLEMENTATION_DEFERRED=true
C27_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## PHPUnit Validation

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC27"
```

Result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

.....                                                               5 / 5 (100%)

Time: 00:00.318, Memory: 20.00 MB

OK (5 tests, 96 assertions)
```

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

430 / 430 (100%)

Time: 00:11.197, Memory: 350.00 MB

OK (430 tests, 10678 assertions)
```

## Focused C27 Runtime Command

Use an explicit PHP memory limit because C27 decodes the large C26 all-param artifact.

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c27-catalog-candidate-raw-ohlc-validate `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --max-params=3 `
  --max-picks=400 `
  --input-c26-artifact=storage/app/watchlist/backtest/c26-catalog-candidate-diagnostic-all-param.json `
  --input-c21-artifact=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json `
  --output=storage/app/watchlist/backtest/c27-catalog-candidate-raw-ohlc-validation-focused.json `
  --overwrite
```

Focused result:

```text
status=PASS
reason_code=WS_BT_C27_RAW_OHLC_VALIDATION_READY
scope=IS_ONLY_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION
artifact_path=storage/app/watchlist/backtest/c27-catalog-candidate-raw-ohlc-validation-focused.json
artifact_hash=ec42b7585e166f72ab57794a3de4667c5f0a04ac
validation_profile_count=9
profile_scope=ALL_DEFAULT_MAX_PARAMS_3_MAX_PICKS_400
evaluated_picks_count=395
raw_ohlc_validated_count=395
raw_ohlc_missing_count=0
raw_ohlc_validation_pass=1
c21_input_artifact_hash=d6c6c72d51b40a0c852ce9bbc6a452c55920df13
c26_input_artifact_hash=e31ee7fd9bfc0cfb05b88ce5ff6fcbc9111d4b56
raw_r09_avg_ret_net=0.00055012547136993
raw_g21_avg_ret_net=0.0010593760028869
raw_g21_median_ret_net=0.0099681739026001
raw_g21_p25_ret_net=-0.00050351870599944
g21_raw_beats_r09=1
g21_raw_catalog_candidate_ready=0
c28_oos_proof_recommended=0
c27_catalog_code=NOT_CREATED
oos_executed=0
production_ready=0
```

## All-Param C27 Runtime Command

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c27-catalog-candidate-raw-ohlc-validate `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --input-c26-artifact=storage/app/watchlist/backtest/c26-catalog-candidate-diagnostic-all-param.json `
  --input-c21-artifact=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json `
  --output=storage/app/watchlist/backtest/c27-catalog-candidate-raw-ohlc-validation-all-param.json `
  --overwrite
```

All-param result:

```text
status=PASS
reason_code=WS_BT_C27_RAW_OHLC_VALIDATION_READY
scope=IS_ONLY_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION
artifact_path=storage/app/watchlist/backtest/c27-catalog-candidate-raw-ohlc-validation-all-param.json
artifact_hash=9bae5ed7227615d64765738b1ff83fa8b9232769
validation_profile_count=9
profile_scope=ALL_DEFAULT
evaluated_picks_count=1575
raw_ohlc_validated_count=1575
raw_ohlc_missing_count=0
raw_ohlc_validation_pass=1
c21_input_artifact_hash=d6c6c72d51b40a0c852ce9bbc6a452c55920df13
c26_input_artifact_hash=e31ee7fd9bfc0cfb05b88ce5ff6fcbc9111d4b56
raw_r09_avg_ret_net=-0.00021743307264814
raw_r09_median_ret_net=-0.00049987503124219
raw_r09_p25_ret_net=-0.021244659600659
raw_g21_avg_ret_net=0.0010363616567251
raw_g21_median_ret_net=0.010022550739163
raw_g21_p25_ret_net=-0.0038892584821657
raw_g13_avg_ret_net=0.0014577651738231
raw_g16_avg_ret_net=0.001722767070267
g21_raw_beats_r09=1
g21_raw_catalog_candidate_ready=0
c28_oos_proof_recommended=0
c27_catalog_code=NOT_CREATED
derived_mfe_mae_used_for_execution=0
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## Final Interpretation

```text
C27_RAW_OHLC_VALIDATION_PASS=true
C27_RAW_OHLC_VALIDATED_COUNT=1575
C27_DERIVED_MFE_MAE_DEPENDENCY_REMOVED=true
C27_LOOKAHEAD_VIOLATION_COUNT=0
C27_G21_RAW_BEATS_R09=true
C27_G21_RAW_CATALOG_CANDIDATE_READY=false
C27_FAILURE_REASON=G21_BUCKET_STABILITY_WEAK
C27_C28_OOS_PROOF_RECOMMENDED=false
NEXT_STEP=C28_RULE_REVISION_OR_G13_G16_TIEBREAK_DIAGNOSTIC_IS_ONLY
```
