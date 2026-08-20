# F-WS-20260820-01 — EOD Temporal Action Intent and Late-Data Ambiguity

## Role

`FINDING`

## Observation

Current strategy already locks Weekly Swing as EOD-only, Market Data as fact owner, and conservative next-open historical modeling. Subsequent review found remaining ambiguity around four product behaviors: whether Top Picks also carry an EOD buy-consideration intent; whether `D+1` means calendar day or next governed trading session; how current runtime behaves when authoritative EOD becomes ready late; and whether weekday patterns such as Tuesday-buy/Friday-sell are canonical assumptions.

## Risk

Without explicit rules, implementation could publish stale prior-date recommendations as current, use an already-passed next-session open after late Market Data publication, silently carry expired recommendations forward, interpret `D+1` as calendar arithmetic, make CONFIRM a substitute for missing EOD recommendation, or add calendar-day trading rules that change Weekly Swing identity without proof.

## Required Resolution

Strategy must define explicit temporal identity, current-run availability states, Top-Pick EOD action intent, governed `NEXT_TRADING_SESSION`, action-window expiry, no silent carry-forward, CONFIRM preconditions, weekday-neutral baseline, and production proof for timely EOD availability.

## Resolution

Resolved by `../../records/decisions/D-WS-20260820-01_EOD_TEMPORAL_ACTION_INTENT_AND_LATE_DATA_CLARIFICATION.md`.

All added mandatory/conditional rules remain `NOT_ASSESSED`; optional CONFIRM rules remain `OPTIONAL_NOT_REQUESTED` until current WS-Bxx verification.
