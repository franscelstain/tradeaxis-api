# Decision — D-MD-B19-A001-007

- ID: `D-MD-B19-A001-007`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Status: `APPROVED`
- Role: Project Owner implementation direction, recorded from the explicit 2026-10-19 reply.
- Related: `MD-S075-R0004`, `CI-MD-B19-A001-001`, `D-MD-B19-A001-006`, `E-MD-B19-A001-022`.

The owner authorizes a per-artifact companion-manifest mechanism; inline duplication is not required solely for R0004. Every individual artifact must bind exact bytes and run/publication identity, expose all applicable V2 bindings with authoritative provenance, distinguish required/inapplicable/unavailable, remain immutable/co-delivered/independently resolvable, and never fabricate or infer historical identity from live state. Incomplete mandatory bindings cannot count as complete proof.

## Application and change-control boundary

Reuse artifact_manifest.json and existing evidence/lineage contexts. This implements the current literal obligation to expose all applicable bindings; it does not remove a binding, classify an unknown value as inapplicable, waive proof, or alter a frozen field/meaning. No strategy text is edited and the 91-document freeze remains MD-STRATEGY-FREEZE-20261018-001. DOCUMENT_CHANGE_POLICY still governs any additional normative clarification found necessary; no such unreviewed revision is self-approved here. Missing source values stay UNAVAILABLE and preclude complete binding admission. Actual and independent expected contexts remain separate. Historical rows, manifests, approved fixtures and issued evidence are preserved; new replay-only supplemental bindings must be captured before persistence, not repaired at export.

R0004 may be admitted only after real producer/source/persistence/companion proof, exact-byte hashes, applicability, missing-value and immutability negatives. Owner direction alone is not admission. Any unresolved semantic question remains explicitly held for independent review. No Git mutation is authorized.
