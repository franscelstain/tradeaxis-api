# WS C22 Operator Validation Commands

C22 validation is IS-only. Do not run OOS, do not create a C22 catalog, and do not change canonical execution rules.

## Final validation status

```text
C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
PHPUNIT_C22=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C22_FOCUSED_RUNTIME_PASS=true
C22_ALL_PARAM_RUNTIME_PASS=true
C22_DIAGNOSTIC_RUNTIME_PASS=true
C22_EXIT_CAPTURE_SIGNAL_FOUND=true
C22_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C23_FIRST_PROFIT_CAPTURE_RULE_CANDIDATE_DIAGNOSTIC
```

## PHPUnit validation evidence

Operator ran:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC22"
```

Result:

```text
OK (6 tests, 302 assertions)
```

Operator then ran full Watchlist regression:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Result:

```text
OK (403 tests, 9802 assertions)
```

## Focused C22 diagnostic runtime

Operator ran:

```powershell
php artisan watchlist:backtest-c22-exit-capture-shadow-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148,152,155 `
  --shadow-profile-codes=C22_S00_CANONICAL_BASELINE,C22_S01_EXIT_D1_CLOSE,C22_S02_EXIT_D2_CLOSE,C22_S03_EXIT_D3_CLOSE,C22_S06_FIRST_PROFITABLE_CLOSE_EXIT,C22_S08_PROFIT_LOCK_AFTER_MFE_1_00PCT,C22_S10_BREAKEVEN_AFTER_MFE_1_00PCT,C22_S11_TRAILING_FROM_MFE_1_50PCT_GIVEBACK_0_75PCT `
  --progress `
  --output=storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-focused.json `
  --overwrite
```

Recorded focused markers:

```text
status=PASS
reason_code=WS_BT_C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC_READY
scope=IS_ONLY_EXIT_CAPTURE_SHADOW_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-focused.json
artifact_hash=2831edfb89c884ccb86072d047e5950dcae463dd
shadow_profile_count=8
profile_scope=EXPLICIT
evaluated_picks_count=394
path_missing_count=11
canonical_avg_ret_net=-0.0025564143113513
canonical_median_ret_net=-0.0005
canonical_p25_ret_net=-0.021287002050553
canonical_win_rate=0.42131979695431
canonical_gave_back_profit_rate=0.52791878172589
best_shadow_profile_code_by_median=C22_S00_CANONICAL_BASELINE
best_shadow_profile_code_by_p25=C22_S00_CANONICAL_BASELINE
best_shadow_profile_code_by_giveback_reduction=C22_S06_FIRST_PROFITABLE_CLOSE_EXIT
best_shadow_median_delta_vs_canonical=0
best_shadow_p25_delta_vs_canonical=0
best_giveback_reduction_vs_canonical=0.060913705583756
exit_capture_signal_found=1
early_exit_suspected_better=0
profit_lock_suspected_better=0
breakeven_suspected_better=1
trailing_suspected_better=0
target_distance_problem_suspected=0
stop_distance_problem_suspected=0
hold_compression_suspected_better=0
c22_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## All-param C22 diagnostic runtime

Operator ran:

```powershell
php artisan watchlist:backtest-c22-exit-capture-shadow-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --shadow-profile-codes=C22_S00_CANONICAL_BASELINE,C22_S01_EXIT_D1_CLOSE,C22_S02_EXIT_D2_CLOSE,C22_S03_EXIT_D3_CLOSE,C22_S04_EXIT_D4_CLOSE,C22_S05_EXIT_D5_CLOSE_ONLY,C22_S06_FIRST_PROFITABLE_CLOSE_EXIT,C22_S07_PROFIT_LOCK_AFTER_MFE_0_75PCT,C22_S08_PROFIT_LOCK_AFTER_MFE_1_00PCT,C22_S09_PROFIT_LOCK_AFTER_MFE_1_50PCT,C22_S10_BREAKEVEN_AFTER_MFE_1_00PCT,C22_S11_TRAILING_FROM_MFE_1_50PCT_GIVEBACK_0_75PCT,C22_S12_TARGET_CLOSE_1_00PCT,C22_S13_TARGET_CLOSE_1_50PCT,C22_S14_TARGET_CLOSE_2_00PCT,C22_S15_STOP_LOSS_1_50PCT_SHADOW,C22_S16_STOP_LOSS_2_00PCT_SHADOW,C22_S17_STOP_LOSS_2_50PCT_SHADOW `
  --progress `
  --output=storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-all-param.json `
  --overwrite
