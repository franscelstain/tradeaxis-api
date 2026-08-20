# F-WS-20260820-02 — Temporal Field Ownership Ambiguity

## Role

`FINDING`

## Observation

Current strategy already distinguishes `requested_trade_date`, `effective_trade_date`, `recommendation_generated_at`, next trading session, and late-data action windows. Review found that field meaning was documented but the semantic owner of each temporal field was not stated in one explicit canonical contract. This left room for implementation to treat producer dates/timestamps as Watchlist-generated values or to treat Watchlist action timestamps as Market Data outputs.

## Risk

Without explicit ownership, a consumer could fabricate `effective_trade_date`, infer producer readiness from wall-clock/database timestamps, conflate `market_data_published_at` with `recommendation_generated_at`, let Market Data define Weekly Swing cutoff/action state, silently overwrite an issued recommendation when producer revision changes, or lose causal provenance between data publication and recommendation generation.

## Required Resolution

Strategy must assign exactly one semantic owner to each canonical temporal field, define allowed strategy derivations, require immutable producer provenance copies, prohibit cross-domain override/fabrication, require timezone-aware timestamps, and define explicit lineage when Market Data revision triggers re-evaluation.

## Resolution

Resolved by `../../records/decisions/D-WS-20260820-02_TEMPORAL_FIELD_OWNERSHIP_CONTRACT.md`.

All added mandatory rules remain `NOT_ASSESSED` until current WS-Bxx implementation evidence proves them.
