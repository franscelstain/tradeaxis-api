# WS C02 Operator Validation Commands and Final Result

## Final status

```text
OPERATOR_VALIDATED / EXECUTED / QUALITY_FAILED / C02_REJECTED_AS_STRATEGY_CATALOG / OOS_NOT_RUN / NOT_PRODUCTION_READY / C03_REQUIRED
```

This document originally defined the required operator commands for C02. Operator output has now been supplied for PHPUnit, full Watchlist PHPUnit, C02 seed, two C02 IS calibration runs, forensic artifact review, and post-docs validation after the final documentation/forensic CSV sync.

Authoring sandbox blockers remain historical only and are not the final C02 result:

```text
php vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC02" = BLOCKED / exit code 1 / missing dom, mbstring, xml, xmlwriter
php artisan list = BLOCKED / exit code 2 / ENV_UNSUPPORTED_PHP_VERSION / current PHP 8.4.16
```

## Operator validation evidence supplied

### 1. C02 unit/static tests

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC02"
```

Operator output:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

............                                                      12 / 12 (100%)

Time: 00:03.113, Memory: 14.00 MB

OK (12 tests, 391 assertions)
```

Result:

```text
PASS / exit code 0
```

### 2. Full Watchlist unit tests

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Operator output:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

...............................................................  63 / 238 ( 26%)
............................................................... 126 / 238 ( 52%)
............................................................... 189 / 238 ( 79%)
.................................................               238 / 238 (100%)

Time: 00:03.575, Memory: 24.00 MB

OK (238 tests, 4182 assertions)
```

Result:

```text
PASS / exit code 0
```

### 3. Seed C02 catalog

Command:

```powershell
php artisan watchlist:backtest-c02-param-grid-seed
```

Operator output markers:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
catalog_version=C02
catalog_count=8
catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
inserted_count=8
updated_count=0
existing_count=0
r1_catalog_count=24
r1_catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
r2_catalog_count=12
r2_catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
c01_catalog_count=8
c01_catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
r1_immutable=1
r2_immutable=1
c01_immutable=1
oos_executed=0
production_ready=0
```

Result:

```text
PASS / exit code 0
```

### 4. C02 IS calibration run 1

Command:

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c02-is-run-1.json --overwrite
```

Operator output markers:

```text
status=C02_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_R2_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
catalog_version=C02
catalog_count=8
catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=8
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=<empty>
best_is_binding_hash=<empty>
max_requested_market_data_date=2025-05-21
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
production_ready=0
```

Result:

```text
EXECUTED / QUALITY_FAILED / NOT_PRODUCTION_READY
```

### 5. C02 IS calibration run 2

Command:

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c02-is-run-2.json --overwrite
```

Operator output markers:

```text
status=C02_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_R2_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
catalog_version=C02
catalog_count=8
catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=8
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=<empty>
best_is_binding_hash=<empty>
max_requested_market_data_date=2025-05-21
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
production_ready=0
```

Result:

```text
EXECUTED / QUALITY_FAILED / NOT_PRODUCTION_READY
```

### 6. C02 two-run determinism

```text
run_1.artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
run_2.artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
hash_equal=1
```

Result:

```text
PASS for deterministic artifact reproduction.
```

## Post-docs validation evidence

After the C02 final documentation and forensic CSV sync, the operator reran focused C02 PHPUnit and the full Watchlist suite.

Scope:

```text
DOCUMENTATION_AND_FORENSIC_CSV_ONLY
runtime_code_changed=false
catalog_changed=false
seed_rerun_required=false
calibration_rerun_required=false
```

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC02"
```

Operator output:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

............                                                      12 / 12 (100%)

Time: 00:01.281, Memory: 14.00 MB

OK (12 tests, 391 assertions)
```

Result:

```text
PASS / exit code 0
```

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Operator output:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

...............................................................  63 / 238 ( 26%)
............................................................... 126 / 238 ( 52%)
............................................................... 189 / 238 ( 79%)
.................................................               238 / 238 (100%)

Time: 00:04.431, Memory: 24.00 MB

OK (238 tests, 4182 assertions)
```

Result:

```text
PASS / exit code 0
post_docs_validation_verdict=PASS
```

Interpretation: the final C02 docs/forensic CSV sync did not break C02 static guards or the full Watchlist unit suite. This evidence does not change C02 strategy verdict: C02 remains `QUALITY_FAILED`, `OOS_NOT_RUN`, and `NOT_PRODUCTION_READY`.

## Final forensic result

Artifact root structure was verified by operator:

```text
all_evaluations
best_is_binding
c01_immutability_proof
catalog_manifest
diagnostic_summary
gate_summary
is_window_manifest
market_data_lineage
meta
no_oos_read_proof
parameter_axes
persistence_manifest
r1_control_reference
r1_immutability_proof
r2_immutability_proof
validation
```

Final manifest markers:

```text
artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
artifact_version=WATCHLIST_C02_IS_CALIBRATION_V1
oos_executed=False
production_ready=False
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
catalog_version=C02
catalog_count=8
catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
is_from=2023-01-02
is_to=2025-05-21
trading_date_count=562
valid_count=0
failed_count=8
best_is_binding_empty=True
strict_is_boundary=True
max_requested_date=2025-05-21
max_allowed_date=2025-05-21
oos_table_unchanged=True
catalog_hash_matches=True
r1_immutable=True
r2_immutable=True
c01_immutable=True
no_oos_market_read=True
no_oos_table_mutation=True
```

Failure reason distribution:

```text
WS_BT_EVAL_DOWNSIDE_FAIL=8
WS_BT_EVAL_ROBUST_RETURN_FAIL=8
WS_BT_EVAL_STABILITY_FAIL=8
```

Gate detail from representative row `00_C01_NEAREST_GATE_REFERENCE`:

```text
average_return_positive=false
median_return_non_negative=false
minimum_coverage=true
minimum_trade_count=true
monthly_average_floor=false
monthly_win_rate_floor=false
p25_downside_bound=false
```

Thresholds:

```text
min_days_covered=390
min_month_avg_ret_net_min=-0.01
min_month_win_rate_min=0.45
min_p25_ret_net_top=-0.03
min_trades=120
```

C02 metric range across all 8 rows:

```text
days_covered=506..508
picks_count=1360..1435
win_rate_top=39.44%..41.82%
median_ret_net_top=-2.10%..-1.72%
p25_ret_net_top=-5.59%..-4.97%
month_win_rate_min=14.03%..23.21%
period_fail_count=18..22 of 27
```

Final strategy-quality verdict:

```text
C02 rejected as IS-quality strategy catalog.
No C02 OOS run is allowed.
No C02 production-ready claim is allowed.
Next required work is C03 catalog design from C02 forensic metrics.
```

## OOS boundary

Do not run OOS for C02. C02 has no valid frozen IS binding and no best IS binding hash.
