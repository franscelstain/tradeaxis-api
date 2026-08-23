# Change Impact Declaration — `MD-B08-A001`

- ID: `CI-MD-B08-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B08` / `MD-B08-A001` / `MD-B08-A001-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260822-001`
- Starting repository revision: `b3e8b6c772b38dcab96d09e96b17b8b7bcf67ffc`
- Starting working tree: `CLEAN`
- Stage precondition: `SC-MD-B07-A001-001`
- Dependency: `MD-DEP-0004` stage-entry applicability/ownership/classification normalization
- Status: `OPEN — IN_PROGRESS`
- Strategy meaning change: `NO`

## Objective

Revalidate and, only where current authority requires it, remediate the source-resilience and controlled-recovery boundary. MD-B08 owns acquisition retry/backoff/throttle/source-protection behavior, failure classification, partial-tolerant acquisition, quarantine/no-auto-repair behavior, explicit manual recovery boundaries, and source-state traceability. It must fail safely without synthetic data, silent readable state, implicit source switching, or provider limits becoming domain limits.

## Stage-entry traceability state

- Current active B08 assignment before normalization: **324 rows**.
- Current provisional classification: **145 REQUIRED** plus **179 REFERENCE_ONLY**.
- Current provisional executable applicability: **13 MANDATORY** plus **132 `MANDATORY_OR_CONDITIONAL`**.
- Current B08 mixed-classification entry debt reported by `MarketDataClassificationConsistencyGate`: **18 reference-only members**.
- The existing Stage Register `0/132` figure is provisional and is not accepted as the final denominator.
- Entry must resolve every transitional applicability value, compose context-dependent fragments into semantic predicates, resolve all 18 mixed-classification members, confirm the actual proof-owning stage, move downstream-owned predicates without reducing obligations for convenience, and recompute the B08 denominator before any coverage claim.

## Authority scope

Primary semantic owner:

- `MD-S029` — EOD Source Operational Resilience Contract.

Supporting current strategy owners assigned to B08:

- `MD-S040` — Manual File Publishability Policy.
- `MD-S053` — Source Data Acquisition Contract.
- `MD-S058` — Trading Status Source Contract.
- `MD-S059` — Yahoo Finance Bootstrap Source Strategy.
- `MD-S067` — Error Taxonomy and Run Status Decision Table.
- `MD-S085` — Reason Codes Registry.

Governance controls include the current verification epoch, strategy freeze, Stage Register, traceability/applicability standard, proof-ownership rules, residue standard, dependency registry, work-record/relationship standards, and closure-manifest standard.

## Impact assessment

- Strategy: no strategy byte change is authorized or expected. Any true strategy contradiction stops work at the authority boundary.
- Application/runtime: inspect all API/range/manual acquisition paths for retry classification, exponential backoff, throttle, effective concurrency ceiling, circuit-breaker/source-protection behavior, partial-tolerant handling, explicit recovery separation, immutable observation continuity, and fail-closed terminal behavior. Existing code is `EXISTING_UNVERIFIED`.
- Configuration: inspect current governed retry/backoff/throttle/circuit-breaker configuration and prove that no provider limitation or operator convenience weakens domain behavior.
- Manual recovery: one-date operational rescue remains explicit; planned historical fill/replay/correction remains a separate workflow. Manual input cannot become a silent multi-day continuity source or bypass provenance/schema/coverage/publication gates.
- Quarantine/no-auto-repair: malformed, wrong-date, stale, unverifiable, conflicting, or source-failed data must remain explicit failure/quarantine/degraded evidence; no synthetic bars, previous-date substitution, scale auto-repair, cross-source voting/averaging, or silent readable state.
- Provider-neutral boundary: provider reason/detail vocabulary may exist inside adapter telemetry, but downstream semantic states and failure outcomes must remain governed and provider-neutral.
- Tests: revalidate positive and negative behavior. Add/repair tests only where actual executable gaps exist, including all acquisition paths rather than only single-date paths. Historical PASS is not inherited.
- Tooling: add stage-specific normalization, traceability and proof tooling sufficient to make the final denominator and proof ownership executable rather than narrative.
- Traceability: current predicates remain NOT_ASSESSED until fresh proof is admitted. No MD-B07 predicate PASS is inherited.
- Findings/dependencies: only the MD-B08 portion of `MD-DEP-0004` may be discharged here. Global dependency state remains open for unopened stages. Any new executable defect that is remediable in this attempt is fixed before documentation-only handling.
- Residue: inspect obsolete acquisition paths, retry/circuit bypasses, manual-source shortcuts, synthetic repair, latest-wins/source-voting behavior, stale provider-limit assumptions, and obsolete tests/tooling in the B08 surface.
- Storage: no broad `storage/**` scan. Raw artifacts are requested only if fresh B08 runtime proof makes a specific artifact material.
- Predecessor: MD-B07 is a valid stage precondition only. B08 changes that actually invalidate a B07 invariant must use current invalidation/revalidation governance; immutable predecessor records are never rewritten.

