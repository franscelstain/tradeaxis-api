# WS C20 Operator Validation Commands

Run these commands from the repository root in the operator environment.

C20 is IS-only. Do not run OOS and do not create a catalog from the output.

## PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC20"
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected markers when the implementation is valid:

```text
PHPUNIT_C20=PASS
FULL_WATCHLIST_PHPUNIT=PASS
```

If the operator environment cannot run PHPUnit, record:

```text
PHPUNIT_C20=OPERATOR_VALIDATION_REQUIRED
FULL_WATCHLIST_PHPUNIT=OPERATOR_VALIDATION_REQUIRED
```

Do not claim PASS without command output.

## Focused C20 diagnostic

```powershell
php artisan watchlist:backtest-c20-regime-trade-date-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148,152,155 `
  --profile-codes=C20_G00_BASELINE_NO_DATE_GATE,C20_G01_MARKET_MOMENTUM_SAFE,C20_G02_BREADTH_HEALTHY,C20_G05_COMBINED_REGIME_QUALITY `
  --progress `
  --output=storage/app/watchlist/backtest/c20-regime-trade-date-diagnostic-focused.json `
  --overwrite
```

Expected console markers:

```text
status=PASS
reason_code=WS_BT_C20_REGIME_TRADE_DATE_DIAGNOSTIC_READY
scope=IS_ONLY_REGIME_TRADE_DATE_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c20-regime-trade-date-diagnostic-focused.json
artifact_hash=<sha1>
profile_count=4
profile_scope=EXPLICIT
best_any_sample_profile_code=<profile-or-empty>
best_promising_sample_profile_code=<profile-or-empty>
best_sample_qualified_profile_code=<profile-or-empty>
best_quality_target_profile_code=<profile-or-empty>
profiles_with_quality_improvement=<int>
profiles_with_promising_continue=<int>
profiles_with_quality_target_reached=<int>
c20_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## Wider C20 diagnostic

Only run this after the focused command produces a readable artifact.

```powershell
php artisan watchlist:backtest-c20-regime-trade-date-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --profile-codes=C20_G00_BASELINE_NO_DATE_GATE,C20_G01_MARKET_MOMENTUM_SAFE,C20_G02_BREADTH_HEALTHY,C20_G03_VOLATILITY_RISK_OFF_FILTER,C20_G04_SECTOR_CONFIRMATION,C20_G05_COMBINED_REGIME_QUALITY,C20_G06_NO_PICK_DAY_ALLOWED_QUALITY_FIRST `
  --progress `
  --output=storage/app/watchlist/backtest/c20-regime-trade-date-diagnostic-run-1.json `
  --overwrite
```

## Artifact inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c20-regime-trade-date-diagnostic-run-1.json | ConvertFrom-Json

$run.data_availability | Format-List

$run.sample_quality_table |
    Select-Object `
        profile_code,
        param_id,
        allowed_trade_dates_count,
        blocked_trade_dates_count,
        no_pick_days_count,
        proposed_recommended_count,
        evaluated_picks_count,
        @{n='avg_pct';e={[math]::Round($_.avg_ret_net_top * 100, 2)}},
        @{n='median_pct';e={[math]::Round($_.median_ret_net_top * 100, 2)}},
        @{n='win_pct';e={[math]::Round($_.win_rate_top * 100, 2)}},
        period_fail_count,
        sample_gate,
        quality_gate,
        quality_improvement,
        promising_continue,
        quality_target_reached |
    Sort-Object quality_target_reached, promising_continue, quality_improvement, evaluated_picks_count -Descending |
    Format-Table -AutoSize

$run.decision | ConvertTo-Json -Depth 8
```

## Decision interpretation

Continue only if the artifact decision says:

```text
decision_status=PROMISING_CONTINUE_TO_C20_TUNING
catalog_allowed=false
oos_allowed=false
```

Even then, the next step is repeat IS proof or focused C20 tuning, not catalog creation and not OOS.

Stop if:

```text
decision_status=C20_DATE_GATE_NOT_ENOUGH
```

Treat a good tiny-sample profile as diagnostic signal only, not a valid candidate.


## Final operator evidence

The operator has completed the required C20 validation commands. These markers are now recorded as final C20 evidence:

```text
PHPUNIT_C20=PASS
OK (6 tests, 84 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
OK (391 tests, 9327 assertions)

C20_FOCUSED_4_PROFILE=PASS
artifact_hash=dac6ff71cee04be7b1c4ddcfd06a899808a89167
profiles_with_quality_improvement=1
profiles_with_promising_continue=0
profiles_with_quality_target_reached=0

C20_FOCUSED_7_PROFILE=PASS
artifact_hash=29a9743052de2b3164653a85a93e57e22a607dbe
profiles_with_quality_improvement=2
profiles_with_promising_continue=0
profiles_with_quality_target_reached=0

C20_ALL_PARAM_7_PROFILE=PASS
artifact_hash=8f8eec9913c107f22ec1f395eed9386da41756c0
profile_count=7
profile_scope=EXPLICIT
best_any_sample_profile_code=C20_G03_VOLATILITY_RISK_OFF_FILTER
best_promising_sample_profile_code=C20_G03_VOLATILITY_RISK_OFF_FILTER
best_sample_qualified_profile_code=C20_G03_VOLATILITY_RISK_OFF_FILTER
best_quality_target_profile_code=
profiles_with_quality_improvement=4
profiles_with_promising_continue=0
profiles_with_quality_target_reached=0
c20_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

Final decision:

```text
decision_status=C20_DATE_GATE_NOT_ENOUGH
catalog_allowed=false
oos_allowed=false
best_quality_target_profile=null
C20_STOP_TUNING=true
NEXT_STEP=C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC_DESIGN
```

No further C20 operator command is required for the current hypothesis. Do not run C20 OOS, do not create a C20 catalog, and do not promote any C20 profile.
