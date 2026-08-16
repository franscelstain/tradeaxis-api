# WS C21 Entry/Exit Behavior Diagnostic

C21 is the final IS-only entry/exit behavior diagnostic after C19 and C20. It is **not C20 tuning**, not a new catalog, not an OOS proof, and not a production execution-model change.

C21 measures what happens after a recommendation has already been fixed: signal close, next-open entry, D+1 through D+5 OHLC path, stop/target timing, MFE/MAE, gave-back-profit behavior, never-profitable behavior, exit-reason concentration, and C20_G03 context as segmentation only.

## Final status

```text
C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
PHPUNIT_C21=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C21_FOCUSED_RUNTIME_PASS=true
C21_ALL_PARAM_RUNTIME_PASS=true
C21_DIAGNOSTIC_RUNTIME_PASS=true
C21_EXECUTION_SIGNAL_FOUND=true
ENTRY_PROBLEM_SUSPECTED=false
EXIT_PROBLEM_SUSPECTED=true
STOP_PROBLEM_SUSPECTED=true
HOLD_PERIOD_PROBLEM_SUSPECTED=true
REGIME_EXPLAINS_EXECUTION_PROBLEM=false
C21_CATALOG_IMPLEMENTATION_DEFERRED=true
C21_CATALOG_CODE=NOT_CREATED
C20_DATE_GATE_NOT_ENOUGH_PRESERVED=true
C19_CATALOG_CANDIDATE_FAILED_PRESERVED=true
C01_TO_C20_IMMUTABLE=true
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC
```

C21 is runtime validated as a diagnostic. It does **not** unlock catalog creation, OOS, production readiness, C20 promotion, C19 reopening, or canonical execution-model mutation.

## Why C21 exists

C19 proved that a ticker-quality signal exists, but the qualified quality core is too small. When the sample is expanded toward the required evaluated-pick zone, quality turns negative again.

```text
C19_SAMPLE_RECOVERY_SOLVED=true
C19_PRICE_EVALUATION_CONFIRMED=true
C19_QUALITY_SIGNAL_FOUND=true
C19_QUALITY_CORE_SAMPLE_TOO_SMALL=true
C19_SAMPLE_QUALIFIED_FRONTIER_QUALITY_FAILED=true
C19_CATALOG_CANDIDATE_FAILED=true
C19_CATALOG_CODE=NOT_CREATED
C19_STOP_TUNING=true
OOS_NOT_RUN=true
production_ready=0
```

C20 proved that trade-date/regime gating gives partial improvement, but it is not enough to justify continuation into catalog or OOS.

```text
C20_DIAGNOSTIC_RUNTIME_PASS=true
C20_7_PROFILE_ALL_PARAM_PASS=true
C20_DATE_GATE_NOT_ENOUGH=true
C20_REGIME_DATE_GATE_STRATEGY_FAILED=true
C20_CATALOG_CANDIDATE_FAILED=true
C20_CATALOG_CODE=NOT_CREATED
C20_STOP_TUNING=true
OOS_NOT_RUN=true
production_ready=0
```

C21 therefore tested the next plausible failure point: execution behavior under the frozen canonical model.

## Implemented source components

```text
Service:
app/Application/Watchlist/Services/WatchlistBacktestC21EntryExitBehaviorDiagnosticService.php

Command:
app/Console/Commands/Watchlist/RunBacktestC21EntryExitBehaviorDiagnoseCommand.php

Command signature:
watchlist:backtest-c21-entry-exit-behavior-diagnose
```

The command is registered in `app/Console/Kernel.php` and supports:

```text
--catalog-code=
--from=
--to=
--param-ids=
--profiles=
--profile-codes=
--progress
--output=
--overwrite
--max-params=
--max-profiles=
--max-picks=
```

## Diagnostic profiles

```text
C21_P00_CANONICAL_PATH_BASELINE
C21_P01_ENTRY_GAP_BUCKET_ANALYSIS
C21_P02_MFE_MAE_PATH_ANALYSIS
C21_P03_EXIT_REASON_DISTRIBUTION
C21_P04_HOLD_DAY_RETURN_PATH
C21_P05_GAVE_BACK_PROFIT_ANALYSIS
C21_P06_NEVER_PROFITABLE_ANALYSIS
C21_P07_C20_G03_SEGMENTED_PATH_ANALYSIS
```

