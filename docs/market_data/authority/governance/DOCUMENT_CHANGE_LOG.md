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

## DOC-CHG-20260821-004 — Semantic predicate context and conditional applicability governance

- Date: 2026-08-21
- Finding/rationale: current traceability execution exposed two governance gaps: non-self-contained child/list fragments could be treated as independent proof predicates, and conditional requirements had no explicit terminal applicability lifecycle when their condition evaluated false.
- Supporting evidence basis: current documentation audit identified rows whose physical `rule_text` was only a filename/metadata/list fragment while the governing obligation lived in the parent statement, plus conditional rules that could remain `NOT_ASSESSED` indefinitely despite a false applicability condition.
- Reviewed decision: explicit user-authorized controlled governance adjustment on 2026-08-21.
- Strategy impact: none; frozen strategy bytes/semantics are unchanged.
- Governance result: proof identity is defined as the semantic predicate rather than a physical source line; non-self-contained fragments require deterministic parent/context binding and a normalized predicate; conditional applicability now has explicit pending/applicable/not-applicable lifecycle and denominator/closure rules; active stages must normalize transitional applicability/context before closure.
- Downstream impact only (not executed by this authority patch): Agent AI must re-derive affected traceability rows, revalidate affected current `SATISFIED` states, recompute stage coverage/denominators, synchronize current stage/dependency/current-state records, and update implementation/tooling if required by the revised authority. Immutable historical evidence/baselines are not edited by this authority revision.

## DOC-CHG-20260822-001 — Platform Config NULL-token owner alignment

- Date: 2026-08-22
- Finding: `F-MD-B04-A001-001`
- Supporting evidence: `E-MD-B04-A001-001`
- Reviewed decision: `D-MD-20260822-06`
- Explicit authorization: user instruction `RESOLVE MD-DEP-0007 — NULL TOKEN AUTHORITY CONFLICT`, received 2026-08-22, authorises the bounded correction after the authority review established that condition.
- Strategy impact: controlled correction to one resolved-key row in `MD-S082`; `MD-S005` and `MD-S034` remain byte-identical and retain canonical NULL-token ownership.
- Result: `market_data.hash.null_token` now declares an explicit zero-byte empty-string default and no environment input. The former `[empty]`/`MARKET_DATA_HASH_NULL_TOKEN` lock contradicted the owner semantics and was not treated as undocumented notation.
- Freeze impact: successor freeze `MD-STRATEGY-FREEZE-20260822-001`; only the registered `MD-S082` fingerprint changes.
- Verification impact: `MD-B04-A001-BL001` and `E-MD-B04-A001-001` remain immutable at the 113/114 boundary. Current closure requires successor `MD-B04-A002`, a new pre-change Baseline Lock, early Change Impact Declaration, and revalidation of `MD-S082-R0062` plus all affected config/hash/serializer-metadata proof.

## DOC-CHG-20260823-001 — Dedicated BAR reason code for zero-volume price movement

- Date: 2026-08-23
- Finding: `F-MD-B09-A001-001`
- Supporting evidence: `E-MD-B09-A001-001`
- Reviewed decision: `D-MD-20260823-01`
- Explicit authorization: user instruction `AUTHORIZE D-MD-20260823-01 AND CONTINUE MD-B09`, received 2026-08-23, authorises only the bounded additive `BAR_ZERO_VOLUME_PRICE_MOVEMENT` vocabulary correction.
- Strategy impact: controlled correction to `MD-S085` only; `MD-S023-R0044` and every other strategy document remain byte-identical.
- Result: `BAR_ZERO_VOLUME_PRICE_MOVEMENT` is the canonical `BAR` / `HARD` reason for a source-backed EOD row with `volume = 0` and non-identical OHLC; the row is invalid/rejected evidence and never canonical. No existing reason code changes meaning.
- Freeze impact: successor freeze `MD-STRATEGY-FREEZE-20260823-001`; only the registered `MD-S085` fingerprint changes.
- Verification impact: `MD-B09-A001-BL001` and `E-MD-B09-A001-001` remain immutable partial records under the predecessor freeze. `MD-B09` resumes through `MD-B09-A002` with a new baseline/CI; reason-code seed/runtime behavior and affected exhaustive seed proof require fresh revalidation. No B00-B08 closure is rewritten.

