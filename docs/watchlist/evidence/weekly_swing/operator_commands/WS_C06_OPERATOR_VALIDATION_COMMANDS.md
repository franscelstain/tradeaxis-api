# WS C06 Operator Validation Commands

Status: EXECUTED_CURRENT_SESSION / IS_QUALITY_FAILED / C06_REJECTED_AS_STRATEGY_CATALOG
Scope: C06 IS-only implementation, seed, and calibration validation
Last updated: 2026-06-12

## Catalog identity

| Field | Value |
| --- | --- |
| catalog_code | `WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06` |
| catalog_version | `C06` |
| catalog_count | `12` |
| catalog_hash | `6c93d67fb77319a02cecc3d96fd99bb0e139a1ac` |

## Commands actually executed

PHP lint for C06 files:

```powershell
php -l app/Application/Watchlist/Services/WatchlistBacktestC06ParamGridCatalog.php
php -l app/Application/Watchlist/Services/WatchlistPlanGroupingService.php
php -l app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php
php -l app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php
php -l app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php
php -l app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php
php -l app/Console/Commands/Watchlist/SeedBacktestC06ParamGridCommand.php
php -l database/seeders/Watchlist/WatchlistBacktestC06ParamGridSeeder.php
php -l app/Console/Kernel.php
php -l tests/Unit/Watchlist/WatchlistBacktestC06ParamGridCatalogTest.php
php -l tests/Unit/Watchlist/WatchlistBacktestC06ParamGridParamsetFactoryTest.php
php -l tests/Unit/Watchlist/WatchlistBacktestC06StaticGuardTest.php
```

Result: PASS / no syntax errors detected.

PHPUnit C06 filter:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC06"
```

Result:

```text
OK (13 tests, 503 assertions)
```

Full Watchlist PHPUnit:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Result:

```text
OK (290 tests, 6168 assertions)
```

C06 seed:

```powershell
php artisan watchlist:backtest-c06-param-grid-seed
```

Result:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06
catalog_version=C06
catalog_count=12
catalog_hash=6c93d67fb77319a02cecc3d96fd99bb0e139a1ac
inserted_count=12
updated_count=0
existing_count=0
r1_catalog_count=24
r1_catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
r2_catalog_count=12
r2_catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
c01_catalog_count=8
c01_catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
c02_catalog_count=8
c02_catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
c03_catalog_count=10
c03_catalog_hash=29e15ceab1b3f7dc31a21f339ac6ab7483e14800
c04_catalog_count=10
c04_catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
c05_catalog_count=12
c05_catalog_hash=476af5dde18079b1270556bc44bbc632edd46e27
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
c04_immutable=1
c05_immutable=1
oos_executed=0
production_ready=0
```

C06 IS calibration run 1:

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c06-is-run-1.json --overwrite
```

Result:

```text
status=C06_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C06_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06
catalog_version=C06
catalog_count=12
catalog_hash=6c93d67fb77319a02cecc3d96fd99bb0e139a1ac
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=12
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
max_requested_market_data_date=2025-05-16
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=ede8ca6f53ea49141a5e047e6094b7a282cdb232
production_ready=0
```

C06 IS calibration run 2:

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c06-is-run-2.json --overwrite
```

Result:

```text
status=C06_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C06_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06
catalog_version=C06
catalog_count=12
catalog_hash=6c93d67fb77319a02cecc3d96fd99bb0e139a1ac
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=12
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
max_requested_market_data_date=2025-05-16
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=ede8ca6f53ea49141a5e047e6094b7a282cdb232
production_ready=0
```

The two IS commands exited non-zero because C06 failed strategy quality. That is expected for a no-valid-candidate result and is not a strategy PASS.

## Pass/fail criteria for any rerun

Implementation validation passes only if:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC06"
vendor\bin\phpunit tests\Unit\Watchlist
```

return exit code `0` and the expected OK markers above.

Seed validation passes only if the seed command returns exit code `0` and includes:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06
catalog_version=C06
catalog_count=12
catalog_hash=6c93d67fb77319a02cecc3d96fd99bb0e139a1ac
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
c04_immutable=1
c05_immutable=1
oos_executed=0
production_ready=0
```

Strategy-quality validation would pass only if a future IS run reports:

```text
is_valid_param_count >= 1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

C06 current result does not meet those strategy-quality pass criteria. OOS has not been run and must not be claimed PASS.
