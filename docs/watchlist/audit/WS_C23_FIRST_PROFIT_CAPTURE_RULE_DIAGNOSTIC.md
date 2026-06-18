# WS C23 First Profit Capture Rule Diagnostic

C23 is an IS-only diagnostic that converts the C22 first-profitable-close shadow direction into realizable non-lookahead rule candidates. It is not a catalog, not OOS, not production tuning, and not a mutation of the canonical execution model.

C23 keeps the recommendation set frozen from the C19 selection diagnostic path, reads D+1 through D+5 OHLC only after the pick is fixed, and evaluates rule exits for measurement only. The canonical model remains:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

## Current status

```text
C23_FIRST_PROFIT_CAPTURE_RULE_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
PHPUNIT_C23_SERVICE=PASS
PHPUNIT_C23_STATIC_GUARD=PASS
C23_COMMAND_REGISTERED=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C23_FOCUSED_RUNTIME_PASS=true
C23_ALL_PARAM_RUNTIME_PASS=true
C23_DIAGNOSTIC_RUNTIME_PASS=true
C23_FOCUSED_ARTIFACT_HASH=5e4c57c85f196749b269400316215c6a80f431b7
C23_ALL_PARAM_ARTIFACT_HASH=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
C23_FIRST_PROFIT_CAPTURE_RULE_SIGNAL_FOUND=true
C23_NON_LOOKAHEAD_RULE_CANDIDATE_FOUND=true
C23_C22_SHADOW_GAP_ACCEPTABLE=false
C23_PARAM_CONSISTENCY_FOUND=true
C23_MONTH_STABILITY_SUFFICIENT=true
C23_CATALOG_CODE=NOT_CREATED
C23_CATALOG_IMPLEMENTATION_DEFERRED=true
C23_CATALOG_ALLOWED=false
OOS_NOT_RUN=true
production_ready=0
C19_CATALOG_CANDIDATE_FAILED_PRESERVED=true
C20_DATE_GATE_NOT_ENOUGH_PRESERVED=true
C21_EXECUTION_SIGNAL_FOUND_PRESERVED=true
C22_EXIT_CAPTURE_SIGNAL_FOUND_PRESERVED=true
NO_C01_TO_C22_MUTATION=true
```

C23 runtime artifacts are now available for the frozen IS window. They remain diagnostic evidence only and do not authorize catalog creation, OOS, or production promotion.

## Implemented source components

```text
Service:
app/Application/Watchlist/Services/WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticService.php

Command:
app/Console/Commands/Watchlist/RunBacktestC23FirstProfitCaptureRuleDiagnoseCommand.php

Command signature:
watchlist:backtest-c23-first-profit-capture-rule-diagnose

Tests:
tests/Unit/Watchlist/WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC23StaticGuardTest.php
```

The command is registered in `app/Console/Kernel.php` and is not scheduled.

## Rule profiles

C23 implements rule candidate profiles only:

```text
C23_R00_CANONICAL_BASELINE
C23_R01_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0
C23_R02_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0_25PCT
C23_R03_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_0_50PCT
C23_R04_EXIT_NEXT_OPEN_AFTER_D1_CLOSE_PROFIT_GT_1_00PCT
C23_R05_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0
C23_R06_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0_25PCT
C23_R07_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_0_50PCT
C23_R08_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_CLOSE_PROFIT_GT_1_00PCT
C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
C23_R10_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0_25PCT
C23_R11_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0_50PCT
C23_R12_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_1_00PCT
C23_R13_COMPRESS_HOLD_TO_D3_IF_NO_PROFIT_BY_D2
C23_R14_COMPRESS_HOLD_TO_D4_IF_NO_PROFIT_BY_D3
C23_R15_COMBO_D1_D2_FIRST_PROFIT_CAPTURE_OR_D3_NO_PROFIT_EXIT
C23_R16_COMBO_D1_D2_D3_FIRST_PROFIT_CAPTURE_OR_D4_NO_PROFIT_EXIT
C23_R17_COMBO_D1_PROFIT_CAPTURE_GT_0_50PCT_OR_D3_DAMAGE_CONTROL
C23_R18_COMBO_D1_D2_PROFIT_CAPTURE_GT_0_50PCT_OR_D3_DAMAGE_CONTROL
```

These profiles are not production paramsets and are not catalog rows.

## Non-lookahead rule

C23 enforces the non-lookahead boundary in row-level output:

```text
D1 close signal exits D2 open
D2 close signal exits D3 open
D3 close signal exits D4 open
rule_signal_day_offset < rule_exit_day_offset
lookahead_safe=true
```

C22 `S06_FIRST_PROFITABLE_CLOSE_EXIT` is recomputed only as a benchmark target:

```text
c22_shadow_s06_used_for_selection=false
rule_exit_used_for_selection=false
rule_ret_net_used_for_selection=false
future_path_price_used_for_selection=false
```

## Artifact surface

C23 writes an aggregate JSON artifact containing:

```text
rule_profiles
pick_rule_rows
canonical_summary
c22_shadow_s06_summary
rule_profile_summary
first_profit_capture_summary
damage_control_summary
combo_rule_summary
param_consistency_summary
profile_consistency_summary
month_stability_summary
lookahead_safety_summary
decision
safety_boundaries
```

