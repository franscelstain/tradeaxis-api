# F-WS-20260819-07 — Market Data Fact Ownership Boundary Gap

## Role

`FINDING`

## Observation

Current strategy already requires authoritative Market Data, forbids several named recomputations, and locks the EOD-only product boundary. However, the prohibition was still partly enumeration-based and did not universally classify every future market fact, did not define one explicit upstream dependency-gap state, and did not fully prevent a downstream runtime/research/proof implementation from temporarily deriving a missing factual feature locally.

## Risk

Without a universal rule, future work could accidentally move factual work such as liquidity-stability metrics, breadth, sector leadership, regime inputs, session facts, corporate-action economics, or other derived market measurements into Watchlist merely because the required producer field did not yet exist. That would create a second market-data authority, break point-in-time/replay identity, and make current/OOS/shadow evidence less trustworthy.

## Required Resolution

Strategy must explicitly establish that all market facts are Market Data-owned; distinguish market facts from Weekly Swing strategy calculations; forbid local substitute facts in runtime/research/replay/proof; define deterministic `UPSTREAM_MARKET_DATA_DEPENDENCY_GAP` behavior; and require producer-contract resolution before a new market feature can enter active Watchlist strategy.

## Resolution

Resolved by `../../records/decisions/D-WS-20260819-07_MARKET_DATA_FACT_OWNERSHIP_AND_NO_LOCAL_SUBSTITUTION.md`.

All new requirements remain current `NOT_ASSESSED` (or `OPTIONAL_NOT_REQUESTED` for optional CONFIRM) until verified through the current `WS-Bxx` lifecycle.