## Local-runtime boundary

This ChatGPT workspace can perform source/static inspection and standalone governance tooling, but it cannot establish the user's local Lumen/PHPUnit/MariaDB runtime result. Fresh local execution will be requested only after all non-local B08 remediation and proof preparation is complete. No local output means no runtime PASS and no B08 closure.

## Closure boundary

MD-B08 may close only after: a final normalized denominator with zero B08 transitional/pending applicability and zero B08 mixed-classification entry debt; conformant implementation/configuration; explicit positive and fail-closed proof for every owned predicate; no harmful executable residue; fresh governed evidence; complete relationships; current traceability/proof binding; all required integrity/governance gates; and the required current full-suite criterion. `MD-B09` must remain unopened until that closure is issued.
## Stage-entry normalization result

The governed MD-B08 entry review is complete for this attempt:

- reviewed B08-related rows: **326**;
- final current MD-B08 mandatory denominator: **138**;
- moved to actual downstream proof owners: **90**;
- reference/context rows: **98**;
- additive semantic fragments recovered from current strategy: **2** (`MD-S029-R0207` dedup and `MD-S029-R0208` run_id);
- B08 transitional applicability: **0**;
- B08 mixed-classification entry debt: **0**;
- remaining `MD-DEP-0004` backlog after B08 entry: **484 members across 12 unopened stages**.

The 138 B08 predicates remain `NOT_ASSESSED` with no current evidence binding until fresh local runtime proof is returned and admitted.

## Executable remediation performed before local proof

Targeted source inspection and proof-hardening found B08-owned executable defects and audit-visibility gaps. They were remediated inside the current A001 attempt before requesting runtime proof:

1. Yahoo single-date acquisition had source protection, but range and benchmark fanout did not apply the circuit breaker consistently. All three fanout paths now use the shared retry/throttle/source-protection boundary.
2. Circuit-breaker open state previously supplied an unregistered terminal reason (`RUN_SOURCE_CIRCUIT_BREAKER_OPEN`). Breaker state is now telemetry; the terminal reason remains the registered underlying source failure such as timeout/rate-limit. The B08 reason-code scanner is deliberately limited to source-adapter `RUN_*` return values so downstream B10/B19 vocabulary is not pulled into B08.
3. The breaker had an implicit minimum-sample floor in addition to the registered `market_data.provider.circuit_breaker_error_rate`. That hidden second threshold was removed. The breaker now evaluates the observed ratio with the authority-required strict crossing and fails closed as `CONFIG_INVALID` when the configured ratio itself is invalid.
4. The effective retry budget was silently clamped with `min(3, api_retry_max)` in the acquisition/pipeline surface. The clamp was removed so the registered retry budget remains the effective policy; retry remains limited to transient timeout/rate-limit classes.
5. `source_priority`, active-source decision, retry-attempt count, `failure_class_summary`, source-protection state, attempted/unattempted acquisition-unit counts, and breaker root-cause context are now propagated through append-only run-event telemetry, run-note recovery, evidence projection/export, backfill/command recovery context, and sector-index operational output. Failure-class summary keeps transient failed attempts visible even when a later retry succeeds.
6. No second provenance schema was introduced. Existing immutable B07 observation truth (`md_source_observations` and rejected-row lineage) is projected into evidence as observation count/reference hash, payload/schema references, validation/outcome summary, and rejection count/reasons.
7. The manual-file recovery adapter carries the same provider-neutral audit semantics (`SECONDARY_CONTROLLED_RECOVERY`, explicit source decision, zero retry attempts and failure classification) without acquiring API identity or bypassing provenance rules.

No strategy or governance authority document was changed. No migration was added or modified.

## Pre-local proof state

- Traceability normalization gate: static PASS at **138 mandatory / 90 moved / 98 reference**.
- Classification consistency gate: static PASS; B08 debt **0**; downstream backlog **484 / 12**.
- Proof-readiness gate: static PASS at **138 mapped / 138 runtime pending / 0 premature SATISFIED / 0 missing surface / 0 missing method**.
- B08 implementation invariant gate: static PASS for configured breaker threshold, no hidden sample floor, no hidden retry clamp, all required fanout protection, audit telemetry propagation, immutable observation projection, and scoped terminal-reason behavior.
- Proof mutation self-test: static PASS and fails closed for denominator reduction, wrong owner/context, premature runtime satisfaction, invalid breaker reason, hidden retry clamp, hidden sample floor, fanout-protection removal, missing audit telemetry and missing proof-map rule.
- Runtime-dependent proof: **PENDING LOCAL EXECUTION**.
- Closure: **WITHHELD**.


## Returned local-proof remediation cycle R1

The first local proof package was read from its actual eight returned files. Environment, fail-closed coverage, and the pre-remediation control plane remained valid, but source-protection, telemetry-integration, and the full suite exposed implementation/test-tooling defects. The attempt remains `MD-B08-A001`; this is not an attempt failure or re-entry.

