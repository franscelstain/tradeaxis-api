# WS C22 Exit Capture Shadow Diagnostic

C22 is an IS-only exit-capture shadow diagnostic after C21. It is not a catalog, not OOS, not production tuning, and not a mutation of the canonical execution model.

C22 uses fixed recommendations from the same C19/C17 selection path used by C21, then reads D+1 through D+5 OHLC only for measurement. The goal is to test whether profit that appears inside the path can be captured better by shadow exit behavior without changing ticker selection, trade-date selection, catalog rows, or production rules.

## Source status

```text
C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
C22_RUNTIME_VALIDATION_REQUIRED=true
PHPUNIT_C22=OPERATOR_VALIDATION_REQUIRED
FULL_WATCHLIST_PHPUNIT=OPERATOR_VALIDATION_REQUIRED
C22_DIAGNOSTIC_RUNTIME_PASS=NOT_RUN
C22_EXIT_CAPTURE_SIGNAL_FOUND=NOT_RUN
C22_CATALOG_IMPLEMENTATION_DEFERRED=true
C22_CATALOG_CODE=NOT_CREATED
C21_EXECUTION_SIGNAL_FOUND_PRESERVED=true
C20_DATE_GATE_NOT_ENOUGH_PRESERVED=true
C19_CATALOG_CANDIDATE_FAILED_PRESERVED=true
C01_TO_C21_IMMUTABLE=true
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=RUN_C22_OPERATOR_VALIDATION
```

No runtime proof is claimed by this source update. Operator validation is required before recording C22 as a runtime PASS or deriving a C23 direction.

## Why C22 exists

C19 proved that a quality signal exists, but the quality-positive core is too small for a catalog-grade sample. Expanding the sample failed the frontier quality requirement.

```text
C19_SAMPLE_RECOVERY_SOLVED=true
C19_PRICE_EVALUATION_CONFIRMED=true
C19_QUALITY_SIGNAL_FOUND=true
C19_QUALITY_CORE_SAMPLE_TOO_SMALL=true
C19_SAMPLE_QUALIFIED_FRONTIER_QUALITY_FAILED=true
C19_CATALOG_CANDIDATE_FAILED=true
C19_CATALOG_CODE=NOT_CREATED
```

C20 proved that regime/trade-date gates are not enough to solve the problem and do not justify a catalog or OOS path.

```text
C20_DATE_GATE_NOT_ENOUGH=true
C20_REGIME_DATE_GATE_STRATEGY_FAILED=true
C20_CATALOG_CANDIDATE_FAILED=true
C20_CATALOG_CODE=NOT_CREATED
```

C21 proved that entry gap is not the primary suspect, while exit behavior, stop behavior, and hold-period behavior are plausible suspects.

```text
C21_EXECUTION_SIGNAL_FOUND=true
ENTRY_PROBLEM_SUSPECTED=false
EXIT_PROBLEM_SUSPECTED=true
STOP_PROBLEM_SUSPECTED=true
HOLD_PERIOD_PROBLEM_SUSPECTED=true
REGIME_EXPLAINS_EXECUTION_PROBLEM=false
gave_back_profit_rate=0.55365079365079
exit_stop_count=5824
exit_target_count=4320
exit_hold_count=2456
```

C22 therefore tests exit capture directly as shadow measurement.

## Implemented source components

```text
Service:
app/Application/Watchlist/Services/WatchlistBacktestC22ExitCaptureShadowDiagnosticService.php

Command:
app/Console/Commands/Watchlist/RunBacktestC22ExitCaptureShadowDiagnoseCommand.php

Command signature:
watchlist:backtest-c22-exit-capture-shadow-diagnose

Tests:
tests/Unit/Watchlist/WatchlistBacktestC22ExitCaptureShadowDiagnosticServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC22StaticGuardTest.php
```

The command is registered in `app/Console/Kernel.php` and supports:

```text
--catalog-code=
--from=
--to=
--param-ids=
--shadow-profile-codes=
--profile-codes=
--profiles=
--progress
--output=
--overwrite
--max-params=
--max-shadow-profiles=
--max-profiles=
--max-picks=
```

## Shadow profiles

C22 implements shadow profiles as measurement profiles only:

