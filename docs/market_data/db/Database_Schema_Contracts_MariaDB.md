# Database Schema Contract — MariaDB (STRATEGY LOCKED)

## Authority and rollout state

Domain owner contracts define meaning. The deployable database shape is the legacy base in `Database_Schema_MariaDB.sql` plus ordered forward migrations. Migration `2026_08_02_000001_add_market_data_strategy_v2_foundation.php` introduces the strategy-corrected foundation as nullable additive fields/tables; null rollout bindings do not satisfy a publication gate.

Production relock additionally requires data backfill, repository adoption, non-null/check/FK enforcement where appropriate, SQLite mirror parity, and executed migration/rollback/integration evidence.

## Required model families

### Immutable acquisition evidence

`md_source_observations` stores one immutable envelope per request/file/response outcome, including stable identity, run/attempt/date, provider symbol and temporal mapping reference, sanitized request identity, timestamps, status/content type, schema fingerprint, adapter version, payload hash/ref/bounded body, outcome/reason, and supersession lineage.

Refetch creates a new row. Secrets are forbidden. Canonical/artifact rows link to accepted observations; rejected/stale/schema-invalid outcomes remain evidence.

### Full configuration snapshots

`md_config_snapshots` stores deterministic typed serialized content, schema/serialization versions, effective and recorded times, registry/build/environment/resolver identity, and `SHA-256` hash. Runs, publication lineage, artifacts, and seals bind the same non-null snapshot.

### Bitemporal identity and expectation

Stable issuer, instrument, and listing IDs are separate from exchange symbols and provider mappings. Symbol/mapping rows have effective intervals and recorded/known time. Calendar/session revisions and trading-status revisions retain the same two-time distinction.

Historical membership and bar expectation are never reconstructed from current `tickers.is_active`, current symbol, provider absence, or dormancy. Overlapping active revisions for the same scope are rejected by validation and ultimately constraints.

### Canonical bars and observations

Publication-bound `eod_bars_history` is authoritative; `eod_bars` may remain a replaceable current projection. Bar rows support listing/observation/config binding, Regular-Market `RAW` OHLCV, optional source-backed previous close, actual traded value, trade count, board/session/status and timestamps, canonicalization version, quality state, and product label.

Provider `adj_close` may be retained only as source lineage/legacy compatibility; it is not canonical RAW close or the structural-adjusted product.

### Corporate actions and factors

Immutable event revisions store lifecycle, verification state, ex/cum/record/payment dates, terms, source observation, and known time. Immutable factor sets/factors link only to verified event revisions and define price/volume transformation intervals.

Price-scale breaks are diagnostic candidates only. Schema must not provide or honor fields/commands that record in-place repairs of canonical or history rows. Legacy corporate-action tables are transitional projections, not factor authority.

### Exchange market structure

`md_exchange_market_structure_revisions` stores append-only, effective-dated and known-time rule
revisions for the governed IDX Regular-Market scope. Each row binds rule type, explicit board and
instrument coverage, authority verification, immutable source observation, human source reference,
content hash, and supersession lineage. `md_exchange_price_band_tiers` stores separate upper/lower
limits for each reference-price tier; `md_exchange_tick_size_tiers` stores the effective price
fraction and maximum step tiers. The minimum Regular-Market price is a sourced scalar on its own
`MINIMUM_PRICE` revision.

For external Stage 7 documents, the linked capture/accepted observation pair must preserve the
verified response status, response content type, exact payload hash/ref/byte length, schema
fingerprint, and bounded capture sample. Manifest metadata is not a substitute for response
identity. A correction is append-only: a new revision and observation pair supersede the old rows.

Recording these tables does not authorize resolution or application. A consumer must resolve one
non-ambiguous verified revision for its trade date and board scope, and must fail closed for an
unknown/excluded board. No current rule may be projected backward, and no unsourced configuration
constant becomes exchange-verified merely because it has the same numeric value.

### One-time current-corpus reconstruction

Stage 8 uses explicit campaign and target tables to freeze every pre-campaign current publication,
its owning run/version, sealed batch hashes, and independent artifact snapshot hashes before any
replacement is attempted. A target may point to a replacement only after the normal correction,
publication, seal, readability, and current-pointer lifecycle succeeds. Resume appends a new
correction after a failed attempt; it never reopens or overwrites the failed evidence.

