# WS C22 Exit Capture Shadow Diagnostic

C22 is an IS-only exit-capture shadow diagnostic after C21. It is not a catalog, not OOS, not production tuning, and not a mutation of the canonical execution model.

C22 uses fixed recommendations from the same C19/C17 selection path used by C21, then reads D+1 through D+5 OHLC only for measurement. The goal is to test whether profit that appears inside the path can be captured better by shadow exit behavior without changing ticker selection, trade-date selection, catalog rows, or production rules.

## Final status

```text
C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
PHPUNIT_C22=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C22_FOCUSED_RUNTIME_PASS=true
C22_ALL_PARAM_RUNTIME_PASS=true
C22_DIAGNOSTIC_RUNTIME_PASS=true
C22_EXIT_CAPTURE_SIGNAL_FOUND=true
C22_FIRST_PROFIT_CAPTURE_DIRECTION_FOUND=true
C22_BREAKEVEN_STANDALONE_REJECTED=true
C22_STOP_DISTANCE_STANDALONE_REJECTED=true
C22_EARLY_EXIT_STANDALONE_WEAK=true
C22_PROFIT_LOCK_STANDALONE_REJECTED=true
C22_TRAILING_STANDALONE_REJECTED=true
C22_TARGET_DISTANCE_STANDALONE_REJECTED=true
C22_CATALOG_IMPLEMENTATION_DEFERRED=true
C22_CATALOG_CODE=NOT_CREATED
C21_EXECUTION_SIGNAL_FOUND_PRESERVED=true
C20_DATE_GATE_NOT_ENOUGH_PRESERVED=true
C19_CATALOG_CANDIDATE_FAILED_PRESERVED=true
C01_TO_C21_IMMUTABLE=true
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C23_FIRST_PROFIT_CAPTURE_RULE_CANDIDATE_DIAGNOSTIC
```

C22 is runtime validated as a diagnostic. It found an exit-capture signal, but the signal is not a catalog candidate. The strongest shadow behavior is `C22_S06_FIRST_PROFITABLE_CLOSE_EXIT`; it is useful as a measurement target, not as a direct production rule because it depends on path information after the recommendation is fixed.

## Final operator evidence