These are analysis profiles only. They are not catalog rows, not production paramsets, and not promotion candidates.

## Selection and price-read contract

C21 uses the existing C19 selection diagnostic output as fixed recommendation source. Ticker and trade-date selection are frozen before any path price is read.

Allowed price usage:

```text
measurement only after recommendation freeze
signal close measurement
next open entry measurement
D+1 through D+5 OHLC path measurement
MFE/MAE measurement
canonical exit reason measurement
```

Forbidden price usage:

```text
choosing ticker
choosing trade_date
creating catalog
promoting paramset
best-of-failed binding
```

## Canonical model remains frozen

C21 computes shadow path diagnostics while preserving the canonical backtest model:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

No production execution rule changes are allowed from C21.

## Per-pick metrics

Each evaluated pick records at minimum:

```text
trade_date
ticker_id
ticker
param_id
row_code
entry_date
entry_price
signal_close_price
entry_gap_pct
exit_date
exit_price
exit_reason
exit_day_offset
ret_net
d1_close_ret
d2_close_ret
d3_close_ret
d4_close_ret
d5_close_ret
mfe_1d..mfe_5d
mae_1d..mae_5d
max_favorable_excursion_pct
max_adverse_excursion_pct
first_profitable_day
first_stop_touch_day
first_target_touch_day
gave_back_profit_flag
never_profitable_flag
gap_open_loss_flag
stop_before_target_flag
target_before_stop_flag
missing_path_data_flag
```

## Summary metrics

The artifact includes:

```text
path_summary
entry_gap_summary
mfe_mae_summary
exit_reason_summary
hold_day_return_summary
gave_back_profit_summary
never_profitable_summary
c20_g03_segment_summary
```

Minimum summary markers include evaluated count, missing count, return metrics, win rate, exit-stop/target/hold counts, entry-gap metrics, never-profitable rate, gave-back-profit rate, MFE/MAE medians, first-profitable-day distribution, exit-day distribution, return-by-day distribution, and exit-reason distribution.

## Operator validation evidence

### PHPUnit validation

```text
PHPUNIT_C21=PASS
OK (6 tests, 173 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
OK (397 tests, 9500 assertions)
```

### Focused C21 diagnostic runtime

Command scope:

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06
from=2023-01-02
to=2025-05-21
param_ids=148,152,155
profile_count=4
profile_scope=EXPLICIT
```

Runtime evidence:

```text
status=PASS
reason_code=WS_BT_C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC_READY
scope=IS_ONLY_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-focused.json
artifact_hash=d80111aa07a0cb20ec7b4e087be0d4e4c3191fa8
evaluated_picks_count=1576
path_missing_count=44
avg_entry_gap_pct=0.00029376446222333
median_entry_gap_pct=0
never_profitable_rate=0.24111675126904
gave_back_profit_rate=0.52791878172589
gap_open_loss_rate=0.2258883248731
exit_stop_count=672
exit_target_count=564
exit_hold_count=340
median_mfe_5d=0.012919645092204
median_mae_5d=-0.018750732450486
diagnostic_signal_found=1
entry_problem_suspected=0
exit_problem_suspected=1
stop_problem_suspected=1
hold_period_problem_suspected=1
regime_explains_execution_problem=0
c21_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

Focused path missing rate is `44 / 1576 = 2.79%`, low enough for diagnostic interpretation.

### All-param C21 diagnostic runtime

Command scope:

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06
from=2023-01-02
to=2025-05-21
profile_count=8
profile_scope=EXPLICIT
```

Runtime evidence:

```text
status=PASS
reason_code=WS_BT_C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC_READY
scope=IS_ONLY_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json
artifact_hash=d6c6c72d51b40a0c852ce9bbc6a452c55920df13
evaluated_picks_count=12600
path_missing_count=360
avg_entry_gap_pct=0.00096323026370276
median_entry_gap_pct=0
never_profitable_rate=0.22603174603175
gave_back_profit_rate=0.55365079365079
gap_open_loss_rate=0.23555555555556
exit_stop_count=5824
exit_target_count=4320
exit_hold_count=2456
median_mfe_5d=0.014354066985646
median_mae_5d=-0.021739130434783
diagnostic_signal_found=1
entry_problem_suspected=0
exit_problem_suspected=1
stop_problem_suspected=1
hold_period_problem_suspected=1
regime_explains_execution_problem=0
c21_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

