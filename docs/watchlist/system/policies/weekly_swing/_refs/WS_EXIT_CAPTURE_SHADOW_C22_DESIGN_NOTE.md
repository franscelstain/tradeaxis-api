# WS Exit Capture Shadow C22 Design Note

C22 is a diagnostic-only extension after C21. It measures hypothetical exit-capture behavior on fixed recommendations. It does not change Watchlist policy ownership, production execution, recommendation boundaries, confirm boundaries, catalog eligibility, or the canonical execution model.

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

## Final C22 runtime result

C22 runtime validation passed as a diagnostic:

```text
PHPUNIT_C22=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C22_FOCUSED_RUNTIME_PASS=true
C22_ALL_PARAM_RUNTIME_PASS=true
C22_DIAGNOSTIC_RUNTIME_PASS=true
C22_EXIT_CAPTURE_SIGNAL_FOUND=true
C22_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

All-param artifact:

```text
artifact_path=storage/app/watchlist/backtest/c22-exit-capture-shadow-diagnostic-all-param.json
artifact_hash=4e939d091a03ed49bbf460c0424ff1a018f98e72
evaluated_picks_count=1575
path_missing_count=45
canonical_avg_ret_net=-0.0046903074630424
canonical_median_ret_net=-0.0041104817284074
canonical_p25_ret_net=-0.023750212591414
canonical_win_rate=0.39238095238095
canonical_gave_back_profit_rate=0.55365079365079
```

## Final C22 interpretation

C22 found an exit-capture signal, but not a production rule.

Strongest shadow direction:

```text
C22_S06_FIRST_PROFITABLE_CLOSE_EXIT:
avg_ret_net=-0.016%
median_ret_net=0.428%
p25_ret_net=-0.825%
win_rate=59.62%
```

Canonical baseline for comparison:

```text
C22_S00_CANONICAL_BASELINE:
avg_ret_net=-0.469%
median_ret_net=-0.411%
p25_ret_net=-2.375%
win_rate=39.24%
```

`C22_S06_FIRST_PROFITABLE_CLOSE_EXIT` is not a production rule because it is a shadow measurement of the first profitable close inside the future path. Its value is directional: it proves that profit capture is a promising design target.

D1 close improved downside shape but is weak as a standalone rule:

```text
C22_S01_EXIT_D1_CLOSE:
avg_ret_net=-0.059%
median_ret_net=-0.050%
p25_ret_net=-0.834%
win_rate=35.94%
```

Breakeven and stop-distance standalone variants are rejected:

```text
C22_BREAKEVEN_STANDALONE_REJECTED=true
C22_STOP_DISTANCE_STANDALONE_REJECTED=true
```

Reason: breakeven had a loss-control signal but damaged average return, win rate, and gave-back behavior. Stop-distance variants improved some loss-control/average components but damaged median, win rate, p25, or gave-back behavior enough to reject standalone use.

## Policy boundary

C22 preserves the canonical execution model:

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

## C23 design direction

C23 must not copy `C22_S06_FIRST_PROFITABLE_CLOSE_EXIT` directly. C23 must convert the shadow direction into a realizable non-lookahead rule candidate.

Candidate family examples for C23:

```text
exit next open after D1 close profit > 0
exit next open after D1 close profit > 0.50%
exit next open after D1 close profit > 1.00%
exit next open after D1 or D2 close profit > threshold
compress hold if no profit appears by D2/D3
combine first-profit capture with D3 damage control
```

C23 must benchmark against both canonical baseline and C22 shadow S06:

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

## Final next direction

```text
NEXT_STEP=C23_FIRST_PROFIT_CAPTURE_RULE_CANDIDATE_DIAGNOSTIC
```
