# WS Signal Quality Q01 Preregistration Lock

Q01 is a separate IS-only strategy-research scope after the sealed P01
failed/not-ready closure. It does not reopen C171, R02, S01, or P01.

```text
SCOPE=WS_SIGNAL_QUALITY_Q01
SOURCE_EVAL_ID=216
SOURCE_PARAM_SET_ID=25
SOURCE_PARAMS_HASH=2fb258a0e5c77ff9ee0347a9656e8ff77f3ae53c
CANONICAL_IS_FROM=2023-01-02
CANONICAL_IS_TO=2025-05-21
HYPOTHESIS_COUNT=2
MAX_CANDIDATES=3
MAX_REMEDIATION_ROUNDS=1
```

The hypotheses and their exact decision-time candidates were locked before
the Q01 diagnostic:

1. `Q01_H1_STRONG_LIQUIDITY_QUALITY`
   - `Q01_C1_DV20_STRONG_5B`
   - exact signal-date `dv20_idr >= 5,000,000,000`
   - threshold source: canonical `dv20_strong_idr`
2. `Q01_H2_VOLUME_PARTICIPATION_QUALITY`
   - `Q01_C2_VOL_RATIO_MODERATE_1P5`
   - exact signal-date `vol_ratio >= 1.5`
   - `Q01_C3_VOL_RATIO_STRONG_2P5`
   - exact signal-date `vol_ratio >= 2.5`
   - threshold sources: canonical diagnostic bucket and `strong_vol_ratio`

No candidate may be persisted unless the read-only diagnostic passes the
unchanged trade-count, average, median, P25, monthly-win-rate,
monthly-average, and source-improvement authorization gates.

```text
NEW_THRESHOLD_AFTER_DIAGNOSTIC_FORBIDDEN=1
FUTURE_RETURN_AS_RUNTIME_INPUT_FORBIDDEN=1
TICKER_BLACKLIST_FORBIDDEN=1
MONTH_BLACKLIST_FORBIDDEN=1
OOS_READ_BEFORE_ALL_CANONICAL_IS_GATES_PASS_FORBIDDEN=1
CANONICAL_GATE_WEAKENING_FORBIDDEN=1
```
