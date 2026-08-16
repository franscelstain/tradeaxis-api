# WS C14 Operator Validation Commands

Status: OPERATOR_REFERENCE / C14_IS_QUALITY_FAILED / OOS_NOT_RUN

## PHP Lint

Commands:

```text
php -l app\Application\Watchlist\Services\WatchlistBacktestC14ParamGridCatalog.php
php -l app\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService.php
php -l app\Application\Watchlist\Services\WatchlistBacktestIsCalibrationService.php
php -l app\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory.php
php -l app\Application\Watchlist\Services\WatchlistCandidateUniverseService.php
php -l app\Application\Watchlist\Services\WatchlistScoringService.php
php -l app\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository.php
php -l app\Console\Commands\Watchlist\SeedBacktestC14ParamGridCommand.php
php -l app\Console\Kernel.php
php -l database\seeders\Watchlist\WatchlistBacktestC14ParamGridSeeder.php
php -l tests\Unit\Watchlist\WatchlistBacktestC14ParamGridCatalogTest.php
php -l tests\Unit\Watchlist\WatchlistBacktestC14ParamGridParamsetFactoryTest.php
php -l tests\Unit\Watchlist\WatchlistBacktestC14StaticGuardTest.php
```

PASS criteria:

```text
No syntax errors detected
exit_code=0
```

## PHPUnit

Commands:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC14"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitAxisSupport"
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected marker from the C14 implementation run:

```text
WatchlistBacktestC14 = OK (10 tests, 458 assertions)
WatchlistBacktestC07 = OK (10 tests, 376 assertions)
WatchlistBacktestExitAxisSupport = OK (11 tests, 59 assertions)
Full Watchlist = OK (329 tests, 7186 assertions)
exit_code=0
```

## C14 Seed

Command:

```text
php artisan watchlist:backtest-c14-param-grid-seed
```

Expected markers:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06
catalog_version=C14
catalog_count=12
catalog_hash=079430de7c94fd0226d0f3b47d5eb1e9f906fd6a
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
c04_immutable=1
c05_immutable=1
c06_immutable=1
c07_immutable=1
oos_executed=0
production_ready=0
exit_code=0
```

PASS criteria:

```text
catalog identity matches C14
inserted_count + existing_count = 12
updated_count=0
all historical immutable markers are 1
oos_executed=0
production_ready=0
```

## C14 IS Calibration Run 1

Command:

```text
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c14-is-run-1.json --overwrite
```

Expected markers:

```text
status=C14_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C14_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06
catalog_version=C14
catalog_count=12
catalog_hash=079430de7c94fd0226d0f3b47d5eb1e9f906fd6a
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=12
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=70d021daafc254fb2ed826ff05015d42bac5dd8d
production_ready=0
expected_exit_code=1
```

PASS criteria:

```text
artifact JSON is written
status is C14_GRID_FAILED_IS_QUALITY
reason_code is WS_BT_C14_NO_VALID_IS_CANDIDATE
all rows reached canonical gates
is_valid_param_count=0
param_id_best_is is empty
best_is_binding_hash is empty
oos_executed=0
production_ready=0
```

## C14 IS Calibration Run 2

Command:

```text
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c14-is-run-2.json --overwrite
```

Expected markers:

```text
status=C14_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C14_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06
catalog_version=C14
catalog_count=12
catalog_hash=079430de7c94fd0226d0f3b47d5eb1e9f906fd6a
is_valid_param_count=0
is_failed_param_count=12
artifact_hash=70d021daafc254fb2ed826ff05015d42bac5dd8d
oos_executed=0
production_ready=0
expected_exit_code=1
```

PASS criteria:

```text
run 2 canonical artifact_hash equals run 1
param_id_best_is is empty
best_is_binding_hash is empty
oos_executed=0
production_ready=0
```

## OOS Guard

OOS command must not be run for C14.

Fail validation if any command writes or claims:

```text
oos_executed=1
production_ready=1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
```

C14 is rejected as a strategy-quality catalog and is not eligible for OOS.
