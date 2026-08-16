# WS C12 Exit Model Redesign Contract Final Result

Status: C12_EXIT_MODEL_REDESIGN_CONTRACT_READY / CATALOG_CREATION_NOT_AUTHORIZED / NEXT_IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT / OOS_NOT_RUN

## Scope

C12 is not a strategy catalog. It turns the C11 blockers into an explicit future redesign contract while keeping catalog creation blocked for this session.

Hard boundaries:

```text
C07 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
C07 catalog_version=C07
C07 catalog_count=12
C07 catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
C12_strategy_catalog_created=false
catalog_creation_authorized=false
exit_model_catalog_authorized=false
OOS=NOT_RUN
production_ready=0
best_of_failed_binding_selected=false
```

R1/R2/C01/C02/C03/C04/C05/C06/C07 remain immutable. C12 did not alter any historical catalog identity, hash, IS quality result, OOS result, or production-readiness status.

## Implementation

C12 adds a contract-only surface:

```text
app/Application/Watchlist/Services/WatchlistBacktestExitModelRedesignContractService.php
app/Console/Commands/Watchlist/RunBacktestExitModelRedesignContractCommand.php
command=watchlist:backtest-exit-model-redesign-contract
```

The command reads the C11 contract-audit JSON and writes a C12 redesign-contract JSON artifact. It does not seed a catalog, does not invoke OOS, does not change IS gates, and does not authorize production readiness.

## Executed Command

Run 1:

```text
php artisan watchlist:backtest-exit-model-redesign-contract --c11-artifact=storage/app/watchlist/backtest/c11-exit-model-contract-audit.json --output=storage/app/watchlist/backtest/c12-exit-model-redesign-contract.json --overwrite
```

Result:

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
```

Run 2 used a separate output path and produced the same canonical artifact hash:

```text
artifact_hash=04d4e2f230685962fadd1bc26c294cbaed10f38b
```

File SHA differs between run 1 and run 2 because `generated_at` and output path are intentionally present in the JSON file but excluded from the canonical artifact hash.

Audit artifact:

```text
storage/app/watchlist/backtest/c12-exit-model-redesign-contract.json
docs/watchlist/evidence/weekly_swing/artifacts/c12-exit-model-redesign-contract.json
file_sha1=B3575122DB69A0CA8EAD4D3C78B328687C2CC894
artifact_hash=04d4e2f230685962fadd1bc26c294cbaed10f38b
```

## Contract Decision

C12 makes the future redesign path explicit:

```text
design_contract_ready=true
catalog_creation_authorized=false
exit_model_catalog_authorized=false
next_required_step=IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG
```

Allowed first-phase axes for future implementation work:

```text
risk.min_rr
risk.stop_atr_mult
```

These axes are conditionally allowed only because they are already represented in the official param-grid schema and runtime metrics path. They are still fixed for R1/R2/C01/C02/C03/C04/C05/C06/C07, so any future catalog must use a new catalog identity and a new factory/calibration definition without mutating historical catalogs.

Blocked first-phase axes:

```text
backtest.holding_days
backtest.target_pct|backtest.stop_pct
```

Reasons:

```text
backtest.holding_days is consumed by metrics, but published-price runtime currently forces HOLD=5 and boundary censoring must be redesigned first.
backtest.target_pct|backtest.stop_pct are consumed by metrics when present, but they are not present in the official param-grid schema or curated rows.
```

Required implementation sequence before any future catalog:

```text
create_new_catalog_identity_only_after_contract_support_exists
keep_c01_c07_fixed_execution_snapshot_guards
add_factory_and_calibration_definitions_for_the_new_family_only
add static/unit guards for no_oos_no_best_of_failed_no_gate_relaxation
seed_new_catalog_idempotently
run_is_calibration_twice_only
allow_oos_only_if_is_valid_param_count_ge_1_and_best_binding_hash_is_non_empty
```

## Decision

```text
C07_REMAINS_REJECTED_AS_STRATEGY_QUALITY_CATALOG
C12_STRATEGY_CATALOG_CREATED=false
CATALOG_CREATION_AUTHORIZED=false
EXIT_MODEL_CATALOG_AUTHORIZED=false
NEXT_REQUIRED_STEP=IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG
OOS_NOT_ELIGIBLE
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C12 does not create a valid IS candidate, `param_id_best_is`, or `best_is_binding_hash`.

The next session should implement the contracted exit-axis support for a future new-family catalog path. It must preserve C01-C07 fixed-execution guards, avoid OOS, and still require IS-only proof before any catalog can be considered for OOS.

## Validation

Executed in this workspace:

```text
php -l app\Application\Watchlist\Services\WatchlistBacktestExitModelRedesignContractService.php = PASS
php -l app\Console\Commands\Watchlist\RunBacktestExitModelRedesignContractCommand.php = PASS
php -l app\Console\Kernel.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestExitModelRedesignContractServiceTest.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestExitModelRedesignContractStaticGuardTest.php = PASS
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitModelRedesignContract" = PASS / OK (3 tests, 33 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestExitModelContractAudit" = PASS / OK (3 tests, 34 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07" = PASS / OK (10 tests, 376 assertions)
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (308 tests, 6669 assertions)
```

OOS was not run and must not be claimed PASS.
