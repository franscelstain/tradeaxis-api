# WS C13 Exit Axis Support Final Result

Status: C13_EXIT_AXIS_SUPPORT_READY / STRATEGY_CATALOG_NOT_CREATED / FUTURE_CATALOG_DEFINITION_WORK_AUTHORIZED / OOS_NOT_RUN

## Scope

C13 is not a strategy catalog. It implements the C12 support boundary for future exit-axis catalog work while preserving the fixed execution behavior of R1/R2/C01/C02/C03/C04/C05/C06/C07.

Hard boundaries:

```text
source_catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
source_catalog_version=C07
source_catalog_count=12
source_catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
C13_strategy_catalog_created=false
catalog_creation_authorized=false
future_catalog_definition_work_authorized=true
exit_model_catalog_authorized=false
OOS=NOT_RUN
production_ready=0
best_of_failed_binding_selected=false
```

R1/R2/C01/C02/C03/C04/C05/C06/C07 remain immutable. C13 did not alter any historical catalog identity, hash, IS quality result, OOS result, or production-readiness status.

## Implementation

C13 adds an exit-axis support surface:

```text
app/Application/Watchlist/Services/WatchlistBacktestExitAxisSupport.php
app/Application/Watchlist/Services/WatchlistBacktestExitAxisSupportAuditService.php
app/Console/Commands/Watchlist/RunBacktestExitAxisSupportAuditCommand.php
command=watchlist:backtest-exit-axis-support-audit
```

The paramset factory now delegates curated-catalog execution-axis resolution to `WatchlistBacktestExitAxisSupport`. Existing catalogs still use the fixed execution policy and still reject drift with the legacy reason:

```text
WS_BT_R2_CATALOG_INVALID: fixed execution/grouping snapshot drifted.
```

C13 does not seed a catalog, does not invoke IS calibration for a new catalog, does not invoke OOS, does not change IS gates, and does not authorize production readiness.

## Executed Command

Run 1:

```text
php artisan watchlist:backtest-exit-axis-support-audit --c12-artifact=storage/app/watchlist/backtest/c12-exit-model-redesign-contract.json --output=storage/app/watchlist/backtest/c13-exit-axis-support-audit.json --overwrite
```

Result:

```text
status=PASS
reason_code=WS_BT_C13_EXIT_AXIS_SUPPORT_READY
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
catalog_version=C07
catalog_count=12
catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
source_c12_artifact_hash=04d4e2f230685962fadd1bc26c294cbaed10f38b
support_ready=1
fixed_guard_rejects_drift=1
variable_policy_accepts_risk_axes=1
variable_policy_blocks_holding_days=1
variable_policy_blocks_target_stop_pct=1
catalog_creation_authorized=0
future_catalog_definition_work_authorized=1
exit_model_catalog_authorized=0
next_required_step=CREATE_NEW_EXIT_AXIS_CATALOG_DEFINITION_AND_SEED_IS_ONLY
strategy_catalog_created=0
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
artifact_hash=73ba035edfa22f19b4b3525ee3f522241fbae291
production_ready=0
```

Run 2 used a separate output path and produced the same canonical artifact hash:

```text
artifact_hash=73ba035edfa22f19b4b3525ee3f522241fbae291
```

Audit artifact:

```text
storage/app/watchlist/backtest/c13-exit-axis-support-audit.json
docs/watchlist/evidence/weekly_swing/artifacts/c13-exit-axis-support-audit.json
file_sha1=11548827E3DD8249BBE3FDAA2F545816A01FA31C
artifact_hash=73ba035edfa22f19b4b3525ee3f522241fbae291
```

## Support Decision

C13 implements first-phase support for the C12-allowed runtime axes:

```text
risk.stop_atr_mult
risk.min_rr
```

These axes are supported only for a future new catalog definition. They remain fixed for R1/R2/C01/C02/C03/C04/C05/C06/C07.

C13 blocks the first-phase axes that C12 did not authorize:

```text
backtest.holding_days
backtest.target_pct
backtest.stop_pct
```

The implementation keeps grouping targets fixed under the variable risk-exit policy. Any future catalog using this support must still create a new catalog identity, seed idempotently, and pass two IS-only calibration runs before OOS can even be considered.

## Decision

```text
C07_REMAINS_REJECTED_AS_STRATEGY_QUALITY_CATALOG
C13_STRATEGY_CATALOG_CREATED=false
CATALOG_CREATION_AUTHORIZED=false
FUTURE_CATALOG_DEFINITION_WORK_AUTHORIZED=true
EXIT_MODEL_CATALOG_AUTHORIZED=false
NEXT_REQUIRED_STEP=CREATE_NEW_EXIT_AXIS_CATALOG_DEFINITION_AND_SEED_IS_ONLY
OOS_NOT_ELIGIBLE
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C13 does not create a valid IS candidate, `param_id_best_is`, or `best_is_binding_hash`.

The next session may create a new exit-axis catalog definition and seed it for IS-only calibration. OOS remains blocked unless the new catalog later produces `is_valid_param_count >= 1`, a non-empty `param_id_best_is`, and a non-empty `best_is_binding_hash`.

## Validation

Executed in this workspace:

```text
php -l app\Application\Watchlist\Services\WatchlistBacktestExitAxisSupport.php = PASS
php -l app\Application\Watchlist\Services\WatchlistBacktestExitAxisSupportAuditService.php = PASS
php -l app\Application\Watchlist\Services\WatchlistBacktestExitModelContractAuditService.php = PASS
php -l app\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory.php = PASS
php -l app\Console\Commands\Watchlist\RunBacktestExitAxisSupportAuditCommand.php = PASS
php -l app\Console\Kernel.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestExitAxisSupportTest.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestExitAxisSupportAuditServiceTest.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestExitAxisSupportStaticGuardTest.php = PASS
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitAxisSupport" = PASS / OK (11 tests, 59 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestR2ParamGridParamsetFactory" = PASS / OK (12 tests, 106 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07" = PASS / OK (10 tests, 376 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitModelRedesignContract" = PASS / OK (3 tests, 33 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitModelContractAudit" = PASS / OK (3 tests, 34 assertions)
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (319 tests, 6728 assertions)
git diff --check = PASS
```

OOS was not run and must not be claimed PASS.