PHPUnit evidence:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC22"
OK (6 tests, 302 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (403 tests, 9802 assertions)
```

Focused runtime evidence:

```text
C22_FOCUSED_RUNTIME_PASS=true
C22_FOCUSED_ARTIFACT_PATH=storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-focused.json
C22_FOCUSED_ARTIFACT_HASH=2831edfb89c884ccb86072d047e5950dcae463dd
C22_FOCUSED_SHADOW_PROFILE_COUNT=8
C22_FOCUSED_PROFILE_SCOPE=EXPLICIT
C22_FOCUSED_EVALUATED_PICKS=394
C22_FOCUSED_PATH_MISSING=11
C22_FOCUSED_CANONICAL_AVG_RET_NET=-0.0025564143113513
C22_FOCUSED_CANONICAL_MEDIAN_RET_NET=-0.0005
C22_FOCUSED_CANONICAL_P25_RET_NET=-0.021287002050553
C22_FOCUSED_CANONICAL_WIN_RATE=0.42131979695431
C22_FOCUSED_CANONICAL_GAVE_BACK_PROFIT_RATE=0.52791878172589
C22_FOCUSED_BEST_BY_MEDIAN=C22_S00_CANONICAL_BASELINE
C22_FOCUSED_BEST_BY_P25=C22_S00_CANONICAL_BASELINE
C22_FOCUSED_BEST_BY_GIVEBACK_REDUCTION=C22_S06_FIRST_PROFITABLE_CLOSE_EXIT
C22_FOCUSED_BEST_MEDIAN_DELTA_VS_CANONICAL=0
C22_FOCUSED_BEST_P25_DELTA_VS_CANONICAL=0
C22_FOCUSED_BEST_GIVEBACK_REDUCTION_VS_CANONICAL=0.060913705583756
C22_FOCUSED_EXIT_CAPTURE_SIGNAL_FOUND=true
C22_FOCUSED_EARLY_EXIT_SUSPECTED_BETTER=false
C22_FOCUSED_PROFIT_LOCK_SUSPECTED_BETTER=false
C22_FOCUSED_BREAKEVEN_SUSPECTED_BETTER=true
C22_FOCUSED_TRAILING_SUSPECTED_BETTER=false
C22_FOCUSED_TARGET_DISTANCE_PROBLEM_SUSPECTED=false
C22_FOCUSED_STOP_DISTANCE_PROBLEM_SUSPECTED=false
C22_FOCUSED_HOLD_COMPRESSION_SUSPECTED_BETTER=false
C22_FOCUSED_OOS_EXECUTED=0
C22_FOCUSED_PRODUCTION_READY=0
```

All-param runtime evidence:

```text
C22_ALL_PARAM_RUNTIME_PASS=true
C22_ALL_PARAM_ARTIFACT_PATH=storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-all-param.json
C22_ALL_PARAM_ARTIFACT_HASH=4e939d091a03ed49bbf460c0424ff1a018f98e72
C22_ALL_PARAM_SHADOW_PROFILE_COUNT=18
C22_ALL_PARAM_PROFILE_SCOPE=EXPLICIT
C22_ALL_PARAM_EVALUATED_PICKS=1575
C22_ALL_PARAM_PATH_MISSING=45
C22_ALL_PARAM_CANONICAL_AVG_RET_NET=-0.0046903074630424
C22_ALL_PARAM_CANONICAL_MEDIAN_RET_NET=-0.0041104817284074
C22_ALL_PARAM_CANONICAL_P25_RET_NET=-0.023750212591414
C22_ALL_PARAM_CANONICAL_WIN_RATE=0.39238095238095
C22_ALL_PARAM_CANONICAL_GAVE_BACK_PROFIT_RATE=0.55365079365079
C22_ALL_PARAM_BEST_BY_AVG=C22_S06_FIRST_PROFITABLE_CLOSE_EXIT
C22_ALL_PARAM_BEST_BY_MEDIAN=C22_S01_EXIT_D1_CLOSE
C22_ALL_PARAM_BEST_BY_P25=C22_S00_CANONICAL_BASELINE
C22_ALL_PARAM_BEST_BY_WIN_RATE=C22_S06_FIRST_PROFITABLE_CLOSE_EXIT
C22_ALL_PARAM_BEST_BY_GIVEBACK_REDUCTION=C22_S06_FIRST_PROFITABLE_CLOSE_EXIT
C22_ALL_PARAM_BEST_MEDIAN_DELTA_VS_CANONICAL=0.0039359241611442
C22_ALL_PARAM_BEST_P25_DELTA_VS_CANONICAL=0
C22_ALL_PARAM_BEST_GIVEBACK_REDUCTION_VS_CANONICAL=0.062222222222222
C22_ALL_PARAM_EXIT_CAPTURE_SIGNAL_FOUND=true
C22_ALL_PARAM_EARLY_EXIT_SUSPECTED_BETTER=false
C22_ALL_PARAM_PROFIT_LOCK_SUSPECTED_BETTER=false
C22_ALL_PARAM_BREAKEVEN_SUSPECTED_BETTER=true
C22_ALL_PARAM_TRAILING_SUSPECTED_BETTER=false
C22_ALL_PARAM_TARGET_DISTANCE_PROBLEM_SUSPECTED=false
C22_ALL_PARAM_STOP_DISTANCE_PROBLEM_SUSPECTED=true
C22_ALL_PARAM_HOLD_COMPRESSION_SUSPECTED_BETTER=false
C22_ALL_PARAM_OOS_SERVICE_INVOKED=0
C22_ALL_PARAM_OOS_REPOSITORY_INVOKED=0
C22_ALL_PARAM_OOS_EXECUTED=0
C22_ALL_PARAM_PRODUCTION_READY=0
```

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

## All-param profile result summary

Canonical baseline remained weak:

```text
C22_S00_CANONICAL_BASELINE:
avg_ret_net=-0.469%
median_ret_net=-0.411%
p25_ret_net=-2.375%
win_rate=39.24%
gave_back_profit_rate=55.365079365079%
```

The strongest shadow measurement was `C22_S06_FIRST_PROFITABLE_CLOSE_EXIT`:

```text
avg_ret_net=-0.016%
median_ret_net=0.428%
p25_ret_net=-0.825%
win_rate=59.62%
```

This is the clearest C22 direction. It suggests that many trades do contain early profit that is not preserved by canonical hold/stop/target behavior. It cannot be copied directly into production because the shadow rule knows the first profitable close in the future path. C23 must convert this into a non-lookahead rule, such as exit next open after a realized profitable close signal.

`C22_S01_EXIT_D1_CLOSE` improved median shape but is not enough as a standalone rule:

```text
avg_ret_net=-0.059%
median_ret_net=-0.050%
p25_ret_net=-0.834%
win_rate=35.94%
median_delta_vs_canonical=0.39359241611442%
```

Breakeven did not work as a standalone production candidate even though the decision flag detected a loss-control signal:

```text
C22_S10_BREAKEVEN_AFTER_MFE_1_00PCT:
avg_ret_net=-0.8693902820005997%
median_ret_net=-0.05020836471356127%
p25_ret_net=-1.1137309988461346%
win_rate=7.492063492063492%
gave_back_profit_rate=66.98412698412698%
gave_back_profit_reduction_vs_canonical=-11.619047619047618%
loss_reduction_rate=32.126984126984126%
```

Stop-distance variants did not work as standalone production candidates:

```text
C22_S15_STOP_LOSS_1_50PCT_SHADOW:
avg_ret_net=-0.3791313370075756%
median_ret_net=-1.6783567134268536%
p25_ret_net=-1.8677198420236966%
win_rate=20.761904761904763%
loss_reduction_rate=29.714285714285715%

C22_S16_STOP_LOSS_2_00PCT_SHADOW:
avg_ret_net=-0.3528372981977802%
median_ret_net=-2.090313547032055%
p25_ret_net=-2.2967754876886817%
win_rate=25.142857142857146%
loss_reduction_rate=21.26984126984127%

C22_S17_STOP_LOSS_2_50PCT_SHADOW:
avg_ret_net=-0.37957387592616166%
median_ret_net=-0.8831324698704806%
p25_ret_net=-2.7792014962842962%
win_rate=27.555555555555557%
loss_reduction_rate=17.77777777777778%
```

## Selection and price-read contract

C22 freezes recommendations before reading any future path price.

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

The C22 runtime artifact contains:

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

## Final C22 interpretation

C22 found an exit-capture signal, but the signal is mixed and must not be treated as a strategy success.

```text
C22_EXIT_CAPTURE_SIGNAL_FOUND=true
C22_FIRST_PROFIT_CAPTURE_DIRECTION_FOUND=true
C22_STRONGEST_SHADOW_DIRECTION=C22_S06_FIRST_PROFITABLE_CLOSE_EXIT
C22_EARLY_EXIT_STANDALONE_WEAK=true
C22_BREAKEVEN_STANDALONE_REJECTED=true
C22_STOP_DISTANCE_STANDALONE_REJECTED=true
C22_PROFIT_LOCK_STANDALONE_REJECTED=true
C22_TRAILING_STANDALONE_REJECTED=true
C22_TARGET_DISTANCE_STANDALONE_REJECTED=true
```

The correct next step is not catalog creation and not OOS. The correct next step is C23 rule-candidate design that tries to approximate the first-profit-capture shadow signal using only non-lookahead information.

C23 should benchmark candidate rules against both canonical baseline and `C22_S06_FIRST_PROFITABLE_CLOSE_EXIT`, with metrics such as:

```text
realizable_signal_day
realizable_exit_day
uses_same_day_close_signal
exit_next_open
lookahead_safe
delta_vs_canonical
delta_vs_c22_shadow_s06
profit_capture_gap_vs_shadow_s06
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
future_path_price_used_for_selection=false
shadow_exit_used_for_selection=false
shadow_ret_net_used_for_selection=false
mfe_mae_used_for_selection=false
```

## Final decision

```text
C22_DIAGNOSTIC_RUNTIME_PASS=true
C22_EXIT_CAPTURE_SIGNAL_FOUND=true
C22_CATALOG_IMPLEMENTATION_DEFERRED=true
C22_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C23_FIRST_PROFIT_CAPTURE_RULE_CANDIDATE_DIAGNOSTIC
```
