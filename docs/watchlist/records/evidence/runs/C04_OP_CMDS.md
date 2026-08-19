# WS C04 Operator Validation Commands

Status: EXECUTED_CURRENT_SESSION / IS_QUALITY_FAILED / C04_REJECTED_AS_STRATEGY_CATALOG
Scope: C04 IS-only implementation, seed, and calibration validation
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-11

## 1. Catalog identity under validation

| Field | Expected value |
| --- | --- |
| catalog_code | `WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06` |
| catalog_version | `C04` |
| catalog_count | `10` |
| catalog_hash | `0ce3a313c45432c5a4d607def12b3f774988f324` |

## 2. Current-session validation output

PHP lint was executed for C04 and modified Watchlist PHP/test files. Every file returned:

```text
No syntax errors detected in <file>
```

PHPUnit C04 filter:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC04"
OK (14 tests, 499 assertions)
```

Full Watchlist PHPUnit:

```text
vendor\bin\phpunit tests\Unit\Watchlist
OK (264 tests, 5142 assertions)
```

C04 seed:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06
catalog_version=C04
catalog_count=10
catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
inserted_count=10
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
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
oos_executed=0
production_ready=0
```

C04 IS calibration run 1:

```text
status=C04_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C04_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06
catalog_version=C04
catalog_count=10
catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=10
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
max_requested_market_data_date=2025-05-07
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=fe964ee879dddc8aa8a83372e8c2d05aed5e8259
production_ready=0
```

C04 IS calibration run 2:

```text
status=C04_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C04_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06
catalog_version=C04
catalog_count=10
catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=10
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
max_requested_market_data_date=2025-05-07
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=fe964ee879dddc8aa8a83372e8c2d05aed5e8259
production_ready=0
```

The two IS commands exited non-zero because C04 failed strategy quality. That is expected for a no-valid-candidate result and must not be interpreted as a PASS strategy catalog.

## 3. Required validation commands

Run from repository root.

### PHP lint

```powershell
php -l app/Application/Watchlist/Services/WatchlistBacktestC04ParamGridCatalog.php
php -l app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php
php -l app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php
php -l app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php
php -l app/Application/Watchlist/Services/WatchlistPlanGroupingService.php
php -l app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php
php -l app/Console/Commands/Watchlist/SeedBacktestC04ParamGridCommand.php
php -l database/seeders/Watchlist/WatchlistBacktestC04ParamGridSeeder.php
php -l app/Console/Kernel.php
php -l tests/Unit/Watchlist/WatchlistBacktestC04ParamGridCatalogTest.php
php -l tests/Unit/Watchlist/WatchlistBacktestC04ParamGridParamsetFactoryTest.php
php -l tests/Unit/Watchlist/WatchlistBacktestC04StaticGuardTest.php
```

Pass criteria: every command prints `No syntax errors detected in <file>` and exits `0`.

### C04 PHPUnit filter

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC04"
```

Expected marker:

```text
OK (14 tests, 499 assertions)
```

Pass criteria: no failures/errors/risky tests and exit code `0`.

### Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected marker:

```text
OK (264 tests, 5142 assertions)
```

Pass criteria: all Watchlist unit tests pass and exit code `0`.

### C04 seed command

```powershell
php artisan watchlist:backtest-c04-param-grid-seed
```

Expected markers:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06
catalog_version=C04
catalog_count=10
catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
oos_executed=0
production_ready=0
```

Pass criteria: all markers match, no historical catalog count/hash changes, and exit code `0`.

### C04 IS calibration run 1

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c04-is-run-1.json --overwrite
```

### C04 IS calibration run 2

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c04-is-run-2.json --overwrite
```

Expected execution markers:

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06
catalog_version=C04
catalog_count=10
catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=<non-empty>
artifact_path=<written>
production_ready=0
```

Strategy-quality pass criteria:

```text
is_valid_param_count >= 1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
```

C04 current result does not meet those pass criteria. It is rejected as a strategy-quality catalog and must not advance to OOS.

## 4. OOS prohibition

Do not run OOS for C04 unless a future separate session first proves:

```text
is_valid_param_count >= 1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
```

OOS has not been run for C04 and must not be claimed PASS.
