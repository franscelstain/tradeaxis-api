# WS C10 Exit Model Diagnostic Final Result

Status: C10_EXIT_MODEL_DIAGNOSTIC_EXECUTED / C07_BATCHED_IS_DRILLDOWN_EXECUTED / NEXT_CATALOG_NOT_DESIGNED / OOS_NOT_RUN

## Scope

C10 is not a strategy catalog. It is an IS-only diagnostic session after C09 closed the nullable event-context semantics gap and left strategy quality as the remaining blocker.

Hard boundaries:

```text
C07 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
C07 catalog_version=C07
C07 catalog_count=12
C07 catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
C10_strategy_catalog_created=false
OOS=NOT_RUN
production_ready=0
best_of_failed_binding_selected=false
```

R1/R2/C01/C02/C03/C04/C05/C06/C07 remain immutable. This session did not alter any historical catalog identity, hash, IS quality result, OOS result, or production-readiness status.

## Runtime Diagnostic Change

C10 adds exit-model evidence to IS-only drilldown artifacts and the batched summary:

```text
exit_model_diagnostic_summary
per_param_exit_outcomes
hit_target_count
hit_stop_count
timeout_hold_expired_count
reason_code_distribution
```

This evidence is diagnostic-only. It does not change canonical IS gates, select a best failed row, rewrite the C07 catalog, or unlock OOS.

## Batched C07 Drilldown

Executed command:

```text
php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c10-batched-c07-exit-model-drilldown --summary=storage/app/watchlist/backtest/c10-batched-c07-exit-model-summary.csv --overwrite
```

Executed result:

```text
status=PASS
reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
catalog_version=C07
catalog_count=12
catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
diagnostic_param_count=12
ready_count=12
blocked_count=0
summary_path=D:\Laravel\watchlist\tradeaxis-api/storage/app/watchlist/backtest/c10-batched-c07-exit-model-summary.csv
output_dir=D:\Laravel\watchlist\tradeaxis-api/storage/app/watchlist/backtest/c10-batched-c07-exit-model-drilldown
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

Summary artifact:

```text
storage/app/watchlist/backtest/c10-batched-c07-exit-model-summary.csv
docs/watchlist/evidence/weekly_swing/artifacts/c10-batched-c07-exit-model-summary.csv
summary_sha1=04EE547EE3F982901CABE23E55078868F14104C9
json_artifact_count=12
```

## C10 Findings

C10 confirms that C07 still fails strategy quality:

```text
picks_count=728..1355
median_ret_net_top=-1.0279%..-0.6993%
p25_ret_net_top=-4.0156%..-3.4276%
month_win_rate_min=17.86%..25.00%
```

Exit outcome diagnostics:

```text
hit_target_count=168..249
hit_stop_count=315..504
timeout_hold_expired_count=443..667
hit_target_total=2585
hit_stop_total=4927
timeout_hold_expired_total=6858
```

Best observed median row:

```text
param_id=102
row_code=05_ANTI_REVERSAL_NOT_OVEREXTENDED
median_ret_net_top=-0.6993%
p25_ret_net_top=-3.4831%
month_win_rate_min=25.00%
```

Best observed p25 downside row:

```text
param_id=106
row_code=09_LOW_ATR_RANGE_SECTOR
median_ret_net_top=-0.7569%
p25_ret_net_top=-3.4276%
month_win_rate_min=20.59%
```

Both rows remain below the locked gates: median return is negative, p25 downside is worse than `-3%`, and monthly win-rate minimum is far below `45%`.

Runtime evidence classification remains:

```text
missing_runtime_evidence_fields=
nullable_runtime_no_positive_evidence_fields=corporate_action_flag|corporate_action_types|event_risk_reasons
next_focus=STRATEGY_QUALITY_DIAGNOSTIC_BEFORE_NEXT_CATALOG
next_decision=NEXT_CATALOG_NOT_DESIGNED
```

## Decision

```text
C07_REMAINS_REJECTED_AS_STRATEGY_QUALITY_CATALOG
C10_STRATEGY_CATALOG_CREATED=false
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_ELIGIBLE
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C10 does not create a valid IS candidate, `param_id_best_is`, or `best_is_binding_hash`.

The exit diagnostics show that C07 trades hit stop and time-expiry far more often than target. That evidence supports a future design discussion around an explicitly approved exit-model or strategy-family redesign, but it is not enough to mutate C07, select a best failed row, or launch OOS.

## Validation

Executed in this workspace:

```text
php -l app\Application\Watchlist\Services\WatchlistBacktestIsFailureDrilldownService.php = PASS
php -l app\Console\Commands\Watchlist\RunBacktestIsDiagnoseBatchCommand.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestIsFailureDrilldownServiceTest.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestIsFailureDrilldownStaticGuardTest.php = PASS
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestIsFailureDrilldown" = PASS / OK (6 tests, 123 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07" = PASS / OK (10 tests, 376 assertions)
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (302 tests, 6602 assertions)
php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c10-batched-c07-exit-model-drilldown --summary=storage/app/watchlist/backtest/c10-batched-c07-exit-model-summary.csv --overwrite = PASS
```

OOS was not run and must not be claimed PASS.
