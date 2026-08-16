# WS C08 Operator Validation Commands

Status: OPERATOR_REFERENCE / C08_RUNTIME_DIAGNOSTIC_ONLY / OOS_NOT_RUN

## PHP Lint

Commands:

```text
php -l app\Application\Watchlist\Services\WatchlistMarketDataConsumerReadService.php
php -l app\Application\Watchlist\Services\WatchlistCandidateUniverseService.php
php -l app\Application\Watchlist\Services\WatchlistScoringService.php
php -l app\Application\Watchlist\Services\WatchlistBacktestStrategyService.php
php -l app\Application\Watchlist\Services\WatchlistBacktestIsFailureDrilldownService.php
php -l app\Console\Commands\Watchlist\RunBacktestIsDiagnoseBatchCommand.php
php -l app\Console\Kernel.php
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

Expected markers from the C08 implementation run:

```text
WatchlistBacktestIsFailureDrilldown = OK (5 tests, 107 assertions)
WatchlistBacktestC07 = OK (10 tests, 376 assertions)
Full Watchlist = OK (301 tests, 6586 assertions)
exit_code=0
```

## Batched C07 IS Drilldown

Command:

```text
php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c08-batched-c07-drilldown --summary=storage/app/watchlist/backtest/c08-batched-c07-drilldown-summary.csv --overwrite
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
all summary rows oos_executed=0
all summary rows production_ready=0
no best IS binding is created
no OOS command is run
```

Current implementation evidence:

```text
summary_path=storage/app/watchlist/backtest/c08-batched-c07-drilldown-summary.csv
summary_sha1=49101D6AA702A898A3F691A7553823A8DFB2F125
docs_artifact=docs/watchlist/evidence/weekly_swing/artifacts/c08-batched-c07-drilldown-summary.csv
```

## OOS Guard

OOS command must not be run in this session.

Fail the validation if any command writes or claims:

```text
oos_executed=1
production_ready=1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
```

C07 remains rejected as a strategy-quality catalog. C08 is runtime diagnostic evidence only.
