# D-WS-20260820-01 — EOD Temporal Action Intent and Late-Data Clarification

## Role

`DECISION`

## Decision

1. Weekly Swing remains EOD-only, weekly-swing-only, decision-support-only, and manual-execution oriented.
2. Final Top Picks carry an EOD action intent: a timely qualified Top Pick may be labeled `ENTRY_CANDIDATE_NEXT_TRADING_SESSION`; this is advice to consider entry according to PLAN, not an order or guaranteed fill.
3. Canonical timing uses `requested_trade_date`, `effective_trade_date`, `recommendation_generated_at`, `intended_entry_session`, and `canonical_entry_cutoff`.
4. `NEXT_TRADING_SESSION` comes from authoritative Market Data calendar/session facts. `D+1` is only legacy shorthand and never means calendar-day arithmetic.
5. New PLAN requires authoritative same-date `READABLE + FRESH` Market Data. If not ready, current state is `MARKET_DATA_UNAVAILABLE_RETRYABLE`; prior-date data may only be previous/stale context.
6. If exact EOD becomes ready later but before the intended-entry cutoff, the same requested EOD may be retried and may still produce current action intent. If recommendation finishes after cutoff, the result is analysis/audit only with `ACTION_WINDOW_EXPIRED`.
7. Expired recommendation is not automatically carried to the following session; a new opportunity requires the next governed EOD recommendation.
8. Optional CONFIRM only evaluates current actionability of an already valid Top Pick whose action window is open. CONFIRM cannot replace missing core EOD recommendation or create a new ticker.
9. Canonical baseline has no Tuesday-buy, Thursday/Friday-sell, or other weekday preference. Weekday/calendar anomaly may only enter as a preregistered challenger with full proof.
10. Historical proof uses the governed next trading session and never reuses an already-passed open after late recommendation. When historical publication timing is unavailable, timeliness is not invented and must be proven in forward shadow/live-equivalent evidence.
11. Forward shadow/production proof must measure same-date readiness, timely recommendation rate, late/expired rate, and stale-context misuse; canonical operational target is at least 95% timely recommendation rate and at most 5% late-action-window expiry on evaluable requested runs.
12. This clarification does not add realtime, orderbook, broker, or Market Data acquisition work to Watchlist.

## Authority Impact

Eleven of fourteen canonical strategy owners are strengthened. Candidate eligibility, candidate classification, and Top-Picks qualification/ranking logic remain unchanged. The complete fourteen-owner pre-change strategy is preserved as `H0413..H0426`.

## Verification Impact

All new current rules are added to the strategy traceability matrix with no inherited PASS. Current mandatory coverage remains zero until WS-Bxx implementation/proof evidence is issued.

## Related Records

- Finding: `../../development/findings/F-WS-20260820-01_EOD_TEMPORAL_ACTION_INTENT_AND_LATE_DATA_AMBIGUITY.md`
- Evidence: `../evidence/E-WS-20260820-01_EOD_TEMPORAL_ACTION_INTENT_VALIDATION.json`
- Change log: `../../authority/governance/DOCUMENT_CHANGE_LOG.md`
