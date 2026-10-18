# Finding â€” `F-MD-B19-A001-010`

- ID: `F-MD-B19-A001-010`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-09T19:11:01+07:00
- Severity: `P2`
- Status: `RESOLVED`
- Class: `PRODUCTION_DEFECT_WITH_FROZEN_VOCABULARY_DECISION`
- Current disposition: target predicates reviewed under E-MD-B19-A001-021; former hold superseded. Historical statements below retain their original checkpoint context.

## Statement

The locked `Reason_Codes_Registry.md` defines the canonical vocabulary for `eod_invalid_bars.invalid_reason_code`. The real `EodBarsIngestService` emits `BAR_CONFLICTING_SOURCE_ROWS` for two non-equivalent source rows of the same ticker/date, but this code is absent from both the locked registry and `Reason_Codes_Seed.sql`. The audit export faithfully writes that persisted value to its required `invalid_reason_code` column. A registered code cannot be substituted without choosing the semantics of a conflict; `BAR_DUPLICATE_SOURCE_ROW` describes deterministic duplicate resolution and is not clearly equivalent to two conflicting rows for which no canonical winner is selected.

The executed `B19InvalidBarsProducerProvenanceTest::test_conflicting_source_rows_expose_the_unregistered_reason_owner_gap` feeds two distinct conflicting rows and an independent clean row through the real ingest. Both conflicts are rejected with the unregistered code and the clean row remains canonical. The test is an explicit tripwire for the current defect; it does not declare that behavior conforming. `MD-S075-R0152` has no reviewed proof basis. The other eight family predicates have independent reviewed bases and remain admissible.

## Decision required

The Project Owner must choose the canonical meaning for conflicting source rows: register `BAR_CONFLICTING_SOURCE_ROWS` by a controlled strategy revision and seed it, or identify an existing registered code whose locked meaning truly covers conflict rejection, then change the producer. Either choice needs red-before/green-after execution, mutation proof, and a renewed R0152 review. No strategy authority or seed has been changed in this checkpoint.

## Related

`E-MD-B19-A001-015`, `CI-MD-B19-A001-001`, `D-MD-B19-A001-001`, `MD-DEP-0025`. Any producer or seed edit must update the successor-pending executable-build inventory before B19 closure. Candidate-v5 remains immutable; candidate-v6 has not been created.

## 2026-10-18 binding owner decision and current boundary

D-MD-B19-A001-005 records the Project Owner selection A: distinct BAR/HARD BAR_CONFLICTING_SOURCE_ROWS. The earlier decision-required/proposal passages above are retained chronology, superseded on selection by D005. The A/B choice must not be reopened. Exact replacement strategy bytes and impact analysis are prepared in B19_F010_F011_CONTROLLED_REVISION_REVIEW.md and E-MD-B19-A001-019 for the independent review retained by the owner. No live strategy/seed/runtime change or successor freeze is claimed. This finding stays OPEN until governed application and executed proof; R0152 remain HELD. Sixteen independent state-pair status/date/seal/version/hash/reason bases proceed without changing these obligations.

## D006 / E020 authority application

Owner-reported independent review corrections and confirmed RAW authorization applied with DOC-CHG-20261018-001 / freeze MD-STRATEGY-FREEZE-20261018-001. External provisional package/hash prerequisites withdrawn by latest owner instruction. Choices remain binding; exact corrected bytes and fingerprints retained. Finding remains OPEN on implementation/executed proof, not owner selection or strategy review.

## E021 current implementation and proof disposition

RESOLVED for this finding under D005/D006 and freeze MD-STRATEGY-FREEZE-20261018-001. R0152 admitted: BAR/HARD SQL registration, stable listing/date normal/recovered conflict rejection and exact audit provenance; deterministic duplicates retain WARN and clean neighbors remain canonical. New authority compound groups remain unproved stage scope; no formal SATISFIED, no B19 closure. Exact proof/mutations/limits in E021.
