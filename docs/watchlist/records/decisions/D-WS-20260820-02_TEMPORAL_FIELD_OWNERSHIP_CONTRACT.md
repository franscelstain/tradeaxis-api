# D-WS-20260820-02 — Temporal Field Ownership Contract

## Role

`DECISION`

## Decision

1. `requested_trade_date` is Watchlist-owned lifecycle intent; caller/scheduler may propose a date, but Watchlist resolves/records it and must validate session meaning from authoritative Market Data calendar facts.
2. `effective_trade_date`, `market_data_published_at`, and `market_data_revision_id` are Market-Data-owned producer provenance fields. Watchlist may copy them but may not fabricate, infer, normalize semantically, or override them.
3. `recommendation_generated_at` is Watchlist-owned and records when the exact recommendation result/version becomes available. It is not an alias for producer publication time.
4. `intended_entry_session` and `canonical_entry_cutoff` are Watchlist strategy-derived fields. Their calendar/session/open inputs come only from Market Data; their action semantics remain Weekly Swing-owned.
5. `action_window_status` is Watchlist-owned and is derived from recommendation timing versus the frozen cutoff. Market Data must not define Weekly Swing actionability state.
6. A physical copy of a field across domain boundaries does not transfer semantic ownership. Producer-owned values persisted by Watchlist must preserve exact value and producer publication/revision provenance.
7. Missing or invalid Market-Data-owned temporal provenance causes fail-closed/unavailable/dependency behavior. No wall-clock, prior-date, latest-row, local-calendar, file-time, or database-timestamp substitution is allowed.
8. Canonical timestamps are timezone-aware/comparable as unambiguous instants; session/date identities remain dates/sessions rather than timestamps.
9. An issued Watchlist recommendation remains bound to the exact Market Data publication/revision used to create it. A later producer correction/revision does not silently rewrite history; any re-evaluation must create explicit new Watchlist lineage.
10. This clarification preserves the EOD Weekly Swing identity and does not transfer Market Data work into Watchlist or Watchlist action semantics into Market Data.

## Authority Impact

Four canonical strategy owners are strengthened: Product Objective/Layers, Market Data Input Requirements, Runtime Flow, and Top Picks Recommendation. The complete fourteen-owner pre-change strategy is preserved as `H0436..H0449`.

## Verification Impact

All added rules are mandatory/conditional current rules and start `NOT_ASSESSED`. No historical PASS or prior implementation is inherited.

## Related Records

- Finding: `../../development/findings/F-WS-20260820-02_TEMPORAL_FIELD_OWNERSHIP_AMBIGUITY.md`
- Evidence: `../evidence/E-WS-20260820-02_TEMPORAL_FIELD_OWNERSHIP_VALIDATION.json`
- Change log: `../../authority/governance/DOCUMENT_CHANGE_LOG.md`
