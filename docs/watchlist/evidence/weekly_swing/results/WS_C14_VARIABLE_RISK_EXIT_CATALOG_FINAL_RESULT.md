# WS C14 Variable Risk-Exit Catalog Final Result

Status: C14_IMPLEMENTED_SEEDED_DETERMINISTIC / IS_QUALITY_FAILED / OOS_NOT_RUN / NOT_PRODUCTION_READY

## Scope

C14 is the first strategy catalog created after C13 exit-axis support. It uses the C13-authorized variable risk-exit axes while reusing the C07 candidate-selection confirmation layer.

Hard boundaries:

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06
catalog_version=C14
catalog_count=12
catalog_hash=079430de7c94fd0226d0f3b47d5eb1e9f906fd6a
OOS=NOT_RUN
production_ready=0
best_of_failed_binding_selected=false
```

R1/R2/C01/C02/C03/C04/C05/C06/C07 remain immutable. C14 did not alter historical catalog identity, hash, IS result, OOS result, or production-readiness status.

## Design Basis

C14 is derived from C10/C11/C12/C13 evidence:

```text
C10: target-hit share was weak and stop-or-timeout dominated.
C11: exit-model catalog was not authorized until a contract existed.
C12: first-phase future axes were limited to risk.min_rr and risk.stop_atr_mult.
C13: VARIABLE_RISK_EXIT_AXIS_V1 support was implemented, with holding_days/target_pct/stop_pct blocked.
```

C14 allowed axes:

```text
risk.stop_atr_mult=0.80..1.70
risk.min_rr=0.75..1.20
```

C14 blocked axes:

```text
backtest.holding_days
backtest.target_pct
backtest.stop_pct
sector whitelist/filter
```

During validation, C14 exposed a runtime enrichment gap: C07 optional runtime metrics were initially gated to catalog version `C07` only. This was fixed by allowing the same C07 confirmation extension to receive extended metrics for C14. This did not loosen gates, add a sector filter, or change C07 behavior.

## Seed Evidence

Command:

```text
php artisan watchlist:backtest-c14-param-grid-seed
```

Result:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06
catalog_version=C14
catalog_count=12
catalog_hash=079430de7c94fd0226d0f3b47d5eb1e9f906fd6a
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
c06_catalog_count=12
c06_catalog_hash=6c93d67fb77319a02cecc3d96fd99bb0e139a1ac
c07_catalog_count=12
c07_catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
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
```

## IS Calibration Evidence

Run 1:

```text
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c14-is-run-1.json --overwrite
```

Result:

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
max_requested_market_data_date=2025-05-21
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=70d021daafc254fb2ed826ff05015d42bac5dd8d
production_ready=0
```

Run 2 used a separate output path and produced the same canonical artifact hash:

```text
artifact_hash=70d021daafc254fb2ed826ff05015d42bac5dd8d
```

Audit artifacts:

```text
storage/app/watchlist/backtest/c14-is-run-1.json
storage/app/watchlist/backtest/c14-is-run-2.json
storage/app/watchlist/backtest/c14-forensic-summary.csv
docs/watchlist/evidence/weekly_swing/artifacts/c14-is-run-1.json
docs/watchlist/evidence/weekly_swing/artifacts/c14-is-run-2.json
docs/watchlist/evidence/weekly_swing/artifacts/c14-forensic-summary.csv
run_json_file_sha1=04AA2A2A1B1D4F7AAB614743617D6FBCADC1AA43
forensic_summary_sha1=CC48BAD6BC21CE4014211960F53A04203C50FAB5
```

## Forensic Summary

C14 reached canonical gate evaluation for all rows:

```text
minimum_trade_count_passed=12/12
minimum_coverage_passed=12/12
median_return_non_negative_passed=0/12
p25_downside_bound_passed=5/12
monthly_win_rate_floor_passed=0/12
monthly_average_floor_passed=0/12
```

Metric ranges:

```text
picks_count=729..1359
avg_ret_net_top=-0.5216%..-0.3528%
median_ret_net_top=-1.5648%..-0.4848%
p25_ret_net_top=-3.5375%..-2.6583%
month_win_rate_min=14.81%..30.77%
month_avg_ret_net_min=-2.5674%..-1.4889%
```

Failure distribution:

```text
WS_BT_EVAL_ROBUST_RETURN_FAIL=12
WS_BT_EVAL_STABILITY_FAIL=12
WS_BT_EVAL_DOWNSIDE_FAIL=7
```

C14 improved p25 downside for some rows, but robust return and monthly stability still failed every row. This is a strategy-quality failure, not a data sufficiency failure.

## Decision

```text
C14_REJECTED_AS_STRATEGY_QUALITY_CATALOG
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
OOS_NOT_ELIGIBLE
OOS_NOT_RUN
production_ready=0
```

OOS must not be run or claimed PASS for C14.

The next session should use C14 forensic evidence to redesign candidate selection and/or strategy family behavior. It should not simply continue lowering targets or choose the best failed C14 row, because every row still failed robust return and stability gates.

## Validation

Executed in this workspace:

```text
php -l app\Application\Watchlist\Services\WatchlistBacktestC14ParamGridCatalog.php = PASS
php -l app\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService.php = PASS
php -l app\Application\Watchlist\Services\WatchlistBacktestIsCalibrationService.php = PASS
php -l app\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory.php = PASS
php -l app\Application\Watchlist\Services\WatchlistCandidateUniverseService.php = PASS
php -l app\Application\Watchlist\Services\WatchlistScoringService.php = PASS
php -l app\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository.php = PASS
php -l app\Console\Commands\Watchlist\SeedBacktestC14ParamGridCommand.php = PASS
php -l app\Console\Kernel.php = PASS
php -l database\seeders\Watchlist\WatchlistBacktestC14ParamGridSeeder.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestC14ParamGridCatalogTest.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestC14ParamGridParamsetFactoryTest.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestC14StaticGuardTest.php = PASS
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC14" = PASS / OK (10 tests, 458 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07" = PASS / OK (10 tests, 376 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitAxisSupport" = PASS / OK (11 tests, 59 assertions)
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (329 tests, 7186 assertions)
```

OOS was not run and must not be claimed PASS.
