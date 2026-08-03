# EOD Bars Contract — Canonical Regular-Market `RAW` OHLCV (LOCKED)

## Purpose

Define the canonical, publication-bound IDX Regular-Market EOD `RAW` bar artifact, its identity, fields, validation, nullability, and immutable revision semantics.

## Product meaning (LOCKED)

A canonical EOD bar represents market-observed Regular-Market OHLCV for one stable listing and completed trade date on the unadjusted `RAW` price scale.

It is not:

- a raw provider payload
- a cash/negotiated-market aggregate
- a zero/missing placeholder
- a coherent adjusted price product
- a provider `adj_close` series

## Logical and physical identity (LOCKED)

Within one publication revision, logical content identity is `(trade_date, listing_id)` or an explicitly documented stable equivalent.

Physical immutable identity must also bind:

- publication/revision identity
- selected source observation identity
- run and configuration identity

Multiple publication revisions may contain different versions for the same logical listing/date. A current pointer selects a readable publication; it does not authorize mutation or deletion of superseded content.

If `eod_bars` remains a materialized current projection, it is non-authoritative and rebuildable from immutable publication-bound rows. Consumers still resolve publication context first, and no projection update may mutate immutable history.

## Required fields

- `trade_date`
- stable `instrument_id` and `listing_id` (`ticker_id` only as governed compatibility key)
- `open`, `high`, `low`, `close` as precision-preserving decimals
- `volume` as non-negative integer
- `price_basis = RAW` or equivalent explicit identity
- `observation_id`/payload hash or immutable reference
- selected source/provider and mapping identity
- `run_id`, `publication_id`, and bar revision identity
- source observed timestamp and platform ingested timestamp

Nullable source-backed extensions:

- previous/reference price
- actual traded value
- trade count/frequency
- board/market-segment code
- trading-status code
- provider adjusted-close observation retained only for lineage/diagnosis

Equivalent naming is allowed only when semantics, units, provenance, and nullability remain identical.

## Validation rules (LOCKED)

A canonical bar is valid only when:

1. `open`, `high`, `low`, and `close` are non-null and strictly greater than zero.
2. `high >= max(open, close)`.
3. `low <= min(open, close)`.
4. `high >= low`.
5. `volume` is non-null, integral, and `>= 0`.
6. stable identity and temporal mapping are unambiguous for `trade_date`.
7. `trade_date` is a completed IDX Regular-Market trading session under the bound calendar version.
8. source observation, run, configuration, and publication/revision references are non-null for readable content.
9. optional numeric fields, when present, satisfy their own units/range/source rules.

Zero volume with valid positive OHLC may represent a source-backed no-trade/unchanged observation and must remain distinguishable from a missing bar. Zero or negative OHLC is always invalid and never canonical.

## Null and missing policy (LOCKED)

- Required canonical OHLCV and identity fields are never `NULL`.
- Missing required provider values create invalid/rejection evidence, not partially-null bars.
- No provider row for an expected listing/date creates missing-delivery evidence, not a canonical row.
- Unknown expectation creates held/unknown evidence, not automatic denominator exclusion.
- Optional source fields may be `NULL` only to mean unavailable/unknown; zero cannot substitute for missing.
- Provider adjusted close, if retained, must be nullable and must not become the analytical price basis.

## Invalid-bar handling

Invalid observations are stored outside canonical bar content with immutable observation linkage and governed reason codes. If an expected canonical bar is missing/invalid, coverage delivery and eligibility reflect the failure explicitly.

Invalid storage is audit evidence and is never a consumer price source.

## Publication and mutation rule (LOCKED)

- Sealed canonical bar content is immutable.
- Insert/update/delete-by-replacement of published logical content creates a new correction run, bar revision, hashes, derived artifacts, seal, publication, and supersession lineage.
- A detector or recovery command cannot update `eod_bars`/history in-place.
- Idempotent reprocessing with identical canonical values and provenance is `NOOP/UNCHANGED` and must not create false mutation.
- A partial recovered source row is candidate input; if it changes a published date, it follows correction/republication rather than direct current-row update.

## Historical impact rule

A changed bar can affect later adjusted products, rolling indicators, eligibility, hashes, and publications. The mutation-impact resolver must identify every affected trade date using the governed market calendar and must either republish all affected versions or hold them as stale/contaminated until safely rebuilt.

## Consumer rule

Consumers read bars only through a resolved sealed/readable publication and explicit price-basis identity. They must not read raw observation storage, invalid rows, unbound current projections, or `MAX(trade_date)` shortcuts.

## Acceptance criterion (LOCKED)

Canonical semantics are singular and testable when zero/null OHLC never appears, missing and invalid remain separate, every bar traces to immutable observation and temporal identity, and every content change creates new revision/publication lineage.

## Cross-contract alignment

- `Canonicalization_Contract_EOD_Bars.md`
- `Dataset_Seal_and_Freeze_Contract_LOCKED.md`
- `Canonical_Row_History_and_Versioning_Policy_LOCKED.md`
- `Historical_Correction_and_Reseal_Contract_LOCKED.md`
- `../registry/Price_Adjustment_Contract_LOCKED.md`
