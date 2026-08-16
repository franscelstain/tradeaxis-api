# WS C26 Operator Validation Commands

C26 validation is complete for source, PHPUnit, focused runtime, and all-param runtime.

C26 remains IS-only. Do not run OOS, do not create a C26 catalog, and do not change canonical execution rules.

## Final C26 Status

```text
C26_CATALOG_CANDIDATE_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
PHPUNIT_C26=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C26_FOCUSED_RUNTIME_PASS=true
C26_ALL_PARAM_RUNTIME_PASS=true
C26_CATALOG_IMPLEMENTATION_DEFERRED=true
C26_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## PHPUnit Validation

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC26"
```

Result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

......                                                              6 / 6 (100%)

Time: 00:00.920, Memory: 340.00 MB

OK (6 tests, 136 assertions)
```

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

425 / 425 (100%)

Time: 00:06.218, Memory: 350.00 MB

OK (425 tests, 10582 assertions)
```

## Focused C26 Diagnostic Command

The default 512 MB PHP memory limit is not sufficient to decode the C25 all-param JSON artifact. Use the explicit memory limit below.

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c26-catalog-candidate-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148,152,155 `
  --candidate-profile-code=C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT `
  --defensive-comparator-code=C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT `
  --next-open-delay-comparator-code=C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT `
  --diagnostic-profile-codes=C26_G00_CANONICAL_BASELINE,C26_G01_C22_S06_SHADOW_BENCHMARK,C26_G02_C23_R09_BASELINE_BRIDGE,C26_G03_C25_G21_PRIMARY_BALANCED_CANDIDATE,C26_G04_C25_G13_DEFENSIVE_DISTRIBUTION_COMPARATOR,C26_G05_C25_G16_NEXT_OPEN_DELAY_COMPARATOR,C26_G06_C23_R15_DOWNSIDE_COMPARATOR,C26_G07_C23_R16_DOWNSIDE_COMPARATOR,C26_G08_G21_WITH_PARAM_CONSISTENCY_GATE,C26_G09_G21_WITH_MONTH_STABILITY_GATE,C26_G10_G21_WITH_BUCKET_STABILITY_GATE,C26_G16_CATALOG_CANDIDATE_READINESS_SCORE `
  --input-c21-artifact=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json `
  --input-c23-artifact=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json `
  --input-c24-artifact=storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json `
  --input-c25-artifact=storage/app/watchlist/backtest/c25-no-signal-fallback-delay-diagnostic-all-param.json `
  --progress `
  --output=storage/app/watchlist/backtest/c26-catalog-candidate-diagnostic-focused.json `
  --overwrite
```

Focused result:

```text
status=PASS
reason_code=WS_BT_C26_CATALOG_CANDIDATE_DIAGNOSTIC_READY
scope=IS_ONLY_CATALOG_CANDIDATE_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c26-catalog-candidate-diagnostic-focused.json
artifact_hash=b1897f7cf82e2fd56bf79ed1bf7edda5f2cb75f9
diagnostic_profile_count=12
profile_scope=EXPLICIT
evaluated_picks_count=394
path_missing_count=45
c21_input_artifact_hash=d6c6c72d51b40a0c852ce9bbc6a452c55920df13
c23_input_artifact_hash=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
c24_input_artifact_hash=feabfbe720d39155a3d741e509cc69cade3ef31c
c25_input_artifact_hash=d464c5bcce398c5405b069ef277d696a10598288
g21_param_pass_count=3
g21_param_fail_count=0
g21_month_pass_count=22
g21_month_fail_count=5
g21_bucket_pass_count=4
g21_bucket_fail_count=0
raw_ohlc_validation_required=1
derived_mfe_mae_dependency_detected=1
lookahead_violation_count=0
ambiguous_intraday_sequence_count=0
g21_primary_candidate_ready=1
g13_defensive_candidate_ready=1
g16_next_open_delay_component_ready=1
c27_catalog_candidate_implementation_recommended=1
c27_requires_raw_ohlc_validation_first=1
exit_rule_path_still_viable=1
selection_quality_revisit_needed=0
c26_catalog_implementation_deferred=1
c26_catalog_code=NOT_CREATED
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## All-Param C26 Diagnostic Command

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c26-catalog-candidate-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --candidate-profile-code=C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT `
  --defensive-comparator-code=C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT `
  --next-open-delay-comparator-code=C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT `
  --diagnostic-profile-codes=C26_G00_CANONICAL_BASELINE,C26_G01_C22_S06_SHADOW_BENCHMARK,C26_G02_C23_R09_BASELINE_BRIDGE,C26_G03_C25_G21_PRIMARY_BALANCED_CANDIDATE,C26_G04_C25_G13_DEFENSIVE_DISTRIBUTION_COMPARATOR,C26_G05_C25_G16_NEXT_OPEN_DELAY_COMPARATOR,C26_G06_C23_R15_DOWNSIDE_COMPARATOR,C26_G07_C23_R16_DOWNSIDE_COMPARATOR,C26_G08_G21_WITH_PARAM_CONSISTENCY_GATE,C26_G09_G21_WITH_MONTH_STABILITY_GATE,C26_G10_G21_WITH_BUCKET_STABILITY_GATE,C26_G11_G21_WITH_RAW_OHLC_REQUIRED_GATE,C26_G12_G21_WITH_NO_DERIVED_MFE_MAE_DEPENDENCY_GATE,C26_G13_G21_VS_G13_DEFENSIVE_TIEBREAK,C26_G14_G21_VS_G16_DELAY_TIEBREAK,C26_G15_G21_VS_R15_R16_DOWNSIDE_TIEBREAK,C26_G16_CATALOG_CANDIDATE_READINESS_SCORE `
  --input-c21-artifact=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json `
  --input-c23-artifact=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json `
  --input-c24-artifact=storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json `
  --input-c25-artifact=storage/app/watchlist/backtest/c25-no-signal-fallback-delay-diagnostic-all-param.json `
  --progress `
  --output=storage/app/watchlist/backtest/c26-catalog-candidate-diagnostic-all-param.json `
  --overwrite
```

