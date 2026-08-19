# WS C10 Operator Validation Commands

Status: OPERATOR_REFERENCE / C10_EXIT_MODEL_DIAGNOSTIC_ONLY / OOS_NOT_RUN

## PHP Lint

Commands:

```text
php -l app\Application\Watchlist\Services\WatchlistBacktestIsFailureDrilldownService.php
php -l app\Console\Commands\Watchlist\RunBacktestIsDiagnoseBatchCommand.php
php -l tests\Unit\Watchlist\WatchlistBacktestIsFailureDrilldownServiceTest.php
php -l tests\Unit\Watchlist\WatchlistBacktestIsFailureDrilldownStaticGuardTest.php
```

PASS criteria:

```text
No syntax errors detected
exit_code=0
```

## PHPUnit

Commands:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestIsFailureDrilldown"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07"
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected markers from the C10 implementation run:

```text
WatchlistBacktestIsFailureDrilldown = OK (6 tests, 123 assertions)
WatchlistBacktestC07 = OK (10 tests, 376 assertions)
Full Watchlist = OK (302 tests, 6602 assertions)
exit_code=0
```

## Batched C07 Exit-Model Drilldown

Command:

```text
php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c10-batched-c07-exit-model-drilldown --summary=storage/app/watchlist/backtest/c10-batched-c07-exit-model-summary.csv --overwrite
```

Expected markers:

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
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
exit_code=0
```

PASS criteria:

```text
summary CSV is written
12 scoped JSON artifacts are written
all summary rows status=DONE
all summary rows hit_target_count is non-empty
all summary rows hit_stop_count is non-empty
all summary rows timeout_hold_expired_count is non-empty
all summary rows missing_runtime_evidence_fields is empty
all summary rows nullable_runtime_no_positive_evidence_fields=corporate_action_flag|corporate_action_types|event_risk_reasons
all summary rows next_focus=STRATEGY_QUALITY_DIAGNOSTIC_BEFORE_NEXT_CATALOG
all summary rows next_decision=NEXT_CATALOG_NOT_DESIGNED
all summary rows oos_executed=0
all summary rows production_ready=0
no best IS binding is created
no OOS command is run
```

Current implementation evidence:

```text
summary_path=storage/app/watchlist/backtest/c10-batched-c07-exit-model-summary.csv
summary_sha1=04EE547EE3F982901CABE23E55078868F14104C9
docs_artifact=docs/watchlist/records/evidence/artifacts/c10-batched-c07-exit-model-summary.csv
```

## Expected Strategy-Quality Markers

Expected C10 summary ranges from the current implementation run:

```text
picks_count=728..1355
median_ret_net_top=-1.0279%..-0.6993%
p25_ret_net_top=-4.0156%..-3.4276%
month_win_rate_min=17.86%..25.00%
hit_target_count=168..249
hit_stop_count=315..504
timeout_hold_expired_count=443..667
```

These markers confirm diagnostic execution only. They do not make C07 OOS-eligible.

## OOS Guard

OOS command must not be run in this session.

Fail validation if any command writes or claims:

```text
oos_executed=1
production_ready=1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
```

C07 remains rejected as a strategy-quality catalog. C10 is exit-model diagnostic evidence only.
