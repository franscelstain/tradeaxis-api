# WS C11 Operator Validation Commands

Status: OPERATOR_REFERENCE / C11_EXIT_MODEL_CONTRACT_AUDIT_ONLY / OOS_NOT_RUN

## PHP Lint

Commands:

```text
php -l app\Application\Watchlist\Services\WatchlistBacktestExitModelContractAuditService.php
php -l app\Console\Commands\Watchlist\RunBacktestExitModelContractAuditCommand.php
php -l app\Console\Kernel.php
php -l tests\Unit\Watchlist\WatchlistBacktestExitModelContractAuditServiceTest.php
php -l tests\Unit\Watchlist\WatchlistBacktestExitModelContractAuditStaticGuardTest.php
```

PASS criteria:

```text
No syntax errors detected
exit_code=0
```

## PHPUnit

Commands:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitModelContractAudit"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestIsFailureDrilldown"
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected markers from the C11 implementation run:

```text
WatchlistBacktestExitModelContractAudit = OK (3 tests, 34 assertions)
WatchlistBacktestC07 = OK (10 tests, 376 assertions)
WatchlistBacktestIsFailureDrilldown = OK (6 tests, 123 assertions)
Full Watchlist = OK (305 tests, 6636 assertions)
exit_code=0
```

## C11 Exit-Model Contract Audit

Command:

```text
php artisan watchlist:backtest-exit-model-contract-audit --c10-summary=storage/app/watchlist/backtest/c10-batched-c07-exit-model-summary.csv --output=storage/app/watchlist/backtest/c11-exit-model-contract-audit.json --overwrite
```

Expected markers:

```text
status=PASS
reason_code=WS_BT_C11_EXIT_MODEL_CONTRACT_AUDIT_READY
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
catalog_version=C07
catalog_count=12
catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
summary_row_count=12
source_summary_sha1=04ee547ee3f982901cabe23e55078868f14104c9
hit_target_total=2585
hit_stop_total=4927
timeout_hold_expired_total=6858
exit_model_catalog_authorized=0
next_decision=NEXT_CATALOG_NOT_DESIGNED
strategy_catalog_created=0
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
artifact_hash=4b8a6a383c1ad9f5cab78394b3851b4b3a3325ea
production_ready=0
exit_code=0
```

PASS criteria:

```text
artifact JSON is written
artifact decision.status=EXIT_MODEL_CATALOG_NOT_AUTHORIZED
artifact decision.exit_model_catalog_authorized=false
artifact decision.strategy_catalog_created=false
artifact decision.next_decision=NEXT_CATALOG_NOT_DESIGNED
artifact blocking_reasons include C01_C07_FACTORY_REJECTS_EXIT_AXIS_DRIFT
artifact blocking_reasons include PUBLISHED_RUNTIME_FORCES_HOLD_5
artifact blocking_reasons include PARAM_GRID_SCHEMA_LACKS_TARGET_STOP_PERCENT_FIELDS
artifact blocking_reasons include C10_SUMMARY_REMAINS_BELOW_LOCKED_IS_GATES
artifact blocking_reasons include C10_EXIT_OUTCOMES_STOP_OR_TIMEOUT_DOMINATE_TARGET
artifact no_oos_leakage_summary.oos_executed=false
artifact meta.production_ready=false
no best IS binding is created
no OOS command is run
```

Current implementation evidence:

```text
artifact_path=storage/app/watchlist/backtest/c11-exit-model-contract-audit.json
docs_artifact=docs/watchlist/audit/_artifacts/c11-exit-model-contract-audit.json
file_sha1=E00E9BA960E50CE1E32ABA717BDFBD1EC0BE54A4
artifact_hash=4b8a6a383c1ad9f5cab78394b3851b4b3a3325ea
```

## OOS Guard

OOS command must not be run in this session.

Fail validation if any command writes or claims:

```text
oos_executed=1
production_ready=1
exit_model_catalog_authorized=1
strategy_catalog_created=1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
```

C07 remains rejected as a strategy-quality catalog. C11 is contract audit evidence only.
