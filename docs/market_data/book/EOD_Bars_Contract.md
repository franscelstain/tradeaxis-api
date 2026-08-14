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

### Cross-field consistency (LOCKED)

Rules 1 to 9 validate each field against its own domain. They cannot detect a bar whose fields are individually valid but jointly impossible. One such combination is common enough, and damaging enough, to be named:

10. **Zero volume with intra-session price movement is invalid.** When `volume = 0`, the session recorded no executed trade, so `open`, `high`, `low`, and `close` must be identical. A bar reporting `volume = 0` alongside `high > low` asserts that price moved without any trade occurring, which no market mechanism produces. It is rejected as invalid with its own reason code, never stored as canonical.

This is the volume-side sibling of the zero-price rule. A zero price is impossible in isolation; a zero volume is legitimate in isolation and impossible only in combination.

Two consequences:

- The contradiction is a **source defect**, not a market fact. It is handled as invalid observation evidence under the missing-versus-invalid model, and the affected listing/date becomes a delivery gap rather than a silently wrong bar.
- Such defects cluster by acquisition date rather than by instrument. When a single trade date carries a materially higher share of zero-volume bars than its neighbours, that is date-level evidence of an acquisition fault, and it must surface even for rows whose OHLC happen to be flat and are therefore individually admissible. **The date-level check and its threshold are owned by `Run_Status_and_Quality_Gates_LOCKED.md`**; this contract owns only the per-row rule, because a per-row rule cannot by construction see a pattern across rows.

A bar accepted before this rule existed does not become valid retroactively; correcting it follows the correction/republication lifecycle like any other content change.

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

## Capability boundary (LOCKED)

**What the validation rules prove.** That each canonical bar is internally coherent, positively priced, integrally volumed, identity-resolved, calendar-bound, and provenance-complete; and, under rule 10, that no bar asserts price movement without trade.

**What they cannot prove.**

- **That the values are the ones the market produced.** Every rule tests the bar against itself or against structural facts. A fabricated bar with plausible, internally consistent values satisfies all ten.
- **That a flat bar is a genuine no-trade day.** `open = high = low = close` with zero volume is admissible and usually correct, but it is also the exact shape a provider produces when it repeats a prior session. The rules cannot separate the two.
- **That a bar belongs to the session it claims.** Rule 7 checks the trade date against the bound calendar. If the calendar itself is wrong, a bar for a non-session passes, and if a real session is missing from the calendar, its bars are rejected as non-trading.
- **That an accepted bar is contamination-free.** Validity is a property of the bar; contamination is a property of its window and belongs to the corporate-action and detection contracts.

Consequently a valid canonical bar may be cited as evidence that **the row is admissible and traceable**, never as evidence that **the price is right**.

## Acceptance criterion (LOCKED)

Canonical semantics are singular and testable when zero/null OHLC never appears, missing and invalid remain separate, every bar traces to immutable observation and temporal identity, and every content change creates new revision/publication lineage.

## Cross-contract alignment

- `Canonicalization_Contract_EOD_Bars.md`
- `Dataset_Seal_and_Freeze_Contract_LOCKED.md`
- `Canonical_Row_History_and_Versioning_Policy_LOCKED.md`
- `Historical_Correction_and_Reseal_Contract_LOCKED.md`
- `../registry/Price_Adjustment_Contract_LOCKED.md`
