# Terminology and Scope

## Purpose

Define the locked strategic scope and terminology for the Market Data Platform (EOD), including its target market, initial consumer profile, time boundaries, operating phases, and distinct raw/adjusted/data-usability products.

The platform target is a decision-grade, point-in-time, reproducible, and auditable IDX Regular-Market EOD data product. A Weekly Swing watchlist is the initial consumer profile used to prioritize frequency and factual fields, while Yahoo Finance is the current bootstrap source and provider-neutral domain contracts remain the future boundary.

This module remains upstream-only. It guarantees the meaning and quality state of decision inputs; it does not promise profit or define watchlist scoring, alpha ranking, entry/exit, position sizing, portfolio action, or broker execution.

## Market, horizon, and session scope (LOCKED)

- **Target market:** IDX-listed equities.
- **Canonical market segment:** Regular Market. Cash and negotiated market observations must not be silently mixed into this product.
- **Data frequency:** End-of-Day (EOD), representing a completed Regular Market session for a trade date.
- **Calendar and timezone:** the IDX trading calendar with platform timezone `Asia/Jakarta`; session completion, board, and trading-status semantics must remain explicit.
- **Initial consumer profile:** Weekly Swing, with an expected holding period of several days to several weeks. This profile is a scope/prioritization input, not a market-data readiness gate.

Tick data, full order book/market depth, intraday or ultra-low-latency processing, execution routing, full cash/negotiated-market analytics, and multi-exchange coverage are outside the current scope.

The product dependency direction is:

`source observation -> canonical RAW EOD -> analytical price product -> indicators/market facts -> data-quality/data-usability facts -> sealed market-data read product`

Market-data owns this complete flow. A downstream watchlist may consume the final read product, but owns candidate selection, tradability thresholds, alpha/ranking, and trading policy. Its outcomes do not determine market-data readiness.

## Dataset and operational time boundaries (LOCKED)

### Intentional dataset start

`2023-01-02` is the intentional dataset start for the initial application baseline.

It means:

- absence before `2023-01-02` is not an active-scope missing-data defect
- pipeline, replay, indicators, and backtest may claim coverage only from this boundary
- indicators return deterministic `NULL` until their required warm-up history exists
- an instrument listed after the boundary starts warm-up from its own listed date
- pre-boundary historical expansion is an explicit possible future scope, not a current requirement or blocker

The dataset start is not the archived proof window. `2023-01-02` through `2025-10-31` identifies a past executed-evidence range only; it is not the dataset end, capability limit, retention boundary, or current-freshness claim.

### Development data frontier

The **development data frontier** is the latest trade date ingested at a given point while the application is being built. It is a moving evidence fact, not the dataset end, a platform capability limit, or an operational-freshness claim.

Before operational activation:

- a gap after the frontier is not a production incident
- `daily_enabled=false` may be a valid development-state choice
- the gap does not block contract, schema, historical-integrity, corporate-action, indicator, or replay corrections
- the frontier and its evidence as-of timestamp must still be disclosed transparently

### Operational activation

The **operational activation date** is the effective boundary from which a forward paper watchlist, user-facing watchlist, or other routine use requires operational freshness. Its normative marker is `OPERATIONAL_START_DATE` or an equivalent governed marker. Historical backfill, a proof run, or a recent development frontier does not set it implicitly.

Before activation the project must:

1. set the activation marker explicitly
2. catch up every required trading date from the development frontier through the activation boundary by controlled backfill
3. enable and prove daily import/promote scheduling
4. enable freshness alerts and stale-consumer protection
5. begin consecutive operational SLO measurement only at the activation boundary

## Scope of Market Data Platform (LOCKED)

Market Data Platform is responsible for producing upstream artifacts that are:

- canonical
- validated
- deterministic
- point-in-time
- reproducible
- auditable
- sealable
- safe for downstream consumption

Its target minimum outputs are:

- immutable source observations and provenance
- canonical Regular-Market EOD bars
- explicit analytical price products and adjustment lineage
- versioned EOD indicators
- separate coverage, quality, liquidity, event-risk, and data-usability facts
- run and configuration identity
- content hashes
- versioned seal/publication metadata

## Price-product terminology (LOCKED)

### Raw source observation

The immutable provider payload or provider fields, together with provenance, source timestamps, acquisition timestamps, and observation identity. A raw source observation is not automatically a canonical bar or a readable publication.

### `RAW` price product

Canonical, validated, Regular-Market EOD OHLCV on the market-observed scale without price adjustment. Uppercase `RAW` identifies a price product and must not be used as a synonym for the provider payload.

### `STRUCTURAL_ADJUSTED` price product

A coherent analytical series in which all OHLC fields are adjusted and volume is adjusted inversely when required by the semantics of a verified, versioned structural corporate action such as a split, reverse split, bonus, or rights event.

`STRUCTURAL_ADJUSTED` is the target default basis for the initial EOD technical-indicator profile. One indicator run must bind one explicit, versioned price basis. Provider `adj_close` must not be used as a per-row fallback or mixed with `close` across one vector.

### `TOTAL_RETURN` price product

A separate performance-evaluation product that includes distribution effects when sufficient data is available. It is not an alias for `STRUCTURAL_ADJUSTED` and is not implicitly produced from provider `adj_close`.

If a material factor is unresolved or unverified, the affected range must be quarantined or blocked by eligibility. A price anomaly alone is not permission to adjust or mutate history.

## Phase terminology (LOCKED)

