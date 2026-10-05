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

## DOC-CHG-20260903-001 — Date-level anomaly configuration registration

- Date: 2026-09-03
- Finding: `F-MD-B17-A001-001`
- Supporting evidence: `E-MD-B17-A001-001`
- Reviewed decision: `D-MD-B17-A001-001`
- Explicit authorization: user instruction `oke saya setujui pembaruan yang akan dilakukan`, received 2026-09-03 in direct response to the bounded decision impact explanation, authorises exactly the six-key correction recorded by `D-MD-B17-A001-001`.
- Strategy impact: six additive resolved-key rows in `MD-S082` only; `MD-S051` and every other strategy document remain byte-identical.
- Result: the zero-volume share, flat-bar share, cross-field contradiction count, neighbouring-trading-day window, neighbour elevation factor, and minimum-row thresholds now have typed configuration identities with explicit defaults and environment inputs owned by `MD-S051`.
- Scope limit: no other key, default, threshold, finding behavior, readiness rule, publishability rule, or strategy semantic is authorised to change.
- Freeze impact: successor freeze `MD-STRATEGY-FREEZE-20260903-001`; only the registered `MD-S082` fingerprint changes.
- Verification impact: `MD-B17-A001-BL001` and both A001 evidence records remain immutable/non-PASS under the predecessor freeze. `MD-B17` resumes only through `MD-B17-A002` with a new baseline/CI, fresh 246-predicate proof, and explicit affected-proof revalidation of the changed B04 exhaustive configuration/snapshot invariants.

## DOC-CHG-20260922-001 — Eligibility decision contract version registration

- Date: 2026-09-22
- Finding: `F-MD-B18-A002-021`
- Supporting evidence: `E-MD-B18-A002-045` through `E-MD-B18-A002-048`
- Reviewed decision: `D-MD-B18-A002-006`
- Explicit authorization: user instruction "OWNER DECISION untuk F-MD-B18-A002-021 / `eligibility_version`: Pilih Opsi B — config-driven eligibility contract version. Tetapkan: canonical config key: `market_data.eligibility.contract_version`; initial value: `eod_eligibility_snapshot_v1`; environment variable: `MARKET_DATA_ELIGIBILITY_CONTRACT_VERSION`," received 2026-09-22, authorises exactly this one-key registration recorded by `D-MD-B18-A002-006`.
- Strategy impact: one additive resolved-key row in `MD-S082` only; every other strategy document remains byte-identical.
- Result: the eligibility decision contract's own version identity (distinct from `coverage_gate.contract_version`) now has a typed configuration identity with an explicit default and environment input, owned by `EOD_Eligibility_Snapshot_Contract_LOCKED.md`.
- Scope limit: no other key, default, threshold, finding behavior, readiness rule, publishability rule, or strategy semantic is authorised to change.
- Freeze impact: successor freeze `MD-STRATEGY-FREEZE-20260922-001`; only the registered `MD-S082` fingerprint changes.
- Verification impact: prior `MD-B18-A002` evidence (`E-MD-B18-A002-001` through `-048`) is unaffected and remains immutable under the predecessor freeze; none of it depended on the `MD-S082` exhaustive key population. The four predicates this registration bears on (`MD-S050-R0002`, `MD-S050-R0014`, `MD-S019-R0071`, `MD-S003-R0003`) are reviewed on their own merits in this work unit's own evidence record, not promoted automatically because this registration exists.

## DOC-CHG-20260923-001 — Immutable historical integrity exception for the documentation gate

- Date: 2026-09-23
- Finding: `F-MD-B18-A002-022`
- Supporting evidence: `E-MD-B18-A002-063` (correction of `E-MD-B18-A002-061`, unresolved item `E061-U1`); `E-MD-B18-A002-064` (gate remediation and fail-closed proof)
- Reviewed decision: `D-MD-B18-A002-007`
- Explicit authorization: owner decision received 2026-09-23: "Do not declare E061 “never issued” and do not edit E061. Adopt a narrowly controlled immutable historical integrity exception mechanism, but only through explicit governance authority and fail-closed gate implementation."
- Strategy impact: none; strategy authority bytes, semantics and freeze unchanged.
- Governance result: `DOCUMENT_INTEGRITY_GATE_STANDARD.md` gains a section defining `DOCUMENT_INTEGRITY_EXCEPTION_REGISTRY.json` as the only exception path for an issued, immutable, structurally invalid evidence record: eligibility conditions, registry contract, and fail-closed gate behaviour. The original gate requirement sentence is unchanged. `MarketDataDocumentationIntegrityGate` validates the registry as its own check and admits a `JSON_PARSE` failure only through a valid `ACTIVE` entry.
- Scope limit: one excepted check (`JSON_PARSE`), one eligible class (issued `IMMUTABLE_AFTER_ISSUE` evidence with an issued correction), one registered entry (`MD-DOCEX-0001`, `E-MD-B18-A002-061`). Any other check, class or artifact requires a new controlled revision.
- Verification impact: prior documentation-gate `PASS` results are unaffected, because the registry held no entry from its creation until this revision and so no earlier result could have used the admission path. Revalidated here: the gate, its self-test with new fail-closed mutations, and the governance gates (`E-MD-B18-A002-064`). No immutable record is edited.

