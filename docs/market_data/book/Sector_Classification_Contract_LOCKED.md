# Sector Classification and Membership Contract (LOCKED)

## Scope decision (LOCKED)

Sector classification is **inside market-data scope**. It is a governed exchange fact about an instrument, of the same kind as listing, board, and trading status, and it is owned here rather than by a downstream consumer.

What follows from that placement:

- Sector membership is a **fact**, resolved point-in-time like every other temporal fact in this package.
- Sector-relative measures published by market-data are **facts derived from facts**, not preferences.
- Sector rotation, over- or under-weighting, and any ordering of sectors by attractiveness remain downstream policy under `Domain_Boundary_Invariants_LOCKED.md`. Owning the classification does not transfer that.

## Authority (LOCKED)

The authoritative classification system is **`IDX-IC`**, sourced from **IDX**.

No other classification system may be stored as `IDX-IC`, and no other system may be introduced without an explicit contract change naming its authority, its effective range, and its relationship to `IDX-IC`.

### Source authority classes

The classification system being authoritative does not make every source of it authoritative. Sources are classified exactly as trading-status sources are in `Trading_Status_Source_Contract_LOCKED.md`:

| Class | May establish membership | Requirement |
|---|---|---|
| `EXCHANGE_AUTHORITATIVE` | yes | published by IDX or its official dissemination channel, with reference retained |
| `DERIVED_REFERENCE` | no | third-party restatement of an IDX classification; may corroborate or trigger review, never establish |
| `OPERATOR_ENTERED` | conditional | permitted only with an explicit authoritative reference, named operator, and governed reason code |

A membership row records its source and the class that source holds. **A row whose source class is `DERIVED_REFERENCE` is not an authoritative membership** regardless of the classification system it names, and must be resolved to an authoritative source or held.

## Membership as a temporal fact (LOCKED)

Sector membership changes. IDX reclassifies instruments, and a series evaluated at trade date `T` must use the classification effective on `T`.

Each membership record binds:

- stable instrument/listing identity, never ticker text alone;
- `sector_code` within a named `classification_system`;
- `effective_from` and nullable `effective_to` under one documented interval convention;
- source, source reference, and source authority class;
- `recorded_at`/`known_at` for as-known replay.

Rules:

- Reclassification **closes the prior interval and opens a new one**. It never edits the prior row, because doing so retroactively rewrites every historical sector-relative value.
- Resolution for `T` uses the interval covering `T`. Current membership is a cached projection and may never resolve a historical date.
- Overlapping intervals for one instrument within one classification system are invalid and fail closed.
- An instrument with no covering interval for `T` resolves to `UNKNOWN`, not to a default sector and not to its current sector.

## Sector index bars versus sector-derived measures (LOCKED)

Two distinct things must not be conflated:

- A **sector index bar** acquired from a source is an observation. It is subject to the same acquisition, canonicalization, validation, and publication rules as any other bar, including immutable observation envelope and provenance. It is not computed by this platform.
- A **sector-derived measure** — sector aggregate return, relative strength against sector, sector strength against the broad index — is a versioned analytical product computed from member bars and the membership resolution above.

Consequences:

- A sector-derived measure inherits the price basis, factor set, and formula version of the run that produced it, and binds the membership revision used.
- A measure computed over a window in which membership changed states which membership revision applied, or is `NULL` with a reason. Silently mixing two sectors across one window is forbidden.
- An acquired sector index bar and a computed sector aggregate are never presented under the same field name.

## Capability boundary (LOCKED)

**What sector resolution proves.** That an instrument's recorded classification for a date was resolved from an interval covering that date, under a named system and a source of stated authority class.

**What it cannot prove.**

- **That the classification is current with the exchange.** Resolution reflects what was recorded. A reclassification IDX published but nobody imported leaves the old interval open, and every date after the real change resolves to the wrong sector with no signal.
- **That an open-ended interval means no change occurred.** A membership with `effective_to` null asserts that nothing was recorded, which is equally consistent with nothing happening and with nothing being captured.
- **That sector-relative measures are comparable across a reclassification.** Relative strength against sector before and after a change compares against different populations, correctly in each period and misleadingly across the boundary.
- **That membership coverage equals universe coverage.** An instrument absent from the membership table is not unclassified by decision; it is simply unrecorded.

Consequently a resolved sector may be cited as evidence that **a recorded classification covered the date**, never as evidence that **the instrument was classified that way by the exchange at that time**.

## Completeness is verified externally (LOCKED)

Sector membership is a root of expectation for every sector-relative measure, so it falls under the shared external-reconciliation rules owned by global gate 13 in `Market_Data_Implementation_Conformance_Matrix_LOCKED.md`. Those rules are not repeated here.

Domain parameters owned by this contract:

- **Authority:** the IDX-published `IDX-IC` classification and its change announcements.
- **Cadence:** reconcile on every IDX-IC classification/reclassification publication before sector-relative products for the affected effective period are finalized; perform a full-range membership reconciliation before any historical period receives an unqualified sector-relative correctness claim.
- **Scope:** from the intentional dataset start onward, covering every instrument in the temporal universe.
- **Qualification:** a claim about sector-relative measures over an unreconciled period must name that period. The direction that matters most here is a reclassification that occurred but was never recorded, since it produces silently wrong sector attribution rather than a gap.

## Dependency order (LOCKED)

Temporal sector membership is a prerequisite for every sector-relative indicator. The implementation sequence therefore closes this contract's temporal foundation at Stage 6 / `W05`, **before** deterministic indicator work at `W14`. Stage 13 / `W16` may consume and expose the already-governed sector-reference state, but may not be the first point at which membership becomes temporal.

## Acceptance criterion (LOCKED)

An instrument reclassified at date `R` resolves to its prior sector for every date before `R` and its new sector from `R` onward, with both intervals present and neither row edited. A resolver that cannot satisfy this is not point-in-time, and every sector-relative measure it feeds inherits that defect.

## Cross-contract alignment

- `Domain_Boundary_Invariants_LOCKED.md`
- `Tickers_and_Identity_Dependency_Contract_LOCKED.md`
- `Trading_Status_Source_Contract_LOCKED.md`
- `Market_Data_Implementation_Conformance_Matrix_LOCKED.md`
- `../registry/Indicator_Registry_Baseline_LOCKED.md`
- `../registry/Platform_Config_Registry_LOCKED.md`