### Import

Import is the upstream acquisition and bar-persistence phase.

Import includes:

- acquisition
- ticker-level processing
- source mapping
- deduplication
- validation
- canonical bars write
- invalid-row write
- bars coverage evidence
- telemetry

Import does **not** include:

- indicators
- eligibility
- hash
- seal
- finalize

### Promote

Promote is the phase that turns persisted import results into a consumer-readable candidate.

Promote includes:

- validation
- bars coverage gate
- indicators
- eligibility
- hash
- seal
- finalize

Promote is the only phase allowed to create requested-date readable success.

### Date-driven capability

The locked capability that the platform accepts explicit requested dates as domain input.

It means:

- one specific requested trade date can be imported explicitly
- one explicit trading-date range can be imported explicitly
- historical and recent dates are both first-class inputs
- provider transport defaults do not define the domain boundary

### Provider limitation abstraction

The rule that provider quirks such as `range=10d`, rate limits, per-ticker request fan-out, or transport-specific parameter shapes must be absorbed by the acquisition strategy rather than inherited as domain limits.

### Fatal failure

Failure that may stop the whole requested-date run immediately. Examples include invalid configuration, a globally broken endpoint contract or parser, and database/storage failure.

### Per-ticker failure

Failure that affects one ticker request/import unit only. Examples include rate limiting, timeout, empty result, and a malformed single payload.

Per-ticker failure must be recorded and tolerated during import. It is evaluated later through coverage/readiness, not treated as an automatic full-run stop.

## Coverage, quality, and eligibility terminology (LOCKED)

### Coverage

Whether a market observation expected for a point-in-time universe and trade date is available in the governed run/publication context. Coverage is not successful-request count and is not a statement of quality, liquidity, or eligibility.

Dormancy and zero-volume history must not hide provider failure from the denominator. Denominator exclusion requires point-in-time evidence that a bar was not expected, such as verified suspension or market status.

### Quality

Whether an available observation can be trusted under the validation, anomaly, provenance, and contamination rules. A delivered observation may exist for coverage purposes while still being quality-blocked.

### Liquidity

Trading facts and explicitly named measures or proxies, including unit, basis, window, and quality. Liquidity must not change whether a bar was expected, hide provider failure, or embed a downstream tradability threshold.

### Event risk

The point-in-time state of unresolved or relevant corporate actions and other governed events that may contaminate a series or block its use.

### Eligibility snapshot

One row per point-in-time universe instrument for trade date D that exposes coverage, quality, liquidity, trading-status, and event-risk facts plus a data-usability decision and explicit reason codes. A compatibility field named `eligible` has only this upstream data-usability meaning.

Eligibility means the upstream data passes declared integrity/readiness gates. It is not a buy/sell signal, alpha approval, ranking preference, liquidity/tradability approval, event-avoidance policy, or portfolio decision.

## Publication and date terminology (LOCKED)

### Requested trade date

The date originally requested for processing by a run.

### Effective trade date

The consumer-readable date resolved by readability rules after finalization.

### Canonical bars

Validated Regular-Market EOD OHLCV rows in a declared price-product, publication, and trade-date context. Canonicalization does not erase source provenance or make a provider payload authoritative by itself.

### Invalid bars

Source-derived rows rejected from canonical publication because they violate locked validation rules.

### Indicators

Deterministic, versioned metrics computed from one declared coherent price basis under locked formula, seed, warm-up, window, nullability, and rounding rules.

### Seal

The act and metadata state that freeze a coherent consumer-readable dataset publication.

### Publication

A versioned, sealed consumer-readable dataset state for one effective trade date. A current pointer may select one publication without erasing superseded publication lineage.

### Replay

Controlled historical re-execution used to verify point-in-time determinism and readiness behavior. Replay is not trading-strategy backtesting.

### Session snapshot

Optional non-streaming supplemental artifact aligned to the effective readable trade date and captured at a real wall-clock time.

## Locked interpretation rules

1. Import completion must never be described as readable publication.
2. Promote owns indicators, eligibility, hash, seal, and finalize.
3. Per-ticker failure must never be described as automatic full-run fatality unless it is truly a global failure.
4. Coverage must never mean successful HTTP request count.
5. Publication must never mean "latest raw row by timestamp"; it means a sealed readable version resolved through publication context.
6. Date-driven capability must never be reduced to provider default recent-window behavior.
7. Provider limitation abstraction must never be described as a domain limitation.
8. `2023-01-02` must never be described as an accidental source limit; the archived proof window must never be described as the dataset end.
9. A development data frontier must never be described as a capability limit or as operational freshness before activation.
10. Operational freshness and consecutive SLO claims must begin only from an explicit governed activation boundary.
11. Raw source observation, canonical `RAW`, `STRUCTURAL_ADJUSTED`, and `TOTAL_RETURN` must never be treated as interchangeable.
12. One indicator run must never mix `close`, provider `adj_close`, or different price bases across dates.
13. Coverage, quality, liquidity, event risk, and eligibility must remain separately explainable.
14. Eligibility must never be described as alpha approval, candidate ranking, tradability approval, or a trading signal.
15. Market-data readiness must never depend on watchlist implementation, ranking stability, strategy metrics, profitability, or user preference.

## Anti-domain-leak rule (LOCKED)

Any wording that implies:

- pick selection
- ranking preference
- strategy approval
- broker action
- portfolio action

is outside this module unless explicitly documented as downstream consumer behavior.
