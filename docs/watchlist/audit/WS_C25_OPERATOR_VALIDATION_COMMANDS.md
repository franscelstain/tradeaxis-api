# WS C25 Operator Validation Commands

C25 validation is IS-only. Do not run OOS, do not create a C25 catalog, and do not change canonical execution rules.

## Current source status

```text
C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
C25_RUNTIME_VALIDATION_REQUIRED=true
C25_DIAGNOSTIC_RUNTIME_PASS=NOT_RUN_BY_OPERATOR
C25_CATALOG_IMPLEMENTATION_DEFERRED=true
C25_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## Syntax checks

```powershell
php -l app/Application/Watchlist/Services/WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService.php
php -l app/Console/Commands/Watchlist/RunBacktestC25NoSignalFallbackDelayDiagnoseCommand.php
php -l tests/Unit/Watchlist/WatchlistBacktestC25NoSignalFallbackDelayDiagnosticServiceTest.php
php -l tests/Unit/Watchlist/WatchlistBacktestC25StaticGuardTest.php
php -l app/Console/Kernel.php
```

Expected:

```text
No syntax errors detected
```

## PHPUnit validation

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC25"
vendor\bin\phpunit tests\Unit\Watchlist
```

If the local environment cannot run PHPUnit, record:

```text
PHPUNIT_C25=OPERATOR_VALIDATION_REQUIRED
FULL_WATCHLIST_PHPUNIT=OPERATOR_VALIDATION_REQUIRED
```

Do not claim PASS without actual output.

## Command registration check

```powershell
php artisan list | Select-String -Pattern "watchlist:backtest-c25-no-signal-fallback-delay-diagnose"
```

Expected command:

```text
watchlist:backtest-c25-no-signal-fallback-delay-diagnose
```

## Focused C25 diagnostic command

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c25-no-signal-fallback-delay-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148,152,155 `
  --diagnostic-profile-codes=C25_G00_CANONICAL_BASELINE,C25_G01_C22_S06_SHADOW_BENCHMARK,C25_G02_C23_R09_BASELINE_BRIDGE,C25_G03_C23_R15_DOWNSIDE_COMBO_COMPARATOR,C25_G04_C23_R16_DOWNSIDE_COMBO_COMPARATOR,C25_G05_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN,C25_G06_NO_SIGNAL_FALLBACK_EXIT_D4_OPEN,C25_G09_R09_PLUS_NO_SIGNAL_D3_DAMAGE_CONTROL,C25_G11_R09_PLUS_R15_STYLE_DOWNSIDE_CONTROL,C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT,C25_G15_PREPLANNED_INTRADAY_TARGET_1_00PCT,C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT `
  --input-c23-artifact=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json `
  --input-c24-artifact=storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json `
  --input-c21-artifact=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json `
  --progress `
  --output=storage/app/watchlist/backtest/c25-no-signal-fallback-delay-diagnostic-focused.json `
  --overwrite
```

## All-param C25 diagnostic command

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c25-no-signal-fallback-delay-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --diagnostic-profile-codes=C25_G00_CANONICAL_BASELINE,C25_G01_C22_S06_SHADOW_BENCHMARK,C25_G02_C23_R09_BASELINE_BRIDGE,C25_G03_C23_R15_DOWNSIDE_COMBO_COMPARATOR,C25_G04_C23_R16_DOWNSIDE_COMBO_COMPARATOR,C25_G05_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN,C25_G06_NO_SIGNAL_FALLBACK_EXIT_D4_OPEN,C25_G07_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN_IF_MAE_LT_2PCT,C25_G08_NO_SIGNAL_FALLBACK_EXIT_D4_OPEN_IF_MAE_LT_2PCT,C25_G09_R09_PLUS_NO_SIGNAL_D3_DAMAGE_CONTROL,C25_G10_R09_PLUS_NO_SIGNAL_D4_DAMAGE_CONTROL,C25_G11_R09_PLUS_R15_STYLE_DOWNSIDE_CONTROL,C25_G12_R09_PLUS_R16_STYLE_DOWNSIDE_CONTROL,C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT,C25_G14_PREPLANNED_INTRADAY_TARGET_0_75PCT,C25_G15_PREPLANNED_INTRADAY_TARGET_1_00PCT,C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT,C25_G17_PREPLANNED_TARGET_0_75PCT_WITH_STOP_1_50PCT,C25_G18_PREPLANNED_TARGET_1_00PCT_WITH_STOP_2_00PCT,C25_G19_NEXT_OPEN_DELAY_ROWS_ONLY_R09,C25_G20_NO_SIGNAL_FALLBACK_ROWS_ONLY_R09,C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT `
  --input-c23-artifact=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json `
  --input-c24-artifact=storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json `
  --input-c21-artifact=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json `
  --progress `
  --output=storage/app/watchlist/backtest/c25-no-signal-fallback-delay-diagnostic-all-param.json `
  --overwrite
```

## Expected C25 console markers

```text
status=PASS
reason_code=WS_BT_C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC_READY
scope=IS_ONLY_NO_SIGNAL_FALLBACK_AND_NEXT_OPEN_DELAY_DIAGNOSTIC
artifact_path=...
artifact_hash=...
diagnostic_profile_count=...
profile_scope=...
evaluated_picks_count=...
path_missing_count=...
c23_input_artifact_hash=...
c24_input_artifact_hash=...
canonical_avg_ret_net=...
c22_s06_avg_ret_net=...
c23_r09_avg_ret_net=...
c23_r15_p25_ret_net=...
c23_r16_p25_ret_net=...
no_signal_fallback_count=...
next_open_delay_count=...
best_profile_code_by_avg=...
best_profile_code_by_median=...
best_profile_code_by_p25=...
best_profile_code_by_distribution_balance=...
best_no_signal_fallback_profile=...
best_next_open_delay_profile=...
no_signal_fallback_fix_found=0|1
next_open_delay_fix_found=0|1
distribution_balance_candidate_found=0|1
intraday_preplanned_order_candidate_found=0|1
exit_rule_path_still_viable=0|1
selection_quality_revisit_needed=0|1
c26_catalog_candidate_diagnostic_recommended=0|1
c25_catalog_implementation_deferred=1
c25_catalog_code=NOT_CREATED
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## Post-runtime documentation rule

Only after operator runtime output exists, update:

```text
docs/watchlist/audit/WS_C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC.md
docs/watchlist/audit/_artifacts/c25-no-signal-fallback-delay-diagnostic-source-summary.json
docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md
docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md
```

Keep these markers honest:

```text
C25_DIAGNOSTIC_RUNTIME_PASS=true/false/NOT_RUN
C25_NO_SIGNAL_FALLBACK_FIX_FOUND=true/false/NOT_RUN
C25_NEXT_OPEN_DELAY_FIX_FOUND=true/false/NOT_RUN
C25_DISTRIBUTION_BALANCE_CANDIDATE_FOUND=true/false/NOT_RUN
C25_INTRADAY_PREPLANNED_ORDER_CANDIDATE_FOUND=true/false/NOT_RUN
C25_EXIT_RULE_PATH_STILL_VIABLE=true/false/NOT_RUN
C25_SELECTION_QUALITY_REVISIT_NEEDED=true/false/NOT_RUN
C25_C26_CATALOG_CANDIDATE_DIAGNOSTIC_RECOMMENDED=true/false/NOT_RUN
C25_CATALOG_IMPLEMENTATION_DEFERRED=true
C25_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```
