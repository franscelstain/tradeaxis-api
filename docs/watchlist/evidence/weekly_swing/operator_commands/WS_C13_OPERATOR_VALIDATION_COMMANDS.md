# WS C13 Operator Validation Commands

Status: OPERATOR_REFERENCE / C13_EXIT_AXIS_SUPPORT_ONLY / OOS_NOT_RUN

## PHP Lint

Commands:

```text
php -l app\Application\Watchlist\Services\WatchlistBacktestExitAxisSupport.php
php -l app\Application\Watchlist\Services\WatchlistBacktestExitAxisSupportAuditService.php
php -l app\Application\Watchlist\Services\WatchlistBacktestExitModelContractAuditService.php
php -l app\Application\Watchlist\Services\WatchlistBacktestParamGridParamsetFactory.php
php -l app\Console\Commands\Watchlist\RunBacktestExitAxisSupportAuditCommand.php
php -l app\Console\Kernel.php
php -l tests\Unit\Watchlist\WatchlistBacktestExitAxisSupportTest.php
php -l tests\Unit\Watchlist\WatchlistBacktestExitAxisSupportAuditServiceTest.php
php -l tests\Unit\Watchlist\WatchlistBacktestExitAxisSupportStaticGuardTest.php
```

PASS criteria:

```text
No syntax errors detected
exit_code=0
```

## PHPUnit

Commands:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitAxisSupport"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestR2ParamGridParamsetFactory"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitModelRedesignContract"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitModelContractAudit"
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected marker from the C13 focused implementation run:

```text
WatchlistBacktestExitAxisSupport = OK (11 tests, 59 assertions)
WatchlistBacktestR2ParamGridParamsetFactory = OK (12 tests, 106 assertions)
WatchlistBacktestC07 = OK (10 tests, 376 assertions)
WatchlistBacktestExitModelRedesignContract = OK (3 tests, 33 assertions)
WatchlistBacktestExitModelContractAudit = OK (3 tests, 34 assertions)
Full Watchlist = OK (319 tests, 6728 assertions)
exit_code=0
```

## C13 Exit-Axis Support Audit

Command:

```text
php artisan watchlist:backtest-exit-axis-support-audit --c12-artifact=storage/app/watchlist/backtest/c12-exit-model-redesign-contract.json --output=storage/app/watchlist/backtest/c13-exit-axis-support-audit.json --overwrite
```

Expected markers:

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
exit_code=0
```

PASS criteria:

```text
artifact JSON is written
artifact decision.status=EXIT_AXIS_SUPPORT_READY_FOR_FUTURE_CATALOG_DEFINITION
artifact decision.support_ready=true
artifact decision.catalog_creation_authorized=false
artifact decision.future_catalog_definition_work_authorized=true
artifact decision.exit_model_catalog_authorized=false
artifact decision.strategy_catalog_created=false
artifact decision.next_required_step=CREATE_NEW_EXIT_AXIS_CATALOG_DEFINITION_AND_SEED_IS_ONLY
artifact support_probe.fixed_guard_rejects_drift=true
artifact support_probe.variable_policy_accepts_risk_axes=true
artifact support_probe.variable_policy_blocks_holding_days=true
artifact support_probe.variable_policy_blocks_target_stop_pct=true
artifact no_oos_leakage_summary.oos_executed=false
artifact meta.production_ready=false
no best IS binding is created
no OOS command is run
```

Current implementation evidence:

```text
artifact_path=storage/app/watchlist/backtest/c13-exit-axis-support-audit.json
docs_artifact=docs/watchlist/evidence/weekly_swing/artifacts/c13-exit-axis-support-audit.json
file_sha1=11548827E3DD8249BBE3FDAA2F545816A01FA31C
artifact_hash=73ba035edfa22f19b4b3525ee3f522241fbae291
```

## OOS Guard

OOS command must not be run in this session.

Fail validation if any command writes or claims:

```text
oos_executed=1
production_ready=1
catalog_creation_authorized=1
exit_model_catalog_authorized=1
strategy_catalog_created=1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
```

C07 remains rejected as a strategy-quality catalog. C13 is exit-axis support evidence only.