Source-scale assessments, per-event factor decisions, and per-listing market-structure bindings are
immutable publication governance facts. Their deterministic set hashes must agree between the
publication and publication-lineage binding. Legacy nullable rows are not backfilled in place.

Campaign failure is fail-safe: the target/campaign becomes `FAILED`/`BLOCKED`, the correction and
run are terminal, an unsealed candidate may remain as immutable failure evidence, and the prior
current pointer remains authoritative. Corpus reconstruction must not trigger ordinary downstream
impact fan-out for each date because the bounded campaign itself owns chronological rebuilding of
the entire frozen range.

`md_corpus_admission_decisions` is the separate immutable authority for a measured conformant
suffix. It preserves `intentional_dataset_start` while recording `admitted_from` and
`measured_through`, threshold/source scope, exact status observations, measurement campaign and
algorithm, canonical input/status hashes, complete measurement JSON, lifecycle/reason, and
supersession. The active decision ID must agree on the Stage 8 campaign, owning run, sealed
publication, and publication-lineage binding. Verified full-session exclusions additionally bind
the exact trading-status revision and source observation on eligibility rows.

The active admission is a read boundary, not a history rewrite: pre-admission publications remain
stored and immutable but are not normal-readable and cannot serve analytical warm-up. A measured
blocked campaign may become `SUPERSEDED` only through the explicit admission identity; its failed
attempts and candidates remain evidence. No current status or current tier may be projected
backward to manufacture an earlier admitted date.

### Indicators and eligibility

Indicator snapshots bind listing, publication, config, factor set, coherent price product, formula/registry version, recursive ATR state, explicit actual/proxy liquidity fields, null reasons, and context revisions.

Data-usability snapshots separately store universe membership, bar expectation/delivery, canonical quality, liquidity, temporal status, event risk, upstream data-usability decision, and all reasons. Existing eligibility naming is compatibility only; it cannot encode tradability or watchlist policy. A primary `reason_code` is compatibility only.

### Publications and read state

Immutable publications retain distinct versions, full manifest/config/observation/revision/factor/formula/read-model binding, content hashes, seal, readiness, supersession, and pointer history. The active pointer is the only normal date-current authority; snapshots remain immutable. Pointer switch is atomic and fenced.

Latest expected/acquired/canonicalized/readable dates and freshness state are stored/evidenced independently. Successful run status, a current projection row, or eligibility does not create readability.

### Replay proof storage

Replay storage binds replay mode, fixture/manifest hash, knowledge cutoff when applicable, exact publication identity for publication replay, observation/config/temporal/factor/product/formula/read-model identities, expected/actual readiness/freshness/reasons/hashes, and mismatch details. The target column shape is specified in `../backtest/Replay_Results_Schema_MariaDB.sql`; legacy `config_identity` alone is transitional and insufficient.

## Required integrity

- stable primary/unique temporal revision keys and no ambiguous overlaps;
- positive OHLC and `high >= max(open, close)`, `low <= min(open, close)`, non-negative volume where database constraints are portable, plus service validation;
- immutable observation, revision, snapshot, manifest, seal, and history surfaces enforced by privileges/repositories and mutation guards;
- non-null observation/config/product/factor/formula/publication lineage before seal;
- all publication artifact rows share the same publication/config context;
- one active pointer per market/product/date scope;
- reason/annotation fields participate in artifact hashes;
- unknown expectation remains in coverage denominator;
- no cascade delete of published evidence.

Logical references left nullable/no-FK during rollout require an explicit enforcement migration before production relock. Application guards are transitional defense, not a permanent substitute for enforceable integrity where MariaDB supports it.

## MariaDB/SQLite compatibility

SQLite may mirror enum/JSON/unsigned/timestamp types as string/text/integer while preserving column meaning, nullability, uniqueness, and query behavior. Compatibility surrogate IDs are test-only and cannot become domain identity. Every repository-used target column/table must exist in both paths.

## Migration acceptance

Executed proof must cover clean MariaDB install, upgrade from the latest supported deployed schema, rollback rehearsal where supported, SQLite test bootstrap, schema-diff assertions, existing-data backfill validation, duplicate/overlap/invalid-row negatives, repository round trips, publication/read integration, and protection against direct history repair.
