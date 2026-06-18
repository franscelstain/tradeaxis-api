# WS C21 Operator Validation Commands

C21 validation is IS-only. Do not run OOS, do not create a C21 catalog, and do not promote C20 G03.

This document now records final operator validation evidence for C21.

## Final validation status

```text
PHPUNIT_C21=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C21_FOCUSED_RUNTIME_PASS=true
C21_ALL_PARAM_RUNTIME_PASS=true
C21_DIAGNOSTIC_RUNTIME_PASS=true
C21_EXECUTION_SIGNAL_FOUND=true
ENTRY_PROBLEM_SUSPECTED=false
EXIT_PROBLEM_SUSPECTED=true
STOP_PROBLEM_SUSPECTED=true
HOLD_PERIOD_PROBLEM_SUSPECTED=true
REGIME_EXPLAINS_EXECUTION_PROBLEM=false
C21_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC
```

## PHPUnit validation

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC21"
```

Operator result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

......                                                              6 / 6 (100%)

Time: 00:00.288, Memory: 20.00 MB

OK (6 tests, 173 assertions)
```

Recorded status:

```text
PHPUNIT_C21=PASS
```

## Full Watchlist regression

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Operator result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

...............................................................  63 / 397 ( 15%)
............................................................... 126 / 397 ( 31%)
............................................................... 189 / 397 ( 47%)
............................................................... 252 / 397 ( 63%)
............................................................... 315 / 397 ( 79%)
............................................................... 378 / 397 ( 95%)
...................                                             397 / 397 (100%)

Time: 00:02.705, Memory: 34.00 MB

OK (397 tests, 9500 assertions)
```

Recorded status:

```text
FULL_WATCHLIST_PHPUNIT=PASS
```

## Focused C21 diagnostic runtime

Command:

```powershell
php artisan watchlist:backtest-c21-entry-exit-behavior-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148,152,155 `
  --profile-codes=C21_P00_CANONICAL_PATH_BASELINE,C21_P02_MFE_MAE_PATH_ANALYSIS,C21_P03_EXIT_REASON_DISTRIBUTION,C21_P07_C20_G03_SEGMENTED_PATH_ANALYSIS `
  --progress `
  --output=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-focused.json `
  --overwrite
```

Operator result markers:

```text
status=PASS
reason_code=WS_BT_C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC_READY
scope=IS_ONLY_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-focused.json
artifact_hash=d80111aa07a0cb20ec7b4e087be0d4e4c3191fa8
profile_count=4
profile_scope=EXPLICIT
evaluated_picks_count=1576
path_missing_count=44
avg_entry_gap_pct=0.00029376446222333
median_entry_gap_pct=0
never_profitable_rate=0.24111675126904
gave_back_profit_rate=0.52791878172589
gap_open_loss_rate=0.2258883248731
exit_stop_count=672
exit_target_count=564
exit_hold_count=340
median_mfe_5d=0.012919645092204
median_mae_5d=-0.018750732450486
diagnostic_signal_found=1
entry_problem_suspected=0
exit_problem_suspected=1
stop_problem_suspected=1
hold_period_problem_suspected=1
regime_explains_execution_problem=0
c21_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

Recorded status:

```text
C21_FOCUSED_RUNTIME_PASS=true
C21_FOCUSED_ARTIFACT_HASH=d80111aa07a0cb20ec7b4e087be0d4e4c3191fa8
```

## All-param C21 diagnostic runtime

Command:

```powershell
php artisan watchlist:backtest-c21-entry-exit-behavior-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --profile-codes=C21_P00_CANONICAL_PATH_BASELINE,C21_P01_ENTRY_GAP_BUCKET_ANALYSIS,C21_P02_MFE_MAE_PATH_ANALYSIS,C21_P03_EXIT_REASON_DISTRIBUTION,C21_P04_HOLD_DAY_RETURN_PATH,C21_P05_GAVE_BACK_PROFIT_ANALYSIS,C21_P06_NEVER_PROFITABLE_ANALYSIS,C21_P07_C20_G03_SEGMENTED_PATH_ANALYSIS `
  --progress `
  --output=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json `
  --overwrite
```

Operator result markers:

```text
status=PASS
reason_code=WS_BT_C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC_READY
scope=IS_ONLY_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json
artifact_hash=d6c6c72d51b40a0c852ce9bbc6a452c55920df13
profile_count=8
profile_scope=EXPLICIT
evaluated_picks_count=12600
path_missing_count=360
avg_entry_gap_pct=0.00096323026370276
median_entry_gap_pct=0
never_profitable_rate=0.22603174603175
gave_back_profit_rate=0.55365079365079
gap_open_loss_rate=0.23555555555556
exit_stop_count=5824
exit_target_count=4320
exit_hold_count=2456
median_mfe_5d=0.014354066985646
median_mae_5d=-0.021739130434783
diagnostic_signal_found=1
entry_problem_suspected=0
exit_problem_suspected=1
stop_problem_suspected=1
hold_period_problem_suspected=1
regime_explains_execution_problem=0
c21_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

Recorded status:

```text
C21_ALL_PARAM_RUNTIME_PASS=true
C21_ALL_PARAM_ARTIFACT_HASH=d6c6c72d51b40a0c852ce9bbc6a452c55920df13
```

## Final validation interpretation

C21 found an execution signal, but not an entry-gap signal.

```text
ENTRY_PROBLEM_SUSPECTED=false
EXIT_PROBLEM_SUSPECTED=true
STOP_PROBLEM_SUSPECTED=true
HOLD_PERIOD_PROBLEM_SUSPECTED=true
REGIME_EXPLAINS_EXECUTION_PROBLEM=false
```

Interpretation:

```text
- NEXT_OPEN entry is not the primary suspect.
- More than half of evaluated trades gave back profit.
- Stop exits dominate target exits.
- Median MAE is materially worse than median MFE.
- C20_G03 does not explain the execution problem strongly enough.
```

## Required next work

C21 is done. The next session must not tune C21 or create a C21 catalog.

Next diagnostic direction:

```text
C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC
```

C22 must remain IS-only and compare hypothetical exit-capture rules over fixed picks only:

```text
fixed recommendations only
future path price measurement only after selection freeze
no ticker reselection
no trade_date reselection
no catalog
no OOS
no production mutation
canonical model unchanged
```

Forbidden actions remain:

```text
DO_NOT_CREATE_C21_CATALOG=true
DO_NOT_RUN_OOS=true
DO_NOT_MUTATE_C01_TO_C20=true
DO_NOT_SET_PRODUCTION_READY=true
DO_NOT_PROMOTE_C20_G03=true
DO_NOT_CHANGE_CANONICAL_ENTRY_EXIT_MODEL=true
```
