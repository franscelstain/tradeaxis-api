# Downstream Consumer Read Model Contract (STRATEGY LOCKED)

## Purpose

The platform exposes one stable, publication-aware market-data read product. A Weekly Swing watchlist is the initial consumer profile, but this contract owns data shape, semantics, lineage, and readiness only. Consumers do not reconstruct market-data meaning by joining internal current tables, choosing adjustment fields, recomputing indicators, or guessing whether a date is ready.

Market-data conformance ends when this read product is semantically correct and atomically readable. Watchlist implementation, ranking behavior, strategy metrics, and profitability are outside this contract.

## Product grain and identity

The minimum row grain is one `listing_id × effective_trade_date × publication_id`. Every response also carries:

- requested trade date and effective trade date;
- immutable publication/dataset ID, version, seal hash, and activation/supersession state;
- point-in-time issuer, instrument, listing, exchange symbol, and provider mapping references;
- calendar/session and trading-status revision references;
- configuration snapshot ID/hash, build/formula/registry versions, and lineage link.

Symbol text is presentation and routing data, never the historical identity key.

## Minimum market-data fields for read-model V1

### Canonical market facts

- Regular-Market `RAW` open, high, low, close, and volume;
- nullable source-backed previous close, actual traded value, trade count/frequency, board/session/status fields when available;
- provider/source observation ID, source timestamp, acquired-at timestamp, schema/adapter version, and quality state;
- explicit expectation, delivery, missing, invalid, quarantine, and reason facts.

### Analytical price product

- coherent `STRUCTURAL_ADJUSTED` open, high, low, close, and adjusted volume where required by the verified action type;
- product/factor-set revision, verified event revisions, factor applicability interval, and contamination state/reasons;
- no provider `adj_close` or mixed adjusted/raw fallback.

### Indicators and daily context

- indicator-set/formula version and per-field values/null reasons;
- stable ATR seed/state lineage and warm-up status;
- actual traded-value metric and close-volume proxy exposed as distinct fields;
- nullable sector/benchmark revision and context;
- daily aggregate completeness/partialness state where aggregates are requested.

### Eligibility facts

- temporal universe membership and bar expectation/delivery;
- quality, liquidity, status, suspension/UMA, event-risk, and freshness facts;
- explicit decision plus complete reason-code sets.

Eligibility is an explainable upstream data-usability fact. A compatibility `eligible` field means `data_usable`; it is not watchlist selection, tradability approval, alpha, ranking, or portfolio policy, and it does not by itself prove that the requested dataset publication is readable.

### Readiness and freshness

- `readiness_state`: `READABLE`, `HELD`, `FAILED`, `BUILDING`, `SUPERSEDED`, or `NOT_AVAILABLE`;
- `freshness_state`: `FRESH`, `STALE`, `DEGRADED`, or `NOT_AVAILABLE`;
- latest expected, latest acquired, latest canonicalized, and latest readable trade dates;
- requested/effective-date relation and explicit fallback reason;
- operational activation context and evaluated-at timestamp.

## Read surface

A versioned view/query service/DTO is the only consumer-facing semantic surface. It atomically resolves the active immutable publication pointer and reads all row families from that same publication. Pagination and filters cannot change the publication binding.

The surface either returns a complete contract-shaped row, returns a reason-coded unavailable/held response, or returns an explicitly stale prior-effective-date result. It must not merge a prior bar with current indicators or identity/status/config.

## Forbidden shortcuts

Consumers and repositories must not:

- query `MAX(trade_date)` from raw/canonical/current tables as a readiness decision;
- read unsealed candidates or superseded publications unless an explicit audit endpoint is used;
- recompute indicators, adjustments, coverage, or eligibility;
- use mutable current projections or current master/status rows for historical output;
- treat `eligible = 1`, successful job status, row presence, or HTTP success as proof of publication readability;
- silently substitute a prior date and label it current/fresh; or
- join rows from different publications/config/factor/formula revisions.

## Versioning and compatibility

Additive optional fields may be introduced within a read-model version. Grain, field meaning, unit, basis, requiredness, null semantics, or readiness behavior changes require a new version and compatibility plan.

Until an implemented read surface and executed contract tests prove atomic publication binding and every minimum field/state above, this strategy is not production-relocked.

## Capability boundary (LOCKED)

**What the read model proves.** That a versioned, stable field set is delivered with declared units, basis, nullability, and identity, so a consumer can bind to a contract rather than to a table shape.

**What it cannot prove.**

- **That a present field is a meaningful one.** Field presence is a schema property. A liquidity proxy, an indicator over a contaminated window, and a value from a shortened session all arrive as ordinary populated fields.
- **That the field set is sufficient for the consumer's decision.** The model delivers what market-data owns. Whether that is enough to decide anything is a downstream question this contract deliberately does not answer.
- **That version stability implies semantic stability.** A field whose upstream meaning shifted while its name and type held is a version problem this contract cannot detect on its own; that is why upstream changes are output-affecting.

Consequently a conforming read model may be cited as evidence that **the delivered shape matches its declared version**, never as evidence that **the values describe the market accurately**.