The artifact marks `catalog_allowed=false`, `oos_allowed=false`, `C23_CATALOG_CODE=NOT_CREATED`, and `C23_CATALOG_IMPLEMENTATION_DEFERRED=true`.

## Validation evidence in this source patch

Actually run in this session:

```text
vendor\bin\phpunit.bat tests/Unit/Watchlist/WatchlistBacktestC23FirstProfitCaptureRuleDiagnosticServiceTest.php
OK (3 tests, 426 assertions)

vendor\bin\phpunit.bat tests/Unit/Watchlist/WatchlistBacktestC23StaticGuardTest.php
OK (3 tests, 61 assertions)

vendor\bin\phpunit.bat tests/Unit/Watchlist --filter "WatchlistBacktestC23"
OK (6 tests, 490 assertions)

vendor\bin\phpunit.bat tests/Unit/Watchlist
OK (409 tests, 10292 assertions)

php artisan list | Select-String -Pattern "watchlist:backtest-c23-first-profit-capture-rule-diagnose"
command_registered=true
```

Runtime attempts and final runtime evidence:

```text
C23_INITIAL_ALL_PROFILE_ALL_PARAM_RUNTIME=TIMEOUT_AFTER_184_SECONDS_NO_ARTIFACT
C23_INITIAL_FOCUSED_RUNTIME_PARAM_148_152_155=TIMEOUT_AFTER_184_SECONDS_NO_ARTIFACT
C23_RUNTIME_TUNING=REUSE_C19_SELECTION_ARTIFACT
C23_ALL_PARAM_RESOURCE_SETTING=php -d memory_limit=2048M

C23_FOCUSED_RUNTIME_PASS=true
C23_FOCUSED_ARTIFACT_PATH=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-focused.json
C23_FOCUSED_ARTIFACT_HASH=5e4c57c85f196749b269400316215c6a80f431b7
C23_FOCUSED_EVALUATED_PICKS=394
C23_FOCUSED_PATH_MISSING=11

C23_ALL_PARAM_RUNTIME_PASS=true
C23_ALL_PARAM_ARTIFACT_PATH=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json
C23_ALL_PARAM_ARTIFACT_HASH=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
C23_ALL_PARAM_EVALUATED_PICKS=1575
C23_ALL_PARAM_PATH_MISSING=45
C23_ALL_PARAM_RULE_PROFILE_COUNT=19
C23_ALL_PARAM_LOOKAHEAD_VIOLATIONS=0
```

C23 all-param result:

```text
canonical_avg_ret_net=-0.0046903074630424
canonical_median_ret_net=-0.0041104817284074
canonical_p25_ret_net=-0.023750212591414
canonical_win_rate=0.39238095238095
canonical_gave_back_profit_rate=0.55365079365079

c22_shadow_s06_avg_ret_net=-0.00016239014891423
c22_shadow_s06_median_ret_net=0.0042799597180262
c22_shadow_s06_p25_ret_net=-0.0082526173206962
c22_shadow_s06_win_rate=0.59619047619048

best_rule_profile_code_by_avg=C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
best_rule_profile_code_by_win_rate=C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
best_rule_profile_code_by_median=C23_R00_CANONICAL_BASELINE
best_rule_profile_code_by_p25=C23_R00_CANONICAL_BASELINE
best_rule_profile_code_by_closest_to_c22_s06=C23_R00_CANONICAL_BASELINE
first_profit_capture_rule_signal_found=1
c22_shadow_gap_acceptable=0
non_lookahead_rule_candidate_found=1
param_consistency_found=1
month_stability_sufficient=1
```

Best non-lookahead average/win-rate rule:

```text
C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
avg_ret_net=-0.000217
median_ret_net=-0.000500
p25_ret_net=-0.021245
win_rate=0.4717
avg_delta_vs_canonical=0.004473
median_delta_vs_canonical=0
p25_delta_vs_canonical=0
improved_pick_rate=0.2559
```

Closed C23 consistency gap:

```text
param_consistency_best_profile=C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
benefited_param_count=12
evaluated_param_count=12
month_stability_best_profile=C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
positive_month_count=23
month_count=27
positive_month_rate=0.8518518518518519
```

Still not run:

```text
OOS
```

## Preserved boundaries

```text
IS_ONLY=true
OOS_NOT_RUN=true
production_ready=0
C23_CATALOG_CODE=NOT_CREATED
C23_CATALOG_IMPLEMENTATION_DEFERRED=true
NO_PROMOTION=true
NO_OOS=true
NO_TICKER_BLACKLIST=true
NO_MONTH_BLACKLIST=true
NO_SECTOR_WHITELIST=true
NO_BEST_OF_FAILED_BINDING=true
NO_C01_TO_C22_MUTATION=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
NO_C21_REOPEN=true
NO_C22_REOPEN=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
```

C23 is ready as a source-level diagnostic implementation. It does not unlock catalog creation, OOS, promotion, production readiness, or any C19-C22 reopening.
