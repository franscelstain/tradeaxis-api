# Market Daily Metrics Contract (LOCKED)

## Purpose

Define optional publication-bound aggregate IDX Regular-Market context without misleading actual/proxy labels or leaking watchlist policy.

## Dataset identity

`market_daily_metrics` is keyed by publication, trade date, market/benchmark identity, metric-set version, and configuration identity.

## Permitted factual metrics

- benchmark/index return from source-backed index series
- breadth counts/ratios from a declared point-in-time universe and eligibility/quality basis
- total actual traded value only from source-backed actual values
- separately named total close-volume proxy
- counts for expected, delivered, valid, advancing/declining/unchanged, suspended, event-risk, and unavailable states
- explicit market quality/freshness state

## Actual/proxy rule (LOCKED)

`total_traded_value_idr_actual` and `total_close_volume_proxy_idr` are distinct fields and never fallback into each other. Every aggregate declares membership universe, missing-value treatment, units, price basis, source/formula version, and completeness/quality state.

An incomplete constituent set cannot be silently scaled to a full-market claim. Partial aggregate state and coverage must remain visible.

## Temporal and publication rules

- Constituents resolve as-of trade date, not current membership.
- All rows come from one coherent publication/config context.
- Corrections create new metric rows/publication lineage.
- A stale prior metric is not current-date context.

## Domain boundary

Metrics are factual context. `risk_flag` or equivalent must be a source/quality state with explicit semantics, not a market-timing, buy/sell, ranking, or portfolio recommendation.

## Acceptance criterion

Every aggregate is reproducible and labelled so actual traded value, nominal proxy, coverage breadth, and strategy interpretation cannot be confused.
