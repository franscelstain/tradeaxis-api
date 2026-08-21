# Work Correlation and Record Registry Standard

## 1. Correlation identity

Attempt ID is the Work ID. Current records created by one attempt use the same correlation identity.

Examples: `F-MD-B05-A001-001`, `E-MD-B05-A001-001`, `D-MD-B05-A001-001`, `CI-MD-B05-A001-001`, `SC-MD-B05-A001-001`.

All current work records MUST be registered in `records/WORK_RECORD_REGISTRY.csv`.

## 2. Explicit relationship registry

Every material cross-record relationship that crosses attempt, stage, or baseline boundaries MUST have an explicit row in `records/WORK_RELATIONSHIP_REGISTRY.csv`.

This includes, where applicable:

- an attempt/evidence record reusing or superseding evidence from another attempt;
- remediation of a finding created by another attempt/stage;
- a decision depending on a finding/evidence record from another correlation identity;
- a closure/baseline/evidence record carrying forward proof from another attempt or baseline;
- dependency-driven work performed by another stage and returned to the blocked stage;
- any canonical `related`, `supersedes`, `derived_from`, `remediates`, `depends_on`, `carried_from`, or equivalent relationship declared by a current record.

A prose mention alone MUST NOT substitute for the required registry row when the relationship is part of the current proof chain.

## 3. Relationship completeness invariant

Relationship integrity is both:

1. **validity** — every relationship row references valid registered records and uses an allowed/non-circular relationship; and
2. **completeness** — every relationship required by current records/proof chains is represented by an explicit registry row.

A relationship gate MUST NOT return `PASS` merely because the registry contains zero rows or because all existing rows are valid.

The gate MUST inspect the canonical relationship-bearing fields/records defined by the current recording standards and fail when a required relationship is missing from `WORK_RELATIONSHIP_REGISTRY.csv`.

If current records declare cross-attempt/stage/baseline relationships and the relationship registry has no matching rows, the result is `FAIL`.

## 4. Closure effect

Missing required relationships invalidate the affected proof chain. A stage/attempt whose closure depends on that proof chain MUST NOT be marked `DONE` until relationship completeness is restored and the relationship integrity gate passes.


## 5. Raw-artifact linkage is subordinate to record correlation

Raw artifacts under `storage/**` are not Work Records and are not registered as independent records in `WORK_RECORD_REGISTRY.csv`.

When a current evidence record relies on a raw artifact, the evidence record carries the Stage/Attempt/Baseline/Epoch correlation and the artifact path/hash/manifest linkage required by `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`.

Do not create a parallel "storage registry" that competes with the Work Record Registry. If an artifact manifest is itself issued as a governed evidence record, register that governed record; otherwise the artifact remains subordinate raw proof.