## DOC-CHG-20260925-001 — Rerun configuration default, governed override and fail-closed rule for `MD-S065-R0003`

- Date: 2026-09-25
- Finding: `F-MD-B18-A002-017` (`G09`)
- Supporting evidence: `E-MD-B18-A002-077` (G09 reconstruction: the real rerun/promote path binds live configuration; `AUTHORITY_AMBIGUITY`); `E-MD-B18-A002-078` (correction of `E-077`'s unsupported "843 recompute runs" statement); `E-MD-B18-A002-079` (revision, ownership transfer, probes and validation)
- Reviewed decision: `D-MD-B18-A002-010` (A1 + C1 + D2), completing `D-MD-B18-A002-009` (Option 2)
- Explicit authorization: owner instruction received 2026-09-25 selecting A1 + C1 + D2 for the `MD-S065-R0003` mechanism: "Owner now explicitly authorizes the minimum bounded controlled revision necessary to encode A1+C1+D2."
- Strategy impact: `Config_Change_Protocol_LOCKED.md` (`MD-S065`) line 7 only, extended in place, with the original sentence kept as its prefix. It now states: by default a rerun of `D` binds the configuration effective for `D` and never implicitly substitutes live/current/latest; an override exists only when a governed correction explicitly requests it and records the configuration identity and reason; `system` may approve such a correction, but an approval without that request is not an override; an override run is distinguishable and never re-stamps its configuration as effective for `D`; without an override, a rerun whose effective-for-`D` configuration is not the live executable configuration is `BLOCKED`; effective dates come from the interval declared in the configuration registry, never from a rerun or backfill requested date. Every other line and every other strategy document is byte-identical.
- Result: the "explicitly documented override" that `MD-S065-R0003` names, and the failure behaviour when no override exists, are defined. Traceability row `MD-S065-R0003` carries the new rule text and fingerprint and, under `D-MD-B18-A002-010` (D2), primary `MD-B21` with supporting `MD-B18;MD-B04`. It stays `REQUIRED`/`MANDATORY`/`NOT_ASSESSED` with no evidence. Stage ownership is recorded in the matrix, not in the strategy text.
- Scope limit: no application code, schema, migration, runtime configuration or PHPUnit test changes. `resolveForRun`, `getOrCreateOwningRun`, `createPromoteRunFromSeed`, `assertConsumedConfiguration` and the correction commands are unchanged. `MD-S082-R0207`/`R0209` are not built. No reason code is registered. `MD-S065` rows `R0001`/`R0002`/`R0004`/`R0005` are unchanged. The platform stays non-conformant (a rerun binds live configuration) until `MD-B21` implements the rule.
- Freeze impact: successor freeze `MD-STRATEGY-FREEZE-20260925-001`; only the registered `MD-S065` fingerprint changes (`FBAC0291...BA0CC` -> `28962247...BF897`).
- Verification impact: `MD-S065-R0003` was `NOT_ASSESSED` with an `INCOMPLETE` basis and no bound evidence, so no current `PASS` or `SATISFIED` depended on its old text; its prior basis is kept audit-only as `TRANSFERRED_OWNERSHIP`. `MD-B18`'s required set loses this row (tool-derived: normalization and readiness report denominator 113; proof basis 91 `PROVEN` / 22 without a reviewed basis). Formal `0/114` in `MD-B18` becomes `0/113` by ownership alone; no `SATISFIED` state changes. `MD-B04`'s counts are unchanged (114 mandatory / 181 moved / 645 reference); `R0003` stays a moved row with `MD-B04` supporting. `MD-S065-R0002` (`SATISFIED` under `MD-B04`, `E-MD-B04-A002-001`) was reviewed: its line and obligation are unchanged, so it is not invalidated. The concern `D-MD-B18-A002-009` already reported (the resolver records a run's requested date as a new version's effective date; `R0002`'s proof is family-level) stands and is reported again for `MD-B04`'s review. Prior evidence is unaffected and stays immutable under the predecessor freeze.

## DOC-CHG-20260927-001 — First independent R0025 fixture admission

- Date: 2026-09-27
- Finding/rationale: `F-MD-B18-A002-017`, first-fixture derivation, review and target-chronology admission ambiguity within the existing `MD-S003-R0024` independent-oracle requirement.
- Supporting evidence and verification-impact review: `E-MD-B18-A002-085`.
- Reviewed owner decision and explicit authorization: `D-MD-B18-A002-011`, Option A selected in the owner's 2026-09-27 first-independent-fixture instruction.
- Change impact declaration: `CI-MD-B18-A002-001` (first-fixture authority declaration before this revision).
- Controlled governance revision: `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 6A defines independently authored expectations, admission review/approval, existing-target eligibility, provenance separation and immutable corrections. Bounded technical clarifications in `Golden_Fixtures_Specification.md` and `Fixture_Package_Manifest_LOCKED.md` align the package specification with it.
- Strategy and freeze impact: none. No bytes under `authority/strategy/` change; `MD-STRATEGY-FREEZE-20260925-001` remains current, so no successor freeze is issued.
- Verification impact: `MD-S003-R0024`'s existing tests prove self-generated/relabelled rejection and remain valid on that scope; they do not prove admission of the first independently authored package. `MD-S003-R0025` remains `INCOMPLETE` until an approved package and the full MariaDB proof chain exist. No prior `PASS` or `SATISFIED` is newly carried into R0025; proof basis 100/13 and formal 0/113 remain unchanged. `MD-DEP-0019` remains BLOCKING.

## DOC-CHG-20261005-001 — Freshness state `NOT_APPLICABLE` for a readable pre-activation publication

- Date: 2026-10-05
- Finding: `F-MD-B18-A002-032` (the freshness state of a `READABLE` publication before operational activation had no representation in the four-state vocabulary; implementation conformance is tracked by `F-MD-B18-A002-033`)
- Supporting evidence: `E-MD-B18-A002-097` (review of candidate-v3 and the authority trace); `E-MD-B18-A002-098` (revision, authority trace, impact classification)
- Reviewed decision: `D-MD-B18-A002-015` (owner Direction D: authority change first), recorded before any edit
- Explicit authorization: owner instruction received 2026-10-05: "Project owner memilih DIRECTION D." with the semantic intent quoted in `D-MD-B18-A002-015`
- Strategy impact: `Downstream_Data_Readiness_Guarantee_LOCKED.md` (`MD-S022`) gains the freshness state `NOT_APPLICABLE` and the section "Readability and operational freshness are independent (LOCKED)" (applicability rule, ordered freshness truth table, fail-safe default, activation context and transition, manifest and hash participation); `Consumer_Readability_Decision_Table_LOCKED.md` (`MD-S009`) line 7 only, original wording kept as prefix; `Downstream_Consumer_Read_Model_Contract_LOCKED.md` (`MD-S021`) line 55 only. Every other line and every other strategy document is byte-identical.
- Result: a `READABLE` publication whose requested trade date precedes the effective activation marker (or with no marker) carries `freshness_state = NOT_APPLICABLE`, which means operational freshness is not in force: not `FRESH`, not `STALE`, not `DEGRADED`, not `NOT_AVAILABLE`. `READABLE` and operational freshness are independent facts.
- Scope limit: no application code, schema, migration, runtime configuration, PHPUnit test, candidate package or expected hash changes. No degraded condition is declared. No B10-owned strategy row changed.
- Freeze impact: successor freeze `MD-STRATEGY-FREEZE-20261005-001`; only the registered `MD-S009`, `MD-S021` and `MD-S022` fingerprints change.
- Verification impact: `MD-S009-R0002` and `MD-S021-R0027` changed text and were `SATISFIED` under `E-MD-B17-A002-001`; they are `NOT_ASSESSED`, the predecessor basis kept audit-only. Eleven new mandatory `MD-S022` rows (`R0040`-`R0042`, `R0045`-`R0052`) and two reference rows are not assessed. `MD-B17` is therefore `244/257` and needs a successor attempt; its other 244 rows keep their predecessor-freeze basis. `MD-B10`: no row text changed; bounded producer remediation and revalidation of `MD-S005-R0056`, `MD-S005-R0071`, `MD-S045-R0058` are required before candidate-v4 and before any conformance claim for pre-activation freshness (`F-MD-B18-A002-033`, `E-MD-B18-A002-098`). Prior evidence is unaffected and stays immutable under the predecessor freeze.