All-param path missing rate is `360 / 12600 = 2.86%`, low enough for diagnostic interpretation.

## Final interpretation

C21 confirms that the next failure point is not primarily entry gap.

Entry evidence:

```text
focused_avg_entry_gap_pct=0.00029376446222333
focused_median_entry_gap_pct=0
focused_gap_open_loss_rate=0.2258883248731

all_param_avg_entry_gap_pct=0.00096323026370276
all_param_median_entry_gap_pct=0
all_param_gap_open_loss_rate=0.23555555555556

entry_problem_suspected=0
```

`ENTRY=NEXT_OPEN` is therefore not the main suspect. C21 does not justify changing entry behavior.

C21 confirms a strong execution-exit signal.

Exit evidence:

```text
focused_gave_back_profit_rate=0.52791878172589
all_param_gave_back_profit_rate=0.55365079365079
exit_problem_suspected=1
```

More than half of evaluated trades became profitable at some point but later gave back profit. This means the signal is not simply dead at entry; profit capture is the stronger suspect.

Stop evidence:

```text
focused_exit_stop_count=672
focused_exit_target_count=564

all_param_exit_stop_count=5824
all_param_exit_target_count=4320
stop_problem_suspected=1
```

In the all-param run, stop exits occur about `1.35x` target exits. Stop behavior is materially more dominant than target capture.

Path asymmetry evidence:

```text
focused_median_mfe_5d=0.012919645092204
focused_median_mae_5d=-0.018750732450486

all_param_median_mfe_5d=0.014354066985646
all_param_median_mae_5d=-0.021739130434783
hold_period_problem_suspected=1
```

Median adverse excursion is materially larger than median favorable excursion. HOLD=5 and canonical stop/target/time exit behavior need shadow comparison before any production change can be considered.

Regime explanation remains unsupported:

```text
regime_explains_execution_problem=0
```

C20_G03 remains segmentation context only. C21 does not reopen C20 and does not promote C20_G03 as a gate.

## Decision gates

C21 reports `diagnostic_signal_found=1` because several gates are met:

```text
gave_back_profit_rate >= 25%
exit_stop_count materially dominates exit_target_count
median_mfe_5d > median_ret_net_top by a meaningful margin
hold_period_problem_suspected=1
```

C21 separately reports:

```text
diagnostic_signal_found=1
entry_problem_suspected=0
exit_problem_suspected=1
stop_problem_suspected=1
hold_period_problem_suspected=1
regime_explains_execution_problem=0
```

These decisions are diagnostic only. They do not allow catalog, OOS, or production readiness.

## Safety boundaries

C21 keeps:

```text
IS_ONLY=true
OOS_NOT_RUN=true
production_ready=0
C21_CATALOG_CODE=NOT_CREATED
C21_CATALOG_IMPLEMENTATION_DEFERRED=true
NO_PROMOTION=true
NO_OOS=true
NO_TICKER_BLACKLIST=true
NO_MONTH_BLACKLIST=true
NO_SECTOR_WHITELIST=true
NO_BEST_OF_FAILED_BINDING=true
NO_C01_TO_C20_MUTATION=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
```

C20 G03 is available only as segmentation/explanation context. It is not a filter decision and must not be promoted as a gate.

## Final C21 conclusion

```text
C21_DIAGNOSTIC_RUNTIME_PASS=true
C21_EXECUTION_SIGNAL_FOUND=true
C21_CATALOG_CANDIDATE_FAILED=true
C21_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC
```

C21 should stop here. Do not tune C21 into a catalog. The correct next step is C22: an IS-only exit-capture shadow diagnostic over fixed recommendations, comparing hypothetical exit capture rules without changing production behavior.
