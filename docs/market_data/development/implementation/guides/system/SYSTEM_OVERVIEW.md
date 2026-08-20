# System Overview

## System purpose
Market-data platform ini bertugas menghasilkan fakta pasar EOD IDX Regular Market yang point-in-time, konsisten, dapat diaudit, dapat direvisi tanpa menulis ulang history, dan dapat dikonsumsi downstream melalui publication/readiness contract yang jelas.

## High-level responsibilities
- govern temporal issuer/instrument/listing/provider-symbol identity
- govern calendar/session/trading-status and temporal IDX-IC sector reference facts
- acquire immutable source observations with provenance and capability boundaries
- validate, canonicalize, and preserve canonical `RAW` EOD bars
- govern verified corporate-action revisions and coherent analytical price products
- compute actual/proxy market metrics and deterministic versioned indicators
- evaluate temporal coverage delivery separately from quality/liquidity/status/event/data-usability facts
- bind output-affecting configuration, lineage, manifests, seals, and publication identity
- publish atomic consumer-readable datasets
- support correction, exact/as-known replay, and reproducibility
- preserve operational evidence and auditability

## Main actors
- source/data providers and authoritative reference sources
- operators
- downstream consumers
- auditors / reviewers

## Main lifecycle
1. resolve configuration and temporal reference facts
2. acquire immutable source observations
3. validate and canonicalize `RAW`
4. resolve verified event/factor revisions and analytical price products
5. compute market metrics and deterministic indicators
6. evaluate coverage and explainable data-usability facts
7. seal/publish and atomically expose the versioned read product
8. correct/revise/replay without in-place historical rewrite
9. archive admissible execution evidence

## Non-responsibilities
Platform ini tidak bertanggung jawab atas:
- watchlist strategy ownership
- alpha model ownership
- recommendation/ranking engine ownership
- tradability preference or buy/sell policy
- execution routing ownership
- portfolio action ownership

## Authority note
Ini summary layer. Behavior rinci tetap dimiliki owner contracts dan urutan pembangunan tetap dimiliki `book/Market_Data_Strategy_Implementation_Blueprint_LOCKED.md`.
