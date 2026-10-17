# Finding — `F-MD-B19-A001-010`

- ID: `F-MD-B19-A001-010`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-09T19:11:01+07:00
- Severity: `P2`
- Status: `OPEN — PROJECT OWNER DECISION REQUIRED`
- Class: `PRODUCTION_DEFECT_WITH_FROZEN_VOCABULARY_DECISION`
- Blocked: `MD-S075-R0152` in `artifact_invalid_bars_export`; the family is 8/9, not complete.

## Statement

The locked `Reason_Codes_Registry.md` defines the canonical vocabulary for `eod_invalid_bars.invalid_reason_code`. The real `EodBarsIngestService` emits `BAR_CONFLICTING_SOURCE_ROWS` for two non-equivalent source rows of the same ticker/date, but this code is absent from both the locked registry and `Reason_Codes_Seed.sql`. The audit export faithfully writes that persisted value to its required `invalid_reason_code` column. A registered code cannot be substituted without choosing the semantics of a conflict; `BAR_DUPLICATE_SOURCE_ROW` describes deterministic duplicate resolution and is not clearly equivalent to two conflicting rows for which no canonical winner is selected.

The executed `B19InvalidBarsProducerProvenanceTest::test_conflicting_source_rows_expose_the_unregistered_reason_owner_gap` feeds two distinct conflicting rows and an independent clean row through the real ingest. Both conflicts are rejected with the unregistered code and the clean row remains canonical. The test is an explicit tripwire for the current defect; it does not declare that behavior conforming. `MD-S075-R0152` has no reviewed proof basis. The other eight family predicates have independent reviewed bases and remain admissible.

## Decision required

The Project Owner must choose the canonical meaning for conflicting source rows: register `BAR_CONFLICTING_SOURCE_ROWS` by a controlled strategy revision and seed it, or identify an existing registered code whose locked meaning truly covers conflict rejection, then change the producer. Either choice needs red-before/green-after execution, mutation proof, and a renewed R0152 review. No strategy authority or seed has been changed in this checkpoint.

## Related

`E-MD-B19-A001-015`, `CI-MD-B19-A001-001`, `D-MD-B19-A001-001`, `MD-DEP-0025`. Any producer or seed edit must update the successor-pending executable-build inventory before B19 closure. Candidate-v5 remains immutable; candidate-v6 has not been created.
