# WS C12 Operator Validation Commands

Status: OPERATOR_REFERENCE / C12_EXIT_MODEL_REDESIGN_CONTRACT_ONLY / OOS_NOT_RUN

## PHP Lint

Commands:

```text
php -l app\Application\Watchlist\Services\WatchlistBacktestExitModelRedesignContractService.php
php -l app\Console\Commands\Watchlist\RunBacktestExitModelRedesignContractCommand.php
php -l app\Console\Kernel.php
php -l tests\Unit\Watchlist\WatchlistBacktestExitModelRedesignContractServiceTest.php
php -l tests\Unit\Watchlist\WatchlistBacktestExitModelRedesignContractStaticGuardTest.php
```

PASS criteria:

```text
No syntax errors detected
exit_code=0
```

## PHPUnit

Commands:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitModelRedesignContract"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitModelContractAudit"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07"
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected marker from the C12 focused implementation run:

```text
WatchlistBacktestExitModelRedesignContract = OK (3 tests, 33 assertions)
WatchlistBacktestExitModelContractAudit = OK (3 tests, 34 assertions)
WatchlistBacktestC07 = OK (10 tests, 376 assertions)
Full Watchlist = OK (308 tests, 6669 assertions)
exit_code=0
```

## C12 Exit-Model Redesign Contract

Command:

```text
php artisan watchlist:backtest-exit-model-redesign-contract --c11-artifact=storage/app/watchlist/backtest/c11-exit-model-contract-audit.json --output=storage/app/watchlist/backtest/c12-exit-model-redesign-contract.json --overwrite
```

Expected markers:

```text
status=PASS
reason_code=WS_BT_C12_EXIT_MODEL_REDESIGN_CONTRACT_READY
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
catalog_version=C07
catalog_count=12
catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
source_c11_artifact_hash=4b8a6a383c1ad9f5cab78394b3851b4b3a3325ea
design_contract_ready=1
catalog_creation_authorized=0
exit_model_catalog_authorized=0
next_required_step=IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG
strategy_catalog_created=0
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
artifact_hash=04d4e2f230685962fadd1bc26c294cbaed10f38b
production_ready=0
exit_code=0
```

PASS criteria:

```text
artifact JSON is written
artifact decision.status=EXIT_MODEL_REDESIGN_CONTRACT_READY
artifact decision.design_contract_ready=true
artifact decision.catalog_creation_authorized=false
artifact decision.exit_model_catalog_authorized=false
artifact decision.strategy_catalog_created=false
artifact decision.next_required_step=IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG
allowed first-phase axes are risk.min_rr and risk.stop_atr_mult
blocked first-phase axes are backtest.holding_days and backtest.target_pct|backtest.stop_pct
artifact no_oos_leakage_summary.oos_executed=false
artifact meta.production_ready=false
no best IS binding is created
no OOS command is run
```

Current implementation evidence:

```text
artifact_path=storage/app/watchlist/backtest/c12-exit-model-redesign-contract.json
docs_artifact=docs/watchlist/evidence/weekly_swing/artifacts/c12-exit-model-redesign-contract.json
file_sha1=B3575122DB69A0CA8EAD4D3C78B328687C2CC894
artifact_hash=04d4e2f230685962fadd1bc26c294cbaed10f38b
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

C07 remains rejected as a strategy-quality catalog. C12 is redesign-contract evidence only.
