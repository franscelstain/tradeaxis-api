# WS Entry/Exit Behavior C21 Design Note

C21 is an audit diagnostic for weekly swing execution behavior. It does not change the policy owner, production execution model, recommendation boundary, confirm boundary, or catalog eligibility.

## Context carried forward

C19 found that quality signal exists but the high-quality core is too small. C20 found that regime/trade-date gating improves some quality metrics but does not reach a valid continuation or catalog target.

C21 therefore inspects the execution path after recommendation freeze:

```text
signal close
next trading day open
D+1 to D+5 OHLC path
canonical stop/target/time exit
MFE/MAE
entry gap
profit give-back
never-profitable path
C20_G03 risk-off segmentation as context only
```

## Policy boundary

C21 must preserve the canonical model:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

Path price is future information relative to recommendation time. It is allowed only after ticker and trade_date are fixed, and only for measurement. It must not feed selection, ranking, gating, catalog creation, or promotion.

## C20 G03 treatment

`C20_G03_VOLATILITY_RISK_OFF_FILTER` may appear only as segmentation/explanation context in C21 output. It remains forbidden as a production gate or C21 catalog filter.

C21 final runtime evidence keeps:

```text
regime_explains_execution_problem=0
c20_g03_used_as_filter=false
```

Therefore C21 does not reopen C20 and does not promote C20_G03 as a date/regime gate.

## Final C21 operator evidence

C21 source and runtime validation passed as diagnostic evidence:

```text
PHPUNIT_C21=PASS: OK (6 tests, 173 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (397 tests, 9500 assertions)
C21_FOCUSED_RUNTIME_PASS=true
C21_FOCUSED_ARTIFACT_HASH=d80111aa07a0cb20ec7b4e087be0d4e4c3191fa8
C21_ALL_PARAM_RUNTIME_PASS=true
C21_ALL_PARAM_ARTIFACT_HASH=d6c6c72d51b40a0c852ce9bbc6a452c55920df13
```

Final diagnostic booleans:

```text
diagnostic_signal_found=1
entry_problem_suspected=0
exit_problem_suspected=1
stop_problem_suspected=1
hold_period_problem_suspected=1
regime_explains_execution_problem=0
```

## Final interpretation

C21 does not support entry gap as the main cause:

```text
all_param_avg_entry_gap_pct=0.00096323026370276
all_param_median_entry_gap_pct=0
all_param_gap_open_loss_rate=0.23555555555556
entry_problem_suspected=0
```

C21 supports exit-capture, stop behavior, and hold-period behavior as the next diagnostic target:

```text
all_param_gave_back_profit_rate=0.55365079365079
all_param_exit_stop_count=5824
all_param_exit_target_count=4320
all_param_exit_hold_count=2456
all_param_median_mfe_5d=0.014354066985646
all_param_median_mae_5d=-0.021739130434783
```

The operational meaning is narrow: many trades become profitable after entry, but canonical exit behavior often fails to preserve that profit. This is a diagnostic finding, not a permission to alter production execution.

## Allowed C21 output

C21 may produce diagnostic booleans:

```text
diagnostic_signal_found
entry_problem_suspected
exit_problem_suspected
stop_problem_suspected
hold_period_problem_suspected
regime_explains_execution_problem
```

These booleans can only guide a next diagnostic design. They cannot authorize OOS, production readiness, catalog creation, or mutation of C01-C20 artifacts.

## Required lock flags

```text
C21_CATALOG_IMPLEMENTATION_DEFERRED=true
C21_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
NO_C01_TO_C20_MUTATION=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
```

## Next diagnostic direction

C21 is complete. The next step is:

```text
C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC
```

C22 must remain IS-only and may only compare hypothetical exit-capture rules over fixed recommendations. It must not use path/future price to select ticker, select trade_date, create catalog, promote strategy, or mutate the canonical model.
