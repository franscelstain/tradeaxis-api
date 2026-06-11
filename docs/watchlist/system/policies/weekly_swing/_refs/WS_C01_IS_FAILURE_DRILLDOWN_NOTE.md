# WS C01 IS Failure Drilldown Reference Note

## Status

```text
LAST_UPDATED=2026-06-11
SESSION=WATCHLIST - C01 IS FAILURE DRILLDOWN PAYLOAD EXPANSION SESSION
SCOPE=C01_IS_FAILURE_DRILLDOWN_PAYLOAD_EXPANSION_IS_ONLY
DONE for C01 IS failure drilldown diagnostic runtime scope
LOCAL_C01_IS_FAILURE_DRILLDOWN_EXECUTED
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_READ
NOT_PRODUCTION_READY
```

This note records the IS-only diagnostic drilldown surface for the immutable failed C01 catalog. It is not an OOS proof, not a promotion decision, and not production readiness evidence.

## Source Evidence Used

Current repository/workspace evidence:

```text
storage/app/watchlist/backtest/c01-is-run-1.json
storage/app/watchlist/backtest/c01-is-run-2.json
storage/app/watchlist/backtest/c01-is-failure-drilldown-run-1.json
storage/app/watchlist/backtest/c01-is-failure-drilldown-run-2.json
docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C01_CALIBRATION_NOTE.md
docs/watchlist/system/policies/weekly_swing/_refs/WS_C01_FAILURE_DIAGNOSTIC_NOTE.md
```

Preserved C01 evidence:

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
catalog_version=C01
catalog_count=8
catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
is_from=2023-01-02
is_to=2025-05-21
calibration_artifact_hash=c8505ce5a9045629234a685984d9138b3990c775
calibration_file_sha1_run_1=04f6c664a0c9006c16542a8380034a0a633041dc
calibration_file_sha1_run_2=04f6c664a0c9006c16542a8380034a0a633041dc
is_valid_param_count=0
best_is_binding=null
oos_executed=false
production_ready=false
```

Current expanded drilldown runtime evidence:

```text
c01_is_failure_drilldown_run_1_exit_code=0
c01_is_failure_drilldown_run_2_exit_code=0
c01_is_failure_drilldown_run_1_file_sha1=2ad5568ae2240822783e696972705a99055baae6
c01_is_failure_drilldown_run_2_file_sha1=2ad5568ae2240822783e696972705a99055baae6
canonical_artifact_hash_run_1=49368eb26aed7975a53cb21701197372d26cd64f
canonical_artifact_hash_run_2=49368eb26aed7975a53cb21701197372d26cd64f
artifact_hash_run_1=49368eb26aed7975a53cb21701197372d26cd64f
artifact_hash_run_2=49368eb26aed7975a53cb21701197372d26cd64f
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
max_requested_market_data_date=2025-05-21
oos_service_invoked=false
oos_repository_invoked=false
oos_table_unchanged=true
oos_executed=false
next_decision=NEXT_CATALOG_NOT_DESIGNED
```

Locked gate and OOS contract files are not changed by this session.

## Implemented Surface

Changed/active source files:

```text
app/Application/Watchlist/Services/WatchlistBacktestIsFailureDrilldownService.php
app/Console/Commands/Watchlist/RunBacktestIsDiagnoseCommand.php
tests/Unit/Watchlist/WatchlistBacktestIsFailureDrilldownServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestIsFailureDrilldownStaticGuardTest.php
docs/watchlist/system/policies/weekly_swing/_refs/WS_C01_IS_FAILURE_DRILLDOWN_NOTE.md
docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md
docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md
```

The command surface is:

```powershell
php artisan watchlist:backtest-is-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage\app\watchlist\backtest\c01-is-failure-drilldown-run-1.json `
  --overwrite
```

Second deterministic runtime proof command:

```powershell
php artisan watchlist:backtest-is-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage\app\watchlist\backtest\c01-is-failure-drilldown-run-2.json `
  --overwrite
