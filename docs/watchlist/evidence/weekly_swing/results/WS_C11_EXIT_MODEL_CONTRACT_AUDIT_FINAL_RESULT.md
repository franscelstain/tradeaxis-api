# WS C11 Exit Model Contract Audit Final Result

Status: C11_EXIT_MODEL_CONTRACT_AUDIT_READY / EXIT_MODEL_CATALOG_NOT_AUTHORIZED / NEXT_CATALOG_NOT_DESIGNED / OOS_NOT_RUN

## Scope

C11 is not a strategy catalog. It is a contract audit after C10 showed that C07 trades still hit stop/time-expiry far more often than target and remained below all locked IS quality gates.

Hard boundaries:

```text
C07 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
C07 catalog_version=C07
C07 catalog_count=12
C07 catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
C11_strategy_catalog_created=false
exit_model_catalog_authorized=false
OOS=NOT_RUN
production_ready=0
best_of_failed_binding_selected=false
```

R1/R2/C01/C02/C03/C04/C05/C06/C07 remain immutable. This session did not alter any historical catalog identity, hash, IS quality result, OOS result, or production-readiness status.

## Implementation

C11 adds an IS-only contract audit surface:

```text
app/Application/Watchlist/Services/WatchlistBacktestExitModelContractAuditService.php
app/Console/Commands/Watchlist/RunBacktestExitModelContractAuditCommand.php
command=watchlist:backtest-exit-model-contract-audit
```

The command reads the C10 batch summary CSV and writes a JSON audit artifact. It does not query OOS services, does not write an OOS table, does not seed a catalog, and does not select a best failed row.

## Executed Command

Run 1:

```text
php artisan watchlist:backtest-exit-model-contract-audit --c10-summary=storage/app/watchlist/backtest/c10-batched-c07-exit-model-summary.csv --output=storage/app/watchlist/backtest/c11-exit-model-contract-audit.json --overwrite
```

Result:

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
```

Run 2 used a separate output path and produced the same canonical artifact hash:

```text
artifact_hash=4b8a6a383c1ad9f5cab78394b3851b4b3a3325ea
```

File SHA differs between run 1 and run 2 because `generated_at` and output path are intentionally present in the JSON file but excluded from the canonical artifact hash.

Audit artifact:

```text
storage/app/watchlist/backtest/c11-exit-model-contract-audit.json
docs/watchlist/evidence/weekly_swing/artifacts/c11-exit-model-contract-audit.json
file_sha1=E00E9BA960E50CE1E32ABA717BDFBD1EC0BE54A4
artifact_hash=4b8a6a383c1ad9f5cab78394b3851b4b3a3325ea
```

## Findings

C11 confirms the following runtime/contract state:

```text
risk.stop_atr_mult=SUPPORTED_BY_GRID_SCHEMA_FACTORY_AND_METRICS / FIXED_FOR_R1_R2_C01_C02_C03_C04_C05_C06_C07
risk.min_rr=SUPPORTED_BY_GRID_SCHEMA_FACTORY_AND_METRICS / FIXED_FOR_R1_R2_C01_C02_C03_C04_C05_C06_C07
backtest.holding_days=METRICS_SERVICE_CONSUMES_VALUE / PUBLISHED_PRICE_RUNTIME_FORCES_HOLD_5
backtest.target_pct|backtest.stop_pct=METRICS_SERVICE_CONSUMES_VALUES_WHEN_PRESENT / NOT_PRESENT_IN_PARAM_GRID_SCHEMA_OR_CURATED_FACTORY_ROWS
```

Code contract audit:

```text
factory_rejects_fixed_execution_snapshot_drift=true
factory_defines_c07_as_fixed_execution_catalog=true
metrics_consumes_stop_atr_mult_and_min_rr=true
metrics_consumes_target_stop_pct_when_present=true
metrics_consumes_holding_days=true
published_runtime_forces_holding_days_5=true
param_grid_schema_exposes_stop_atr_mult_and_min_rr=true
param_grid_schema_exposes_target_stop_pct=false
oos_service_dependency=false
oos_repository_dependency=false
```

C10 strategy-quality evidence remains failed:

```text
best_median_ret_net_top=-0.0069927968181773
best_p25_ret_net_top=-0.034275706780085
best_month_win_rate_min=0.25
target_hit_share=17.99%
stop_or_timeout_share=82.01%
```

## Blocking Reasons

```text
C01_C07_FACTORY_REJECTS_EXIT_AXIS_DRIFT
PUBLISHED_RUNTIME_FORCES_HOLD_5
PARAM_GRID_SCHEMA_LACKS_TARGET_STOP_PERCENT_FIELDS
C10_SUMMARY_REMAINS_BELOW_LOCKED_IS_GATES
C10_EXIT_OUTCOMES_STOP_OR_TIMEOUT_DOMINATE_TARGET
```

## Decision

```text
C07_REMAINS_REJECTED_AS_STRATEGY_QUALITY_CATALOG
C11_STRATEGY_CATALOG_CREATED=false
EXIT_MODEL_CATALOG_AUTHORIZED=false
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_ELIGIBLE
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C11 does not create a valid IS candidate, `param_id_best_is`, or `best_is_binding_hash`.

The next strategy session must first define an explicit exit-model or strategy-family redesign contract, including schema/factory/runtime boundary behavior and tests. Only after that contract exists should a new catalog be considered.

## Validation

Executed in this workspace:

```text
php -l app\Application\Watchlist\Services\WatchlistBacktestExitModelContractAuditService.php = PASS
php -l app\Console\Commands\Watchlist\RunBacktestExitModelContractAuditCommand.php = PASS
php -l app\Console\Kernel.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestExitModelContractAuditServiceTest.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestExitModelContractAuditStaticGuardTest.php = PASS
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitModelContractAudit" = PASS / OK (3 tests, 34 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07" = PASS / OK (10 tests, 376 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestIsFailureDrilldown" = PASS / OK (6 tests, 123 assertions)
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (305 tests, 6636 assertions)
```

OOS was not run and must not be claimed PASS.
