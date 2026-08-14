# System Boundary

## Upstream boundary
Upstream adalah provider bar/source feeds serta authoritative reference sources yang memasok observation atau fakta rujukan seperti listing lifecycle, calendar/session, trading status, corporate action, dan IDX-IC classification.

## Internal boundary
Di dalam boundary market-data, area utama adalah:
- temporal issuer/instrument/listing/symbol/provider mapping
- temporal calendar/session/trading-status and sector-reference resolution
- immutable source acquisition and provenance
- validation, anomaly classification, canonicalization, and invalid/missing separation
- corporate-action verification and analytical price-product construction
- actual/proxy market metrics and deterministic indicators
- temporal coverage expectation/delivery
- quality, liquidity, status, event-risk, indicator-validity, and data-usability facts
- configuration snapshot, persistence, publication, seal, and pointer integrity
- correction / reseal / exact-and-as-known replay
- observability and audit evidence

## Downstream boundary
Downstream consumer, termasuk watchlist PLAN/CONFIRM, hanya dipandang sebagai consumer. Market-data boleh menentukan readability, freshness, lineage, factual metrics, dan data-usability guarantee, tetapi tidak boleh mengambil alih strategy logic consumer.

## Out of scope
- security selection
- signal confirmation logic sebagai owner behavior consumer
- recommendation ranking / sector attractiveness ranking
- tradability threshold sebagai policy consumer
- order execution and broker routing
- capital allocation logic

## Boundary rule
Bila dokumen market-data mulai menentukan apa yang harus dibeli/dijual, ranking mana yang harus diprioritaskan, atau portfolio action apa yang harus diambil, dokumen tersebut sudah keluar domain.
