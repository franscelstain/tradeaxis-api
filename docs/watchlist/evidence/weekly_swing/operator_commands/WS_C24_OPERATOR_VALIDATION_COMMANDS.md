# WS C24 Operator Validation Commands

C24 validation is IS-only. Do not run OOS, do not create a C24 catalog, and do not change canonical execution rules.

## Current validation status

```text
C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
PHPUNIT_C24_FILTER=PASS
C24_COMMAND_REGISTERED=PASS
C24_RUNTIME_VALIDATED=true
C24_ALL_PARAM_RUNTIME_PASS=true
C24_ARTIFACT_HASH=feabfbe720d39155a3d741e509cc69cade3ef31c
C24_DECISION_STATUS=C24_C22_SHADOW_GAP_STILL_MATERIAL
C24_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## PHPUnit and syntax evidence already run

Operator ran:

```powershell
php -l app/Application/Watchlist/Services/WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService.php
```

Result:

```text
No syntax errors detected
```

Operator ran:

```powershell
php -l app/Console/Commands/Watchlist/RunBacktestC24C22ShadowGapBridgeDiagnoseCommand.php
```

Result:

```text
No syntax errors detected
```

Operator ran:

```powershell
vendor\bin\phpunit.bat tests/Unit/Watchlist --filter "WatchlistBacktestC24"
```

Result:

```text
OK (4 tests, 64 assertions)
```

Operator reran the C23 filter after C24 registration:

```powershell
vendor\bin\phpunit.bat tests/Unit/Watchlist --filter "WatchlistBacktestC23"
```

Result:

```text
OK (6 tests, 490 assertions)
```

Operator ran the full Watchlist regression after C24:

```powershell
vendor\bin\phpunit.bat tests/Unit/Watchlist
```

Result:

```text
OK (413 tests, 10356 assertions)
```

Operator checked command registration:

```powershell
php artisan list | Select-String -Pattern "watchlist:backtest-c24-c22-shadow-gap-bridge-diagnose"
```

Result:

```text
watchlist:backtest-c24-c22-shadow-gap-bridge-diagnose  Run C24 IS-only C22 shadow gap bridge diagnostic from the C23 artifact without catalog, OOS, or production readiness.
```

## C24 diagnostic runtime

C24 reads the C23 all-param artifact and writes a compact bridge artifact. The full C23 artifact is large, so use the same raised memory limit used by the C23 all-param run.

Runtime command:

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c24-c22-shadow-gap-bridge-diagnose `
  --input=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json `
  --output=storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json `
  --overwrite
```

Runtime result:

```text
status=PASS
reason_code=WS_BT_C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC_READY
scope=IS_ONLY_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json
artifact_hash=feabfbe720d39155a3d741e509cc69cade3ef31c
c23_artifact_hash=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
candidate_profile_code=C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
evaluated_picks_count=1575
candidate_avg_ret_net=-0.00021743307264814
candidate_median_ret_net=-0.00049987503124219
candidate_p25_ret_net=-0.021244659600659
candidate_win_rate=0.47174603174603
c22_shadow_s06_avg_ret_net=-0.00016239014891423
c22_shadow_s06_median_ret_net=0.0042799597180262
c22_shadow_s06_p25_ret_net=-0.0082526173206962
c22_shadow_s06_win_rate=0.59619047619048
avg_gap_vs_c22_s06=5.5042923733914E-5
median_gap_vs_c22_s06=0.0047798347492684
p25_gap_vs_c22_s06=0.012992042279963
win_rate_gap_vs_c22_s06=0.12444444444444
avg_capture_ratio_vs_c22_s06=0.98784365528006
median_capture_ratio_vs_c22_s06=0.43032380598996
p25_capture_ratio_vs_c22_s06=0.16167366271912
win_rate_capture_ratio_vs_c22_s06=0.38940809968847
rows_where_c22_beats_candidate_rate=0.35492063492063
dominant_gap_component=no_rule_profit_signal_before_fallback
c24_gap_bridge_explained=1
c24_catalog_implementation_deferred=1
c24_catalog_code=NOT_CREATED
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## Expected C24 console markers

```text
status=
reason_code=
scope=
artifact_path=
artifact_hash=
c23_artifact_hash=
candidate_profile_code=
evaluated_picks_count=
candidate_avg_ret_net=
candidate_median_ret_net=
candidate_p25_ret_net=
candidate_win_rate=
c22_shadow_s06_avg_ret_net=
c22_shadow_s06_median_ret_net=
c22_shadow_s06_p25_ret_net=
c22_shadow_s06_win_rate=
avg_gap_vs_c22_s06=
median_gap_vs_c22_s06=
p25_gap_vs_c22_s06=
win_rate_gap_vs_c22_s06=
avg_capture_ratio_vs_c22_s06=
median_capture_ratio_vs_c22_s06=
p25_capture_ratio_vs_c22_s06=
win_rate_capture_ratio_vs_c22_s06=
rows_where_c22_beats_candidate_rate=
dominant_gap_component=
c24_gap_bridge_explained=
c24_catalog_implementation_deferred=1
c24_catalog_code=NOT_CREATED
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

Do not run any OOS command from C24 validation. C24 remains diagnostic-only even though it explains the remaining C22 shadow gap.
