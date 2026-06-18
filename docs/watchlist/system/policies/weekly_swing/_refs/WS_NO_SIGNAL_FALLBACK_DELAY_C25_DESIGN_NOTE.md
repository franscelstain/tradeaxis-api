# WS No-Signal Fallback Delay C25 Design Note

C25 is the diagnostic follow-up to C24. It is designed to answer whether the remaining C22 shadow gap can be reduced by realistic, non-lookahead exit behavior without changing selection, trade date, catalog, OOS, or the canonical execution model.

## Why C25 exists

C24 proved that C23 R09 nearly closes average return versus C22 S06, but the gap is still material in median, p25, and win-rate. The dominant remaining gap is no-rule-profit-signal fallback. The secondary gap is next-open delay after a close signal.

C25 therefore measures four paths:

```text
no-signal fallback damage control
R15/R16 downside combo comparison
preplanned intraday target/protection order diagnostics
next-open-delay and no-signal rows-only slices
```

## Execution and lookahead rules

C25 keeps the production/canonical model frozen:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

Close-signal rules remain realistic: if a signal is known only after close Dn, the earliest possible rule exit is D(n+1) open. Same-day close exit is forbidden for rule candidates.

Preplanned intraday profiles are diagnostic only. Their thresholds are fixed before evaluating high/low-derived MFE/MAE. If target and stop are both touched in the same daily candle and the intraday sequence is unknown, C25 uses conservative stop-first logic or marks the row as ambiguous.

## Source data model

C25 uses the completed C23 and C24 artifacts as fixed evidence. It optionally uses C21 derived path fields for MFE/MAE-based intraday diagnostics. C23 does not include raw D1-D5 OHLC, so C25 records that limitation in `data_availability` and keeps raw OHLC fields null.

This is intentional. C25 must not invent raw OHLC data or pretend intraday sequence is known.

## Decision boundary

C25 may set diagnostic flags:

```text
no_signal_fallback_fix_found
next_open_delay_fix_found
distribution_balance_candidate_found
intraday_preplanned_order_candidate_found
exit_rule_path_still_viable
selection_quality_revisit_needed
c26_catalog_candidate_diagnostic_recommended
```

C25 may not set:

```text
catalog_allowed=true
oos_allowed=true
production_ready=1
C25_CATALOG_CODE=<anything other than NOT_CREATED>
```

Even if C25 recommends C26, that recommendation is only for a later catalog-candidate diagnostic. It is not a catalog, not OOS approval, and not production approval.
