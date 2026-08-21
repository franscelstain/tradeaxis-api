# Market Data Document Change Log

## DOC-CHG-20260820-001 — Architecture normalization and current verification rebaseline

- Date: 2026-08-20
- Finding: `F-MD-20260820-01`
- Decision: `D-MD-20260820-01`
- Evidence: `E-MD-20260820-01`
- Result: Watchlist-equivalent `authority/development/records` architecture adopted; 91 current strategy documents moved byte-for-byte; pre-rebaseline implementation/audit verdicts are historical-only.

## DOC-CHG-20260820-002 — Full legacy composite semantic normalization

- Date: 2026-08-20
- Finding: `F-MD-20260820-02`
- Decision: `D-MD-20260820-02`
- Evidence: `E-MD-20260820-02`
- Result: material composite legacy sources decomposed into exact role-pure extracts; strategy authority bytes unchanged; composite originals removed only after full reconstruction coverage.

## DOC-CHG-20260821-001 — Governance execution hardening and authority-mutability clarification

- Date: 2026-08-21
- Review basis: current documentation-governance audit after dependency-driven revalidation; existing findings include `F-MD-B00-A001-002` and `F-MD-B01-A001-001`.
- Reviewed decision: explicit user-authorized controlled governance adjustment on 2026-08-21.
- Strategy impact: none; strategy authority bytes/semantics unchanged.
- Governance result: clarified authority-versus-mutability semantics; required relationship completeness enforcement; predicate-only traceability requirements; deterministic single exact resume point for dependency-driven remediation; complete generated `CURRENT_STATE` summary requirements; formal Change Impact Declaration as a material-attempt closure requirement.
- Verification impact: current verification relying on relationship-gate validity without completeness, ambiguous resume orchestration, non-predicate traceability assignments, or missing required `CI-*` records must be re-evaluated/revalidated under the clarified rules before closure.

## DOC-CHG-20260821-002 — Downstream current-state synchronization after governance hardening

- Date: 2026-08-21
- Basis: downstream synchronization required by `DOC-CHG-20260821-001`; no additional strategy or governance semantic change.
- Strategy impact: none; strategy authority bytes/semantics unchanged.
- Updated mutable current-state artifacts: `development/implementation/MD_IMPLEMENTATION_STAGE_REGISTER.md`, `development/implementation/CURRENT_STATE.md`, `development/implementation/MD_DEPENDENCY_REGISTRY.csv`, `development/findings/F-MD-B01-A001-001_MATRIX_REQUIREMENT_ASSIGNMENT_DEFECT.md`, and `records/WORK_RELATIONSHIP_REGISTRY.csv`.
- Invalidation effect: `MD-B00` prior closure sufficiency and pre-closure integrity/relationship-gate results for open `MD-B01`/`MD-B03` are marked for current revalidation; immutable baselines/evidence/closure manifests are not edited.
- Exact resume effect: current execution is reconciled to one resume point — open `MD-B00-A002` against the revised governance, then return to `MD-B01` for `MD-DEP-0004` remediation.
- Explicit non-action: the traceability matrix is not mechanically rewritten in this synchronization; its 210-row classification/proof-ownership defect requires semantic re-derivation under the revised standard.

## DOC-CHG-20260821-003 — Runtime artifact / governed evidence boundary

- Date: 2026-08-21
- Finding: `F-MD-20260821-03`
- Evidence: `E-MD-20260821-03`
- Decision: `D-MD-20260821-03`
- Reviewed decision: explicit user-authorized controlled governance adjustment on 2026-08-21.
- Strategy impact: none; strategy authority bytes/semantics unchanged.
- Governance result: added explicit docs-first start/resume order for executed proof; separated governed evidence records from raw `storage/**` artifacts; defined when storage inspection is mandatory versus unnecessary; required current correlation plus path/hash/manifest integrity when external raw artifacts are material; prohibited raw historical artifacts from becoming current proof implicitly; defined missing/mismatched artifact handling and closure effect.
- Downstream synchronization: aligned authority ownership/change-impact matrices, `START_HERE.md`, root/records evidence navigation, generated `CURRENT_STATE.md`, current implementation `SYSTEM_READ_ORDER.md`, and executed-proof admission guidance.
- Verification impact: no automatic retroactive rewrite/invalidation of immutable issued evidence solely because application storage was not part of this docs-only snapshot. New executed proof, open-attempt closure, and future carry-forward of execution proof must satisfy the new artifact-linkage/integrity rule when external raw artifacts are required.
