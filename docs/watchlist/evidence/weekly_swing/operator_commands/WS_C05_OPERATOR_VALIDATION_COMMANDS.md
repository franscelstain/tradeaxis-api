# WS C05 Operator Validation Commands

Status: EXECUTED_CURRENT_SESSION / IS_QUALITY_FAILED / C05_REJECTED_AS_STRATEGY_CATALOG
Scope: C05 IS-only implementation, seed, and calibration validation
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-11

## 1. Catalog identity under validation

| Field | Expected value |
| --- | --- |
| catalog_code | `WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06` |
| catalog_version | `C05` |
| catalog_count | `12` |
| catalog_hash | `476af5dde18079b1270556bc44bbc632edd46e27` |

## 2. Current-session validation output

PHPUnit C05 filter:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC05"
OK (13 tests, 523 assertions)
```

Full Watchlist PHPUnit:

```text
vendor\bin\phpunit tests\Unit\Watchlist
OK (277 tests, 5665 assertions)
```

C05 seed:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06
catalog_version=C05
catalog_count=12
catalog_hash=476af5dde18079b1270556bc44bbc632edd46e27
inserted_count=12
updated_count=0
existing_count=0
r1_catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
r2_catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
c01_catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
c02_catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
c03_catalog_hash=29e15ceab1b3f7dc31a21f339ac6ab7483e14800
c04_catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
c04_immutable=1
oos_executed=0
production_ready=0
```

C05 IS calibration run 1 and run 2:

```text
status=C05_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C05_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06
catalog_version=C05
catalog_count=12
catalog_hash=476af5dde18079b1270556bc44bbc632edd46e27
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
artifact_hash=f8288cb2d395e397f433dae854c0ad80b4650a8d
production_ready=0
```

The two IS commands exited non-zero because C05 failed strategy quality. That is expected for a no-valid-candidate result and is not a strategy PASS.

## 3. Required validation commands

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC05"
vendor\bin\phpunit tests\Unit\Watchlist
php artisan watchlist:backtest-c05-param-grid-seed
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c05-is-run-1.json --overwrite
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c05-is-run-2.json --overwrite
```

Strategy-quality pass criteria remain:

```text
is_valid_param_count >= 1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
```

C05 current result does not meet those pass criteria. OOS has not been run and must not be claimed PASS.
