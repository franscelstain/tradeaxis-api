# Point-in-Time Backtest Input Contract (STRATEGY LOCKED)

## Boundary

Trading-strategy backtests consume only a versioned snapshot/export of the market-data read model. Market Data Platform supplies facts, analytical products, indicators, data usability, readiness, and lineage; the backtest owns tradability thresholds, signal, ranking, execution, cost, and portfolio policy.

## Knowledge-time rule

Each decision timestamp declares a `knowledge_cutoff`. Inputs contain only observations and identity, calendar, status, event, factor, config, and formula revisions recorded/known by that cutoff and effective for the evaluated context.

Today's universe, symbol, sector, action verification, or current publication may not be backfilled into an earlier decision.

## Required input identity

Every row/export binds listing identity, requested/effective trade date, knowledge cutoff, as-known replay ID/publication-like artifact ID, read-model version, full config hash, factor/formula versions, and lineage. Availability timestamp is distinct from market trade date.

## Survivorship and revisions

Inactive/delisted securities remain present when they were in the temporal universe. Symbol changes/reuse use listing IDs. Late corrections/actions produce a distinct later-known dataset and do not rewrite the earlier-known dataset.

Compatibility `eligible` may be used only as the upstream `data_usable` state. Liquidity, status, and event facts remain independently inspectable; their strategy interpretation belongs to the backtest and is not a future-performance label or an instruction to trade.

## Price and execution boundary

Signal features use the declared coherent analytical product. Simulated execution must separately choose realistic executable prices/times from allowed facts; it may not trade on same-session information before its availability timestamp. Corporate-action cashflow/total-return treatment is explicit and cannot be inferred from provider adjusted close.

## Acceptance fixtures

At minimum prove inactive-now/active-then membership, symbol transition/reuse, late action verification, late config/calendar/status correction, unavailable same-day data, explicit stale fallback, and original-versus-corrected as-known datasets.