```

## Boundary Enforced

```text
explicit_from=2023-01-02
explicit_to=2025-05-21
catalog_code_explicit=required
no_latest_catalog_fallback=true
no_active_catalog_fallback=true
no_current_date_default=true
no_max_trade_date_default=true
hard_market_data_to_date=2025-05-21
max_requested_market_data_date=2025-05-21
oos_service_invoked=false
oos_repository_invoked=false
oos_table_unchanged=true
oos_executed=false
production_ready=false
best_is_binding=null
```

The service does not inject OOS service/repository dependencies, does not write `watchlist_bt_oos_eval_ws`, does not promote any paramset, and writes a file-only diagnostic artifact.

## Diagnostic Artifact Shape

The expanded drilldown artifact includes:

```text
catalog_code
catalog_version
catalog_hash
catalog_count
is_from
is_to
is_trading_date_hash
artifact_hash
canonical_artifact_hash
per_param_status
per_param_failure_codes
per_param_key_metrics
nearest_gate_gap
worst_gate_gap
candidate_count_summary
days_covered_summary
month_win_rate_min_summary
month_avg_ret_min_summary
downside_metric_summary
robust_return_metric_summary
stability_metric_summary
ticker_loss_cluster_summary
ticker_profit_cluster_summary
month_failure_cluster_summary
month_profit_cluster_summary
trade_date_failure_cluster_summary
setup_bucket_summary
breakout_extension_bucket_summary
momentum_roc_bucket_summary
volume_ratio_bucket_summary
liquidity_dv20_bucket_summary
atr_bucket_summary
score_bucket_summary
sector_bucket_summary
score_component_effectiveness_summary
param_axis_effectiveness_summary
runtime_consumed_parameter_summary
dead_parameter_or_silent_default_summary
runtime_field_availability_summary
data_quality_diagnostic_summary
no_oos_leakage_summary
diagnostic_reason_summary
next_focus_recommendation
```

Timestamp-like metadata such as `generated_at` is excluded from the canonical artifact hash. `artifact_hash` and `canonical_artifact_hash` are equal for the current deterministic payload.

## Feature Field Availability

Current runtime evidence does not export the following feature-level fields into the evaluated trade payload:

```text
close_to_hh20_pct
roc20
vol_ratio
dv20_idr
sector_code
score_components
```

Therefore the affected diagnostic sections are emitted with explicit markers:

```text
FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE
NOT_DERIVED
NOT_USED_FOR_NEXT_CATALOG_DECISION
```

Affected sections:

```text
breakout_extension_bucket_summary
momentum_roc_bucket_summary
volume_ratio_bucket_summary
liquidity_dv20_bucket_summary
sector_bucket_summary
score_component_effectiveness_summary
```

This is an evidence finding, not a catalog recommendation. C02 or any next semantic catalog remains not designed.

## Local Validation Performed

```text
php -l app/Application/Watchlist/Services/WatchlistBacktestIsFailureDrilldownService.php
PASS / exit code 0

php -l app/Console/Commands/Watchlist/RunBacktestIsDiagnoseCommand.php
PASS / exit code 0

php -l tests/Unit/Watchlist/WatchlistBacktestIsFailureDrilldownServiceTest.php
PASS / exit code 0

php -l tests/Unit/Watchlist/WatchlistBacktestIsFailureDrilldownStaticGuardTest.php
PASS / exit code 0

vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestIsFailureDrilldown"
PASS / 4 tests / 65 assertions / exit code 0

vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC01"
PASS / 12 tests / 381 assertions / exit code 0

vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestIsCalibration"
PASS / 3 tests / 26 assertions / exit code 0

vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestMetricsServiceTest"
PASS / 15 tests / 113 assertions / exit code 0

vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestPublishedPrice"
PASS / 18 tests / 177 assertions / exit code 0

vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestOos"
PASS / 24 tests / 228 assertions / exit code 0

vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktest"
PASS / 134 tests / 2894 assertions / exit code 0

vendor/bin/phpunit tests/Unit/Watchlist
PASS / 226 tests / 3782 assertions / exit code 0

vendor/bin/phpunit tests/Unit/MarketData --filter "MarketDataPublishedEodSeries"
PASS / 7 tests / 37 assertions / exit code 0

vendor/bin/phpunit tests/Unit/MarketData --filter "MarketDataTradingCalendar"
PASS / 4 tests / 16 assertions / exit code 0

vendor/bin/phpunit tests/Unit/MarketData --filter "MarketDataWatchlistReadModelTest"
PASS / 3 tests / 41 assertions / exit code 0
```

## Current Decision

```text
DONE for C01 IS failure drilldown diagnostic runtime scope
LOCAL_C01_IS_FAILURE_DRILLDOWN_EXECUTED
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_READ
NOT_PRODUCTION_READY
```

OOS-proof eligibility:

```text
NOT_ELIGIBLE_FOR_OOS_PROOF - no valid IS parameter
```

Promotion eligibility:

```text
NOT_ELIGIBLE - OOS proof missing
```

Next session should decide whether runtime feature fields can be exported safely into IS diagnostic evidence. It must not create C02 until a runtime-consumed root-cause focus is proven.
