# Change Impact Declaration — `MD-B07-A001`

- ID: `CI-MD-B07-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B07` / `MD-B07-A001` / `MD-B07-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260822-001`
- Stage precondition: `SC-MD-B06-A001-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Status: `EXECUTED — COMPLETE; CLOSED BY SC-MD-B07-A001-001`
- Strategy meaning change: `NO`

## Objective

Revalidate and, where required, remediate the immutable source-observation and acquisition adapter boundary. Every provider response, manual-file unit, empty outcome, and failure must acquire immutable provenance before parsing/canonicalization; provider vocabulary must stop at the adapter; target-date, temporal mapping, schema, payload, and source identity must remain explicit; and invalid, ambiguous, stale, unverifiable, or secret-bearing inputs must fail closed without manufacturing canonical/readable state.

## Authority and traceability scope

- Primary semantic owners: `MD-S053` Source Data Acquisition Contract and `MD-S054` Source Mapping Contract.
- Supporting owned predicates: `MD-S020-R0010`, `MD-S041-R0029`, `MD-S041-R0056`, `MD-S052-R0026`, `MD-S055-R0024`, `MD-S058-R0048`, `MD-S059-R0040`, and `MD-S066-R0001`, subject to parent/context composition and proof-ownership review.
- Current provisional B07 state: 255 active assigned rows, comprising 142 provisional executable rows (7 already mandatory and 135 transitional) plus 113 reference-only rows.
- `MD-DEP-0004`: resolve every B07 transitional applicability value, bind context-dependent fragments, resolve the 25 mixed-classification reference members reported for B07, confirm proof-owning stage, recompute coverage, and revalidate any affected prior state before proof binding.
- Headings, introducers, examples, bare field/value fragments, capability limitations, and cross-contract references remain non-executable unless they compose into an objectively testable semantic predicate.

## Impact assessment

- Strategy: no strategy byte change is authorised or expected.
- Schema/data: inspect immutable observation envelope/payload-reference, acquisition run/checkpoint, invalid/quarantine/failure, temporal mapping, divergence, and provenance persistence. Any gap is remediated additively; no issued observation or historical publication may be rewritten.
- Configuration: inspect active source/adapter/schema identity, sanitized request handling, payload retention/reference behavior, and secret-bearing inputs. Configuration cannot weaken source-neutral invariants.
- Runtime: inspect provider response/manual-file capture-before-parse, content/hash/length identity, explicit requested date/range, timezone/session mapping, schema/cardinality validation, immutable re-fetch lineage, same-date divergence detection, provider-neutral normalized candidates, and fail-closed canonical handoff.
- API/contracts: provider-specific parameters and payload paths stop at adapter/import boundaries; acquisition success cannot imply publication/readability or value correctness.
- Backfill/replay: range-window acquisition must preserve per-date scope, calendar-derived warmup, checkpoint identity, isolated failure telemetry, and resume-only-failed semantics. No historical backfill is claimed merely by repository tests.
- Tests/gates: add or repair behavioral positive, negative, mutation, schema/migration, traceability-normalization, exact-proof, compatibility, residue, and closure-control tests. Resume reconciliation additionally hardens the documentation gate so Document ID completeness/uniqueness and canonical Stage Register shape are executable invariants, with fail-closed mutations in the existing integrity self-test.
- Operations: manual-file acquisition remains explicit controlled transport/rescue and cannot bypass provenance/schema/target-date rules. B08 owns broader resilience orchestration; B19 owns full operational execution/evidence export.
- Compatibility: preserve provider-neutral ports and B05/B06 temporal identities. Reject current-symbol fallback, `adj_close` close fallback, provider-empty-as-not-expected inference, placeholder values, latest-wins conflicts, and in-place observation mutation.
- Residue/rework: search only the acquisition/adapter/canonical-handoff surface for write-before-envelope, mutable observation updates, secret leakage, implicit target-date replacement, default provider windows as domain limits, stale cross-ticker telemetry, and executable legacy source paths.
- Evidence: issue new A001 governed evidence after actual execution; historical evidence is supporting context only and is not edited or inherited.
- Relationships: register baseline-to-B06 precondition, CI-to-baseline, evidence-to-baseline/CI, any carried proof, dependency discharge, and closure relationships explicitly.
- Dependencies/downstream: only the B07 portion of `MD-DEP-0004` may be discharged here. Predicates moved to later proof owners remain unproven there; no downstream stage is opened.
- Raw artifacts/storage: do not scan `storage/**` broadly. Repository/database/test proof is expected first. If a runtime payload, checkpoint, replay, or retained execution artifact becomes material, the governed evidence must bind its path/manifest/hash and execution identity before use.

## Closure boundary

Closure requires a final B07 semantic denominator with zero transitional or pending applicability and no B07 mixed-classification debt; conformant actual schema/code/configuration; positive and fail-closed proof for every owned predicate; immutable observation and provenance guarantees; no harmful executable residue; current evidence; complete relationships; current Change Impact result; and all required integrity/governance gates.

## Actual impact and result

- **Attempt continuity**: A001 was interrupted after implementation and stage-entry normalization and before any proof ran. It was continued, not restarted. No second baseline lock, no duplicate declaration, no `MD-B07-A002`, and no rollback of the landed normalization.
- **Traceability**: the provisional 142-row executable count (7 mandatory + 135 transitional) resolved to **115 mandatory**, zero transitional, 52 structural reference, and 88 predicates moved to their actual downstream proof owners. B07 mixed-classification debt is zero and the global backlog stands at 502 across 13 unopened stages.
- **Implementation**: landed before the cutoff and re-validated here — `SourceObservationRepository` +376 lines, `PublicApiEodBarsAdapter` +420, `EodBarsIngestService` +154, two additive migrations creating four observation tables, and a 106-line extension of the SQLite mirror. Twenty predicates are annotated with the attempt that remediated them.
- **Proof**: the entire B07 proof surface executed — **18 files, 165 tests, 824 assertions, zero failures** — and all 115 predicates are bound to `E-MD-B07-A001-001`. The proof gate independently confirms every named surface and method exists, so no map entry points at a method nobody wrote.
- **Recovered synchronization**: five B07 tooling files were physical but unregistered, which failed the documentation gate and, through it, the relationship gate self-test's own control run. Registering them cleared both.
- **Strategy changed**: **NO**.
- **Storage**: not inspected, not mutated. No database was mutated in this session.
- **Evidence**: `E-MD-B07-A001-001`, issued and registered. **Closure**: `SC-MD-B07-A001-001` **not issued**.
- **Why closure is withheld**: MariaDB is unreachable on this host, so `MigrationIntegrityAndDriftTest` skips and the two migrations this attempt added cannot be shown to be applied to the deployed schema. This declaration's own closure boundary requires conformant actual schema. What was verified instead is that the SQLite mirror matches both migrations column for column, so the behavioural proof does not run against a schema the migration corpus does not declare — deployment is unproven, design is not. Classified `ENVIRONMENT_UNAVAILABLE`; no finding, dependency, or successor attempt was raised, and the two suite errors it causes belong to `MD-B19` and `MD-B16` surfaces that were green before the outage and were not edited to survive it.

## Resume reconciliation after repository-comprehension control-plane audit

- The issued `E-MD-B07-A001-001` remains immutable and is not rewritten. Its 115/115 semantic predicate proof remains the current B07 behavioral evidence, but its documentation-integrity subclaim is insufficient for closure because the executable gate did not enforce every invariant required by `DOCUMENT_INTEGRITY_GATE_STANDARD.md`.
- `DOCUMENT_ID_REGISTRY.csv` is reconciled append-only from 844 to 866 registrations for the pre-existing corpus using the next unused `MD-DOC-*` identities and the already-canonical Document Role rows; the new read-only B07 deployed-schema probe is then registered as the 867th physical/role/ID/current-verification document (`MD-DOC-00912`). No existing document identity is replaced or duplicated.
- `MarketDataDocumentationIntegrityGate.php` now enforces Document ID completeness/uniqueness/role consistency, current-verification completeness/uniqueness, and canonical Stage Register shape/single-resume structure.
- `MarketDataRelationshipIntegrityGateSelfTest.php` now mutates away a Document ID, duplicates a Document ID, and removes a canonical stage row; each mutation must make the real documentation gate fail before the control-plane proof can be accepted. `MarketDataB07DeployedSchemaProbe.php` adds a read-only local MariaDB proof for the exact B07 migration-ledger, column, and index contract because migration-file/ledger proof alone cannot establish deployed schema shape.
- `MD-DEP-0004` is synchronized to the already-proven B07 normalization state: 502 reference-only mixed-classification members across 13 unopened stages. Dependency lifecycle remains `OPEN_NON_BLOCKING` for those later stage-entry obligations.
- This is a control-plane/test-tooling and registry reconciliation inside the existing A001 impact envelope; strategy meaning is unchanged. No application source or migration is altered by this reconciliation.
- Closure impact: prior documentation-gate PASS is not reused as proof of the newly enforced invariants. A local proof cycle must execute the hardened documentation gate/self-test, regenerate CURRENT_STATE consistently, run `MarketDataB07DeployedSchemaProbe.php` against reachable MariaDB, prove migration drift/ledger state, re-run the exact B07 proof surface and governance gates, and run the full suite. Only returned actual outputs may support successor/current evidence and `SC-MD-B07-A001-001`.


## Returned local proof and final closure result

- Returned proof package: `LOCAL-B07.zip`, SHA-256 `F4C1D9E9BD4B3217BED85C116EAE497A029014197EF1E0F9D98588F0E9F80723`. Exact per-file paths/hashes are governed by `E-MD-B07-A001-002`; raw console logs remain external under `manual_proof/**`.
- Repository identity: actual local Git HEAD `4aa5679a2f60d2b3e393b17f0647c3526e90d5f4`, matching the expected source handoff. Post-test status contains only the known A001 patch plus `manual_proof/`; no generator/test-created tracked drift was observed.
- MariaDB: reachable (`tradeaxis`, `10.4.27-MariaDB`). Both B07 migrations are applied. The exact read-only B07 deployed-schema probe reports every required column/index check `PASS`. `MigrationIntegrityAndDriftTest` executes **9 tests / 21 assertions** with no skip.
- Control plane: returned hardened documentation gate, relationship gate, and 18-case mutation/self-test all pass. The pre-issuance corpus is 867 physical / role / Document ID / current-verification rows; CURRENT_STATE regeneration is deterministic at the returned pre-closure hash.
- B07 proof: all 18 proof files re-execute **165 tests / 824 assertions**, all exit 0. Traceability remains **115/115**, zero unbound, zero transitional/pending B07 applicability and zero B07 mixed-classification debt.
- Full suite: **1819 tests / 17215 assertions**, 0 failures, 0 errors, 0 skipped, exit 0.
- Immutable evidence discipline: `E-MD-B07-A001-001` was not edited. New additive `E-MD-B07-A001-002` captures the returned proof and discharges the prior environment blocker.
- Final outcome: the closure boundary is satisfied; `SC-MD-B07-A001-001` is issued. `MD-B08` remains `NOT_STARTED` in this work unit.
