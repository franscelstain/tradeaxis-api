# WS Exit Capture Shadow C22 Design Note

C22 is a diagnostic-only extension after C21. It measures hypothetical exit-capture behavior on fixed recommendations. It does not change Watchlist policy ownership, production execution, recommendation boundaries, confirm boundaries, or catalog eligibility.

## Context carried forward

C19 found a quality signal but no sample-qualified quality-positive catalog candidate. C20 found that regime/date gating is not enough. C21 found that entry gap is not the primary suspect and that exit behavior, stop behavior, and hold period are the next suspects.

C21 all-param evidence carried into C22:

```text
entry_problem_suspected=0
exit_problem_suspected=1
stop_problem_suspected=1
hold_period_problem_suspected=1
regime_explains_execution_problem=0
gave_back_profit_rate=0.55365079365079
exit_stop_count=5824
exit_target_count=4320
exit_hold_count=2456
```

## Policy boundary

C22 must preserve the canonical execution model:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

Shadow profiles are only measurement profiles. They are not production rules, not catalog rows, and not selector rules.

## Future price rule

D+1 to D+5 OHLC is future information relative to recommendation time. C22 may read it only after ticker and trade_date are fixed, and only for diagnostic measurement.

Forbidden use:

```text
selecting ticker
selecting trade_date
ranking candidates
creating catalog
promoting paramset
changing PLAN/RECOMMENDATION/CONFIRM boundary
running OOS
setting production_ready
```

## Allowed C22 output

C22 may output diagnostic booleans:

```text
exit_capture_signal_found
early_exit_suspected_better
profit_lock_suspected_better
breakeven_suspected_better
trailing_suspected_better
target_distance_problem_suspected
stop_distance_problem_suspected
hold_compression_suspected_better
```

These booleans can only guide C23 rule-candidate design. They cannot authorize production change.

## Required lock flags

```text
C22_CATALOG_IMPLEMENTATION_DEFERRED=true
C22_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
NO_C01_TO_C21_MUTATION=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
NO_C21_REOPEN=true
```

## Next direction

If C22 runtime validation finds a robust exit-capture signal, the next step is C23 rule-candidate design with non-lookahead rule specification. If not, stop this diagnostic branch.
