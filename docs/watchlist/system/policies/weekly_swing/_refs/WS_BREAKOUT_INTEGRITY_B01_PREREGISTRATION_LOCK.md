# WS Breakout Integrity B01 Preregistration Lock

B01 is a separate IS-only strategy scope after Q01 and the bounded M01
diagnostic both produced no authorized candidate. It does not reopen C171,
R02, S01, P01, Q01, or M01.

```text
SCOPE=WS_BREAKOUT_INTEGRITY_B01
SOURCE_EVAL_ID=216
SOURCE_PARAM_SET_ID=25
SOURCE_PARAMS_HASH=2fb258a0e5c77ff9ee0347a9656e8ff77f3ae53c
HYPOTHESIS_COUNT=2
PREDECLARED_CANDIDATE_COUNT=3
MAX_REMEDIATION_ROUNDS=1
```

Locked candidates:

1. `B01_C1_CLOSE_TO_HH20_FLOOR_NEG5`
   - exact signal-date `close_to_hh20_pct >= -0.05`
   - threshold source: historical `FAR_BELOW <= -5%` worst bucket boundary
2. `B01_C2_CLOSE_TO_HH20_FLOOR_NEG2`
   - exact signal-date `close_to_hh20_pct >= -0.02`
   - threshold source: canonical `bo_near_below_pct`
3. `B01_C3_RANGE_POSITION_20_GTE_80`
   - exact signal-date `range_position_20_pct >= 0.80`
   - threshold source: canonical upper-range quality boundary

Only candidates passing the unchanged diagnostic authorization gates may be
persisted. The P01 C1 ROC20, IHSG regime, minimum price, scoring, grouping,
and sequential execution core remain unchanged.

```text
NEW_THRESHOLD_AFTER_DIAGNOSTIC_FORBIDDEN=1
FUTURE_RETURN_AS_RUNTIME_INPUT_FORBIDDEN=1
TICKER_BLACKLIST_FORBIDDEN=1
MONTH_BLACKLIST_FORBIDDEN=1
OOS_READ_BEFORE_ALL_CANONICAL_IS_GATES_PASS_FORBIDDEN=1
CANONICAL_GATE_WEAKENING_FORBIDDEN=1
```
