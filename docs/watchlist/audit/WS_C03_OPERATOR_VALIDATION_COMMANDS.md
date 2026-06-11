# WS C03 Operator Validation Commands

Status: OPERATOR_VALIDATED / IS_QUALITY_FAILED / C03_REJECTED_AS_STRATEGY_CATALOG
Scope: C03 IS-only validation final result
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-11

## 1. Catalog identity under validation

| Field | Expected value |
| --- | --- |
| catalog_code | `WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06` |
| catalog_version | `C03` |
| catalog_count | `10` |
| catalog_hash | `29e15ceab1b3f7dc31a21f339ac6ab7483e14800` |


## 1A. Operator-provided final result

The operator executed the required C03 validation commands in the supported project runtime.

Implementation validation:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC03"
OK (12 tests, 461 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (250 tests, 4643 assertions)
```

Seed validation:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06
catalog_version=C03
catalog_count=10
catalog_hash=29e15ceab1b3f7dc31a21f339ac6ab7483e14800
inserted_count=10
updated_count=0
existing_count=0
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
oos_executed=0
production_ready=0
```

IS calibration run 1:

```text
status=C03_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C03_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=10
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=649e8fead0c57262307f749a4776f053f5ccd0f8
production_ready=0
```

IS calibration run 2:

```text
status=C03_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C03_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=10
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=649e8fead0c57262307f749a4776f053f5ccd0f8
production_ready=0
```

Final verdict:

```text
C03_IMPLEMENTED
C03_OPERATOR_VALIDATED
C03_SEEDED
C03_IS_EXECUTED
C03_DETERMINISTIC_TWO_RUN
C03_FAILED_IS_QUALITY
C03_REJECTED_AS_STRATEGY_QUALITY_CATALOG
OOS_NOT_RUN
NOT_PRODUCTION_READY
C04_REQUIRED
```

## 2. Validation rule

Do not claim PASS for PHPUnit, Artisan, seed, calibration, artifact creation, replay, OOS, or production readiness unless the operator provides the actual command output.

If any command cannot run because of environment, dependency, database, permission, or data issues, record the command as one of:

- `BLOCKED`
- `NOT_RUN`
- `OPERATOR_VALIDATION_REQUIRED`

C03 is allowed to proceed to OOS only after C03 IS calibration produces at least one valid IS candidate and all IS/OOS boundary guards remain clean.

## 3. PHP lint

Run from repository root.

```powershell
php -l app/Application/Watchlist/Services/WatchlistBacktestC03ParamGridCatalog.php
php -l app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php
php -l app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php
php -l app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php
php -l app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php
php -l app/Console/Commands/Watchlist/SeedBacktestC03ParamGridCommand.php
php -l database/seeders/Watchlist/WatchlistBacktestC03ParamGridSeeder.php
php -l app/Console/Kernel.php
php -l tests/Unit/Watchlist/WatchlistBacktestC03ParamGridCatalogTest.php
php -l tests/Unit/Watchlist/WatchlistBacktestC03ParamGridParamsetFactoryTest.php
php -l tests/Unit/Watchlist/WatchlistBacktestC03StaticGuardTest.php
```

Expected marker:

```text
No syntax errors detected in <file>
```

Pass criteria:

- every file returns the expected marker;
- every command exits with code `0`.

Fail criteria:

- any parse error;
- any non-zero exit code.

## 4. C03 PHPUnit filter

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC03"
```

Expected marker:

```text
OK (12 tests, <assertions> assertions)
```

Pass criteria:

- test count is `12` for the C03 filter in this repository state;
- no failure/error/risky test;
- exit code `0`.

Fail criteria:

- any failed assertion;
- any test error;
- unexpected test count without explanation from changed scope;
- non-zero exit code.

## 5. Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected marker:

```text
OK (250 tests, <assertions> assertions)
```

Pass criteria:

- all Watchlist unit tests pass;
- expected test count is `250` in this repository state: previous `238` plus 12 C03 tests;
- no regression in R1/R2/C01/C02 tests;
- exit code `0`.

Fail criteria:

- any failed assertion;
- any test error;
- unexpected regression in prior catalogs;
- non-zero exit code.

## 6. C03 seed command

```powershell
php artisan watchlist:backtest-c03-param-grid-seed
```

Expected markers:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06
catalog_version=C03
catalog_count=10
catalog_hash=29e15ceab1b3f7dc31a21f339ac6ab7483e14800
updated_count=0
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
oos_executed=0
production_ready=0
```

First successful seed should include:

```text
inserted_count=10
```

Idempotent rerun may include:

```text
inserted_count=0
existing_count=10
```

Pass criteria:

- expected catalog code/version/count/hash are printed;
- `updated_count=0`;
- prior catalogs are immutable: `r1_immutable=1`, `r2_immutable=1`, `c01_immutable=1`, `c02_immutable=1`;
- OOS and production readiness remain false;
- exit code `0`.

Fail criteria:

- catalog hash/count mismatch;
- any prior catalog mutation;
- any update to existing C03 row;
- OOS marker not false;
- production readiness marker not false;
- non-zero exit code.

## 7. C03 IS calibration run 1

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c03-is-run-1.json --overwrite
```

Expected common markers:

```text
catalog_version=C03
catalog_count=10
catalog_hash=29e15ceab1b3f7dc31a21f339ac6ab7483e14800
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=<non-empty>
artifact_path=storage/app/watchlist/backtest/c03-is-run-1.json
production_ready=0
```

If C03 passes IS quality, expected markers include:

```text
status=PASS
is_valid_param_count=<number >= 1>
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
```

If C03 fails IS quality, expected markers include:

```text
status=C03_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C03_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
oos_executed=0
production_ready=0
```

Pass criteria for command execution:

- artifact is written;
- IS/OOS boundary guards are clean;
- OOS is not invoked;
- production readiness remains false;
- if status is PASS, valid IS binding fields are non-empty;
- if status is C03 quality failed, OOS remains NOT_RUN and the failure is documented for forensic follow-up.

Fail criteria:

- OOS service/repository invoked;
- OOS table changed;
- catalog identity mismatch;
- artifact hash missing;
- production readiness true;
- unexpected exception.

Exit-code criteria:

- `0` if at least one valid IS candidate is found;
- non-zero is acceptable only when the command explicitly reports `C03_GRID_FAILED_IS_QUALITY` with clean OOS guards and written diagnostics/artifact.

## 8. C03 IS calibration run 2

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c03-is-run-2.json --overwrite
```

Expected common markers are the same as run 1, with:

```text
artifact_path=storage/app/watchlist/backtest/c03-is-run-2.json
artifact_hash=<non-empty>
```

Determinism pass criteria:

- run 1 and run 2 have the same `catalog_hash`;
- run 1 and run 2 have the same `is_trading_date_hash`;
- run 1 and run 2 have the same `is_valid_param_count`;
- run 1 and run 2 have the same `is_failed_param_count`;
- run 1 and run 2 have the same `param_id_best_is`;
- run 1 and run 2 have the same `best_is_binding_hash`;
- run 1 and run 2 have the same `artifact_hash` when the artifact writer is deterministic for identical inputs.

Fail criteria:

- any deterministic marker differs without explained data/runtime change;
- any OOS guard is dirty;
- catalog identity mismatch;
- production readiness true.

## 9. OOS rule

Do not run OOS in this session.

OOS may be prepared only in a later session after all conditions are true:

```text
is_valid_param_count >= 1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
production_ready=0
```

Until an OOS command is explicitly requested, executed, and validated by operator output, OOS status remains `NOT_RUN` and must not be claimed PASS.


## 10. Final C03 decision

C03 is rejected as a strategy-quality catalog. It has no valid IS candidate and no best IS binding.

OOS must remain `NOT_RUN` for C03. Production readiness must remain `false`.

The next same-focus attempt must be C04 as a new catalog identity. C04 must be based on C02/C03 failure evidence and must change candidate-selection logic/axis using supported runtime fields instead of merely tightening or loosening existing C03 numeric thresholds.
