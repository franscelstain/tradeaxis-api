# WS No-Signal Fallback Delay C25 Design Note

C25 is the diagnostic follow-up to C24. It answers whether the remaining C22 shadow gap can be reduced by realistic, non-lookahead exit behavior without changing selection, trade date, catalog, OOS, or the canonical execution model.

## Why C25 exists

C24 proved that C23 R09 nearly closes average return versus C22 S06, but the gap is still material in median, p25, and win-rate. The dominant remaining gap is no-rule-profit-signal fallback. The secondary gap is next-open delay after a close signal.

C25 measures four paths:

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

## Final diagnostic evidence

C25 final operator evidence:

```text
PHPUNIT_C25=PASS: OK (6 tests, 90 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (419 tests, 10446 assertions)
C25_FOCUSED_RUNTIME_PASS=true: artifact_hash=7bd6221bdd7993d9897a4d9bfaf23db22800f263
C25_ALL_PARAM_RUNTIME_PASS=true: artifact_hash=d464c5bcce398c5405b069ef277d696a10598288
C25_ALL_PARAM_EVALUATED_PICKS=1575
C25_ALL_PARAM_PATH_MISSING=45
C25_ALL_PARAM_PROFILE_COUNT=22
C25_LOOKAHEAD_VIOLATION_COUNT=0
```

C25 final decision:

```text
no_signal_fallback_fix_found=true
next_open_delay_fix_found=true
distribution_balance_candidate_found=true
intraday_preplanned_order_candidate_found=true
exit_rule_path_still_viable=true
selection_quality_revisit_needed=false
c26_catalog_candidate_diagnostic_recommended=true
catalog_allowed=false
oos_allowed=false
production_ready=0
```

## Candidate interpretation

C25 final candidate split:

```text
PRIMARY_BALANCED_CANDIDATE=C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT
DEFENSIVE_COMPARATOR=C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT
NEXT_OPEN_DELAY_COMPARATOR=C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
DOWNSIDE_COMPARATORS=C23_R15,C23_R16
```

Reasoning:

```text
G21 is the strongest balanced profile: avg=+0.0045%, median=+0.9487%, p25=-0.4499%, win_rate=63.17%, lookahead=0, ambiguous=0.
G13 is the strongest defensive profile: median=+0.4493%, p25=-0.0500%, win_rate=73.21%, but avg=-0.2257%.
G16 is the strongest next-open-delay comparator, but p25=-1.7163% remains too weak for primary use.
R15/R16 improve downside versus R09 but lose too much average/win-rate to become the primary candidate.
```

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

Even though C25 recommends C26, that recommendation is only for a later catalog-candidate diagnostic. It is not a catalog, not OOS approval, and not production approval.

## C26 handoff

C26 must be IS-only and must test whether G21 can be lifted from diagnostic profile to catalog-candidate rule. C26 must compare G21 against G13, G16, R09, R15, and R16; validate month/param/bucket stability; validate raw OHLC/order realism where possible; and keep all OOS/production/catalog promotion gates locked until a later explicit proof stage.