```text
C22_S00_CANONICAL_BASELINE
C22_S01_EXIT_D1_CLOSE
C22_S02_EXIT_D2_CLOSE
C22_S03_EXIT_D3_CLOSE
C22_S04_EXIT_D4_CLOSE
C22_S05_EXIT_D5_CLOSE_ONLY
C22_S06_FIRST_PROFITABLE_CLOSE_EXIT
C22_S07_PROFIT_LOCK_AFTER_MFE_0_75PCT
C22_S08_PROFIT_LOCK_AFTER_MFE_1_00PCT
C22_S09_PROFIT_LOCK_AFTER_MFE_1_50PCT
C22_S10_BREAKEVEN_AFTER_MFE_1_00PCT
C22_S11_TRAILING_FROM_MFE_1_50PCT_GIVEBACK_0_75PCT
C22_S12_TARGET_CLOSE_1_00PCT
C22_S13_TARGET_CLOSE_1_50PCT
C22_S14_TARGET_CLOSE_2_00PCT
C22_S15_STOP_LOSS_1_50PCT_SHADOW
C22_S16_STOP_LOSS_2_00PCT_SHADOW
C22_S17_STOP_LOSS_2_50PCT_SHADOW
```

These profiles are not production paramsets. They do not select tickers, select trade dates, mutate catalog rows, or change the canonical production model.

## Selection and price-read contract

C22 must freeze recommendations before reading any future path price.

Allowed price usage:

```text
measurement after recommendation freeze
D+1 to D+5 OHLC path measurement
canonical stop/target/time comparison
shadow exit date/price/return measurement
MFE/MAE measurement
profit giveback measurement
```

Forbidden price usage:

```text
choosing ticker
choosing trade_date
ranking recommendation
creating catalog
promoting paramset
best-of-failed binding
OOS proof
production rule mutation
```

## Canonical model remains frozen

C22 preserves the canonical model as baseline:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

Shadow exits are computed only as diagnostic comparison rows. They do not replace canonical execution.

## Artifact contract

The C22 artifact contains:

```text
artifact_type=C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC
scope=IS_ONLY_EXIT_CAPTURE_SHADOW_DIAGNOSTIC
source_catalog
source_evidence
data_availability
shadow_profiles
canonical_rows
pick_shadow_rows
canonical_summary
shadow_profile_summary
per_shadow_profile_summary
hold_day_comparison_summary
profit_lock_summary
breakeven_summary
trailing_summary
target_distance_summary
stop_distance_summary
decision
safety_boundaries
```

Per shadow row includes canonical baseline fields, shadow profile code/family, shadow exit date/price/day/reason, shadow return, delta versus canonical, win flags, profit-capture flags, loss-reduction flags, and missing-path flags.

## C22 gate

C22 does not search for strategy success. It searches for a diagnostic signal that exit-capture behavior deserves C23 rule-candidate design.

`exit_capture_signal_found=true` requires at least one meaningful improvement condition:

```text
median_delta_vs_canonical >= 0.005
or p25_delta_vs_canonical >= 0.005
or gave_back_profit_reduction_vs_canonical >= 0.15
or improved_pick_rate >= 0.55 without severe p25 deterioration
or loss_reduction_rate >= 0.20
```

Decision output separates:

```text
exit_capture_signal_found
early_exit_suspected_better
profit_lock_suspected_better
breakeven_suspected_better
trailing_suspected_better
target_distance_problem_suspected
stop_distance_problem_suspected
hold_compression_suspected_better
catalog_allowed=false
oos_allowed=false
next_step
```

## Guard status

C22 preserves:

```text
IS_ONLY=true
OOS_NOT_RUN=true
production_ready=0
C22_CATALOG_CODE=NOT_CREATED
C22_CATALOG_IMPLEMENTATION_DEFERRED=true
NO_PROMOTION=true
NO_OOS=true
NO_TICKER_BLACKLIST=true
NO_MONTH_BLACKLIST=true
NO_SECTOR_WHITELIST=true
NO_BEST_OF_FAILED_BINDING=true
NO_C01_TO_C21_MUTATION=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
NO_C21_REOPEN=true
```

## Runtime validation requirement

Run the operator commands documented in `WS_C22_OPERATOR_VALIDATION_COMMANDS.md`. Until the operator provides output, keep:

```text
PHPUNIT_C22=OPERATOR_VALIDATION_REQUIRED
FULL_WATCHLIST_PHPUNIT=OPERATOR_VALIDATION_REQUIRED
C22_DIAGNOSTIC_RUNTIME_PASS=NOT_RUN
C22_EXIT_CAPTURE_SIGNAL_FOUND=NOT_RUN
production_ready=0
```
