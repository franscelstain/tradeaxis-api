# WS C23 Operator Validation Commands

C23 validation is IS-only. Do not run OOS, do not create a C23 catalog, and do not change canonical execution rules.

## Current validation status

```text
C23_FIRST_PROFIT_CAPTURE_RULE_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
PHPUNIT_C23_SERVICE=PASS
PHPUNIT_C23_STATIC_GUARD=PASS
C23_COMMAND_REGISTERED=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C23_FOCUSED_RUNTIME_PASS=true
C23_ALL_PARAM_RUNTIME_PASS=true
C23_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## PHPUnit evidence already run

Operator ran:

```powershell
vendor\bin\phpunit.bat tests/Unit/Watchlist/WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticServiceTest.php
```

Result:

```text
OK (3 tests, 426 assertions)
```

Operator ran:

```powershell
vendor\bin\phpunit.bat tests/Unit/Watchlist/WatchlistBacktestC23StaticGuardTest.php
```

Result:

```text
OK (3 tests, 61 assertions)
```

Operator checked command registration:

```powershell
php artisan list | Select-String -Pattern "watchlist:backtest-c23-first-profit-capture-rule-diagnose"
```

Result:

```text
watchlist:backtest-c23-first-profit-capture-rule-diagnose  Run C23 IS-only first profit capture rule diagnostic without catalog, OOS, or production readiness.
```

Operator also ran the combined C23 filter:

```powershell
vendor\bin\phpunit.bat tests/Unit/Watchlist --filter "WatchlistBacktestC23"
```

Result:

```text
OK (6 tests, 490 assertions)
```

## Full regression evidence already run

Operator ran:

```powershell
vendor\bin\phpunit.bat tests\Unit\Watchlist
```

Result:

```text
OK (409 tests, 10292 assertions)
```

## C23 diagnostic runtime attempts and final evidence

All-profile all-param runtime was attempted with the full `C23_R00` to `C23_R18` profile list. The command exceeded the 184-second tool timeout, left a PHP process running, and no `c23-first-profit-capture-rule-diagnostic-all-param.json` artifact was created. The PHP process was stopped.

Focused runtime was then attempted for `param_ids=148,152,155` with default fast C23 profiles. The command also exceeded the 184-second tool timeout, left a PHP process running, and no `c23-first-profit-capture-rule-diagnostic-focused.json` artifact was created. The PHP process was stopped.

Runtime was then tuned to reuse the existing C19 selection diagnostic artifact instead of recomputing it. Focused C23 completed with cached selection. All-param C23 completed after running PHP with `memory_limit=2048M` to allow the large aggregate artifact to be built.

Focused runtime command attempted:

```powershell
php artisan watchlist:backtest-c23-first-profit-capture-rule-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148,152,155 `
  --rule-profile-codes=C23_R00_CANONICAL_BASELINE,C23_R01_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0,C23_R03_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0_50PCT,C23_R05_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0,C23_R07_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0_50PCT,C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0,C23_R13_COMPRESS_HOLD_TO_D3_IF_NO_PROFIT_BY_D2,C23_R15_COMBO_D1_D2_FIRST_PROFIT_CAPTURE_OR_D3_NO_PROFIT_EXIT `
  --progress `
  --output=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-focused.json `
  --overwrite
```

Focused runtime command that completed:

```powershell
php artisan watchlist:backtest-c23-first-profit-capture-rule-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148,152,155 `
  --selection-output=storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-focused.json.c19-selection-analysis.json `
  --reuse-selection-artifact `
  --progress `
  --output=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-focused.json `
  --overwrite
```

Focused result:

```text
status=PASS
artifact_hash=5e4c57c85f196749b269400316215c6a80f431b7
evaluated_picks_count=394
path_missing_count=11
rule_profile_count=8
first_profit_capture_rule_signal_found=1
c22_shadow_gap_acceptable=0
non_lookahead_rule_candidate_found=1
oos_executed=0
production_ready=0
```

Expected C23 console markers include:

```text
status=
reason_code=
scope=
artifact_path=
artifact_hash=
rule_profile_count=
profile_scope=
evaluated_picks_count=
path_missing_count=
canonical_avg_ret_net=
canonical_median_ret_net=
canonical_p25_ret_net=
canonical_win_rate=
canonical_gave_back_profit_rate=
c22_shadow_s06_avg_ret_net=
c22_shadow_s06_median_ret_net=
c22_shadow_s06_p25_ret_net=
c22_shadow_s06_win_rate=
best_rule_profile_code_by_median=
best_rule_profile_code_by_p25=
best_rule_profile_code_by_win_rate=
best_rule_profile_code_by_closest_to_c22_s06=
best_rule_median_delta_vs_canonical=
best_rule_p25_delta_vs_canonical=
best_rule_giveback_reduction_vs_canonical=
best_rule_profit_capture_gap_vs_c22_s06=
first_profit_capture_rule_signal_found=
c22_shadow_gap_acceptable=
non_lookahead_rule_candidate_found=
damage_control_candidate_found=
combo_rule_candidate_found=
param_consistency_found=
month_stability_sufficient=
c23_catalog_implementation_deferred=1
c23_catalog_code=NOT_CREATED
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## All-profile C23 diagnostic runtime attempted

All-profile IS-only runtime command completed:

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c23-first-profit-capture-rule-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --rule-profile-codes=C23_R00_CANONICAL_BASELINE,C23_R01_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0,C23_R02_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0_25PCT,C23_R03_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0_50PCT,C23_R04_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_1_00PCT,C23_R05_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0,C23_R06_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0_25PCT,C23_R07_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0_50PCT,C23_R08_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_1_00PCT,C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0,C23_R10_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0_25PCT,C23_R11_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0_50PCT,C23_R12_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_1_00PCT,C23_R13_COMPRESS_HOLD_TO_D3_IF_NO_PROFIT_BY_D2,C23_R14_COMPRESS_HOLD_TO_D4_IF_NO_PROFIT_BY_D3,C23_R15_COMBO_D1_D2_FIRST_PROFIT_CAPTURE_OR_D3_NO_PROFIT_EXIT,C23_R16_COMBO_D1_D2_D3_FIRST_PROFIT_CAPTURE_OR_D4_NO_PROFIT_EXIT,C23_R17_COMBO_D1_PROFIT_CAPTURE_GT_0_50PCT_OR_D3_DAMAGE_CONTROL,C23_R18_COMBO_D1_D2_PROFIT_CAPTURE_GT_0_50PCT_OR_D3_DAMAGE_CONTROL `
  --selection-output=storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-all-param.json.c19-selection-analysis.json `
  --reuse-selection-artifact `
  --progress `
  --output=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json `
  --overwrite
```

All-param result:

```text
status=PASS
artifact_hash=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
evaluated_picks_count=1575
path_missing_count=45
rule_profile_count=19
best_rule_profile_code_by_avg=C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
best_rule_profile_code_by_win_rate=C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
first_profit_capture_rule_signal_found=1
c22_shadow_gap_acceptable=0
non_lookahead_rule_candidate_found=1
param_consistency_found=1
month_stability_sufficient=1
oos_executed=0
production_ready=0
```

Do not run any OOS command from C23 validation. C23 remains diagnostic-only even though one rule family improves average return and win rate in IS.