All-param result:

```text
status=PASS
reason_code=WS_BT_C26_CATALOG_CANDIDATE_DIAGNOSTIC_READY
scope=IS_ONLY_CATALOG_CANDIDATE_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c26-catalog-candidate-diagnostic-all-param.json
artifact_hash=e31ee7fd9bfc0cfb05b88ce5ff6fcbc9111d4b56
diagnostic_profile_count=17
profile_scope=EXPLICIT
evaluated_picks_count=1575
path_missing_count=45
c21_input_artifact_hash=d6c6c72d51b40a0c852ce9bbc6a452c55920df13
c23_input_artifact_hash=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
c24_input_artifact_hash=feabfbe720d39155a3d741e509cc69cade3ef31c
c25_input_artifact_hash=d464c5bcce398c5405b069ef277d696a10598288
r09_avg_ret_net=-0.00021743307264814
r09_median_ret_net=-0.00049987503124219
r09_p25_ret_net=-0.021244659600659
r09_win_rate=0.47174603174603
g21_avg_ret_net=4.5024430405955E-5
g21_median_ret_net=0.0094868843085056
g21_p25_ret_net=-0.0044988752811797
g21_win_rate=0.63174603174603
g13_avg_ret_net=-0.0022569891038408
g13_median_ret_net=0.0044929823419092
g13_p25_ret_net=-0.00049987503124219
g13_win_rate=0.73206349206349
g16_avg_ret_net=-0.00078855778982916
g16_median_ret_net=0.0095805282237183
g16_p25_ret_net=-0.017162872333018
g16_win_rate=0.57587301587302
g21_param_pass_count=8
g21_param_fail_count=4
g21_month_pass_count=24
g21_month_fail_count=3
g21_bucket_pass_count=4
g21_bucket_fail_count=0
raw_ohlc_validation_required=1
derived_mfe_mae_dependency_detected=1
lookahead_violation_count=0
ambiguous_intraday_sequence_count=0
g21_primary_candidate_ready=1
g13_defensive_candidate_ready=1
g16_next_open_delay_component_ready=1
c27_catalog_candidate_implementation_recommended=1
c27_requires_raw_ohlc_validation_first=1
exit_rule_path_still_viable=1
selection_quality_revisit_needed=0
c26_catalog_implementation_deferred=1
c26_catalog_code=NOT_CREATED
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## Final Next Step

```text
NEXT_STEP=C27_CATALOG_CANDIDATE_IMPLEMENTATION_WITH_RAW_OHLC_VALIDATION_FIRST_IS_ONLY
```

C27 may implement a catalog-candidate source path only after adding raw OHLC/high-low validation first. C26 does not allow OOS, production catalog creation, or promotion.
