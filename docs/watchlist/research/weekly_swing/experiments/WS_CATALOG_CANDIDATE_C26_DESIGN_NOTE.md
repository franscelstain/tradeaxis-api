# WS Catalog Candidate C26 Design Note

C26 is an IS-only catalog-candidate diagnostic note. It is a design bridge from C25 to a possible C27 catalog-candidate implementation, not a production rule and not an OOS permission.

## Behavioral Boundary

C26 preserves the canonical weekly-swing execution model:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

C26 must not mutate PLAN, RECOMMENDATION, CONFIRM, C01-C25 catalog/source evidence, or any production execution behavior.

Forbidden in C26:

```text
production catalog creation
OOS execution
promotion
ticker blacklist
month blacklist
sector whitelist
best-of-failed binding
same-day close-signal exit
using path/future price to select ticker or trade_date
```

## Candidate Under Test

Primary candidate:

```text
C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT
```

G21 behavior under diagnostic interpretation:

```text
base R09 first-profit close signal, exit next open
preplanned intraday target 1.00%
no-signal fallback D3 damage-control exit
threshold fixed before path evaluation
no same-day close-signal exit
no lookahead selection
```

Comparators:

```text
C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT
C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
C23_R15
C23_R16
C23_R09
C22_S06
canonical
```

G13 remains defensive because p25 and win-rate are strong while average remains negative. G16 remains a next-open-delay component/comparator because it improves the delay bucket but is not the primary balanced candidate. R15/R16 remain downside comparators.

## Readiness Gates

G21 primary candidate readiness requires:

```text
G21 avg_ret_net >= R09 avg_ret_net
G21 median_ret_net - R09 median_ret_net >= 0.005
G21 p25_ret_net - R09 p25_ret_net >= 0.010
G21 win_rate - R09 win_rate >= 0.10
lookahead_violation_count=0
ambiguous_intraday_sequence_count=0
improvement appears in more than one param
month stability is not one-month-only
bucket stability confirms no-signal fallback and next-open-delay improvements
```

C26 all-param result satisfies those readiness gates but also sets:

```text
RAW_OHLC_VALIDATION_REQUIRED=true
DERIVED_MFE_MAE_DEPENDENCY_DETECTED=true
```

That means C27 may proceed only as an IS-only catalog-candidate implementation with raw OHLC/high-low validation first. C26 itself still does not create a catalog.

## Raw OHLC Requirement

C25/C26 artifacts prove derived MFE/MAE availability, not raw D1-D5 open/high/low/close execution validation. A later implementation must validate preplanned intraday target behavior against raw OHLC/high-low bars before treating the candidate as catalog-candidate code.

If target and stop are both touched in one daily candle and intraday sequence is unknown, conservative stop-first fill or explicit ambiguous-sequence accounting remains required.

## Final C26 Decision

```text
C26_DECISION_STATUS=C26_RAW_OHLC_VALIDATION_REQUIRED
G21_PRIMARY_CANDIDATE_READY=true
G13_DEFENSIVE_CANDIDATE_READY=true
G16_NEXT_OPEN_DELAY_COMPONENT_READY=true
C27_CATALOG_CANDIDATE_IMPLEMENTATION_RECOMMENDED=true
C27_REQUIRES_RAW_OHLC_VALIDATION_FIRST=true
CATALOG_ALLOWED=false
OOS_ALLOWED=false
production_ready=0
```