```

Recorded all-param markers:

```text
status=PASS
reason_code=WS_BT_C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC_READY
scope=IS_ONLY_EXIT_CAPTURE_SHADOW_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-all-param.json
artifact_hash=4e939d091a03ed49bbf460c0424ff1a018f98e72
shadow_profile_count=18
profile_scope=EXPLICIT
evaluated_picks_count=1575
path_missing_count=45
canonical_avg_ret_net=-0.0046903074630424
canonical_median_ret_net=-0.0041104817284074
canonical_p25_ret_net=-0.023750212591414
canonical_win_rate=0.39238095238095
canonical_gave_back_profit_rate=0.55365079365079
best_shadow_profile_code_by_median=C22_S01_EXIT_D1_CLOSE
best_shadow_profile_code_by_p25=C22_S00_CANONICAL_BASELINE
best_shadow_profile_code_by_giveback_reduction=C22_S06_FIRST_PROFITABLE_CLOSE_EXIT
best_shadow_median_delta_vs_canonical=0.0039359241611442
best_shadow_p25_delta_vs_canonical=0
best_giveback_reduction_vs_canonical=0.062222222222222
exit_capture_signal_found=1
early_exit_suspected_better=0
profit_lock_suspected_better=0
breakeven_suspected_better=1
trailing_suspected_better=0
target_distance_problem_suspected=0
stop_distance_problem_suspected=1
hold_compression_suspected_better=0
c22_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## All-param interpretation extract commands

Operator extracted:

```powershell
$run = Get-Content storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-all-param.json | ConvertFrom-Json

$run.shadow_profile_summary |
    Sort-Object median_delta_vs_canonical -Descending |
    Select-Object `
        profile_code,
        evaluated_picks_count,
        @{n='avg';e={[math]::Round($_.avg_ret_net * 100, 3)}},
        @{n='median';e={[math]::Round($_.median_ret_net * 100, 3)}},
        @{n='p25';e={[math]::Round($_.p25_ret_net * 100, 3)}},
        @{n='win_rate';e={[math]::Round($_.win_rate * 100, 2)}},
        @{n='median_delta';e={[math]::Round($_.median_delta_vs_canonical * 100, 3)}},
        @{n='p25_delta';e={[math]::Round($_.p25_delta_vs_canonical * 100, 3)}},
        @{n='giveback_reduction';e={[math]::Round($_.gave_back_profit_reduction_vs_canonical * 100, 2)}},
        @{n='improved_rate';e={[math]::Round($_.improved_pick_rate * 100, 2)}} |
    Format-Table -AutoSize

$run.breakeven_summary | ConvertTo-Json -Depth 10
$run.stop_distance_summary | ConvertTo-Json -Depth 10
$run.decision | ConvertTo-Json -Depth 10
```

Key extraction results:

```text
C22_S06_FIRST_PROFITABLE_CLOSE_EXIT:
avg=-0.016%
median=0.428%
p25=-0.825%
win_rate=59.62%

C22_S01_EXIT_D1_CLOSE:
avg=-0.059%
median=-0.050%
p25=-0.834%
win_rate=35.94%

C22_S00_CANONICAL_BASELINE:
avg=-0.469%
median=-0.411%
p25=-2.375%
win_rate=39.24%
```

Breakeven standalone warning:

```text
C22_S10_BREAKEVEN_AFTER_MFE_1_00PCT:
avg=-0.8693902820005997%
win_rate=7.492063492063492%
gave_back_profit_rate=66.98412698412698%
gave_back_profit_reduction_vs_canonical=-11.619047619047618%
loss_reduction_rate=32.126984126984126%
```

Stop-distance standalone warning:

```text
C22_S15_STOP_LOSS_1_50PCT_SHADOW:
median=-1.6783567134268536%
win_rate=20.761904761904763%
gave_back_profit_reduction_vs_canonical=-6.793650793650796%
loss_reduction_rate=29.714285714285715%

C22_S16_STOP_LOSS_2_00PCT_SHADOW:
median=-2.090313547032055%
win_rate=25.142857142857146%
gave_back_profit_reduction_vs_canonical=-5.460317460317465%
loss_reduction_rate=21.26984126984127%

C22_S17_STOP_LOSS_2_50PCT_SHADOW:
p25=-2.7792014962842962%
win_rate=27.555555555555557%
gave_back_profit_reduction_vs_canonical=-4.444444444444451%
loss_reduction_rate=17.77777777777778%
```

## Final interpretation rule

C22 found an exit-capture diagnostic signal. It did not find a production-ready exit rule.

The strongest C23 direction is first-profit-capture converted into a non-lookahead rule. C23 must not copy `C22_S06_FIRST_PROFITABLE_CLOSE_EXIT` directly because it is shadow measurement. C23 must test realizable alternatives such as exit next open after a profitable close signal or after a fixed early profit condition.

Do not create a C22 catalog. Do not run OOS. Do not set production ready.

## Final next step

```text
NEXT_STEP=C23_FIRST_PROFIT_CAPTURE_RULE_CANDIDATE_DIAGNOSTIC
```