Root-cause remediation is intentionally minimal and authority-bound:

1. **Breaker ratio denominator.** The first implementation divided failures by attempts-so-far. With a strict configured threshold, a first failed acquisition unit therefore appeared as a 100% failure ratio and could stop a partial-tolerant fanout immediately. The breaker now applies the single registered threshold to failed planned acquisition units divided by the planned acquisition universe. This preserves the locked partial-tolerant rule, retains strict `>` semantics, introduces no sample floor/second threshold, and still stops the remaining universe once the configured share of planned units has failed.
2. **Benchmark root-cause preservation.** Empty benchmark rows used PHP array union when adding `RUN_SOURCE_NO_VALID_DATA`; because request telemetry already contained `final_reason_code => null`, the union retained null and the later aggregate fell back to timeout. The merge now explicitly overrides the request-success placeholder with the actual rejected-row reason. Breaker telemetry remains separate from terminal reason semantics.
3. **Provider-boundary static tests.** B08 makes generic source-protection state audit-visible above the adapter. Earlier date-driven tests treated the generic identifier `circuit_breaker` itself as Yahoo/provider vocabulary and therefore flagged correct provider-neutral telemetry. The tests now continue to prohibit actual provider request-shape/transport quirks (`period1`, `period2`, `includePrePost`, adapter retry implementation) above the adapter without prohibiting governed generic resilience telemetry.
4. **Audit-projection test ownership.** Immutable observation export remains repository-owned. A static test incorrectly required `MarketDataEvidenceExportService` to call `exportRunSourceObservationAudit` directly even though the repository already composes it into source-attempt telemetry. The guard now proves the method on the repository and the `source_observation_audit` projection on the export service, preserving the single-truth boundary.
5. **Authorized telemetry expectations.** Evidence/backfill/manual integration tests that asserted pre-B08 summary shapes were updated to verify the newly required audit-visible telemetry instead of requiring those fields to be absent. No implementation field was removed merely to preserve a stale test snapshot.
6. **Current-corpus mutation tests.** The scope-boundary mutation test no longer hard-codes the pre-B08 global matrix row count; it proves exactly one row is removed from the current matrix. The alias-boundary scanner now excludes generic `closure_eligibility.eligible` JSON metadata from the consumer compatibility-alias measurement, avoiding a false positive in immutable B07 evidence without rewriting that evidence or weakening the actual compatibility-field scan.
7. **Proof-gate strengthening.** B08 implementation invariants now require the planned-universe denominator. A new mutation replaces it with attempts-so-far and must fail closed, so the local-proof regression cannot silently return.

No strategy authority, governance authority, migration, immutable Baseline Lock, B07 evidence, or B07 closure record was modified by this remediation cycle. The 138-predicate denominator and proof ownership are unchanged pending corrected local runtime proof.


## Returned local-proof remediation cycle R2

The actual `LOCAL-B08-R1` package was inspected file-by-file. R1 source-protection, focused regressions, and control-plane proof remained green. The telemetry-targeted run and the full suite each failed on the same single assertion in `MarketDataPipelineIntegrationTest::test_run_daily_api_success_after_retry_exports_source_context_in_run_evidence`; no second runtime root cause was present.

The R2 correction keeps the same `MD-B08-A001` attempt and resolves two related authority-backed inconsistencies:

1. **Evidence source-summary projection.** The adapter/pipeline/run-event path already carried B08 resilience telemetry, and `buildSourceContext()` already reconstructed it, but `MarketDataEvidenceExportService::buildSourceSummaryString()` still emitted only the legacy subset. The summary projection now includes `source_priority`, active-source decision, `retry_attempt_count`, and `failure_class_summary` while retaining the existing transport/protection fields. This fixes the executable export boundary rather than weakening the test.
2. **Canonical primary-source classification.** Current strategy locks `api_free/yahoo_finance` as the bootstrap **primary** source. The adapter previously treated telemetry whose source mode was `api_free` as secondary because it recognized only the internal alias `api` as primary, while other pipeline/evidence paths defaulted to `PRIMARY/api`. The canonical B08 audit representation is now `source_priority=PRIMARY` and `active_source_decision=api_free` for either internal `api` or governed `api_free`; manual-file recovery remains `SECONDARY_CONTROLLED_RECOVERY/manual_file`. Tests and static proof were corrected to that authority-backed representation.
3. **Proof hardening.** The B08 proof family now explicitly owns the success-after-retry evidence-export integration test. Static invariants require both `api` and `api_free` to map to the primary source, require the pipeline decision label `api_free`, and require the evidence source-summary projection to contain all four mandatory audit fields. Mutation self-tests fail closed if `api_free` is misclassified as secondary or if the evidence summary drops a required audit field.

R2 does not change strategy authority, governance authority, migrations, the immutable Baseline Lock, B07 records, the normalized B08 denominator, or `MD-DEP-0004` ownership. Runtime-dependent B08 predicates remain `NOT_ASSESSED` until corrected local proof returns.
