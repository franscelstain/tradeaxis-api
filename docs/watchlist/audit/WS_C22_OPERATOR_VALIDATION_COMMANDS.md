# WS C22 Operator Validation Commands

C22 validation is IS-only. Do not run OOS, do not create a C22 catalog, and do not change canonical execution rules.

## Current validation status

```text
C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
PHPUNIT_C22=OPERATOR_VALIDATION_REQUIRED
FULL_WATCHLIST_PHPUNIT=OPERATOR_VALIDATION_REQUIRED
C22_FOCUSED_RUNTIME_PASS=OPERATOR_VALIDATION_REQUIRED
C22_ALL_PARAM_RUNTIME_PASS=OPERATOR_VALIDATION_REQUIRED
C22_DIAGNOSTIC_RUNTIME_PASS=NOT_RUN
C22_EXIT_CAPTURE_SIGNAL_FOUND=NOT_RUN
C22_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## PHPUnit validation

Run:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC22"
```

Expected source-level success marker after operator run:

```text
PHPUNIT_C22=PASS
```

Then run full Watchlist regression:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected source-level success marker after operator run:

```text
FULL_WATCHLIST_PHPUNIT=PASS
```

## Focused C22 diagnostic runtime

Run the focused validation first:

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

Expected console markers:

```text
status=PASS
reason_code=WS_BT_C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC_READY
scope=IS_ONLY_EXIT_CAPTURE_SHADOW_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-focused.json
artifact_hash=<hash>
shadow_profile_count=<count>
profile_scope=EXPLICIT
evaluated_picks_count=<count>
path_missing_count=<count>
canonical_avg_ret_net=<value>
canonical_median_ret_net=<value>
canonical_p25_ret_net=<value>
canonical_win_rate=<value>
canonical_gave_back_profit_rate=<value>
best_shadow_profile_code_by_median=<profile>
best_shadow_profile_code_by_p25=<profile>
best_shadow_profile_code_by_giveback_reduction=<profile>
best_shadow_median_delta_vs_canonical=<value>
best_shadow_p25_delta_vs_canonical=<value>
best_giveback_reduction_vs_canonical=<value>
exit_capture_signal_found=0|1
early_exit_suspected_better=0|1
profit_lock_suspected_better=0|1
breakeven_suspected_better=0|1
trailing_suspected_better=0|1
target_distance_problem_suspected=0|1
stop_distance_problem_suspected=0|1
hold_compression_suspected_better=0|1
c22_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## All-param C22 diagnostic runtime

Run after the focused runtime is clean:

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

Record exact output markers and artifact hash. Do not infer PASS from file creation alone.

## If validation cannot run

Record exactly:

```text
PHPUNIT_C22=OPERATOR_VALIDATION_REQUIRED
FULL_WATCHLIST_PHPUNIT=OPERATOR_VALIDATION_REQUIRED
C22_FOCUSED_RUNTIME_PASS=OPERATOR_VALIDATION_REQUIRED
C22_ALL_PARAM_RUNTIME_PASS=OPERATOR_VALIDATION_REQUIRED
C22_DIAGNOSTIC_RUNTIME_PASS=NOT_RUN
C22_EXIT_CAPTURE_SIGNAL_FOUND=NOT_RUN
OOS_NOT_RUN=true
production_ready=0
```

## Interpretation rule

If C22 finds a signal, the next step is C23 rule-candidate design. That still does not allow catalog creation, OOS proof, promotion, or production readiness.

If C22 does not find a signal, stop the exit-capture hypothesis for this path unless a stronger non-lookahead diagnostic is justified. Do not polish a weak shadow result into strategy success.
