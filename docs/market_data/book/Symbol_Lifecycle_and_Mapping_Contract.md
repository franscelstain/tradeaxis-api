# Symbol Lifecycle and Mapping Contract (LOCKED)

## Purpose

Define how exchange/display symbols and provider transport symbols map to stable issuer, instrument, and listing identities over time.

## Core rule (LOCKED)

Symbol text is mutable metadata, not identity. Every lookup for trade date T and provider P must resolve through an explicit effective-dated mapping to a stable `listing_id`/`instrument_id`.

## Required mapping record

Each exchange or provider-symbol mapping must include:

- immutable mapping identity
- stable `instrument_id` and `listing_id`
- provider code or exchange-symbol namespace
- symbol text exactly as used in that namespace
- `valid_from` and nullable `valid_to` under one documented interval convention
- source/provenance and mapping revision
- `recorded_at`/`known_at` for as-known replay when required
- optional change reason such as listing, rename, symbol change, delisting, relisting, provider correction, or board movement

Appending `.JK` may be a Yahoo adapter rendering rule, but it is not an identity rule and cannot substitute for a mapping record.

## Lifecycle rules (LOCKED)

- A symbol change closes the old mapping interval and opens a new interval; it does not rewrite old observations.
- A provider correction creates a new mapping revision/effective record and preserves prior lineage.
- Delisting closes listing and symbol validity at the governed effective boundary.
- Relisting must explicitly identify whether it continues the same instrument with a new listing or represents a different instrument.
- Board/market movement is effective-dated; Regular-Market observations retain the listing/board context valid on their trade date.
- Symbol reuse by another security must bind to a different stable identity with non-overlapping effective intervals.

## Uniqueness and ambiguity rules (LOCKED)

For one provider/namespace and effective instant:

- one symbol must resolve to at most one listing
- one active mapping identity must have one stable target
- overlapping mappings to different instruments are invalid
- gaps or overlaps required for requested T produce explicit mapping failure; current mapping must not be used as a silent fallback

## Observation and publication binding

Every raw observation stores the provider symbol actually requested/returned and the mapping identity used. Canonical artifacts store stable identity and preserve linkage to the observation/mapping revision. Re-resolving today's symbol must not change old publication identity.

## Historical replay rule (LOCKED)

Replay uses the mapping effective on trade date T and, for as-known mode, only the revision known by replay cutoff. It must not use a future rename, relisting, provider correction, or current symbol to resolve historical data.

## Failure behavior

- unknown symbol: reject/quarantine the observation
- ambiguous mapping: fail closed for the affected observation
- symbol/date outside mapping validity: reject with explicit reason
- mapping dependency unavailable: do not fabricate `ticker_id`, do not publish affected row, and expose coverage impact

## Capability boundary (LOCKED)

**What mapping resolution proves.** That a symbol resolves to at most one listing for a provider and effective instant; that reuse recorded as reuse resolves correctly on each side of its boundary; that gaps and overlaps fail closed rather than falling back to the current mapping.

**What it cannot prove.**

- **That an unrecorded rename or reuse was noticed.** A symbol change never recorded as a mapping interval resolves to one identity across both eras and satisfies every uniqueness rule. It is indistinguishable from a symbol that genuinely never changed.
- **That the recorded effective boundary is the real one.** A boundary off by even one session attaches observations to the wrong identity, consistently and without conflict.
- **That the provider used the symbol the platform assumes.** The mapping records what the platform requested and stored; a provider that silently serves a different security for a given symbol produces a well-formed mapping to the wrong instrument.

Mapping completeness therefore inherits the external reconciliation requirement in `Tickers_and_Identity_Dependency_Contract_LOCKED.md`. A mapping set that is internally consistent is not thereby complete.

## Acceptance criterion (LOCKED)

Given the same symbol text reused by two instruments at different times, observations before and after the reuse boundary must resolve to their respective stable identities without moving historical rows.

This proves the mapping mechanism. It does not prove that every rename, relisting, or reuse in the period was recorded; that is established by the external reconciliation above.

## Cross-contract alignment

- `Tickers_and_Identity_Dependency_Contract_LOCKED.md`
- `Source_Data_Acquisition_Contract_LOCKED.md`
- `Canonicalization_Contract_EOD_Bars.md`
- `Replay_Verification_Contract_LOCKED.md`
