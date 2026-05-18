# LUMEN_CONTRACT_TRACKER

## ACTIVE SESSION

ACTIVE SESSION:
- Audit Docs Synchronization

[SESSION_STATUS] LOCKED

[SESSION_SCOPE]
- Re-open the canonical audit-docs synchronization contract after sessions 1-8 to synchronize implementation status, contract tracker, inventories, and static guards.
- Preserve canonical contracts and previous LOCKED claims only where concrete evidence already exists.
- Record post-session proof honestly, including carried operator-local proof and current container runtime blockers.
- Contract is LOCKED after local/operator post-guard-scope PHPUnit proof was supplied after this patch.

[SESSION_GOAL]
- Ensure audit docs remain the source of truth after the latest hardening sequence without duplicating contracts, overwriting history, or claiming unsupported runtime proof.

[SESSION_NOTES]
- Container PHP 8.4.16 is unsupported for evidence output and fails closed via `ENV_UNSUPPORTED_PHP_VERSION`.
- Container PHPUnit is blocked by missing `dom`, `mbstring`, `xml`, and `xmlwriter`.
- Existing locked contracts for sessions 1-8 retain their own local evidence and final rules.
- `AUDIT_DOCS_SYNCHRONIZATION_CONTRACT` is the canonical owner for this synchronization pass; no duplicate contract is introduced.
- The post-session synchronization lock condition is satisfied by the final local StaticGuard and full MarketData suite PASS after this patch.

[RUNTIME_ENVIRONMENT]
- Container PHP version: PHP 8.4.16.
- Container artisan status: EXPECTED_FAIL_CLOSED with clean `ENV_UNSUPPORTED_PHP_VERSION`; not a runtime PASS.
- Container PHPUnit status: BLOCKED_CONTAINER_RUNTIME_ENV due to missing dom, mbstring, xml, and xmlwriter.
- Operator-local historical proof available: StaticGuard OK (164 tests, 3702 assertions) and full MarketData OK (435 tests, 6299 assertions) from Ops Environment Baseline closure.
- Runtime authority for LOCKED post-session synchronization: operator-local post-guard-scope proof supplied on 2026-05-18.

---
## OPERATIONAL STATUS

[CURRENT_AUDIT_MODE]
- CLEAN_START_RETEST

[HISTORICAL_STATUS_POLICY]
- Previous DONE/LOCKED contract claims are not copied as current status without fresh scoped evidence.
- Contract status is rebuilt one concern at a time and mapped to implementation evidence.
- Revalidated contracts must be represented as canonical entries, not repeated hotfix/session fragments.

[DEFAULT_RULE]
- No contract may be marked DONE without current implementation evidence.
- No contract may be marked LOCKED without FINAL_RULE and VALIDATED evidence.
- One contract concern must have one canonical tracker entry.

---

## CURRENT WORKING CONTRACT


- AUDIT_DOCS_SYNCHRONIZATION_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-18

  [RELATED_IMPLEMENTATION] Audit Docs Synchronization

  [REVIEW_STATUS] POST_SESSION_1_8_LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-08 -> Contract opened as canonical audit-docs synchronization contract under audit governance.
  - 2026-05-08 -> Static trace found active-session drift, missing audit-docs canonical contract, missing dedicated inventory, and no dedicated guard preventing audit docs drift.
  - 2026-05-08 -> Enforcement patch added active-session synchronization, implementation/tracker alignment, audit-docs inventory, governance hard rules, registry/seed sync verification, latest full-suite evidence recording, and `AuditDocsSynchronizationStaticGuardTest.php`.
  - 2026-05-08 -> Contract held at ENFORCED because uploaded ZIP had no `vendor/`; targeted and full local PHPUnit were required before LOCKED.
  - 2026-05-08 -> Operator-local first retest found two AuditDocs/static/full-suite failures: the guard missed unicode-arrow historical contract headings and the inventory lacked the exact phrase `not a new container PHPUnit run`.
  - 2026-05-08 -> Follow-up patch fixed canonical contract parsing for both `->` and `→`, added the exact inventory evidence phrase, and preserved the first failed retest as reconciliation history.
  - 2026-05-08 -> Contract promoted from ENFORCED to LOCKED after operator-local validation PASS: `AuditDocsSynchronizationStaticGuardTest.php` OK (9 tests, 153 assertions); `AuditDocs` filter OK (9 tests, 153 assertions); `StaticGuard` filter OK (93 tests, 2160 assertions); `Evidence` filter OK (39 tests, 678 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (358 tests, 4711 assertions).
  - 2026-05-18 -> Contract re-opened as the current active contract for post-session 1-8 synchronization after Ops Environment Baseline closure.
  - 2026-05-18 -> Contract status set to ENFORCED, not LOCKED, because this patch changes docs/static guards and current container cannot run PHPUnit; fresh operator-local proof is required after this patch.
  - 2026-05-18 -> Post-session inventory added and audit-docs/static-guard expectations synchronized so historical sessions remain locked without being pinned as active session.
  - 2026-05-18 -> Operator-local partial rerun after the first post-session patch passed `php artisan list`, direct AuditDocs guard OK (9 tests, 261 assertions), and `AuditDocs` filter OK (9 tests, 261 assertions), but `StaticGuard` failed because `OpsEnvironmentBaselineStaticGuardTest.php` still demanded historical ops proof markers directly from both active audit lumen docs.
  - 2026-05-18 -> Contract remains ENFORCED; `OpsEnvironmentBaselineStaticGuardTest.php` was scoped so the historical Ops Environment proof markers can live in the ops evidence surface while current audit lumen docs remain aligned to Audit Docs Synchronization.
  - 2026-05-18 -> Operator-local final post-guard-scope validation passed: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (164 tests, 3721 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` -> OK (435 tests, 6318 assertions). Contract promoted from ENFORCED to LOCKED.

  [DEFINED]
  - Audit docs are the official implementation-status and contract-status record for market-data.
  - Audit docs must be updated append-only after every material market-data behavior, test, command, evidence, replay, registry, ops, or audit change.
  - Current active session/current working entry must represent the latest active synchronization concern.
  - Historical DONE/LOCKED claims must remain preserved but cannot be reused as proof for the current patch unless clearly marked as carried historical evidence.
  - Current post-session synchronization is LOCKED because operator-local StaticGuard and full MarketData PHPUnit proof was supplied after this patch.

  [IMPLEMENTED]
  - Active session changed from Ops Environment Baseline to Audit Docs Synchronization in both audit lumen files.
  - `AUDIT_DOCS_SYNCHRONIZATION_CONTRACT` remains the only canonical audit-docs synchronization contract.
  - `AUDIT_DOCS_SYNCHRONIZATION_POST_SESSION_1_8_INVENTORY.md` added for the eight-session synchronization matrix and current proof/risk state.
  - Static guards updated to preserve Ops Environment and Config / ENV historical proof without requiring those sessions to stay active.

  [ENFORCED]
  - `AuditDocsSynchronizationStaticGuardTest.php` checks active-session alignment, current-working positioning, canonical contract uniqueness, implementation/tracker synchronization, governance rules, latest evidence markers, and pending-lock requirements for the post-session audit-docs sync.
  - `OpsEnvironmentBaselineStaticGuardTest.php` and `ConfigEnvGovernanceCleanupStaticGuardTest.php` now check historical DONE/LOCKED evidence instead of hard-pinning active session to Ops Environment Baseline.
  - Current contract status is LOCKED because final local proof is recorded.

  [VALIDATED]
  - Container static trace completed across audit docs and related guards.
  - Container `php artisan list` -> expected clean fail-closed `ENV_UNSUPPORTED_PHP_VERSION`; not a runtime PASS.
  - Container PHPUnit -> BLOCKED_CONTAINER_RUNTIME_ENV because `dom`, `mbstring`, `xml`, and `xmlwriter` are missing.
  - Prior Fail-Safe Behavior local proof retained: full `vendor/bin/phpunit tests/Unit/MarketData` OK (349 tests, 4558 assertions). This is not a new container PHPUnit run.
  - Prior Audit Docs Synchronization local proof retained: `AuditDocsSynchronizationStaticGuardTest.php` OK (9 tests, 153 assertions); `AuditDocs` OK (9 tests, 153 assertions); `StaticGuard` OK (93 tests, 2160 assertions); full MarketData OK (358 tests, 4711 assertions). This is not a new container PHPUnit run.
  - Prior Operational Readiness local proof retained: full `vendor/bin/phpunit tests/Unit/MarketData` OK (368 tests, 4927 assertions). This is not a new container PHPUnit run.
  - Latest Ops Environment Baseline local proof retained: `StaticGuard` OK (164 tests, 3702 assertions); full MarketData OK (435 tests, 6299 assertions). This is not a new container PHPUnit run.
  - Operator-local partial rerun after the first post-session patch: `php artisan list` clean; `AuditDocsSynchronizationStaticGuardTest.php` OK (9 tests, 261 assertions); `AuditDocs` filter OK (9 tests, 261 assertions); `StaticGuard` FAIL (164 tests, 3704 assertions, 1 failure) caused by stale OpsEnvironment guard scoping.
  - Post-session 1-8 local proof after this guard-scope patch: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (164 tests, 3721 assertions).
  - Post-session 1-8 local proof after this guard-scope patch: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (435 tests, 6318 assertions).

  [FINAL_RULE]
  - LOCKED. Audit docs must remain synchronized, append-only, non-duplicated, and evidence-backed. DONE/LOCKED claims must stay tied to concrete operator-local proof, not container blocked status or historical assumptions.
  - Future audit-docs synchronization changes must update implementation status, contract tracker, post-session inventory, and audit static guards together, then rerun targeted AuditDocs/static guard checks plus full `tests/Unit/MarketData`.

  [LOCK_CONDITION]
  - SATISFIED. Exact post-guard-scope local proof is recorded: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (164 tests, 3721 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` -> OK (435 tests, 6318 assertions).

  [NEXT_ACTION]
  - Keep this contract LOCKED. Reopen only if future audit-doc, active-session, contract-tracker, inventory, or audit static-guard changes create new drift.

- CONFIG_ENV_GOVERNANCE_CLEANUP_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-18

  [RELATED_IMPLEMENTATION] Config / ENV Governance Cleanup

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-17 -> Contract opened to lock schema/config/env cleanup and prevent stale `Yes/No` semantics for numeric schema fields.
  - 2026-05-17 -> Schema truth for `tickers.is_active` confirmed as boolean/TINYINT `1/0`.
  - 2026-05-17 -> Runtime config renamed from `active_yes_value` to numeric `active_value` and env templates renamed from `MARKET_DATA_TICKERS_ACTIVE_YES_VALUE` to `MARKET_DATA_TICKERS_ACTIVE_VALUE`.
  - 2026-05-17 -> Unused multi-source config surfaces pruned while preserving locked no-mixed-source behavior.
  - 2026-05-17 -> Static guard and repository behavioral guard added; container PHPUnit remained blocked by missing PHP extensions.
  - 2026-05-18 -> Operator-local runtime proof supplied and passed for direct config/env guard, ticker repository test, targeted Config/Env/Ticker/Eligibility/Coverage/StaticGuard/DbIntegrity/Publication/Pointer/Read-side filters, and full MarketData suite.
  - 2026-05-18 -> `--filter "SourceMode"` returned `No tests executed!`; this is documented as non-blocking because full MarketData suite passed and source-mode non-regression remains covered by broader static/contract guards.

  [DEFINED]
  - Config/env must not conflict with schema truth.
  - Numeric/boolean-like schema fields must not be configured through stale string values such as `Yes` or `No`.
  - Every active `MARKET_DATA_*` key must exist in config and env templates and have a runtime caller or documented operational purpose.
  - Unused/stale config/env keys must be pruned or explicitly documented as deprecated/pruned.
  - Cleanup must not weaken source-mode, coverage, read-side pointer, publication, replay, evidence, or DB integrity contracts.

  [IMPLEMENTED]
  - `CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md` records schema/config alignment, inventory, pruning, caller trace, patch, and validation matrices.
  - `config/market_data.php` uses numeric `market_data.tickers.active_value` and prunes unused multi-source config keys.
  - `.env.example` and `.env.testing` are synchronized with `config/market_data.php`.
  - `TickerMasterRepository` uses strict active-value filtering.
  - `ConfigEnvGovernanceCleanupStaticGuardTest.php` and `TickerMasterRepositoryTest.php` enforce the cleanup.

  [ENFORCED]
  - Static guard rejects reintroduction of `MARKET_DATA_TICKERS_ACTIVE_YES_VALUE`, `active_yes_value`, ticker `Yes` fixtures, env/config drift, and active stale multi-source config keys.
  - Repository behavioral test proves stale `Yes` rows do not count as active ticker universe rows.
  - Source mode non-regression remains tied to `IMPORT_PROMOTE_SEPARATION_CONTRACT`.
  - Read-side non-regression remains tied to `READ_SIDE_POINTER_ENFORCEMENT_CONTRACT`.
  - DB integrity FK/implicit policy non-regression remains tied to `DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_CONTRACT`.

  [VALIDATED]
  - Container syntax passed for changed PHP files.
  - Container PHPUnit is blocked by missing `dom`, `mbstring`, `xml`, and `xmlwriter`; container is not the runtime authority for this LOCKED claim.
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php` -> OK (10 tests, 118 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData/TickerMasterRepositoryTest.php` -> OK (1 test, 1 assertion).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "Config"` -> OK (14 tests, 140 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "Env"` -> OK (11 tests, 142 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "Ticker"` -> OK (12 tests, 145 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "Eligibility"` -> OK (9 tests, 47 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "Coverage"` -> OK (57 tests, 662 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "SourceMode"` -> No tests executed; non-blocking because full suite passed and source-mode non-regression is covered by broader guards.
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (156 tests, 3601 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "DbIntegrity"` -> OK (11 tests, 880 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` -> OK (106 tests, 1266 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` -> OK (82 tests, 1161 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "Readside"` -> OK (13 tests, 258 assertions).
  - Operator-local full suite: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (427 tests, 6198 assertions).

  [FINAL_RULE]
  - LOCKED. Market-data config/env must remain schema-aligned, typed, caller-traced, synchronized across config and env templates, pruned of stale/unused keys, and protected against `Yes/No` ticker-active regression.
  - LOCKED. `tickers.is_active` must remain numeric/boolean-like (`1/0`) in config, docs, fixtures, and repository filtering unless a future schema migration explicitly changes the type and is validated by a new contract.
  - LOCKED. Config/env cleanup must not weaken source-mode, coverage, read-side pointer, publication, replay, evidence, or DB integrity contracts.

  [NEXT_ACTION]
  - No remaining runtime blocker for this contract. Future config/env/schema/caller changes must rerun the direct config/env guard, impacted targeted filters, and full MarketData suite before this contract remains LOCKED.

- DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-17

  [RELATED_IMPLEMENTATION] DB Integrity FK / Implicit Integrity Decision

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-17 -> Contract opened as a scoped hardening layer under existing DB integrity governance.
  - 2026-05-17 -> Contract explicitly rejects the false claim that the whole schema sync failed; only live artifact relation policy needed classification.
  - 2026-05-17 -> Relation decisions were classified as `EXPLICIT_FK_REQUIRED`, `IMPLICIT_GUARD_ACCEPTED`, or `HYBRID_REQUIRED`; no relation is left `TBD` without blocker in the new inventory.
  - 2026-05-17 -> Static guard added to preserve the policy and prevent accidental live artifact FK/implicit guard drift.
  - 2026-05-17 -> Operator-local PHPUnit proof supplied and passed: direct DbIntegrity FK/Implicit static guard, DbIntegrity filter, StaticGuard filter, and full MarketData suite.

  [DEFINED]
  - Every live artifact relation must be either explicitly DB-enforced, implicitly guarded with tests, hybrid, no-relation, or deferred with reason.
  - Stable immutable proof relations may use FK.
  - Phase-dependent lifecycle relations may stay implicit only when repository/service/static/evidence/replay tests guard them.
  - Current read-side contract remains pointer-only and must not be relaxed by this DB integrity decision.

  [IMPLEMENTED]
  - `DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md` records the decision matrices.
  - `Database_Schema_MariaDB.sql` documents the `HYBRID_REQUIRED` policy and scoped audit interpretation.
  - `DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` guards inventory, schema comments, existing explicit FKs, implicit guard surfaces, audit-doc local proof status, and anti latest/MAX shortcuts.

  [ENFORCED]
  - Explicit FKs remain required for pointer publication and immutable history publication relations.
  - Current live artifact publication/run/ticker relations are not upgraded to FK in this session; they remain mandatory context plus implicit guard.
  - Publication/run mirror, pointer run/version, correction lineage, evidence historical resolver, and replay historical resolver stay reason-coded implicit integrity.

  [VALIDATED]
  - Container syntax passed: `php -l tests/Unit/MarketData/DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` -> No syntax errors detected.
  - Container PHPUnit is blocked by missing `dom`, `mbstring`, `xml`, and `xmlwriter`; container is not the runtime authority for this LOCKED claim.
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData/DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` -> OK (5 tests, 434 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "DbIntegrity"` -> OK (11 tests, 874 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (146 tests, 3470 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (416 tests, 6066 assertions).

  [FINAL_RULE]
  - LOCKED. The final rule is `HYBRID_REQUIRED`: explicit FK only for stable pointer/history publication proof; implicit guard required for phase-dependent live artifact/current/correction/replay/evidence relations; no raw/latest/MAX/current-pointer bypass may be introduced.

  [NEXT_ACTION]
  - No remaining runtime blocker for this contract. Any future FK expansion must be handled as a separate migration/data-cleanup contract with fresh local runtime proof.

- REPLAY_HISTORICAL_DETERMINISM_HARDENING_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-17

  [RELATED_IMPLEMENTATION] Replay Historical Determinism Hardening

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-15 -> Contract opened as hardening edge case under existing Replay Determinism and Evidence Historical Lineage Completeness contracts.
  - 2026-05-15 -> Gap found: replay verify actual-state resolution was current-pointer dependent and could lose historical publication context after pointer movement.
  - 2026-05-15 -> Patch added replay-specific historical actual-state resolver, publication-scoped artifact proof, historical-aware replay context fields, reason codes, inventory, and static guard.
  - 2026-05-17 -> Guard expectation drift was fixed after local feedback: repository-method assertion corrected and audit-docs reason-code sync count updated to 324.
  - 2026-05-17 -> Operator-local ReplayHistorical, Replay, StaticGuard, and full MarketData PHPUnit proof passed; contract promoted to LOCKED.

  [DEFINED]
  - Replay historical actual-state proof may resolve a sealed historical publication by explicit selector.
  - Current replay actual-state proof must still validate current pointer.
  - Consumer read resolver must remain current-pointer-only.
  - Historical replay proof must never use latest/MAX/current fallback, raw/staging shortcut, or pointer mutation.

  [IMPLEMENTED]
  - `ReplayVerificationService::resolvePublicationForReplayActualState()` wraps evidence audit resolver for historical selector-scoped proof.
  - Replay context records current vs historical resolution mode, selector id, current pointer requirement/status, lineage status, and publication-scoped artifact scope.
  - Historical replay artifacts use evidence publication-scoped reason-code and eligibility export.
  - Historical replay reason codes are added to registry and seed; both remain synchronized at 324 entries.
  - Static guard covers resolver separation, docs, reason codes, anti latest/MAX fallback, and preservation of consumer current-pointer-only behavior.

  [ENFORCED]
  - Historical sealed publication can be compared without becoming current.
  - Historical unsealed/missing/mismatched publication fails reason-coded.
  - Current replay context remains current-pointer validated.
  - Consumer read path remains current pointer only and is not made historical-aware.

  [VALIDATED]
  - Container static syntax checks passed for changed PHP files.
  - Container PHPUnit is blocked by missing `dom`, `mbstring`, `xml`, and `xmlwriter`; container is not the runtime authority for this LOCKED claim.
  - Operator-local PHPUnit `tests/Unit/MarketData/ReplayHistoricalDeterminismHardeningStaticGuardTest.php` -> PASS; OK (6 tests, 70 assertions).
  - Operator-local PHPUnit `tests/Unit/MarketData --filter "ReplayHistorical"` -> PASS; OK (6 tests, 70 assertions).
  - Operator-local PHPUnit `tests/Unit/MarketData --filter "Replay"` -> PASS; OK (53 tests, 819 assertions).
  - Operator-local PHPUnit `tests/Unit/MarketData --filter "StaticGuard"` -> PASS; OK (141 tests, 3029 assertions).
  - Operator-local PHPUnit full `tests/Unit/MarketData` -> PASS; OK (411 tests, 5625 assertions).

  [FINAL_RULE]
  - LOCKED. Replay historical actual-state proof must be selector-scoped, lineage-validated, sealed-publication aware, publication-scoped, and independent from current pointer fallback.
  - LOCKED. Current replay and consumer read behavior must remain current-pointer validated.
  - LOCKED. Historical replay must never create MATCH by reading current publication, raw/staging/latest data, MAX/latest shortcut, or by mutating pointer state.

  [LOCK_CONDITION]
  - Satisfied for this source-of-truth ZIP by operator-local direct ReplayHistorical guard, ReplayHistorical filter, Replay filter, StaticGuard filter, and full `tests/Unit/MarketData` PASS.

- EVIDENCE_HISTORICAL_LINEAGE_COMPLETENESS_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-14

  [RELATED_IMPLEMENTATION] Evidence Historical Lineage Completeness

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-13 → Contract opened as hardening under existing evidence/replay/read-side/linkage governance, not as a duplicate consumer read contract.
  - 2026-05-13 → Static trace found evidence resolver risk: run evidence could depend on current readable publication resolution and fail for old sealed publications after pointer replacement.
  - 2026-05-13 → Patch added selector-scoped evidence audit resolver, historical publication output labels, publication-scoped historical artifact export, correction/replay historical lineage proof fields, and static guard coverage.

  [DEFINED]
  - Evidence export is an audit/proof surface and may resolve historical sealed publication by explicit selector.
  - Consumer read resolver remains current-pointer-only and must not be made historical-aware.
  - Historical evidence proof must be selector-scoped, lineage-validated, reason-coded, and publication-scoped.

  [IMPLEMENTED]
  - `EodEvidenceRepository::resolvePublicationForEvidenceAudit()` resolves explicit historical/current publication proof without using current pointer fallback.
  - `MarketDataEvidenceExportService` uses the audit resolver for run evidence and labels output with current vs historical resolution mode.
  - Correction and replay evidence include historical lineage fields.
  - `EVIDENCE_HISTORICAL_LINEAGE_COMPLETENESS_INVENTORY.md` records matrices and validation requirements.
  - `EvidenceHistoricalLineageCompletenessStaticGuardTest.php` guards separation between consumer resolver and evidence audit resolver.

  [ENFORCED]
  - Historical resolver validates publication exists, selector matches, run-publication mirror, trade date, SEALED state, source run seal, SUCCESS/READABLE/PASS state, coverage telemetry, and artifact hashes.
  - Historical artifact evidence uses `publication_id`-scoped lookup and historical table for non-current eligibility proof.
  - Unsealed/missing/mismatched historical proof fails with reason code instead of falling back to current publication.
  - Consumer resolver was not modified and remains current pointer only.

  [VALIDATED]
  - Static syntax proof passed for changed PHP files and the new static guard.
  - Container PHPUnit is blocked by missing `dom`, `mbstring`, `xml`, and `xmlwriter` extensions.
  - Initial state before local proof: operator-local targeted/full PHPUnit proof was required before this contract could become LOCKED. READY_FOR_LOCAL_RUNTIME_VALIDATION is retained here as historical transition marker.
  - Operator-local `StaticGuard` PASS after audit-doc synchronization fix: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> `OK (135 tests, 2952 assertions)`.
  - Operator-local full MarketData suite PASS after audit-doc synchronization fix: `vendor/bin/phpunit tests/Unit/MarketData` -> `OK (403 tests, 5542 assertions)`.

  [FINAL_RULE]
  - Evidence export may prove historical sealed publication only through explicit selector-scoped audit resolution.
  - Evidence audit resolver must never use current pointer fallback, latest publication fallback, raw/staging shortcut, or `MAX(date)` style lookup for historical proof.
  - Consumer read resolver must remain current-pointer-only and must not expose historical non-current publication as readable consumer data.

  [OPERATOR_LOCAL_EVIDENCE_2026_05_14]
  - Direct historical lineage static guard PASS: `OK (5 tests, 51 assertions)`.
  - Targeted Evidence/Replay/Correction/Publication/Pointer/Readable/ReadSide/CommandSurface/Integration filters PASS in operator-local environment.
  - StaticGuard/full suite failures were audit-doc synchronization failures only: implementation status active session was `Evidence Historical Lineage Completeness`, while contract tracker/current working entries still pointed to `Coverage Gate Candidate Scope Hardening`.
  - Fix1 synchronized the active session/current working contract without changing runtime evidence resolver code.
  - Operator-local `StaticGuard` PASS after fix1: `OK (135 tests, 2952 assertions)`.
  - Operator-local full `tests/Unit/MarketData` PASS after fix1: `OK (403 tests, 5542 assertions)`.

  [FINAL_CLOSURE_2026_05_14]
  - Contract promoted to LOCKED because direct historical-lineage guard, targeted Evidence/Replay/Correction/Publication/Pointer/Readable/ReadSide/CommandSurface/Integration filters, StaticGuard, and full MarketData suite all passed locally.

  [NEXT_ACTION]
  - Keep this contract locked. Future changes touching evidence export, historical publication proof, correction/replay evidence, publication-scoped artifact export, current pointer resolver, audit docs, or static guards must rerun targeted evidence/replay/read-side/static filters plus full `tests/Unit/MarketData`.

- COVERAGE_GATE_ENFORCEMENT_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-13

  [RELATED_IMPLEMENTATION] Coverage Gate Candidate Scope Hardening

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-13 -> Candidate-scope hardening opened under existing coverage gate contract; this is not coverage gate enforcement ulang.
  - 2026-05-13 -> Promote/manual promote/correction coverage path patched to resolve candidate publication context before coverage evaluation.
  - 2026-05-13 -> Candidate artifact coverage lookup patched to filter by `publication_id` and avoid current/latest/baseline fallback.
  - 2026-05-13 -> Command/evidence/replay proof surfaces now expose candidate coverage basis fields.
  - 2026-05-13 -> Operator-local first retest exposed recovery gap: finalize transaction closure did not import `$correction`, causing candidate/promotion/finalize/correction integration paths to error before proof completion.
  - 2026-05-13 -> Recovery patch imported `$correction` into finalize closure and removed duplicate canonical contract tracking by preserving the prior coverage/read-side locked history as historical context inside the tracker.
  - 2026-05-13 -> Fix1 operator-local retest passed direct candidate-scope guard and most targeted filters, but exposed remaining Promote/Finalize/Integration status regressions where direct manual promote without a candidate import produced `FAILED` instead of controlled `HELD` or successful force-replace publication.
  - 2026-05-13 -> Fix2 operator-local retest passed Finalize, StaticGuard, and Integration; remaining Promote errors were command-surface DB isolation issues where source telemetry export queried `eod_run_events` through the default MySQL connection without an output artifact request.
  - 2026-05-13 -> Fix3 made source telemetry artifact export lazy when no `output_dir` is requested, preserving operator telemetry artifact behavior while avoiding unintended DB access in command-surface summaries.
  - 2026-05-13 -> Operator-local fix3 retest passed Promote, Finalize, StaticGuard, and Integration; full suite still exposed command-surface source telemetry recovery gaps and a stale eligibility unit expectation for candidate publication coverage.
  - 2026-05-13 -> Fix4 made source telemetry DB lookup fail-safe on connection refusal, lets no-output command summaries still recover telemetry from mocked evidence repositories, and updates eligibility unit proof to expect candidate `publication_id` scoped coverage.
  - 2026-05-13 -> Operator-local final validation after fix4 passed full `vendor/bin/phpunit tests/Unit/MarketData`: `OK (397 tests, 5461 assertions)`. Contract promoted to LOCKED for candidate-scope hardening.
  - 2026-05-13 -> Fix2 keeps coverage candidate-scoped by materializing direct manual promote into a candidate publication before coverage, not by falling back to live/current baseline; pointer conflict outcomes are explicitly reason-coded before invariant validation.

  [FINAL_RULE]
  - Promote/manual promote/correction coverage must use candidate publication artifact scope. Baseline/current publication is lineage/comparison/preservation only.
  - Missing/incomplete candidate artifacts must fail/hold/not-readable and must not switch pointer.

  [VALIDATED]
  - Container `php -l` passed for changed PHP files.
  - Operator-local fix1 partial retest passed candidate-scope guard, Manual, Correction, Publication, Pointer, Evidence, Replay, and CommandSurface; remaining failures before fix2 were Promote, Finalize, StaticGuard, and Integration.
  - Operator-local fix2 partial retest passed Finalize, StaticGuard, and Integration; Promote still errored in OpsCommandSurface because no-output command summaries attempted source telemetry DB export against the default MySQL connection.
  - Recovery patch `php -l` passed for `MarketDataPipelineService.php`, `AuditDocsSynchronizationStaticGuardTest.php`, and `CoverageGateCandidateScopeHardeningStaticGuardTest.php`; fix3/fix4 `php -l` passed for `AbstractMarketDataCommand.php`, and fix4 `php -l` passed for `MarketDataPipelineServiceTest.php`.
  - Operator-local fix3 retest passed Promote, Finalize, StaticGuard, and Integration.
  - Operator-local fix4 final full-suite validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> `OK (397 tests, 5461 assertions)`.
  - Operator-local first retest FAILED before recovery patch with `Undefined variable: correction` across promote/manual/correction/finalize/publication/pointer/evidence/integration paths and audit-doc static guard failures.
  - Container PHPUnit is blocked by missing `dom`, `mbstring`, `xml`, and `xmlwriter`.

  [LOCK_CONDITION]
  - Satisfied. Operator-local targeted filters passed across candidate-scope, Promote, Manual, Correction, Finalize, Publication, Pointer, Evidence, Replay, CommandSurface, StaticGuard, and Integration surfaces; full `tests/Unit/MarketData` passed with `OK (397 tests, 5461 assertions)`.

- READ_SIDE_POINTER_ENFORCEMENT_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-12

  [RELATED_IMPLEMENTATION] Read-Side Consumer Surface Final Sweep

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [RUNTIME_ENVIRONMENT]
  - Operator-local PHP version: PHP 7.4.33
  - Operator-local PHPUnit version: PHPUnit 9.6.34
  - Required PHP extensions available locally: dom, mbstring, pdo_mysql, pdo_sqlite, xml, xmlreader, xmlwriter
  - Container PHPUnit status: BLOCKED_CONTAINER_RUNTIME_ENV due to missing dom, mbstring, xml, xmlwriter
  - Runtime authority for LOCKED: operator-local PHPUnit output, not container PHPUnit, because container PHPUnit is extension-blocked.

  [HISTORICAL_CONTEXT_2026_05_01]
  - Historical baseline is preserved inside this same canonical `READ_SIDE_POINTER_ENFORCEMENT_CONTRACT` entry, not duplicated as a second contract entry.
  - Historical related implementation: `Read-Side Enforcement / Anti Bypass Total`.
  - Historical review status: `REVIEWED_OK`.
  - Historical last updated: `2026-05-01`.
  - Historical lock proof remains below in `[HISTORY]`, `[DEFINED]`, `[IMPLEMENTED]`, `[ENFORCED]`, `[VALIDATED]`, `[FINAL_RULE]`, and `[LOCK_CONDITION]`.

  [HISTORY]
  - 2026-05-12 -> Read-Side Consumer Surface Final Sweep reopened this existing contract against the latest source-of-truth ZIP; the purpose is final consumer-surface proof, not a new read-side contract.
  - 2026-05-12 -> Static trace found no HTTP/controller/resource/dashboard/report market-data consumer; session snapshot capture and scope are the real read-side consumer surfaces and remain pointer-resolved.
  - 2026-05-12 -> Evidence/replay paths were classified as `EVIDENCE_REPLAY_AUDIT`, repair path as `ADMIN_REPAIR_DIAGNOSTIC`, and ingest/build/promote/finalize/artifact paths as `WRITE_SIDE_PRODUCER`.
  - 2026-05-12 -> Added `READ_SIDE_CONSUMER_SURFACE_FINAL_SWEEP_INVENTORY.md` and `ReadSideConsumerSurfaceFinalSweepStaticGuardTest.php` to guard this final sweep.
  - 2026-05-12 -> Container static validation passed `php -l` for changed guard files, but PHPUnit is blocked in this container by missing `dom`, `mbstring`, `xml`, and `xmlwriter`; contract cannot be promoted back to current LOCKED status for this sweep until operator-local targeted/full PHPUnit proof is supplied.
  - 2026-05-12 -> Operator-local partial final-sweep validation supplied: `ReadSide` OK (12 tests, 226 assertions), `Readable` OK (57 tests, 426 assertions), `Pointer` OK (76 tests, 1117 assertions), `Publication` OK (98 tests, 1193 assertions), `Consumer` OK (13 tests, 222 assertions), `CommandSurface` OK (49 tests, 359 assertions), `Replay` OK (43 tests, 717 assertions), and direct final-sweep guard OK (8 tests, 157 assertions).
  - 2026-05-12 -> `Evidence` and `StaticGuard` initially failed only at `ProductionValidationRuntimeProofStaticGuardTest::test_validation_inventory_requires_runtime_evidence_before_done`; the missing exact audit evidence marker was `20-command command list/full help`.
  - 2026-05-12 -> Patched the Production Validation audit wording to include the exact `20-command command list/full help` marker while preserving the locked Production Validation runtime proof.
  - 2026-05-12 -> Operator-local final rerun passed after the audit-phrase patch: `Evidence` OK (45 tests, 812 assertions), `StaticGuard` OK (124 tests, 2785 assertions), and full `vendor/bin/phpunit tests/Unit/MarketData` OK (391 tests, 5345 assertions).
  - 2026-05-12 -> Current final sweep re-promoted `READ_SIDE_POINTER_ENFORCEMENT_CONTRACT` to LOCKED for this ZIP because no consumer bypass remains and targeted/full MarketData proof passed locally.
  - 2026-05-12 -> Runtime environment baseline was recorded in the always-read audit materials: operator-local PHP 7.4.33, PHPUnit 9.6.34, required PHP extensions, and container PHPUnit blocked by missing XML/mbstring extensions.
  - 2026-05-12 -> Audit-doc correction restored the original 2026-05-01 read-side locked baseline details that had been flattened during final-sweep tracker update; history is preserved inside the single canonical contract entry rather than as a duplicate contract.
  - 2026-05-01 → Canonical read-side pointer enforcement contract opened under audit governance.
  - 2026-05-01 → Static trace confirmed the official consumer gateway is `EodPublicationRepository::resolveCurrentReadablePublicationForTradeDate($tradeDate)`.
  - 2026-05-01 → Gap found: pointer-scoped eligibility/evidence reads did not uniformly require `coverage_gate_state = PASS` and run mirror fields matching pointer publication metadata.
  - 2026-05-01 → Gap fixed in repository predicates and guarded through integration/static tests.
  - 2026-05-01 → Contract document synchronized to explicitly include coverage PASS and run mirror validation.
  - 2026-05-01 → Operator local PHPUnit evidence found correction/fallback regressions when consumer-only run mirror predicates were added to the internal prior-readable fallback lookup.
  - 2026-05-01 → Contract clarified that internal fallback lookup is not a consumer read gateway; consumer gateway/evidence/eligibility scope remain mirror-enforced.
  - 2026-05-01 → Operator retest confirmed targeted readable/pointer tests, full MarketData suite, readable-publication integration test, and pointer static guard all PASS after the regression patch.

  [DEFINED]
  - Consumer read paths must resolve through `eod_current_publication_pointer`.
  - Valid readable context requires sealed current publication, pointer/publication/run identity match, `terminal_status = SUCCESS`, `publishability_state = READABLE`, `coverage_gate_state = PASS`, `run.is_current_publication = 1`, and run `publication_id/publication_version` mirror match to the pointer.
  - Artifact rows returned to consumers must be scoped by `publication_id` and pointer-resolved `trade_date_effective`/trade date context.
  - No readable pointer context means fail-safe: empty controlled output, not-readable response, controlled exception, or explicit command/evidence/replay failure.
  - Internal prior-readable fallback lookup is allowed only for pipeline hold/degraded-mode/correction preservation and must not be used as an API/evidence/replay/consumer latest shortcut.

  [IMPLEMENTED]
  - `EligibilitySnapshotScopeRepository` enforces coverage PASS and run mirror match.
  - `EodEvidenceRepository::findPublicationForRun` enforces pointer/current/sealed/SUCCESS/READABLE/PASS/current/mirror validation.
  - `EodEvidenceRepository::exportEligibilityRows` enforces pointer-scoped readable eligibility context.
  - `EodEvidenceRepository::dominantReasonCodes` no longer returns reason-code output when the publication/run context is not current-readable/PASS/mirror-valid.
  - `EodPublicationRepository::findLatestReadablePublicationBefore` remains an internal fallback lookup only; it preserves pipeline correction/fallback behavior and must not be used as a consumer gateway.
  - Static guards and integration tests were extended for coverage PASS and run mirror requirements.

  [ENFORCED]
  - Static guard coverage exists for forbidden latest/MAX shortcuts in consumer files.
  - Static guard coverage exists for pointer gateway predicates.
  - Static guard coverage exists for pointer-scoped eligibility/evidence coverage PASS and run mirror checks.
  - Integration coverage exists for no-leak behavior when coverage is not PASS or run mirror mismatches pointer metadata.
  - Regression reconciliation exists for internal fallback lookup so consumer enforcement does not break prior-readable preservation behavior.

  [VALIDATED]
  - Container static grep/query scan completed.
  - Container `php -l` completed for changed PHP files.
  - Local command: `php artisan migrate:fresh --env=testing` → PASS.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "readable"` → PASS; `OK (45 tests, 256 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "pointer"` → PASS; `OK (51 tests, 551 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData` → PASS; `OK (250 tests, 2355 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php` → PASS; `OK (8 tests, 15 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/PublicationCurrentPointerReadinessStaticGuardTest.php` → PASS; `OK (3 tests, 23 assertions)`.
  - Operator-local final-sweep command: `vendor/bin/phpunit tests/Unit/MarketData/ReadSideConsumerSurfaceFinalSweepStaticGuardTest.php` -> PASS; `OK (8 tests, 157 assertions)`.
  - Operator-local final-sweep command: `vendor/bin/phpunit tests/Unit/MarketData --filter "ReadSide"` -> PASS; `OK (12 tests, 226 assertions)`.
  - Operator-local final-sweep command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Readable"` -> PASS; `OK (57 tests, 426 assertions)`.
  - Operator-local final-sweep command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` -> PASS; `OK (76 tests, 1117 assertions)`.
  - Operator-local final-sweep command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` -> PASS; `OK (98 tests, 1193 assertions)`.
  - Operator-local final-sweep command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Consumer"` -> PASS; `OK (13 tests, 222 assertions)`.
  - Operator-local final-sweep command: `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` -> PASS; `OK (49 tests, 359 assertions)`.
  - Operator-local final-sweep command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> PASS; `OK (43 tests, 717 assertions)`.
  - Operator-local final-sweep command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` -> PASS; `OK (45 tests, 812 assertions)`.
  - Operator-local final-sweep command: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> PASS; `OK (124 tests, 2785 assertions)`.
  - Operator-local final-sweep command: `vendor/bin/phpunit tests/Unit/MarketData` -> PASS; `OK (391 tests, 5345 assertions)`.

  [CURRENT_FINAL_SWEEP_STATUS]
  - Current final-sweep status is LOCKED for this ZIP: local ReadSide/Readable/Pointer/Publication/Consumer/CommandSurface/Replay/direct final-sweep guard/Evidence/StaticGuard/full MarketData proof has been supplied.
  - Static result is `NO_CONSUMER_BYPASS_FOUND`: no real consumer was found using raw/staging/latest/MAX(date) shortcuts.
  - Historical 2026-05-01 LOCKED proof remains preserved below as prior evidence for the same contract; the 2026-05-12 final-sweep lock is based on fresh operator-local proof for this latest ZIP.
  - Required local validation is documented and recorded in `docs/market_data/audit/READ_SIDE_CONSUMER_SURFACE_FINAL_SWEEP_INVENTORY.md`.

  [FINAL_RULE]
  - LOCKED. No market-data consumer may read raw/staging/latest/current artifact data unless it is resolved through the current readable publication pointer and validated against sealed publication, SUCCESS/READABLE/PASS run, current state, run mirror metadata, and publication scope.
  - No consumer may fallback to MAX/latest/raw/staging data when pointer resolution fails.
  - Internal prior-readable fallback remains allowed only for pipeline hold/degraded-mode/correction preservation and must not be exposed as consumer latest/read gateway.

  [CURRENT_LOCK_CONDITION]
  - Satisfied for the current final-sweep ZIP: direct final-sweep guard, ReadSide, Readable, Pointer, Publication, Consumer, CommandSurface, Replay, Evidence, StaticGuard, and full `tests/Unit/MarketData` all passed locally with concrete output.

  [LOCK_CONDITION]
  - This contract is locked for the current source-of-truth ZIP after targeted and full MarketData PHPUnit validation.
  - Reopen only if a future market-data read path, evidence/replay flow, repository method, command output, or fallback rule changes the pointer/readability enforcement contract.

---

- PRODUCTION_VALIDATION_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-09

  [RELATED_IMPLEMENTATION] Production Validation / Manual + Runtime Proof

  [REVIEW_STATUS] LOCKED_LOCAL_RUNTIME_PROOF

  [HISTORY]
  - 2026-05-08 -> Contract opened as canonical production validation proof contract under audit governance.
  - 2026-05-08 -> Static trace found many prior contracts had targeted/full local proof recorded, but production validation needed a single inventory that distinguishes historical proof, static proof, missing proof, and actual runtime proof.
  - 2026-05-08 -> Enforcement patch added `PRODUCTION_VALIDATION_INVENTORY.md` and `ProductionValidationRuntimeProofStaticGuardTest.php`.
  - 2026-05-08 -> Contract initially held at ENFORCED because `vendor/` is absent in the uploaded ZIP and new local PHPUnit/artisan/evidence/replay runtime proof had not yet been supplied.
  - 2026-05-08 -> Operator supplied local runtime proof: related targeted PHPUnit filters passed (OperationalReadiness 10/199, CommandSurface 47/348, Evidence 44/767, Replay 39/655, Correction 65/1287, FailSafe 5/108), artisan list showed 19 market-data commands, and seven core help surfaces displayed usage/options without fatal error.
  - 2026-05-08 -> ProductionValidation guard/filter and full MarketData suite initially failed only because the inventory lacked the exact lowercase string `manual validation`; fix1 corrected the inventory and operator-local rerun passed ProductionValidation and full MarketData suite.
  - 2026-05-08 -> Operator supplied daily/import-only, promote/finalize, and run evidence export output. These passed and were recorded as runtime proof.
  - 2026-05-08 -> Operator replay smoke/verify exposed `SQLSTATE[22001]` on `md_replay_daily_metrics.mismatch_summary` during mismatch persistence; the committed valid fixture also does not match the runtime `run_id=1` data and should produce MISMATCH, not SQL failure.
  - 2026-05-08 -> Contract patch added replay mismatch persistence hardening, schema/migration/docs sync to LONGTEXT, concise operator mismatch summaries with full JSON detail retention, and command reason-code preservation for fixture domain errors.
  - 2026-05-09 -> Operator supplied failed/held runtime proof and held-run evidence proof for low-coverage manual file `run_id=2`.
  - 2026-05-09 -> Operator supplied correction request/guard/approve/run proof and correction evidence proof for `correction_id=1`.
  - 2026-05-10 -> Runtime proof recovery container recheck against the uploaded ZIP found `vendor/` present, but PHPUnit was blocked in the container by missing PHP extensions `dom`, `mbstring`, `xml`, and `xmlwriter`; `.env.testing` was also missing in the container, so database/runtime artisan proof was not rerun there. This is container-only evidence: container proof remains limited to command registration and PHP syntax checks, and it does not describe the operator-local environment.
  - 2026-05-12 -> Operator-local runtime proof recovery completed successfully: PHP 7.4.33 has the required extensions (`dom`, `mbstring`, `pdo_mysql`, `pdo_sqlite`, `xml`, `xmlreader`, `xmlwriter`); `migrate:fresh --env=testing` completed all market-data migrations; `MarketDataReasonCodesSeeder` completed successfully; Replay PASS (43 tests, 717 assertions); Evidence PASS (44 tests, 781 assertions); StaticGuard PASS (116 tests, 2628 assertions); full `tests/Unit/MarketData` PASS (383 tests, 5188 assertions). Operator-local proof is the current runtime authority for this Production Validation session.

  [DEFINED]
  - Production validation is the final proof layer before any market-data implementation can be called DONE or any contract can be called LOCKED.
  - Static proof may support validation but must not replace targeted PHPUnit, full MarketData PHPUnit, artisan command list/help, evidence output, replay verification, and runtime flow/failure proof.
  - Missing runtime proof must be recorded as PENDING_RUNTIME_EVIDENCE, PENDING_EVIDENCE_RUNTIME_PROOF, PENDING_REPLAY_RUNTIME_PROOF, or PENDING_FLOW_RUNTIME_PROOF.
  - Partial proof must be recorded as PARTIAL_RUNTIME_PROOF and must list remaining gaps.
  - READY_FOR_LOCAL_RUNTIME_VALIDATION is the maximum status when the ZIP lacks `vendor/` and commands/tests cannot be executed in container.

  [IMPLEMENTED]
  - `docs/market_data/audit/PRODUCTION_VALIDATION_INVENTORY.md` defines proof categories, runtime inventory, PHPUnit matrix, artisan matrix, evidence/replay/flow/failure checklists, regression reconciliation, expected output, and pass/fail criteria.
  - `tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` statically guards the production validation inventory and audit docs against false DONE/LOCKED claims.
  - `LUMEN_IMPLEMENTATION_STATUS.md` now tracks `Production Validation / Manual + Runtime Proof` as DONE after operator-local full production-validation proof was supplied.
  - `LUMEN_CONTRACT_TRACKER.md` now tracks `PRODUCTION_VALIDATION_CONTRACT` as LOCKED after full runtime proof was supplied.
  - `database/migrations/2026_05_08_000001_expand_replay_mismatch_summary_to_longtext.php` upgrades runtime replay persistence for long mismatch summaries.
  - `ReplayVerificationService` now writes concise operator summaries and keeps detailed mismatch proof in `mismatches_json`.
  - `VerifyReplayCommand` now preserves domain reason codes from fixture/replay exceptions when the exception message starts with a reason-code prefix.
  - Failed/held production validation evidence now records `run_id=2` low-coverage proof with `HELD`, `NOT_READABLE`, `COVERAGE_BELOW_THRESHOLD`, `RUN_PARTIAL_DATA`, and `pointer_switched=false`.
  - Correction production validation evidence now records `correction_id=1` request guard, approval, published correction run `3`, resealed candidate publication `3`, and correction evidence export.

  [ENFORCED]
  - Static guard requires runtime proof language, required PHPUnit commands, required artisan commands, evidence export proof, replay proof, missing-proof pending statuses, expected output, and pass/fail criteria.
  - Static guard fails if Production Validation is marked DONE or `PRODUCTION_VALIDATION_CONTRACT` is marked LOCKED without runtime evidence.
  - Audit docs keep prior DONE/LOCKED history intact while preventing the new production validation scope from inheriting old runtime proof as a false current claim.
  - Replay runtime persistence fix is tracked in the inventory, schema docs, migration, service, command, and static guard so future replay defects cannot be patched without audit trace.
  - Failed/held runtime proof and correction lifecycle/evidence proof are tracked in the inventory, implementation status, and contract tracker before any final DONE/LOCKED promotion.

  [VALIDATED]
  - Container static file creation completed.
  - Container `php -l tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` passed for this ZIP release.
  - PHPUnit/artisan/evidence/replay were not run in container because `vendor/` is absent.
  - Operator-local ProductionValidation proof PASS: direct guard OK (10 tests, 131 assertions); ProductionValidation filter OK (10 tests, 131 assertions).
  - Operator-local related targeted PHPUnit proof PASS: OperationalReadiness OK (10 tests, 199 assertions); CommandSurface OK (47 tests, 348 assertions); Evidence OK (44 tests, 767 assertions); Replay OK (39 tests, 655 assertions); Correction OK (65 tests, 1287 assertions); FailSafe OK (5 tests, 108 assertions).
  - Operator-local full MarketData proof PASS before final recovery patch: `vendor/bin/phpunit tests/Unit/MarketData` OK (378 tests, 5072 assertions).
  - Operator-local final runtime proof PASS after final recovery patch: Replay OK (43 tests, 717 assertions); Evidence OK (44 tests, 781 assertions); StaticGuard OK (116 tests, 2628 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (383 tests, 5188 assertions).
  - Operator-local artisan command list/help proof PASS after fixture generator: command discovery shows 20 registered market-data commands including `market-data:replay:fixture:generate`, and required help surfaces display usage/options without fatal error.
  - Operator-local flow proof PASS: daily import-only created `run_id=1` without promotion/current pointer switch; promote/finalize made publication `1` current/readable/sealed with coverage PASS; run evidence export produced complete 9-file evidence.
  - Operator-local replay proof PARTIAL after fix3: replay smoke/verify no longer hits `SQLSTATE[22001]`; stale committed `valid_case` returns clean MISMATCH, reason-code mismatch returns clean MISMATCH/pass, and broken/missing fixture cases surface `REPLAY_FIXTURE_SCHEMA_MISMATCH` / `REPLAY_EXPECTED_PROOF_INCOMPLETE`.
  - Operator-local replay proof PASS after fix4: generated runtime fixture command produced `fixture_generated=1` and `expected_result=MATCH`; generated fixture verify produced `replay_id=5`, `comparison_result=MATCH`, `mismatch_count=0`, `artifact_changed_scope=none`, and replay artifact path; smoke with `--generate_runtime_valid_case` produced `all_passed=1`, generated valid MATCH/pass, reason-code mismatch MISMATCH/pass, broken manifest ERROR/pass, and missing file ERROR/pass.
  - Operator-local replay evidence export PASS after fix5: `market-data:evidence:export --replay_id=5 --trade_date=2026-02-18 --output_dir=storage/app/market-data/evidence` produced selector=replay, status `SUCCESS`, comparison `MATCH`, 5 files, and replay evidence pack files.
  - Operator-local failed/held runtime proof PASS after fix6: `run_id=2` daily import-only accepted 5 rows and stayed unpromoted/current; promote produced `HELD`, `NOT_READABLE`, `coverage_gate_state=FAIL`, `coverage_reason_code=COVERAGE_BELOW_THRESHOLD`, `coverage_summary=available=5/901 | missing=896 | ratio=0.0055 | threshold=0.9800`, `final_reason_code=RUN_PARTIAL_DATA`, and `pointer_switched=false`.
  - Operator-local held-run evidence export PASS_WITH_WARNING after fix6: `market-data:evidence:export --run_id=2` produced `evidence_completeness_state=INCOMPLETE`, `pointer_resolve_status=MISSING`, `fallback_used=1`, `file_count=8`, and `EVIDENCE_INCOMPLETE` warning for the non-readable held run.
  - Operator-local correction proof PASS after fix6: request produced `correction_id=1`; premature run was blocked with `COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`; approve transitioned to `APPROVED`; correction run produced `run_id=3`, `SUCCESS`, `READABLE`, `PUBLISHED`, `RESEALED`, baseline publication `1`, candidate publication `3`, and pointer switched to current publication `3`; correction evidence export produced `correction_evidence.json`.
  - Operator-local fresh command-list/full-help proof PASS after fix7: `php artisan list | findstr market-data` shows 20 registered market-data commands including `market-data:replay:fixture:generate`; `replay:fixture:generate --help` shows `run_id`, `--case`, and `--output_dir`; `replay:smoke --help` shows `--generate_runtime_valid_case`; `replay:verify`, `evidence:export`, `daily`, `promote`, `run:finalize`, `correction:request`, `correction:approve`, and `correction:run` help surfaces display usage/options without fatal error.
  - Replay generated MATCH artifact, replay evidence export by `--replay_id=5`, failed/held coverage proof, held-run evidence, correction lifecycle, correction guard, correction evidence export, and fresh command-list/full-help proof are now RUNTIME_PROOF_PASS or PASS_WITH_WARNING where the held run is intentionally incomplete.
  - Container runtime proof recovery on 2026-05-10: `php vendor/bin/phpunit --version` is blocked in the container by missing `dom`, `mbstring`, `xml`, and `xmlwriter`; `.env.testing` is absent in the container; `php artisan list` lists 20 market-data commands with PHP 8.4 deprecation warnings; `php -l` passed for 128 market-data PHP files. Status for this container run is `BLOCKED_CONTAINER_RUNTIME_ENV`, not runtime PASS.
  - Operator-local runtime proof recovery on 2026-05-12: PHP 7.4.33 has required extensions, testing migration and reason-code seed completed, Replay/Evidence/StaticGuard targeted filters passed, and full `tests/Unit/MarketData` passed with OK (383 tests, 5188 assertions). This operator-local result is the final runtime authority for this session.

  [FINAL_RULE]
  - LOCKED. Production Validation contract is locked because operator-local runtime proof is complete and current: required PHP extensions are available, testing migration/seed succeeded, 20 registered market-data commands are confirmed, Replay/Evidence/StaticGuard targeted filters passed, full `tests/Unit/MarketData` passed with OK (383 tests, 5188 assertions), and flow/evidence/replay/failure/correction runtime artifacts are recorded. Container-only `BLOCKED_CONTAINER_RUNTIME_ENV` remains historical/support context and does not override the operator-local PASS result. Static guard and PHPUnit proof alone are not substitutes for runtime artifacts.

  [NEXT_ACTION]
  - Continue append-only runtime evidence updates after future command/behavior changes.

---

- OPERATIONAL_READINESS_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-08

  [RELATED_IMPLEMENTATION] Operational Readiness

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-08 -> Contract opened as canonical operational readiness contract under audit governance.
  - 2026-05-08 -> Static trace found the command surface was registered and several supporting ops docs existed, but no single canonical operational runbook made the complete operator flow executable without source-code knowledge.
  - 2026-05-08 -> Enforcement patch added operational runbook, operational readiness inventory, command docs index alignment, and `OperationalReadinessStaticGuardTest.php`.
  - 2026-05-08 -> Contract initially remained ENFORCED, not LOCKED, because uploaded ZIP had no `vendor/`; targeted and full local MarketData PHPUnit validation were required.
  - 2026-05-08 -> Operator-local validation PASS: `OperationalReadinessStaticGuardTest.php` OK (10 tests, 196 assertions); `OperationalReadiness` filter OK (10 tests, 196 assertions); `CommandSurface` filter OK (47 tests, 348 assertions); `Evidence` filter OK (41 tests, 718 assertions); `Replay` filter OK (38 tests, 643 assertions); `Correction` filter OK (65 tests, 1287 assertions); `FailSafe` filter OK (5 tests, 108 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (368 tests, 4927 assertions).
  - 2026-05-08 -> Operator-local artisan validation PASS: `php artisan list | findstr market-data` listed 19 market-data commands, and help spot checks passed for `market-data:daily`, `market-data:promote`, `market-data:evidence:export`, `market-data:replay:verify`, `market-data:correction:request`, `market-data:correction:approve`, and `market-data:correction:run`.
  - 2026-05-08 -> Contract promoted from ENFORCED to LOCKED after local PHPUnit/artisan evidence confirmed operator runbook coverage, command surface alignment, evidence/replay/correction/fail-safe related behavior, and full MarketData regression suite.

  [DEFINED]
  - Operators must be able to run market-data without reading source code.
  - Runbook is the operational source of truth.
  - Commands must document required input, safe default, expected output, reason code, terminal state, and next action.
  - HELD / FAILED / NOT_READABLE states must block readable publication and preserve pointer safety.
  - Evidence export must prove run/publication/pointer/coverage/source/reason/correction/replay metadata without manual DB query.
  - Replay verification must be proof mechanism, not smoke-only decoration.
  - Manual file import-only must not bypass promote, coverage, seal, finalize, or pointer gates.
  - Correction lifecycle must be request/approve/run/evidence/replay driven and preserve previous current on unsafe candidates.
  - Manual DB action must be exceptional, documented, reason-coded, backed up, and followed by evidence/replay or pointer validation.
  - raw/staging/latest/MAX(date), coverage bypass, seal bypass, finalize bypass, direct pointer update, direct readable update, and empty-success output are forbidden operational shortcuts.

  [IMPLEMENTED]
  - `docs/market_data/ops/OPERATIONAL_RUNBOOK.md` defines the operator flow and checklists.
  - `docs/market_data/audit/OPERATIONAL_READINESS_INVENTORY.md` records the readiness inventory.
  - `docs/market_data/ops/commands/README.md` now references the operational runbook as canonical operator source of truth.
  - `tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` protects the runbook/command/audit docs synchronization.
  - `tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` no longer hardcodes a pending Operational Readiness state; it now recognizes the active Operational Readiness session as DONE/LOCKED while preserving the locked Audit Docs Synchronization contract and evidence history.

  [ENFORCED]
  - Static guard checks all registered commands are documented.
  - Static guard checks terminal states, next action, evidence/replay, manual file import/promote, correction lifecycle, manual DB policy, and forbidden shortcut terms.
  - Audit docs identify this contract as LOCKED with local targeted/full PHPUnit and artisan command discovery/help evidence.

  [VALIDATED]
  - Container static trace completed across command classes, Console Kernel, ops docs, audit docs, command safety inventory, evidence/replay/correction/fail-safe docs and tests.
  - Container `php -l tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` passed.
  - Container `php -l tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` passed.
  - Container grep/static scan confirmed operational runbook, contract name, all registered commands, terminal states, reason-code handling, next action, manual file import-only/promote, coverage gate, seal, finalize, pointer, evidence, replay, manual DB policy, and raw/staging/latest/MAX(date) forbidden shortcut coverage.
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` OK (10 tests, 196 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"` OK (10 tests, 196 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` OK (47 tests, 348 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` OK (41 tests, 718 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` OK (38 tests, 643 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` OK (65 tests, 1287 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"` OK (5 tests, 108 assertions).
  - Operator-local validation PASS: full `vendor/bin/phpunit tests/Unit/MarketData` OK (368 tests, 4927 assertions).
  - Operator-local artisan discovery PASS: `php artisan list | findstr market-data` listed 19 market-data commands including daily, promote, evidence export, replay verify/smoke/backfill, correction request/approve/run, current-publication repair, session snapshot, and session snapshot purge.
  - Operator-local artisan help spot checks PASS for `market-data:daily`, `market-data:promote`, `market-data:evidence:export`, `market-data:replay:verify`, `market-data:correction:request`, `market-data:correction:approve`, and `market-data:correction:run`.

  [FINAL_RULE]
  - LOCKED. Operational readiness may be claimed only when the operational runbook remains the operator source of truth, every registered market-data command is documented, terminal states have reason-coded next actions, evidence/replay/correction/manual-file/manual-DB flows are operator-runnable, forbidden shortcuts remain explicit, and targeted OperationalReadiness/CommandSurface/Evidence/Replay/Correction/FailSafe plus full `tests/Unit/MarketData` validation remain passing.

  [NEXT_ACTION]
  - Continue with the next market-data hardening contract from a fresh source-of-truth ZIP. Preserve OPERATIONAL_READINESS_CONTRACT as LOCKED unless a future scoped regression provides contrary evidence.

- OPS_ENVIRONMENT_BASELINE_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-18

  [RELATED_IMPLEMENTATION] Ops Environment Baseline

  [REVIEW_STATUS] LOCKED_LOCAL_RUNTIME_PROOF

  [HISTORY]
  - 2026-05-18 -> Contract opened to make clean operator/CI runtime a precondition for using market-data command output as evidence.
  - 2026-05-18 -> Container runtime observed PHP 8.4.16; pre-patch `php artisan list` emitted Lumen/vendor PHP 8.4 deprecation warnings, so it could not be used as runtime evidence.
  - 2026-05-18 -> Container PHPUnit remained blocked by missing `dom`, `mbstring`, `xml`, and `xmlwriter`; Composer command is unavailable in container.
  - 2026-05-18 -> Unsupported PHP guard added to `artisan` before `vendor/autoload.php`.
  - 2026-05-18 -> PHPUnit bootstrap guard added through `tests/bootstrap.php` and `phpunit.xml`.
  - 2026-05-18 -> Environment baseline ops doc, audit inventory, runbook gate, and static guard added.
  - 2026-05-18 -> Composer/platform lock change deferred with reason to avoid `composer.json` / `composer.lock` drift without Composer.
  - 2026-05-18 -> Operator-local runtime proof supplied: PHP 7.4.33, Composer 2.8.4, required extensions, clean `artisan` command output, clean market-data help output, and targeted OpsEnvironment/Evidence/Replay/Command PHPUnit PASS.
  - 2026-05-18 -> Full suite before guard synchronization failed only on stale `ConfigEnvGovernanceCleanupStaticGuardTest` active-session assertion.
  - 2026-05-18 -> Guard synchronization patch updated Config / ENV static guard to preserve the LOCKED historical contract without requiring it to be the active session.
  - 2026-05-18 -> Final operator-local rerun passed: Config / ENV guard OK (10 tests, 119 assertions), StaticGuard OK (164 tests, 3702 assertions), and full MarketData OK (435 tests, 6299 assertions).

  [DEFINED]
  - Market-data command output is evidence and must be clean.
  - Clean evidence output means no PHP warnings, PHP deprecations, vendor/framework deprecations, missing-extension warnings, timezone warnings, debug noise, or stack trace caused by environment mismatch.
  - Unsupported PHP must fail closed before vendor/project autoload rather than producing noisy output.
  - Supported clean-output PHP range for the current dependency set is PHP `>= 7.3` and `< 8.4`; preferred operator/CI baseline is PHP 8.3.x.
  - Required local/CI extensions are `dom`, `json`, `libxml`, `mbstring`, `pdo_mysql`, `pdo_sqlite`, `tokenizer`, `xml`, `xmlreader`, and `xmlwriter`.
  - DONE/LOCKED requires supported operator-local or CI artisan/PHPUnit proof with version and extension context plus full MarketData suite PASS after guard synchronization; this proof is now supplied and recorded.

  [IMPLEMENTED]
  - `artisan` blocks PHP `< 7.3` and `>= 8.4` with `ENV_UNSUPPORTED_PHP_VERSION` before `vendor/autoload.php`.
  - `tests/bootstrap.php` blocks unsupported PHP before project autoload during PHPUnit proof.
  - `phpunit.xml` now uses `tests/bootstrap.php`.
  - `docs/market_data/ops/OPS_ENVIRONMENT_BASELINE.md` records baseline version, extension, timezone, `.env.testing`, clean-output, and manual validation requirements.
  - `docs/market_data/audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md` records trace, matrices, container status, patch scope, Composer decision, validation, operator-local proof, stale guard finding, and final PASS closure.
  - `docs/market_data/ops/OPERATIONAL_RUNBOOK.md` now contains an environment baseline gate.
  - `tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` guards this policy and final DONE/LOCKED proof status.
  - `tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php` now preserves historical Config / ENV LOCKED proof without binding that historical session as active.

  [ENFORCED]
  - Unsupported PHP is blocked before vendor autoload for artisan command evidence.
  - Unsupported PHP is blocked before project autoload for PHPUnit proof bootstrap.
  - Audit docs may mark this contract LOCKED because supported operator-local full suite proof passed after guard synchronization.
  - Composer/platform change remains deferred unless Composer lock can be regenerated intentionally.
  - Existing market-data domain contracts remain unchanged and are not reopened by this environment baseline.

  [VALIDATED]
  - Container source structure check: required source files/folders exist.
  - Container `php -v`: PHP 8.4.16 -> unsupported for evidence output.
  - Container `composer --version`: Composer unavailable -> BLOCKED_CONTAINER_RUNTIME_ENV.
  - Container `php -m`: missing `dom`, `mbstring`, `xml`, and `xmlwriter` -> BLOCKED_CONTAINER_RUNTIME_ENV for PHPUnit.
  - Container pre-patch `php artisan list`: command registration visible but output contained PHP 8.4 Lumen/vendor deprecation warnings -> NOISY_OUTPUT_NOT_EVIDENCE.
  - Container `php vendor/bin/phpunit tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php`: blocked by missing PHPUnit extensions.
  - Container post-patch `php artisan list`: clean `ENV_UNSUPPORTED_PHP_VERSION` fail-closed before vendor autoload -> EXPECTED_FAIL_CLOSED.
  - Syntax: `php -l artisan` -> No syntax errors detected.
  - Syntax: `php -l tests/bootstrap.php` -> No syntax errors detected.
  - Syntax: `php -l tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> No syntax errors detected.
  - Operator-local: `php -v` -> PHP 7.4.33.
  - Operator-local: `composer --version` -> Composer 2.8.4 using PHP 7.4.33.
  - Operator-local: required extensions are present, including dom, mbstring, pdo_mysql, pdo_sqlite, xml, xmlreader, and xmlwriter.
  - Operator-local: `php artisan list` and market-data daily/evidence/replay/finalize/promote help output are clean.
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> OK (8 tests, 88 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "OpsEnvironment"` -> OK (8 tests, 88 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` -> OK (53 tests, 938 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> OK (53 tests, 819 assertions).
  - Operator-local: `vendor/bin/phpunit tests/Unit/MarketData --filter "Command"` -> OK (74 tests, 764 assertions).
  - Operator-local full suite before guard-sync patch: 435 tests, 6276 assertions, 1 failure in stale Config / ENV active-session guard.
  - Operator-local after guard-sync patch: `vendor/bin/phpunit tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php` -> OK (10 tests, 119 assertions).
  - Operator-local after guard-sync patch: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (164 tests, 3702 assertions).
  - Operator-local after guard-sync patch: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (435 tests, 6299 assertions).

  [FINAL_RULE]
  - LOCKED. Market-data command output must never be used as evidence if it contains PHP warning/deprecation/noise.
  - LOCKED. Unsupported PHP must fail closed before vendor/project autoload with `ENV_UNSUPPORTED_PHP_VERSION`.
  - LOCKED. Supported operator-local proof confirms clean artisan/help output and full MarketData PHPUnit PASS after guard synchronization.

  [RECONCILIATION]
  - Previous Config / ENV Governance Cleanup contract remains valid; this session does not change active env keys, config typing, ticker active semantics, source-mode, coverage, read-side pointer, publication, replay, evidence, correction, or DB integrity behavior.
  - Prior DONE/LOCKED contracts are not promoted or demoted by this environment baseline patch.
  - Config / ENV static guard now preserves historical LOCKED proof without requiring Config / ENV Governance Cleanup to remain active.
  - Structural contract status is now `LOCKED` because final local full-suite PASS has been supplied after guard synchronization.

  [NEXT_ACTION]
  - No remaining blocker for this scope. Keep this contract LOCKED unless a future PHP/runtime/CI/output-noise change reopens the contract.

- FAIL_SAFE_NO_SILENT_FAILURE_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-08

  [RELATED_IMPLEMENTATION] Fail-Safe Behavior / No Silent Failure

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-08 -> Contract opened as canonical fail-safe/no-silent-failure contract under audit governance.
  - 2026-05-08 -> Static trace identified empty-success gaps in manual file, generic API, Yahoo no-target-date bars, ingest zero-valid-bars, and finalize explicit zero-valid-data handling.
  - 2026-05-08 -> Enforcement patch added reason-coded no-data blocking, pointer-preserving recoverable API no-valid-data handling, finalize no-fake-success guard, registry/seed sync, inventory, and static guard coverage.
  - 2026-05-08 -> Contract held at ENFORCED because uploaded ZIP has no `vendor/`; targeted and full local PHPUnit are required before LOCKED.
  - 2026-05-08 -> Operator-local PHPUnit found a static guard literal mismatch and generic API retry telemetry regression: evidence/backfill source summaries missed `attempt_count`, `success_after_retry`, and `final_http_status`, and full suite raised `Undefined index: attempt_count`.
  - 2026-05-08 -> Follow-up enforcement patch corrected the static guard assertion and preserved generic API request/retry telemetry into terminal source context for success, empty-response, and malformed-response outcomes.
  - 2026-05-08 -> Contract promoted from ENFORCED to LOCKED after operator-local validation PASS: `FailSafeNoSilentFailureStaticGuardTest.php` OK (5 tests, 108 assertions); `Source` filter OK (37 tests, 420 assertions); `Evidence` filter OK (37 tests, 594 assertions); `Integration` filter OK (91 tests, 1450 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (349 tests, 4558 assertions).

  [DEFINED]
  - No valid data means no readable publication.
  - Empty manual/API source output is not valid input proof.
  - Zero valid canonical bars cannot create a publishable artifact.
  - Finalize cannot produce `SUCCESS + READABLE` when explicit valid data proof is zero.
  - Source failures and no-data outcomes must be reason-coded.
  - Current pointer and correction baseline must be preserved when candidate proof is unsafe.
  - Evidence/replay/command surfaces must expose final status, reason code, source context, row counts, and pointer preservation context.
  - Reason codes used by fail-safe guards must be registered and seeded.

  [IMPLEMENTED]
  - Empty manual CSV/JSON blocked by `LocalFileEodBarsAdapter`.
  - Empty/no-valid API output blocked by `PublicApiEodBarsAdapter`; generic API retry telemetry remains available in source context after successful retry and fail-safe no-data/malformed outcomes.
  - Empty source rows and zero valid canonical bars blocked by `EodBarsIngestService`.
  - API `RUN_SOURCE_NO_VALID_DATA` routed through recoverable source failure fallback preservation.
  - Explicit zero valid data proof blocked by `FinalizeDecisionService`.
  - `FAIL_SAFE_NO_SILENT_FAILURE_INVENTORY.md` and `FailSafeNoSilentFailureStaticGuardTest` added.
  - Registry/seed synchronized for fail-safe reason-code family.

  [ENFORCED]
  - Static guard fails if no-data/manual-empty/finalize-zero-data guards, registry/seed codes, audit inventory, or no-shortcut constraints disappear.
  - Runtime paths now throw `SourceAcquisitionException` with failed telemetry instead of returning empty success for the patched source/ingest cases.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for changed PHP files.
  - Operator-local failure output reviewed; follow-up patch prepared and validated.
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData/FailSafeNoSilentFailureStaticGuardTest.php` OK (5 tests, 108 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Source"` OK (37 tests, 420 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` OK (37 tests, 594 assertions).
  - Operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` OK (91 tests, 1450 assertions).
  - Operator-local validation PASS: full `vendor/bin/phpunit tests/Unit/MarketData` OK (349 tests, 4558 assertions).
  - PHPUnit/artisan not run in container because uploaded ZIP has no `vendor/`; local operator evidence is the LOCKED evidence.

  [FINAL_RULE]
  - LOCKED. Empty/failed/unproven data must never become readable, sealed, published, or pointer-switched. Manual/API no-data, zero valid canonical bars, empty/failing source proof, coverage-not-evaluable proof, and explicit zero valid data finalize context must end as reason-coded `FAILED`, `HELD`, `BLOCKED`, or `NOT_READABLE`, while preserving the current pointer/correction baseline. Evidence, replay, command output, registry, seed, and static guards must keep this behavior visible and regression-resistant.

- IMPORT_PROMOTE_SEPARATION_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-08

  [RELATED_IMPLEMENTATION] Import vs Promote Separation

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-08 -> Contract opened as canonical import/promote boundary contract under audit governance.
  - 2026-05-08 -> Static trace found request mode was not yet first-class persisted DB/run contract, even though daily/promote command split already existed.
  - 2026-05-08 -> Enforcement patch added request-mode persistence, request-mode immutability, import-only side-effect checks, explicit promote gate context, command/evidence/replay import-promote proof, reason-code registry/seed sync, schema docs sync, inventory, and static guard coverage.
  - 2026-05-08 -> Contract held at ENFORCED because uploaded ZIP has no `vendor/`; targeted and full local PHPUnit required before LOCKED.
  - 2026-05-08 -> Operator-local validation found failures in the new static guard and older strict Mockery expectations. Follow-up enforcement patch fixed the static assertions, removed mutating candidate lookup from import-only guard validation, and reconciled affected request-mode/reason-code test expectations.
  - 2026-05-08 -> Operator-local rerun passed ImportPromote static guard, ImportPromote filter, Manual, Finalize, Publication, Evidence, and StaticGuard filters. Source filter had one remaining strict Mockery expectation mismatch for enriched `touchStage()` attributes; latest patch updates that expectation.
  - 2026-05-08 -> Operator-local rerun after the Source expectation patch passed Source, Provider, Coverage, Pointer, Correction, CommandSurface, and Integration filters. Replay filter and full suite had two remaining errors in `ReplayVerificationServiceTest` because expected replay lineage fixtures did not include newly exported `current_publication_id`; latest patch updates the replay expected publication/lineage context. Contract remains ENFORCED pending Replay rerun and full suite.
  - 2026-05-08 -> Contract promoted from ENFORCED to LOCKED after final operator-local validation passed: `Replay` filter OK (37 tests, 624 assertions); full `vendor/bin/phpunit tests/Unit/MarketData` OK (341 tests, 4436 assertions).

  [DEFINED]
  - `import_only` is allowed to receive/store data and candidate/import context only.
  - `import_only` must not set `READABLE`, current publication, current pointer, or correction published/consumed state.
  - `promote` must be explicit and must pass coverage, hash, seal, finalize, run-publication mirror, pointer target, and post-switch resolver validation.
  - Manual file and API source identity must remain traceable and must not imply publishability.
  - Evidence/replay/command surfaces must distinguish import-only from promoted publication.
  - Reason codes used by import/promote guards must be registered and seeded.

  [IMPLEMENTED]
  - `eod_runs.request_mode` added to migration, SQLite bootstrap, and MariaDB schema docs.
  - `MarketDataStageInput`, `EodRunRepository`, and `MarketDataPipelineService` now carry and enforce request mode.
  - Import-only side-effect assertion blocks readable/current/pointer violations.
  - Promote run context is derived as `request_mode=promote` and continues through coverage/hash/seal/finalize.
  - Command output, evidence export, and replay verification expose import/promote boundary context.
  - `Import_Promote_Separation_Contract.md` and `IMPORT_PROMOTE_SEPARATION_INVENTORY.md` define the proof surface.
  - Registry/seed are synchronized for import/promote reason-code families.

  [ENFORCED]
  - Static guard fails if request mode persistence, import-only block, promote gate strings, command/evidence/replay proof, registry/seed reason codes, or forbidden latest-date shortcuts disappear.
  - Runtime guard fails if `import_only` attempts to enter non-ingest stages or if an import-only result becomes readable/current/pointer-switched.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for changed PHP files after each patch. PHPUnit/artisan were not run in container because uploaded ZIP has no `vendor/`; final runtime proof is operator-local PHPUnit evidence.
  - Operator-local PASS: `ImportPromoteSeparationStaticGuardTest.php` OK (6 tests, 136 assertions); `ImportPromote` filter OK (6 tests, 136 assertions); `Manual` OK (21 tests, 227 assertions); `Source` OK (36 tests, 400 assertions); `Provider` OK (7 tests, 135 assertions); `Coverage` OK (50 tests, 577 assertions); `Finalize` OK (46 tests, 355 assertions); `Publication` OK (97 tests, 1182 assertions); `Pointer` OK (73 tests, 1054 assertions); `Correction` OK (64 tests, 1276 assertions); `Evidence` OK (37 tests, 594 assertions); `Replay` OK (37 tests, 624 assertions); `CommandSurface` OK (47 tests, 348 assertions); `StaticGuard` OK (79 tests, 1899 assertions); `Integration` OK (91 tests, 1450 assertions); full `tests/Unit/MarketData` OK (341 tests, 4436 assertions).

  [FINAL_RULE]
  - LOCKED. `request_mode=import_only` is an ingest/import contract only and must not create consumer-readable publication state, current publication state, current pointer switch, or correction published state. `request_mode=promote` is the explicit publish path and must pass coverage, hash, seal, finalize, run-publication mirror, pointer target, post-switch resolver, command/evidence/replay, and reason-code proof before any readable/current publication is exposed.

  [NEXT_ACTION]
  - Keep this contract LOCKED. Reopen only for a future import/promote policy change or regression touching request mode, source mode, import-only side effects, promote gates, correction publish flow, command output, evidence, replay, schema, or reason-code registry/seed.

- RUN_PUBLICATION_POINTER_LINKAGE_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-08

  [RELATED_IMPLEMENTATION] Run / Publication / Pointer Linkage

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-08 -> Contract opened as canonical run/publication/pointer/correction lineage contract under audit governance.
  - 2026-05-08 -> Static trace found missing explicit correction baseline/replacement publication lineage.
  - 2026-05-08 -> Enforcement patch added correction publication linkage schema/indexes, repository persistence, pipeline propagation, run-publication mirror guard, pointer-linkage reason-coded failures, replay/evidence lineage context, command output linkage summary, registry/seed sync, inventory, and static guard coverage.
  - 2026-05-08 -> Contract held at ENFORCED because uploaded ZIP has no `vendor/`; targeted and full local PHPUnit required before LOCKED.
  - 2026-05-08 -> Operator-local retest found linkage/static/runtime regressions: missing explicit lineage strings in pipeline static guard, missing finalize seal reason literals in hash/seal static guard, outdated correction mock expectations, and unsafe clearing of a valid current pointer on uncontrolled non-correction replacement block.
  - 2026-05-08 -> Recovery patch preserves current pointer on `CURRENT_PUBLICATION_REPLACE_BLOCKED`, keeps correction publication lineage arguments explicit, restores finalize seal reason literals, and keeps contract status at ENFORCED pending local retest.
  - 2026-05-08 -> Contract promoted from ENFORCED to LOCKED after operator-local validation passed: `RunPublicationPointerLinkageStaticGuardTest.php` OK (5 tests, 169 assertions); `Publication` filter OK (97 tests, 1182 assertions); `Pointer` filter OK (73 tests, 1054 assertions); `Finalize` filter OK (46 tests, 355 assertions); `StaticGuard` filter OK (73 tests, 1763 assertions); `Integration` filter OK (91 tests, 1450 assertions); full `tests/Unit/MarketData` OK (335 tests, 4300 assertions).

  [DEFINED]
  - Every publication must have a valid source run and a consistent run-publication mirror.
  - Every current pointer must target an existing, trade-date aligned, `SUCCESS + READABLE + SEALED + coverage PASS` publication/run pair.
  - Pointer switch must be validated before switch, updated atomically, and post-verified through the pointer resolver.
  - Correction must record pointer-resolved baseline publication/run lineage and replacement publication/run lineage when published.
  - Failed, unchanged, or cancelled corrections must preserve the baseline current pointer.
  - Replay and evidence must include lineage proof sufficient to explain run/publication/pointer/correction state without raw database shortcuts.
  - Reason codes used by linkage guards must be registered and seeded.

  [IMPLEMENTED]
  - `eod_dataset_corrections` includes `baseline_publication_id` and `replacement_publication_id`.
  - `EodCorrectionRepository` persists correction publication linkage across correction state transitions.
  - `MarketDataPipelineService` propagates baseline/replacement publication ids and force-replace reason-coded context.
  - `MarketDataInvariantGuard` enforces run-publication mirror validation as part of pointer target validation.
  - `EodPublicationRepository` exposes reason-coded linkage failures for missing publication/run, invalid target state, current replace block, correction baseline mismatch, and pointer orphan/mismatch recovery.
  - `ReplayVerificationService`, `MarketDataEvidenceExportService`, and `AbstractMarketDataCommand` expose lineage context.
  - `RUN_PUBLICATION_POINTER_LINKAGE_INVENTORY.md` and `RunPublicationPointerLinkageStaticGuardTest` define and guard the contract.
  - Registry/seed are synchronized for linkage reason-code families.

  [ENFORCED]
  - Static guard fails if correction publication linkage fields/indexes disappear.
  - Static guard fails if pointer switch no longer validates target/mirror/post-switch resolver.
  - Static guard fails if replay/evidence/command lineage context is removed.
  - Static guard fails if linkage reason-code registry/seed drift or forbidden current-selection shortcuts reappear in key files.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for changed PHP files.
  - PHPUnit/artisan not run in container because uploaded ZIP has no `vendor/`; final runtime proof is operator-local PHPUnit evidence.
  - Operator-local PASS: `RunPublicationPointerLinkageStaticGuardTest.php` OK (5 tests, 169 assertions); `Publication` OK (97 tests, 1182 assertions); `Pointer` OK (73 tests, 1054 assertions); `Finalize` OK (46 tests, 355 assertions); `StaticGuard` OK (73 tests, 1763 assertions); `Integration` OK (91 tests, 1450 assertions); full `tests/Unit/MarketData` OK (335 tests, 4300 assertions).

  [FINAL_RULE]
  - LOCKED. Every readable/current publication must remain traceable to a valid source run through a consistent run-publication mirror; every current pointer must resolve to an existing trade-date aligned `SUCCESS + READABLE + SEALED + coverage PASS` publication/run pair; correction must preserve explicit baseline publication lineage and replacement publication lineage when published; failed/unchanged/cancelled correction paths must preserve the baseline current pointer; replay/evidence/command surfaces must expose lineage proof and reason-coded failure context without raw/staging/latest/MAX(date) shortcuts.
  - Future changes touching run-publication mirror, pointer target validation, pointer switch, correction baseline/replacement linkage, replay/evidence lineage proof, command output, schema/indexes, or reason-code registry/seed must rerun targeted linkage filters plus full `tests/Unit/MarketData`.

  [NEXT_ACTION]
  - Keep this contract LOCKED. Reopen only for a future lineage policy change or regression.

- HASH_SEAL_DATASET_INTEGRITY_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-07

  [RELATED_IMPLEMENTATION] Hash / Seal / Dataset Integrity

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-07 -> Contract opened for deterministic hash, seal, manifest, immutability, finalize, correction, replay/evidence proof, command output, and reason-code sync.
  - 2026-05-07 -> Runtime/static patch added config-driven canonical hash serialization, seal/finalize integrity guards, live sealed artifact mutation guard, enriched manifest, command summary integrity output, registry/seed sync, and static guard tests.
  - 2026-05-07 -> Contract held at ENFORCED because uploaded ZIP has no `vendor/`; targeted and full local PHPUnit required before LOCKED.
  - 2026-05-07 -> Recovery applied after operator-local failures: source/API timeout default reconciled to 20, candidate hash/run mirror synchronized, promotion validation order fixed, and replacement candidate artifacts/hash are isolated in history until force-replace promotion.
  - 2026-05-07 -> Recovery round 2 applied after local retest: SQLite test bootstrap now enforces the 20-second source/API baseline, and replacement candidate publication versions are history-backed for indicators, eligibility, and hash from the compute/build/hash stages.
  - 2026-05-07 -> Recovery round 3 applied after local retest: replacement candidates materialize candidate-bound bars history from current live rows when missing, so seal preconditions are complete without mutating sealed/current/readable baseline rows.
  - 2026-05-07 -> Contract promoted from ENFORCED to LOCKED after operator-local final validation passed: `Finalize` filter OK (46 tests, 355 assertions); `Integration` filter OK (91 tests, 1443 assertions); full `tests/Unit/MarketData` OK (329 tests, 4110 assertions).

  [DEFINED]
  - Dataset hash must be deterministic, repeatable, config-driven, input-order independent, and based only on explicit artifact columns.
  - Seal must require valid hash and manifest context before normal publication can become SEALED.
  - Finalize must reject missing or mismatched hash/seal context before readable/current promotion.
  - Sealed/current/readable datasets must be immutable through normal artifact mutation paths.
  - Replacement promote flows must build candidate artifacts in publication-bound history and may not overwrite sealed baseline live rows before finalize authorization.
  - Correction must preserve baseline and publish changes through a new candidate/seal path.
  - Evidence/replay/command output must expose hash/seal/source/coverage/manifest context.
  - Reason codes used by integrity guards must be registered and seeded.

  [IMPLEMENTED]
  - `DeterministicHashService` implements config-driven canonical serialization and canonical row sorting.
  - `MarketDataPipelineService` records `DATASET_HASH_CREATED` and hash contract context, including history-backed replacement candidates.
  - `EodPublicationRepository` verifies manifest/hash context before seal and hash equality before promotion; manifest output includes hash/seal/source/coverage/column/order proof.
  - `EodArtifactRepository` blocks sealed/current/readable live artifact mutation via `SEALED_DATASET_MUTATION_BLOCKED`.
  - `EodArtifactRepository::ensureBarsHistoryFromCurrentTradeDate()` materializes missing candidate-bound bars history from current live rows without mutating the sealed/current/readable baseline.
  - `AbstractMarketDataCommand` renders hash/seal/integrity summary fields.
  - Registry/seed and static guard tests cover the new contract.
  - Market-data SQLite bootstrap pins source/API timeout to `20` for deterministic source/provider contract tests.
  - Replacement candidate publication versions use history-backed bars, indicators, eligibility, and hash generation before finalize/pointer decisions.

  [ENFORCED]
  - Seal/finalize mutation paths now fail-safe on missing/mismatched integrity context.
  - Live artifact replacement cannot overwrite a different sealed/current/readable baseline; replacement candidates must stay in history until an allowed pointer switch.
  - Static guard prevents removal of config-driven hash, manifest context, mutation guard, command output, and reason-code sync.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for changed PHP files after the follow-up patch.
  - Operator-local rerun passed ImportPromote static guard, ImportPromote filter, Manual, Finalize, Publication, Evidence, and StaticGuard filters.
  - Operator-local Source filter exposed one remaining strict Mockery expectation mismatch for enriched `touchStage()` attributes; patch applied in `MarketDataPipelineServiceTest`.
  - PHPUnit/artisan not run in container because uploaded ZIP has no `vendor/`.
  - Operator-local `vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"` -> PASS: OK (46 tests, 355 assertions).
  - Operator-local `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` -> PASS: OK (91 tests, 1443 assertions).
  - Operator-local full `vendor/bin/phpunit tests/Unit/MarketData` -> PASS: OK (329 tests, 4110 assertions).

  [FINAL_RULE]
  - LOCKED. Hash/seal/dataset integrity must remain deterministic, config-driven, reason-coded, and auditable. No publication may become readable/current through missing or mismatched hash/seal/manifest context.
  - Sealed/current/readable live datasets must not be mutated through normal artifact replacement; correction and replacement promote flows must use publication-bound candidate history until finalize authorizes pointer/current promotion.
  - Future changes touching hash serialization, seal lifecycle, artifact mutation, finalize promotion, replacement candidates, correction, replay/evidence integrity proof, command output, or reason-code registry/seed must rerun targeted integrity/finalize/integration tests plus full `tests/Unit/MarketData`.

  [LOCK_CONDITION]
  - This contract remains LOCKED for the current source-of-truth ZIP. Reopen only if a future hash/seal/dataset mutation policy change or integrity regression is introduced.

---

- LOGGING_TRACEABILITY_REASON_CODES_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-07

  [RELATED_IMPLEMENTATION] Logging / Traceability / Reason Codes

  [REVIEW_STATUS] LOCKED_LOCAL_PHPUNIT_PASS

  [HISTORY]
  - 2026-05-07 -> Contract opened as canonical logging/traceability/reason-code contract under audit governance.
  - 2026-05-07 -> Static trace found registry/seed drift for runtime-used reason-code families and incomplete persisted trace for run creation and selected pointer/correction recovery paths.
  - 2026-05-07 -> Enforcement patch added `RUN_CREATED` persisted events, enriched stage-start context, reason-coded correction outcome events, reason-coded pointer recovery trace events, registry/seed reconciliation, logging inventory, and static guard coverage.
  - 2026-05-07 -> Contract held at ENFORCED because uploaded ZIP has no `vendor/`; local targeted/full PHPUnit is required before LOCKED.
  - 2026-05-07 -> Contract promoted from ENFORCED to LOCKED after operator-local validation passed: `LoggingTraceabilityReasonCodesStaticGuardTest.php` OK (7 tests, 134 assertions); targeted filters `Reason`, `Trace`, `Log`, `Event`, `Lifecycle`, `CommandSurface`, `Coverage`, `Finalize`, `Pointer`, `Publication`, `Correction`, `Replay`, `Evidence`, `Source`, `Provider`, `ManualFile`, and `Integration` all PASS; full `tests/Unit/MarketData` OK (319 tests, 4033 assertions).

  [DEFINED]
  - Every important market-data lifecycle event must be persisted or represented by an auditable trace artifact.
  - Failure, held, blocked, skipped, not-readable, mismatch, destructive, correction, replay, and evidence-incomplete outcomes must use registered reason codes.
  - Run lifecycle must be traceable from `RUN_CREATED` through stage events and terminal finalize/held/failed events.
  - Source/API/manual-file, coverage, finalize, pointer/publication, correction, replay, evidence, session snapshot, repair, and command surfaces must preserve enough context for operator/audit explanation.
  - Reason-code registry and seed must remain synchronized.

  [IMPLEMENTED]
  - `EodRunRepository` persists `RUN_CREATED` for newly created owning runs and seed-derived promote runs.
  - `MarketDataPipelineService` enriches `STAGE_STARTED` payloads and reason-codes correction unchanged/published events.
  - Pointer restore/resolution/mirror-repair/cleanup recovery branches append reason-coded trace events.
  - `Reason_Codes_Registry.md` and `Reason_Codes_Seed.sql` were reconciled for run, coverage, publication, pointer, correction, evidence, and replay reason-code families.
  - `LOGGING_TRACEABILITY_REASON_CODES_INVENTORY.md` defines the current traceability inventory and static/PHPUnit status.
  - `LoggingTraceabilityReasonCodesStaticGuardTest` enforces registry/seed sync and minimum lifecycle/recovery traceability constraints.

  [ENFORCED]
  - Static guard fails if registry and seed drift.
  - Static guard fails if critical lifecycle trace events, failure reason codes, correction/pointer trace markers, logging inventory, or no-latest shortcut protections are removed.
  - Runtime code now writes explicit trace events for run creation and selected pointer/correction recovery paths.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` PASS for changed PHP files and new static guard.
  - PHPUnit/artisan not run in container because uploaded ZIP has no `vendor/`.
  - Operator-local `vendor/bin/phpunit tests/Unit/MarketData/LoggingTraceabilityReasonCodesStaticGuardTest.php` -> PASS: OK (7 tests, 134 assertions).
  - Operator-local targeted filters `Reason`, `Trace`, `Log`, `Event`, `Lifecycle`, `CommandSurface`, `Coverage`, `Finalize`, `Pointer`, `Publication`, `Correction`, `Replay`, `Evidence`, `Source`, `Provider`, `ManualFile`, and `Integration` -> PASS.
  - Operator-local full `vendor/bin/phpunit tests/Unit/MarketData` -> PASS: OK (319 tests, 4033 assertions).

  [FINAL_RULE]
  - LOCKED. Market-data code must not create final failure/held/not-readable/skipped/blocked/mismatch/destructive outcomes without a registered reason code and auditable trace context.
  - `Reason_Codes_Registry.md` and `Reason_Codes_Seed.sql` must stay synchronized.
  - `RUN_CREATED`, `STAGE_STARTED`, stage completion/failure, and terminal finalize/held/failed events are the minimum run lifecycle trace chain.
  - Correction unchanged/published and pointer recovery outcomes must be reason-coded and linked to run/publication/correction context.

  [NEXT_ACTION]
  - Keep this as the canonical locked contract for logging/traceability/reason codes.
  - Future changes touching lifecycle logging, reason codes, registry/seed, command output, provider/manual file, correction, replay, evidence, pointer, finalize, coverage, or publication state must rerun targeted filters plus full `tests/Unit/MarketData`.


- COMMAND_SURFACE_SAFETY_OPS_LAYER_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-07

  [RELATED_IMPLEMENTATION] Command Surface Safety / Ops Layer

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-07 -> Contract opened as canonical command/ops layer safety contract under audit governance.
  - 2026-05-07 -> Static trace found destructive purge gap in `market-data:session-snapshot:purge`: row deletion had no explicit apply guard and no dry-run default.
  - 2026-05-07 -> Enforcement patch added dry-run/apply purge behavior, candidate counting, reason-coded operator output, command validation helpers, command reason-code registry/seed entries, command surface inventory, session snapshot runbook update, and static guard coverage.
  - 2026-05-07 -> Contract held at ENFORCED because uploaded ZIP has no `vendor/`; operator-local targeted/full PHPUnit is required before LOCKED.
  - 2026-05-07 -> Operator-local validation confirmed purge dry-run/apply behavior and most targeted filters, but exposed one static guard false negative coupling `COMMAND_DRY_RUN_ONLY` to the command file instead of the service-owned purge summary.
  - 2026-05-07 -> Fix2 updates the static guard architecture check and makes `SessionSnapshotService::purge()` dry-run by default unless `$apply=true` is explicit.
  - 2026-05-07 -> Contract promoted from ENFORCED to LOCKED after operator-local Fix2 validation passed: `CommandSurfaceSafetyStaticGuardTest.php` OK (5 tests, 81 assertions); `SessionSnapshotServiceTest.php` OK (6 tests, 38 assertions); `CommandSurface` filter OK (47 tests, 348 assertions); `DryRun` filter OK (2 tests, 15 assertions); `Apply` filter OK (4 tests, 26 assertions); full `tests/Unit/MarketData` OK (312 tests, 3899 assertions).

  [DEFINED]
  - Every market-data command must have clear input/output behavior.
  - Destructive operations must be non-mutating by default unless protected by a narrower lifecycle contract.
  - Purge/repair commands must require explicit `--apply` for mutation.
  - Invalid operator input must return `status=BLOCKED` and a registered `COMMAND_*` reason code.
  - Promote force behavior must remain default-off and auditable by reason.
  - Command output must not claim readable/published/success without the underlying service/repository contract proving that state.

  [IMPLEMENTED]
  - `COMMAND_SURFACE_SAFETY_INVENTORY.md` lists all registered market-data commands and their safety posture.
  - `SessionSnapshotService::purge()` defaults to dry-run, supports explicit apply, and includes candidate-row count.
  - `SessionSnapshotRepository::countBefore()` supports non-mutating purge previews.
  - `PurgeSessionSnapshotCommand` defaults to dry-run and requires `--apply` for deletion.
  - `RepairCurrentPublicationIntegrityCommand` renders dry-run/apply reason-code context.
  - `AbstractMarketDataCommand` centralizes command blocked output and common date/source validation.
  - `Reason_Codes_Registry.md` and `Reason_Codes_Seed.sql` include `COMMAND_*` reason codes.
  - `CommandSurfaceSafetyStaticGuardTest` guards inventory, destructive purge protection, service-owned purge reason codes, registry/seed sync, promote force guard, and repair apply guard.

  [ENFORCED]
  - `market-data:session-snapshot:purge` default execution is `DRY_RUN` and does not delete rows.
  - Purge delete only happens when `--apply` is supplied.
  - Command validation failures must be reason-coded and operator-readable.
  - Command-surface static guard prevents removal of purge/repair apply protection and command reason-code registry/seed sync.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for changed PHP files after the follow-up patch.
  - Operator-local rerun passed ImportPromote static guard, ImportPromote filter, Manual, Finalize, Publication, Evidence, and StaticGuard filters.
  - Operator-local Source filter exposed one remaining strict Mockery expectation mismatch for enriched `touchStage()` attributes; patch applied in `MarketDataPipelineServiceTest`.
  - PHPUnit/artisan not run in container because uploaded ZIP has no `vendor/`.
  - Operator-local pre-Fix2 evidence showed one static guard false negative while behavior-level purge/ops/session snapshot validations passed.
  - Operator-local Fix2 PASS: `CommandSurfaceSafetyStaticGuardTest.php` -> OK (5 tests, 81 assertions).
  - Operator-local Fix2 PASS: `SessionSnapshotServiceTest.php` -> OK (6 tests, 38 assertions).
  - Operator-local Fix2 PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` -> OK (47 tests, 348 assertions).
  - Operator-local Fix2 PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "DryRun"` -> OK (2 tests, 15 assertions).
  - Operator-local Fix2 PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Apply"` -> OK (4 tests, 26 assertions).
  - Operator-local Fix2 PASS: full `vendor/bin/phpunit tests/Unit/MarketData` -> OK (312 tests, 3899 assertions).

  [FINAL_RULE]
  - LOCKED. Market-data command surfaces may not perform destructive mutation by default, application services behind destructive commands must default to non-mutating behavior unless apply is explicit where the operation is destructive, command output must render registered reason-code summaries, and commands may not bypass locked publication/pointer/coverage/correction/replay/evidence service contracts. Purge and repair commands require explicit `--apply` for mutation; force-like behavior must remain default-off and reason-auditable.

  [NEXT_ACTION]
  - None for this contract. Future market-data command changes must preserve the command-surface static guard, registered reason-code registry/seed sync, destructive dry-run/apply behavior, and full MarketData PHPUnit validation.

---

- DB_INTEGRITY_CONSTRAINT_ENFORCEMENT_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-07

  [RELATED_IMPLEMENTATION] DB Integrity & Constraint Enforcement

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-07 -> Contract opened under audit governance without duplicating `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT`; this contract focuses on integrity/constraint enforcement rather than only four-way schema synchronization.
  - 2026-05-07 -> Static trace found SQLite mirror/index gaps, missing additive integrity migration, and missing reason-code registration for `RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID`.
  - 2026-05-07 -> Enforcement patch synchronized critical runtime indexes across SQL schema, additive migration, SQLite mirror, and schema/static tests.
  - 2026-05-07 -> Contract held at ENFORCED because uploaded ZIP has no `vendor/`; local migration/PHPUnit validation is required before LOCKED.
  - 2026-05-07 -> Operator-local tests exposed a test-fixture regression caused by the newly enforced publication version unique key; the fixture now uses a valid unique publication version and corrupts only the pointer mirror value to test repository fail-safe behavior.
  - 2026-05-07 -> Behavioral coverage inventory keeps the historical `ENFORCED_PENDING_LOCAL_PHPUNIT` marker required by its existing static guard while preserving `LOCKED_LOCAL_PHPUNIT_PASS` as the current behavioral status.
  - 2026-05-07 -> Contract promoted from ENFORCED to LOCKED after operator-local targeted validation passed for Repository, Pointer, Publication, Coverage, Integration, and full `tests/Unit/MarketData` passed with `OK (305 tests, 3795 assertions)`.

  [DEFINED]
  - Critical market-data tables must have explicit primary keys.
  - Critical business identities must have a unique key, primary key, or deterministic implicit guard with tests.
  - Runtime query paths must have supporting indexes in SQL schema, additive migration, and SQLite mirror.
  - Pointer/current publication resolution must be pointer-first and must validate publication/run mirror, coverage PASS, sealed publication, and `SUCCESS + READABLE`.
  - Physical FK coverage is selective; non-FK lifecycle relations must be explicitly guarded and tested as implicit integrity.
  - Enum-like values and reason codes must not exist only as raw runtime strings; registry/seed/test proof is required.

  [IMPLEMENTED]
  - `Database_Schema_MariaDB.sql` includes added indexes for readable run lookup, source identity, publication readable lookup, publication run/date lookup, pointer run/version lookup, publication-scoped artifact reads, correction status/execution lookup, correction prior/new linkage, replay publication identity, and replay reason-code lookup.
  - `2026_05_07_000002_enforce_market_data_db_integrity_indexes.php` adds idempotent index enforcement for already-bootstrapped databases.
  - `UsesMarketDataSqlite.php` mirrors critical PK/unique/index behavior, including replay reason-code composite primary key.
  - `MarketDataSqliteSchemaSyncTest` validates primary key and index mirror integrity.
  - `DbIntegrityConstraintEnforcementStaticGuardTest` validates locked schema integrity, implicit guard surfaces, enum-like values, registry/seed sync, and forbidden latest-date shortcuts.
  - `Reason_Codes_Registry.md` and `Reason_Codes_Seed.sql` include `RUN_FINALIZE_IDEMPOTENCY_POINTER_INVALID`.

  [ENFORCED]
  - SQL schema, additive migration, SQLite mirror, and tests must remain aligned for DB integrity keys/indexes.
  - Tests cannot pass against a weaker SQLite schema that omits critical runtime indexes or composite identities.
  - Current pointer ambiguity is blocked by pointer PK/publication uniqueness plus repository mirror checks.
  - Repository negative tests that simulate corrupted pointer mirrors must use schema-valid publication identities before corrupting pointer-only fields.
  - Publication ambiguity is constrained by `(trade_date, publication_version)` uniqueness and guarded run/publication lookup paths.
  - Replay reason-code counts are keyed by `(replay_id, trade_date, reason_code)`.
  - Any lifecycle relation without FK must stay covered by implicit repository/service/static guard proof.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for changed PHP files, including the fix2 `PublicationRepositoryIntegrationTest.php` change.
  - PHPUnit/artisan not run in container because uploaded ZIP has no `vendor/`.
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Repository"` -> OK (38 tests, 220 assertions).
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` -> OK (65 tests, 837 assertions).
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` -> OK (90 tests, 1007 assertions).
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Coverage"` -> OK (48 tests, 527 assertions).
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` -> OK (91 tests, 1443 assertions).
  - Operator-local PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (305 tests, 3795 assertions).

  [FINAL_RULE]
  - LOCKED. Market-data runtime code may not depend on a primary key, unique business key, pointer/publication/run relation, index, enum-like value, nullable/default assumption, or reason code that is not present in SQL schema/migration/SQLite mirror or protected by explicit implicit integrity guard and test. Schema-valid negative tests may corrupt only the specific mirror/context field under test; they must not bypass locked DB constraints to manufacture invalid state.

  [NEXT_ACTION]
  - None for this contract. Any future market-data schema/repository/read-side change must preserve the DB integrity static/schema guards and pass full `tests/Unit/MarketData`.

---

## RECENT LOCKED CONTRACT

- TEST_COVERAGE_BEHAVIORAL_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-07

  [RELATED_IMPLEMENTATION] Test Coverage Behavioral

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-07 -> Contract opened as canonical test coverage behavioral contract under audit governance.
  - 2026-05-07 -> Static trace found that core lifecycle areas already have DB-backed integration proof, but command surface tests are internal mock-heavy and must not be counted as lifecycle proof.
  - 2026-05-07 -> Gap found and patched: manual-file import-only behavior now has explicit DB-backed proof that it writes candidate bars without finalize, seal, coverage gate, current publication, or pointer switch.
  - 2026-05-07 -> Gap found and patched: manual-file promote from an imported partial dataset now has explicit DB-backed proof that coverage gate blocks readable publication and pointer switch with reason-coded finalization.
  - 2026-05-07 -> Behavioral coverage inventory and static guard were added to keep critical proof classification stable.
  - 2026-05-07 -> Contract held at ENFORCED because uploaded ZIP has no `vendor/`; operator-local targeted/full PHPUnit was required before LOCKED.
  - 2026-05-07 -> Operator-local targeted validation PASS: manual-file import-only 1 test / 19 assertions; manual-file promote partial 1 test / 17 assertions; `TestCoverageBehavioralStaticGuardTest.php` 5 tests / 108 assertions.
  - 2026-05-07 -> Operator-local filtered validation PASS: Behavior, Integration, Pipeline, Finalize, Coverage, Pointer, Correction, Replay, Evidence, Readable, Command, Manual, and Source filters all passed.
  - 2026-05-07 -> Operator-local focused file validation PASS: pipeline integration, readable publication contract, replay verification, replay determinism static guard, market-data evidence export, and ops command surface tests all passed.
  - 2026-05-07 -> Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 298 tests / 3327 assertions.
  - 2026-05-07 -> Contract promoted from ENFORCED to LOCKED after targeted, filtered, focused file, static guard, integration, and full MarketData unit validation passed.

  [DEFINED]
  - Lifecycle-critical coverage must be proven by runtime-like DB-backed tests whenever the behavior mutates run/publication/pointer/evidence/replay state.
  - Unit tests, command surface tests, static guards, and mock-heavy orchestration tests may support proof but must not be treated as primary lifecycle proof.
  - Internal repository/service mocks cannot be used to claim finalize, coverage, pointer, fallback, correction, replay, evidence, or read-side behavior is fully proven.
  - Boundary mocks are allowed only for external provider API, file input isolation, clock/time, command IO, or documented orchestration shells.
  - PASS/DONE/LOCKED requires local targeted and full MarketData PHPUnit validation.

  [IMPLEMENTED]
  - `docs/market_data/tests/Behavioral_Test_Coverage_Inventory.md` records area-level coverage, mock level, runtime proof status, gaps, and action.
  - `MarketDataPipelineIntegrationTest` includes explicit manual-file import-only and manual-file promote coverage-gate DB-backed tests.
  - `TestCoverageBehavioralStaticGuardTest` enforces inventory presence, DB-backed proof files, pipeline proof names, command-support classification, and static-guard-as-support rule.
  - Existing DB-backed proof files remain canonical for pipeline, repository, pointer/read-side, correction, replay result persistence, and SQLite schema.

  [ENFORCED]
  - Import-only cannot be accepted as publishable proof: test asserts unsealed non-current candidate, no pointer, no finalize event, and no coverage/seal/hash state.
  - Manual-file promote cannot bypass coverage: test asserts coverage FAIL, NOT_READABLE, no current pointer/publication, coverage counts, promote context, and reason-coded finalize event.
  - Static guard prevents lifecycle proof files from becoming internal Mockery/`shouldReceive` based.
  - Static guard requires command surface mock-heavy status to stay explicit and support-only.

  [VALIDATED]
  - Container static trace completed.
  - `php -l tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> no syntax errors detected.
  - `php -l tests/Unit/MarketData/TestCoverageBehavioralStaticGuardTest.php` -> no syntax errors detected.
  - Operator-local targeted validation PASS: manual-file import-only 1 test / 19 assertions; manual-file promote partial 1 test / 17 assertions; `TestCoverageBehavioralStaticGuardTest.php` 5 tests / 108 assertions.
  - Operator-local filtered validation PASS: Behavior 5 tests / 108 assertions; Integration 91 tests / 1443 assertions; Pipeline 91 tests / 1432 assertions; Finalize 44 tests / 311 assertions; Coverage 48 tests / 527 assertions; Pointer 65 tests / 837 assertions; Correction 61 tests / 1208 assertions; Replay 34 tests / 550 assertions; Evidence 34 tests / 520 assertions; Readable 54 tests / 375 assertions; Command 58 tests / 475 assertions; Manual 21 tests / 227 assertions; Source 35 tests / 386 assertions.
  - Operator-local focused file validation PASS: `MarketDataPipelineIntegrationTest.php` 55 tests / 1227 assertions; `ReadablePublicationReadContractIntegrationTest.php` 8 tests / 15 assertions; `ReplayVerificationServiceTest.php` 6 tests / 17 assertions; `ReplayDeterminismStaticGuardTest.php` 5 tests / 155 assertions; `MarketDataEvidenceExportServiceTest.php` 3 tests / 87 assertions; `OpsCommandSurfaceTest.php` 42 tests / 260 assertions.
  - Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 298 tests / 3327 assertions.

  [FINAL_RULE]
  - LOCKED. Behavioral coverage may be claimed only when lifecycle-critical behavior is backed by runtime-like DB/state proof, negative/fail-safe assertions, reason-code assertions, and regression static guards. Mock-heavy command/service/repository tests and static guards remain support evidence only and must not be used as primary lifecycle proof. Manual-file import-only must remain non-publishable, while manual-file promote must enforce coverage before any readable/current pointer switch.

  [NEXT_ACTION]
  - None for this contract. Keep future test additions aligned with the locked mock policy and behavioral inventory.

- REPLAY_DETERMINISM_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-07

  [RELATED_IMPLEMENTATION] Replay Determinism

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-07 -> Contract opened as canonical replay determinism contract under audit governance.
  - 2026-05-07 -> Static trace found fixture metadata/completeness, reason-coded mismatch, non-readable actual proof, replay artifact schema, command output, and registry gaps.
  - 2026-05-07 -> Enforcement patch implemented fixture schema v2 validation, complete expected/actual lifecycle context comparison, reason-coded mismatch families, fail-safe proof-incomplete behavior, deterministic-vs-volatile field separation, replay artifact persistence, command proof summary, fixture updates, and static guards.
  - 2026-05-07 -> Contract held at ENFORCED because container cannot run PHPUnit/artisan without `vendor/`; operator-local targeted and full validation still required before LOCKED.
  - 2026-05-07 -> Operator-local targeted validation PASS: replay verifier, replay static guard, replay evidence export, market-data evidence export, and ops command surface tests passed.
  - 2026-05-07 -> Operator-local filtered validation PASS: Replay/replay, Evidence, Command, Coverage, Pointer, Finalize, Correction, Manual, and Source filters passed.
  - 2026-05-07 -> Operator-local integration validation PASS: MarketData pipeline integration and readable publication read contract integration passed.
  - 2026-05-07 -> Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 291 tests / 3183 assertions.
  - 2026-05-07 -> Contract promoted from ENFORCED to LOCKED after targeted, filtered, integration, static guard, and full MarketData unit validation passed.

  [DEFINED]
  - Replay fixture is the expected proof source and must be stable, versioned, schema-checked, and self-contained.
  - Replay actual proof must come from evidence lifecycle context, not raw/staging/latest/MAX-date shortcut or volatile current DB state as expected source.
  - Replay must compare run, requested/effective date, request/source/provider/manual-file, coverage, artifact/hash/seal, publication, pointer, fallback, correction, final reason, and lineage context.
  - Every mismatch must have an explicit replay reason code and be persisted/exported in replay artifact/evidence.
  - Replay must ignore documented volatile runtime fields only; deterministic fields remain compared.
  - Incomplete fixture/actual proof must fail safe and cannot become wildcard PASS.

  [IMPLEMENTED]
  - `ReplayVerificationService` fixture loading, expected proof validation, actual evidence context building, comparison, mismatch reason coding, volatile-field tracking, and non-readable run handling.
  - `ReplayResultRepository`, migration, SQL schema, and SQLite test schema replay metric columns for fixture metadata, expected/actual contexts, mismatches, mismatch reason codes, deterministic fields checked, ignored volatile fields, and final reason code.
  - `MarketDataEvidenceExportService` replay export context extensions.
  - `VerifyReplayCommand` operator-grade output.
  - `ReplayDeterminismStaticGuardTest`, updated `ReplayVerificationServiceTest`, replay fixture v2 packages, and reason-code registry/seed entries.

  [ENFORCED]
  - `REPLAY_FIXTURE_SCHEMA_MISMATCH` for missing/incompatible fixture schema.
  - `REPLAY_EXPECTED_PROOF_INCOMPLETE` for missing expected fixture sections/files.
  - `REPLAY_ACTUAL_PROOF_INCOMPLETE` for missing actual run proof.
  - Specific replay reason-code families for source, provider, coverage, artifact/seal, publication, pointer, fallback, correction, final status/reason, lineage, unexpected success/failure, and non-deterministic output.
  - Static guard prevents latest/MAX/raw/staging shortcut usage in replay verifier/commands/repository and requires command/artifact/schema/registry surfaces.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for changed PHP files, including the fix2 `PublicationRepositoryIntegrationTest.php` change.
  - Operator-local targeted replay/evidence/command/static guard validation PASS.
  - Operator-local filtered replay/evidence/command/coverage/pointer/finalize/correction/manual/source validation PASS.
  - Operator-local integration validation PASS.
  - Operator-local full `tests/Unit/MarketData` validation PASS: 291 tests / 3183 assertions.

  [FINAL_RULE]
  - LOCKED. Replay may only produce deterministic MATCH when stable expected fixture proof and actual lifecycle proof match under explicit comparison. Any missing proof or divergent deterministic field must produce a failed/mismatched replay result with clear reason code. Replay verification must not mutate fixtures, derive expected from actual, or use latest/MAX/raw/staging shortcuts.

  [NEXT_ACTION]
  - None for replay determinism. Future replay changes must preserve this contract and re-run targeted plus full MarketData validation before any tracker change.

---

## VERIFIED CONTRACT ENTRIES

- SOURCE_PROVIDER_RESILIENCE_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-06

  [RELATED_IMPLEMENTATION] Source / Provider Resilience

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-03 -> Canonical source/provider resilience contract opened under audit governance.
  - 2026-05-03 -> Static trace reconciled existing source identity, ingest, degraded source, fallback preservation, coverage gate, finalize/publishability, evidence export, replay verification, command output, reason-code registry, repository persistence, and DB schema contracts.
  - 2026-05-03 -> Gap found: manual-file failure reason codes were not explicit because missing/unreadable/malformed input paths used generic runtime exceptions.
  - 2026-05-03 -> Gap found: Yahoo provider request telemetry was last-request based and did not aggregate per-ticker attempts/failures/missing tickers.
  - 2026-05-03 -> Gap found: partial provider output lacked a distinct source lifecycle reason code separate from coverage failure reason.
  - 2026-05-03 -> Gap found: evidence/replay did not fully persist and compare source/provider lifecycle context.
  - 2026-05-03 -> Enforcement patch added explicit manual-file source exceptions, aggregate Yahoo attempt/failure telemetry, source partial response reason code, source context evidence/replay persistence/comparison, command source-mode output, schema/registry sync, and static guards.
  - 2026-05-03 -> Operator-local validation found recovery gaps: `md_replay_daily_metrics` must not persist actual `source_file_*` columns, and static guard must assert the pipeline snake-case `source_final_reason_code` field instead of unrelated camel-case naming.
  - 2026-05-03 -> Recovery patch reconciled replay schema with existing SQLite contract, added a cleanup migration for prior-ZIP actual source file columns, and corrected source/provider static guard assertion.
  - 2026-05-06 -> Recovery-2 validation confirmed targeted source/provider recovery suites PASS: `PublicApiEodBarsAdapterTest.php` 12 tests / 70 assertions; `MarketDataEvidenceExportServiceTest.php` 3 tests / 52 assertions; `ReadablePublicationReadContractIntegrationTest.php` 8 tests / 15 assertions; `ReplayVerificationServiceTest.php` 5 tests / 15 assertions.
  - 2026-05-06 -> Full operator-local validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 276 tests / 2722 assertions.
  - 2026-05-06 -> Contract promoted from ENFORCED to LOCKED after targeted and full MarketData unit validation passed.

  [DEFINED]
  - Source mode must be explicit and immutable for the run lifecycle.
  - API and manual-file source identities must not be mixed.
  - Timeout, rate-limit, retry exhaustion, manual-file missing/unreadable/malformed, and partial source response must have explicit reason codes.
  - Source failure must not create `SUCCESS + READABLE`, switch pointer, make candidate current, hide reason code, or bypass coverage/finalize.
  - Partial source output must remain under coverage gate and finalize/publishability decision.
  - Valid source fallback must use internal previous readable publication resolver only, never raw/staging/latest/MAX-date shortcut.
  - Evidence, replay, and command surfaces must expose source/provider lifecycle context.

  [IMPLEMENTED]
  - `LocalFileEodBarsAdapter` maps manual-file input failures to explicit `SourceAcquisitionException` reason codes.
  - `PublicApiEodBarsAdapter` aggregates Yahoo per-ticker telemetry and marks partial source output with `RUN_SOURCE_PARTIAL_RESPONSE`.
  - `MarketDataEvidenceExportService` preserves source-failure evidence through explicit source telemetry paths, while `EodEvidenceRepository::dominantReasonCodes()` remains gated by valid readable pointer/publication/run context to prevent non-readable reason-code leakage.
  - `ReplayVerificationService` and `ReplayResultRepository` persist and compare source/provider expected/actual context.
  - Runtime migration, SQL schema, and SQLite mirror include replay source/provider lifecycle columns.
  - Replay actual source file hash columns are intentionally not persisted in `md_replay_daily_metrics`; only expected source file fields remain there, while run/publication/evidence context keeps source file identity where the schema already permits it.
  - `AbstractMarketDataCommand` exposes source mode and source lifecycle context for operator output/artifacts.
  - Reason-code registry/seed includes partial/manual-file source codes.
  - `SourceProviderResilienceStaticGuardTest` protects source/provider resilience invariants.

  [ENFORCED]
  - Manual file is `LOCAL_FILE` with provider `null`; API remains provider-backed and does not read manual file.
  - Source timeout/rate-limit/retry attempt context is carried to run/evidence/command and replay.
  - Partial provider output is not silently full success; it is traceable and still coverage-gated.
  - Non-readable source-failure run evidence/replay does not require a fake readable publication path.
  - Replay can detect source mode/provider/reason/retry/file context mismatch when fixture expectations provide those fields.
  - Static guard blocks identity mixing, silent source failure, missing source lifecycle context, and latest-date shortcut patterns.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for all changed PHP files.
  - `vendor/` and `vendor/bin/phpunit` are absent from uploaded ZIP; PHPUnit/artisan validation was performed operator-local.
  - Container runtime shortcut scan found no forbidden latest trade-date fallback patterns in app runtime paths; forbidden literals exist only in static guard/test docs by design.
  - Operator-local first validation failed for Source/Provider filters due schema/static-guard recovery issues; recovery patch was applied.
  - Operator-local targeted source/provider recovery validation PASS: `PublicApiEodBarsAdapterTest.php` -> 12 tests / 70 assertions; `MarketDataEvidenceExportServiceTest.php` -> 3 tests / 52 assertions; `ReadablePublicationReadContractIntegrationTest.php` -> 8 tests / 15 assertions; `ReplayVerificationServiceTest.php` -> 5 tests / 15 assertions.
  - Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 276 tests / 2722 assertions.

  [FINAL_RULE]
  - LOCKED. Source/provider failure must remain explicit, traceable, and non-readable unless coverage/finalize produce a valid readable publication or internal fallback preserves a previous readable publication. API/manual-file identity, timeout/retry/rate-limit telemetry, partial response handling, evidence/replay source context, command output, and pointer preservation are protected by code/static guards and validated by targeted plus full MarketData unit PASS evidence.

  [LOCK_CONDITION]
  - Satisfied by operator-local targeted source/provider recovery validation and full `tests/Unit/MarketData` PASS.

---

- CORRECTION_LIFECYCLE_SAFETY_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-03

  [RELATED_IMPLEMENTATION] Correction Lifecycle Safety

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-03 -> Canonical correction lifecycle safety contract opened under audit governance.
  - 2026-05-03 -> Static trace reconciled existing finalize/lock/pointer determinism, coverage gate enforcement, read-side pointer enforcement, publishability state integrity, fallback preservation, artifact seal, evidence export, replay verification, command output, repository persistence, and DB schema contracts.
  - 2026-05-03 -> Gap found: correction evidence did not reliably derive baseline/candidate publication ids from prior/new run linkage.
  - 2026-05-03 -> Gap found: artifact diff was boolean-only and lacked explicit invalid/incomplete hash state, reason code, changed scope, and hash context.
  - 2026-05-03 -> Gap found: replay did not persist/compare correction lifecycle context, allowing correction expected/actual drift to remain hidden.
  - 2026-05-03 -> Gap found: correction command did not display unchanged/reseal/baseline/candidate pointer state.
  - 2026-05-03 -> Enforcement patch added deterministic artifact comparison, invalid diff fail-fast, unchanged no-reseal/no-switch context, correction evidence linkage derivation, replay correction expected/actual fields, command lifecycle output, DB/schema sync, and static guards.
  - 2026-05-03 -> Operator-local validation returned migration PASS but targeted/full PHPUnit FAIL due evidence `seal_state` access, stale `PublicationDiffService` mock expectations, and static guard string interpolation.
  - 2026-05-03 -> Recovery patch fixed those regressions without changing the final correction lifecycle contract rule.
  - 2026-05-03 -> Operator-local recovery validation PASS: targeted Correction, Unchanged, Reseal, Hash, Evidence, Replay, Finalize, Publication suites and full `tests/Unit/MarketData` all passed; contract promoted to LOCKED.

  [DEFINED]
  - Correction baseline must be current-readable pointer-resolved and must satisfy `SUCCESS + READABLE + SEALED + coverage PASS + run/publication mirror`.
  - Correction baseline must not use `MAX(trade_date)`, `latest('trade_date')`, `orderByDesc('trade_date')`, latest successful run, sealed-only fallback, raw/staging shortcut, or arbitrary latest date shortcut.
  - Unchanged artifacts must produce unchanged/no-reseal/no-pointer-switch/no-new-current behavior.
  - Changed artifacts must produce reseal/pointer switch only after complete deterministic hash comparison and valid linkage.
  - Evidence/replay/command surfaces must expose correction lifecycle context.

  [IMPLEMENTED]
  - `PublicationDiffService::compare()` defines `INVALID`, `UNCHANGED`, and `CHANGED` decisions with reason code and hash context.
  - `MarketDataPipelineService::completeFinalize()` blocks invalid correction artifact comparison before pointer switch and requires `CHANGED` before correction history promotion/reseal path.
  - `EodEvidenceRepository` derives correction publication context from prior/new run linkage.
  - `MarketDataEvidenceExportService` writes `correction_lifecycle` with baseline/candidate/run/seal/current/reseal/changed/final-outcome context.
  - `ReplayVerificationService` and `ReplayResultRepository` carry and compare correction lifecycle context when fixture expectations provide it.
  - Runtime migration, SQL schema, and SQLite mirror include correction lifecycle replay columns.
  - `RunCorrectionCommand` outputs correction outcome, reseal status, baseline publication id, candidate publication id, candidate switch state, and final outcome note.
  - `CorrectionLifecycleSafetyStaticGuardTest` guards the critical static invariants.
  - Recovery patch aligns tests and evidence access with the enforced `PublicationDiffService::compare()` contract.

  [ENFORCED]
  - Invalid/incomplete correction hashes cannot proceed to pointer switch.
  - Unchanged correction keeps previous current readable publication and records `NOT_RESEALED_UNCHANGED` context.
  - Changed correction requires explicit changed artifact comparison before reseal/promotion.
  - Replay can compare correction expected/actual lifecycle fields and fail on mismatch when expected fields are present.
  - Evidence derives correction publication context from durable run/publication linkage rather than assuming non-schema correction columns.
  - Command output no longer hides correction lifecycle state.

  [VALIDATED]
  - Container static trace completed.
  - Container `php -l` passed for all changed PHP files.
  - `vendor/` and `vendor/bin/phpunit` are absent from uploaded ZIP; no PHPUnit/artisan command was run in container.
  - Operator-local migration PASS.
  - Operator-local first PHPUnit validation FAIL: Correction filter 3 errors + 1 failure; full `tests/Unit/MarketData` 5 errors + 1 failure; focused PipelineIntegration, PublicationFinalizeOutcome, and ReadablePublicationReadContractIntegration PASS.
  - Recovery ZIP container `php -l` passed for changed recovery files.
  - Operator-local recovery validation PASS: `Correction` 59 tests / 1146 assertions; `Unchanged` 9 / 63; `Reseal` 5 / 46; `Hash` 8 / 24; `Evidence` 27 / 241; `Replay` 25 / 257; `Finalize` 42 / 261; `Publication` 88 / 906.
  - Operator-local full validation PASS: `vendor/bin/phpunit tests/Unit/MarketData` -> 271 tests / 2613 assertions.

  [FINAL_RULE]
  - LOCKED. Correction may publish a new readable current publication only when baseline is pointer-resolved, artifacts are complete and changed, reseal/linkage is valid, and post-switch pointer resolution matches the candidate. Unchanged or invalid corrections must preserve the previous current readable publication and expose the lifecycle outcome in evidence/replay/command surfaces.

  [LOCK_CONDITION]
  - Satisfied by operator-local targeted correction lifecycle validation and full `tests/Unit/MarketData` PASS.

---

- FINALIZE_LOCK_POINTER_DETERMINISM_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-03

  [RELATED_IMPLEMENTATION] Finalize / Lock / Pointer Determinism

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-02 -> Canonical finalize/lock/pointer determinism contract opened under audit governance.
  - 2026-05-02 -> Static trace reconciled existing publishability state integrity, coverage gate enforcement, read-side pointer enforcement, fallback preservation, correction safety, evidence export, replay verification, command output, repository persistence, and static guards.
  - 2026-05-02 -> Existing contract coverage confirmed: pointer promotion is transaction-protected, post-switch pointer resolver mismatch throws, readable resolver enforces SUCCESS/READABLE/PASS/SEALED/current/mirror state, and fallback does not use raw/staging/latest shortcut.
  - 2026-05-02 -> Gap found and patched: completed `SUCCESS + READABLE + current` finalize rerun could return idempotently from run state without re-validating current-readable pointer identity.
  - 2026-05-02 -> Enforcement added: completed-readable rerun must resolve through the current-readable pointer contract to the same run/publication/version; malformed pointer fails safe as `HELD + NOT_READABLE + RUN_LOCK_CONFLICT` without duplicate publication or pointer switch.
  - 2026-05-02 -> Static guard and integration test were added for the idempotency pointer corruption edge.
  - 2026-05-03 -> Operator local validation passed migration, targeted finalize/idempotent/lock/pointer/publication/readable/correction/fallback/evidence/replay/command suites, full `tests/Unit/MarketData`, and required focused test files. Contract promoted to LOCKED.

  [DEFINED]
  - Finalize idempotency boundary is `run_id`, constrained by requested trade date and the persisted final state.
  - Completed `SUCCESS + READABLE + current` finalize rerun is valid only when current-readable pointer resolution returns the same run id, publication id, publication version, and trade date.
  - Pointer validity requires sealed current publication, run/publication mirror consistency, run terminal status SUCCESS, publishability state READABLE, coverage PASS, and pointer-resolved identity.
  - Lock/promotion mutation must remain atomic: invalid post-switch state rolls back or fails safe without leaving candidate current.
  - Fallback/correction must preserve previous readable pointer context and must not invent effective date or switch to latest/MAX date shortcut.

  [IMPLEMENTED]
  - `MarketDataPipelineService` validates completed-readable idempotency through `findReadableCurrentPublicationForRun()` before short-circuiting.
  - `MarketDataPipelineService` fails safe when a completed-readable rerun no longer resolves to the same current-readable pointer identity.
  - Existing `EodPublicationRepository` current-readable resolver remains the authoritative pointer gate and enforces SUCCESS/READABLE/PASS/SEALED/current/mirror predicates.
  - Existing promotion path remains transaction-wrapped and post-switch resolver-asserted.
  - Integration and static guard tests cover the idempotency pointer corruption edge.

  [ENFORCED]
  - Completed readable rerun cannot return from run state alone.
  - Malformed current pointer cannot keep a run exposed as readable through finalize idempotency.
  - Duplicate publication/current pointer creation is blocked on completed-run rerun.
  - Static guard checks the presence of pointer validation, identity comparison, fail-safe clearing, explicit event, and `RUN_LOCK_CONFLICT` reason code.
  - Runtime tests confirm finalize/idempotent/lock/pointer/publication/readable/correction/fallback/evidence/replay/command paths remain compatible with the contract.

  [VALIDATED]
  - Container static scan completed.
  - Container `php -l` passed for changed PHP files, including the fix2 `PublicationRepositoryIntegrationTest.php` change.
  - Operator local command: `php artisan migrate:fresh --env=testing` -> PASS.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"` -> PASS; `OK (41 tests, 248 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "finalize"` -> PASS; `OK (41 tests, 248 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Idempotent"` -> PASS; `OK (2 tests, 15 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "idempotent"` -> PASS; `OK (2 tests, 15 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Lock"` -> PASS; `OK (16 tests, 87 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "lock"` -> PASS; `OK (16 tests, 87 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` -> PASS; `OK (57 tests, 633 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "pointer"` -> PASS; `OK (57 tests, 633 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` -> PASS; `OK (85 tests, 887 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "readable"` -> PASS; `OK (51 tests, 309 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` -> PASS; `OK (54 tests, 1081 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Fallback"` -> PASS; `OK (29 tests, 609 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` -> PASS; `OK (26 tests, 228 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> PASS; `OK (24 tests, 237 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Command"` -> PASS; `OK (52 tests, 331 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData` -> PASS; `OK (264 tests, 2542 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> PASS; `OK (53 tests, 1191 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php` -> PASS; `OK (13 tests, 66 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php` -> PASS; `OK (12 tests, 52 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php` -> PASS; `OK (8 tests, 15 assertions)`.

  [FINAL_RULE]
  - LOCKED. Finalize rerun may return an existing final outcome only if the final run state and current-readable pointer identity still agree. A completed `SUCCESS + READABLE` run with malformed/mismatched pointer must fail safe and must not create another publication, switch pointer blindly, or expose an invalid readable/current state.
  - Pointer-valid readable state requires `SUCCESS + READABLE + PASS + SEALED + current + pointer-resolved + run/publication mirror` consistency.
  - Fallback/correction paths must preserve deterministic previous-readable pointer context and must not use latest/MAX/raw/staging shortcuts.

  [LOCK_CONDITION]
  - LOCKED after operator local validation confirmed targeted finalize/idempotent/lock/pointer/publication/readable/correction/fallback/evidence/replay/command suites, full `tests/Unit/MarketData`, and focused pipeline/finalize/outcome/readable test files all PASS.
  - Reopen only if a future finalize/pointer/lock/fallback/correction/evidence/replay/command/repository path changes this idempotency or pointer-determinism contract.
---

- PUBLISHABILITY_STATE_INTEGRITY_CONTRACT -> LOCKED

  [LAST_UPDATED] 2026-05-02

  [RELATED_IMPLEMENTATION] Publishability State Integrity / No Invalid State Combination

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-02 -> Canonical publishability state integrity contract opened under audit governance.
  - 2026-05-02 -> Static trace reconciled existing coverage gate, read-side pointer, finalize, fallback, correction, evidence, replay, command, DB schema, and static guard contracts.
  - 2026-05-02 -> Gap found and patched: missing publication identity could be treated as a readable pointer match through null-to-empty-string comparison.
  - 2026-05-02 -> Gap found and patched: post-switch pointer mismatch returned false instead of failing promotion/restore transaction.
  - 2026-05-02 -> Gap found and patched: evidence/replay did not fully carry and compare state context for publishability/publication/current-pointer identity.
  - 2026-05-02 -> Operator local validation exposed a false post-switch integrity failure: valid promotions were rejected with `RUN_PUBLICATION_ID_MISMATCH` because pointer-resolved rows did not expose `pointer_publication_id`.
  - 2026-05-02 -> Recovery patch requires pointer publication identity aliases on resolver rows and validates raw pointer/publication/run mirrors before resolving the current readable publication.
  - 2026-05-02 -> Recovery-1 local validation proved pointer switching now PASS but finalize still downgraded valid paths to HELD.
  - 2026-05-02 -> Recovery-2 requires Lumen-safe Carbon timestamp DB priming before pointer switch and requires pipeline finalize to re-resolve the current readable publication through the pointer resolver before accepting READABLE outcome.
  - 2026-05-02 -> Operator local validation after Recovery-2 confirmed the repository/integration/evidence contract path is healthy; remaining failures were unit-test mock expectations that omitted the enforced post-promotion readable resolver proof.
  - 2026-05-02 -> Recovery-3 updates the unit proof surface to require `resolveCurrentReadablePublicationForTradeDate()` in correction publish/conflict tests, preserving the stricter contract while unblocking final local validation.
  - 2026-05-02 -> Final operator local validation passed targeted `Finalize`, targeted `Correction`, and full `tests/Unit/MarketData`; contract promoted to LOCKED.

  [DEFINED]
  - `READABLE` is valid only when run terminal status is SUCCESS, run publishability state is READABLE, coverage gate is PASS with complete telemetry, publication is SEALED, publication is current, pointer resolves to the same publication/run/version, and run-publication mirror fields match.
  - `NOT_READABLE`, `HELD`, or controlled failure is required when coverage, seal, pointer, mirror, fallback, correction baseline, or state context is invalid.
  - `HELD` may preserve only a previous readable publication resolved through the fallback/pointer contract.
  - Candidate publications must not become consumer-readable unless they pass the complete state matrix.

  [IMPLEMENTED]
  - Publication outcome now requires explicit candidate/current identity before READABLE and rejects unchanged correction if current pointer identity is unproven.
  - Pointer promotion/restore now fails transaction on unresolved or mismatched post-switch current-readable pointer state.
  - Pointer-resolved repository rows now carry `pointer_publication_id`, and post-switch assertion uses raw pointer state to distinguish real mirror violations from missing selected aliases.
  - Candidate promotion requires a pre-approved `SUCCESS + READABLE` run before pointer switch, validates intended final READABLE identity in memory, and persists run publication/current mirrors only after pointer/publication switch state is written.
  - Pipeline pre-approval uses `Carbon::now(config('market_data.platform.timezone'))` to avoid silently failing DB priming in Lumen contexts where the `now()` helper is unavailable.
  - Pipeline outcome uses `resolveCurrentReadablePublicationForTradeDate()` after promotion as the authoritative proof of current-readable publication identity.
  - Unit-level correction finalize tests now model the same resolver proof instead of implicitly treating `promoteCandidateToCurrent()` return value as sufficient proof.
  - Evidence export now includes run terminal status, publishability state, coverage state, publication identity/version/seal/current state, and pointer validation context.
  - Replay verification and replay result persistence now include expected/actual terminal, publishability, publication id, publication run id, and current-publication state context.
  - Command output now surfaces effective date, publication id/version, and current-publication flag when available.

  [ENFORCED]
  - Static guards assert no readable outcome from missing publication identity.
  - Static guards assert post-switch pointer checks throw instead of returning false.
  - Static guards assert pointer-resolved current rows select `ptr.publication_id as pointer_publication_id` and post-switch checks inspect raw pointer integrity.
  - Static guards assert pipeline finalize uses Lumen-safe Carbon timestamp priming and authoritative pointer resolver proof before readable outcome.
  - Static guards assert evidence/replay contain publishability and publication/current-pointer state fields.
  - Schema sync tests assert SQL, migration, and SQLite replay metric state-context columns.

  [VALIDATED]
  - Container static scan completed.
  - Container `php -l` completed for changed PHP files.
  - Local PHPUnit/artisan validation was supplied by operator because `vendor/` is absent from the uploaded ZIP.
  - Operator local validation after first patch showed migration and several targeted suites PASS, but full MarketData suite failed with valid promotion/correction paths becoming HELD due to `RUN_PUBLICATION_ID_MISMATCH`; Recovery-1 patch applied.
  - Operator local validation after Recovery-1: pointer filter PASS, but Publication/Finalize/Correction/Evidence/Pipeline/full suite still failed because valid finalize paths remained HELD; Recovery-2 patch applied.
  - Operator local validation after Recovery-2: Publication, Evidence, and PipelineIntegration all PASS; full suite had only two remaining Mockery expectation errors in `MarketDataPipelineServiceTest`; Recovery-3 patch applied.
  - Operator local validation after Recovery-3: `vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"` -> PASS; `OK (39 tests, 225 assertions)`.
  - Operator local validation after Recovery-3: `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` -> PASS; `OK (54 tests, 1081 assertions)`.
  - Operator local validation after Recovery-3: `vendor/bin/phpunit tests/Unit/MarketData` -> PASS; `OK (262 tests, 2519 assertions)`.

  [FINAL_RULE]
  - LOCKED. No market-data path may expose a publication as READABLE/current unless run, publication, pointer, coverage, fallback/correction, evidence, replay, and command state context agree on the same valid publication identity; pointer publication identity must be present in resolver rows before mirror checks are evaluated, and pipeline finalize must use the current-readable pointer resolver as authoritative post-promotion proof.
  - Invalid state combinations must fail safe as NOT_READABLE, HELD, controlled exception, or preserved previous readable pointer context according to the locked contract.

  [LOCK_CONDITION]
  - LOCKED after operator local validation confirmed targeted `Finalize`, targeted `Correction`, and full `tests/Unit/MarketData` all PASS without weakening assertions or schema constraints.
  - Reopen only if a future finalize/publication/pointer/fallback/correction/evidence/replay/command/repository path changes this no-invalid-state-combination contract.

---

[HISTORICAL_CONTEXT_2026_05_02_COVERAGE_GATE_ENFORCEMENT_LOCKED]

  [LAST_UPDATED] 2026-05-02

  [RELATED_IMPLEMENTATION] Coverage Gate Enforcement / No Coverage Bypass

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 -> Contract enforcement session opened under audit governance.
  - 2026-05-01 -> Static trace found readable/current paths that relied on PASS state without complete coverage telemetry proof.
  - 2026-05-01 -> Enforcement added to guard, finalize decision, publication outcome, pipeline finalize guard states, pointer repository predicates, and static tests.
  - 2026-05-01 -> Operator local validation exposed recovery gaps: static guard Lumen path resolution, coverage alias conflict handling, incomplete mocked coverage summaries, and readable baseline/fallback fixtures missing complete telemetry.
  - 2026-05-01 -> Recovery patch applied to keep contract strict while restoring valid correction/fallback behavior through complete coverage telemetry and post-query guard validation.
  - 2026-05-01 -> Recovery validation exposed and resolved correction/fallback regressions without weakening coverage no-bypass enforcement.

  - 2026-05-02 -> Final operator local validation passed: pipeline integration, pointer, coverage, finalize, publication, readable, evidence, replay, command, evaluator, finalize decision, publication outcome, static guard, and full MarketData suite. Contract promoted to LOCKED.

  [DEFINED]
  - Coverage gate is valid only when expected universe count, available EOD count, missing EOD count, coverage ratio, threshold value, threshold mode, gate state, reason code, universe basis, and contract version are deterministic and traceable.
  - READABLE/current publication requires coverage PASS plus complete persisted coverage telemetry.
  - FAIL or NOT_EVALUABLE coverage must not publish a new readable publication or switch current pointer.
  - Empty universe or incomplete PASS context is NOT_EVALUABLE/fail-safe unless a future locked contract explicitly says otherwise.

  [IMPLEMENTED]
  - `MarketDataInvariantGuard` enforces complete coverage telemetry for readable/current/promotion/fallback states.
  - `FinalizeDecisionService` downgrades incomplete PASS coverage to NOT_EVALUABLE.
  - `PublicationFinalizeOutcomeService` preserves coverage summary for outcome guard validation.
  - `CoverageGateEvaluator` dedupes universe/available ticker counts and emits basis/contract/reason aliases.
  - `EodPublicationRepository` requires complete run coverage telemetry on readable pointer resolution and re-validates resolved rows through `MarketDataInvariantGuard`.
  - `EligibilitySnapshotScopeRepository` and `EodEvidenceRepository` require complete coverage telemetry before returning pointer-scoped consumer/evidence rows.
  - `CoverageGateNoBypassStaticGuardTest` added and made independent from Lumen `base_path()`.

  [ENFORCED]
  - Static guard coverage exists for complete telemetry requirements and no latest trade-date shortcut in runtime coverage/finalize/evidence/replay paths.
  - Runtime guard treats conflicting `coverage_gate_state` / `coverage_gate_status` aliases as NOT_EVALUABLE instead of allowing one alias to hide failure.
  - Syntax validation completed for changed PHP files.
  - Local PHPUnit validation passed after recovery patches, including targeted and full MarketData suites.

  [VALIDATED]
  - Container static scan completed.
  - Container `php -l` completed for changed PHP files.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` -> PASS; `OK (52 tests, 1182 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "pointer"` -> PASS; `OK (52 tests, 586 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData` -> PASS; `OK (258 tests, 2461 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "coverage"` -> PASS; `OK (38 tests, 283 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "finalize"` -> PASS; `OK (37 tests, 216 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` -> PASS; `OK (79 tests, 836 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "readable"` -> PASS; `OK (49 tests, 297 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` -> PASS; `OK (26 tests, 216 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> PASS; `OK (24 tests, 215 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Command"` -> PASS; `OK (52 tests, 327 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/CoverageGateEvaluatorTest.php` -> PASS; `OK (4 tests, 38 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/FinalizeDecisionServiceTest.php` -> PASS; `OK (13 tests, 66 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php` -> PASS; `OK (10 tests, 43 assertions)`.
  - Operator local command: `vendor/bin/phpunit tests/Unit/MarketData/CoverageGateNoBypassStaticGuardTest.php` -> PASS; `OK (4 tests, 96 assertions)`.

  [FINAL_RULE]
  - LOCKED. No market-data path may mark a run/publication READABLE/current based only on `coverage_gate_state = PASS`. Complete coverage telemetry and internally consistent count/ratio/threshold math are required.
  - Coverage FAIL, NOT_EVALUABLE, empty universe, incomplete PASS context, conflicting coverage aliases, or invalid pointer/fallback telemetry must fail-safe and must not switch pointer to a new readable publication.
  - Evidence/replay/command surfaces must carry and validate coverage context, including threshold mode, universe basis, contract version, reason code, and expected/available/missing/ratio fields.

  [LOCK_CONDITION]
  - LOCKED for the current source-of-truth ZIP after local validation confirmed targeted coverage/finalize/publication/pointer/evidence/replay/command tests and full `tests/Unit/MarketData` all PASS.
  - Reopen only if a future coverage/finalize/publication/pointer/evidence/replay/command/repository path changes this no-bypass contract.

---

[HISTORICAL_CONTEXT_2026_05_01_READ_SIDE_POINTER_ENFORCEMENT_LOCKED]

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] Read-Side Enforcement / Anti Bypass Total

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Canonical read-side pointer enforcement contract opened under audit governance.
  - 2026-05-01 → Static trace confirmed the official consumer gateway is `EodPublicationRepository::resolveCurrentReadablePublicationForTradeDate($tradeDate)`.
  - 2026-05-01 → Gap found: pointer-scoped eligibility/evidence reads did not uniformly require `coverage_gate_state = PASS` and run mirror fields matching pointer publication metadata.
  - 2026-05-01 → Gap fixed in repository predicates and guarded through integration/static tests.
  - 2026-05-01 → Contract document synchronized to explicitly include coverage PASS and run mirror validation.
  - 2026-05-01 → Operator local PHPUnit evidence found correction/fallback regressions when consumer-only run mirror predicates were added to the internal prior-readable fallback lookup.
  - 2026-05-01 → Contract clarified that internal fallback lookup is not a consumer read gateway; consumer gateway/evidence/eligibility scope remain mirror-enforced.
  - 2026-05-01 → Operator retest confirmed targeted readable/pointer tests, full MarketData suite, readable-publication integration test, and pointer static guard all PASS after the regression patch.

  [DEFINED]
  - Consumer read paths must resolve through `eod_current_publication_pointer`.
  - Valid readable context requires sealed current publication, pointer/publication/run identity match, `terminal_status = SUCCESS`, `publishability_state = READABLE`, `coverage_gate_state = PASS`, `run.is_current_publication = 1`, and run `publication_id/publication_version` mirror match to the pointer.
  - Artifact rows returned to consumers must be scoped by `publication_id` and pointer-resolved `trade_date_effective`/trade date context.
  - No readable pointer context means fail-safe: empty controlled output, not-readable response, controlled exception, or explicit command/evidence/replay failure.
  - Internal prior-readable fallback lookup is allowed only for pipeline hold/degraded-mode/correction preservation and must not be used as an API/evidence/replay/consumer latest shortcut.

  [IMPLEMENTED]
  - `EligibilitySnapshotScopeRepository` enforces coverage PASS and run mirror match.
  - `EodEvidenceRepository::findPublicationForRun` enforces pointer/current/sealed/SUCCESS/READABLE/PASS/current/mirror validation.
  - `EodEvidenceRepository::exportEligibilityRows` enforces pointer-scoped readable eligibility context.
  - `EodEvidenceRepository::dominantReasonCodes` no longer returns reason-code output when the publication/run context is not current-readable/PASS/mirror-valid.
  - `EodPublicationRepository::findLatestReadablePublicationBefore` remains an internal fallback lookup only; it preserves pipeline correction/fallback behavior and must not be used as a consumer gateway.
  - Static guards and integration tests were extended for coverage PASS and run mirror requirements.

  [ENFORCED]
  - Static guard coverage exists for forbidden latest/MAX shortcuts in consumer files.
  - Static guard coverage exists for pointer gateway predicates.
  - Static guard coverage exists for pointer-scoped eligibility/evidence coverage PASS and run mirror checks.
  - Integration coverage exists for no-leak behavior when coverage is not PASS or run mirror mismatches pointer metadata.
  - Regression reconciliation exists for internal fallback lookup so consumer enforcement does not break prior-readable preservation behavior.

  [VALIDATED]
  - Container static grep/query scan completed.
  - Container `php -l` completed for changed PHP files.
  - Local command: `php artisan migrate:fresh --env=testing` → PASS.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "readable"` → PASS; `OK (45 tests, 256 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "pointer"` → PASS; `OK (51 tests, 551 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData` → PASS; `OK (250 tests, 2355 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php` → PASS; `OK (8 tests, 15 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/PublicationCurrentPointerReadinessStaticGuardTest.php` → PASS; `OK (3 tests, 23 assertions)`.

  [FINAL_RULE]
  - LOCKED. No market-data consumer may read raw/staging/latest/current artifact data unless it is resolved through the current readable publication pointer and validated against sealed publication, SUCCESS/READABLE/PASS run, current state, run mirror metadata, and publication scope.
  - No consumer may fallback to MAX/latest/raw/staging data when pointer resolution fails.
  - Internal prior-readable fallback remains allowed only for pipeline hold/degraded-mode/correction preservation and must not be exposed as consumer latest/read gateway.

  [LOCK_CONDITION]
  - This contract is locked for the current source-of-truth ZIP after targeted and full MarketData PHPUnit validation.
  - Reopen only if a future market-data read path, evidence/replay flow, repository method, command output, or fallback rule changes the pointer/readability enforcement contract.


---

- AUDIT_REBUILD_BASELINE_CONTRACT → LOCKED

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] Audit Rebuild Baseline / One-by-One Regression Review

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Clean contract tracker rebuild started; previous broad LOCKED/DONE list intentionally removed from active tracker until one-by-one retest evidence is supplied.
  - 2026-05-01 → First reviewed contract scope completed through `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT`; clean rebuild workflow is validated for continued use.

  [DEFINED]
  - This contract controls the clean audit rebuild mode after historical status uncertainty.
  - It requires future contract restoration to happen one scope at a time using current evidence.

  [IMPLEMENTED]
  - Implemented as a clean tracker structure with active session tracking, canonical contract entries, and no unverified historical LOCKED claims.
  - First restored locked contract is `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT`.

  [ENFORCED]
  - Any restored contract must have a matching implementation entry in `LUMEN_IMPLEMENTATION_STATUS.md`.
  - Any restored LOCKED contract must include current validation evidence and a final rule.
  - Duplicate contract fragments must be merged into the canonical contract entry.

  [VALIDATED]
  - First one-by-one retest scope completed: `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT` is validated and locked with local migration/PHPUnit evidence.

  [FINAL_RULE]
  - LOCKED. The audit rebuild model must restore contract status one concern at a time, backed by current evidence, with no duplicate contract entries and no unverified historical LOCKED carry-forward.

  [LOCK_CONDITION]
  - This governance baseline remains locked unless the audit strategy itself changes through an explicit audit-governance session.

---

- DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT → LOCKED

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] DB Schema & Migration Sync / Runtime Schema Four-Way Synchronization

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Contract enforcement started for DB schema synchronization across SQL docs, migrations, SQLite test schema, repository/query usage, and fixtures.
  - 2026-05-01 → Runtime-orphan SQLite surrogate keys were removed and artifact/history identity rules were aligned with runtime composite keys.
  - 2026-05-01 → Replay index naming and ticker timestamp behavior were synchronized between SQL schema and migrations.
  - 2026-05-01 → Migration-chain idempotency gaps were resolved for `md_session_snapshots` and correction reexecution policy fields.
  - 2026-05-01 → Strict SQLite/runtime constraints exposed fixture drift; fixtures were corrected rather than weakening the schema mirror.
  - 2026-05-01 → Repository restore-prior validation and pipeline promotion-failure fallback effective-date handling were aligned with pointer/publication/run integrity rules.
  - 2026-05-01 → Final local evidence confirmed migration fresh, schema guard, repository tests, pipeline integration tests, and full MarketData PHPUnit suite all PASS.
  - 2026-05-01 → Audit recovery applied: prior DB schema contract hotfix fragments were merged into this canonical locked contract entry.

  [DEFINED]
  - Runtime schema reference: `docs/market_data/db/Database_Schema_MariaDB.sql`.
  - Migration/runtime generation reference: market-data migrations under `database/migrations/`.
  - Test mirror reference: `tests/Support/UsesMarketDataSqlite.php`.
  - Query validation scope: market-data repository layer under `app/Infrastructure/Persistence/MarketData/` plus market-data services that persist artifacts, publications, runs, evidence, and correction outcomes.
  - Fixture/test validation scope: MarketData unit/integration tests that seed or read market-data runtime tables.

  [IMPLEMENTED]
  - SQLite-only surrogate keys were removed from current/history artifact tables.
  - SQL schema and migration replay index names were aligned.
  - Ticker timestamp behavior was aligned between migration and SQL schema.
  - Additive migrations were hardened against duplicate table/column creation when the canonical SQL schema already represents final state.
  - SQLite mirror defaults and constraints were aligned with MariaDB behavior where appropriate.
  - Repository/read-contract/pipeline fixtures now seed runtime-required fields explicitly.
  - Restore-prior validation rejects invalid fallback runs before restoring current pointer state.
  - Pipeline correction promotion failure handling preserves valid fallback effective date without publishing failed candidate state.

  [ENFORCED]
  - Market-data schema changes must be represented consistently across SQL docs, migration final state, SQLite test mirror, repository/query usage, and fixtures.
  - SQLite test schema must not contain runtime-orphan fields or looser behavior that creates false-positive tests.
  - Tests must obey runtime-required fields and composite unique keys.
  - Current pointer replacement and fallback restoration require aligned pointer/publication/run mirror metadata.
  - Migration history may use idempotent guards when the canonical SQL schema bootstrap already creates the final-state table or column.

  [VALIDATED]
  - `php artisan migrate:fresh --env=testing` → PASS.
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "schema"` → PASS; `OK (3 tests, 70 assertions)`.
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "Repository"` → PASS; `OK (33 tests, 180 assertions)`.
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → PASS; `OK (52 tests, 1182 assertions)`.
  - `vendor/bin/phpunit tests/Unit/MarketData` → PASS; `OK (244 tests, 2327 assertions)`.
  - Static validation during patch sequence: changed PHP files passed `php -l` before local reruns.

  [FINAL_RULE]
  - LOCKED. Market-data DB schema changes must stay in four-way sync across `Database_Schema_MariaDB.sql`, Laravel/Lumen migrations, SQLite test schema, and repository/test usage.
  - No market-data field, identity key, nullable/default behavior, index, unique constraint, enum/status value, or repository-used column may exist only in one layer.
  - Fixture/test failures caused by runtime-aligned constraints must be fixed in fixtures or implementation, not hidden by loosening SQLite schema.
  - Any future drift must be fixed directly or recorded as an explicit policy gap before related implementation work is marked DONE.

  [LOCK_CONDITION]
  - This contract remains locked for the current source-of-truth ZIP.
  - Reopen only through a schema/contract session if future migration, SQL schema, SQLite mirror, repository query, or fixture change introduces new drift or requires a deliberate breaking change.

## Recovery-3 malformed fallback pointer fix — Coverage Gate Enforcement / No Coverage Bypass

- Status: SUPERSEDED_BY_FINAL_LOCK.
- Local evidence received: static guard, Coverage, Publication, readable, Evidence, Replay, and Command suites passed; one integration/pointer failure remained for malformed fallback pointer effective-date handling.
- Recovery-3 fix: when correction pointer mismatch occurs and no contract-valid readable fallback exists, `trade_date_effective` is explicitly cleared to null instead of retaining the requested candidate date.
- Final result: superseded by Recovery-5 final local validation; `MarketDataPipelineIntegrationTest`, pointer filter, and full `tests/Unit/MarketData` all PASS.

## Recovery-4 fallback mirror fixture alignment — COVERAGE_GATE_ENFORCEMENT_CONTRACT

- Status: SUPERSEDED_BY_FINAL_LOCK.
- Local evidence received after Recovery-3: all targeted suites except pipeline integration/pointer fallback cases passed; full MarketData suite had four remaining fallback/effective-date failures.
- Enforcement recovery: fallback publication fixtures now satisfy strict pointer/publication/run mirror identity, and correction baseline pointer mismatch is treated as a pointer-integrity failure instead of a generic promotion error.
- Final result: superseded by Recovery-5 final local validation; `MarketDataPipelineIntegrationTest`, pointer filter, and full `tests/Unit/MarketData` all PASS.

## Recovery-5 baseline pointer mismatch message preservation — COVERAGE_GATE_ENFORCEMENT_CONTRACT

- Status: LOCKED by final local validation.
- Local evidence after Recovery-5: `MarketDataPipelineIntegrationTest`, pointer filter, targeted coverage/finalize/publication/readable/evidence/replay/command suites, core service tests, static guard, and full `tests/Unit/MarketData` all PASS.
- Enforcement recovery: pointer-integrity failures keep specific operator/audit messages for correction baseline mismatch while generic post-switch mismatch cases continue using the generic current publication pointer resolution message.
- Final lock completed for `COVERAGE_GATE_ENFORCEMENT_CONTRACT`.

## HASH_SEAL_DATASET_INTEGRITY_CONTRACT — Recovery round 3

- Status: LOCKED by final local validation.
- Enforcement recovery: replacement candidates must own a complete hashable candidate artifact scope before seal. When a promote run is derived from an existing current/complete seed without fresh candidate bars history, the system creates candidate-bound bars history from current live rows and keeps all derived artifacts/hash/seal operations in history scope.
- Final validation: `vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"` PASS with `OK (46 tests, 355 assertions)`; `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` PASS with `OK (91 tests, 1443 assertions)`; full `vendor/bin/phpunit tests/Unit/MarketData` PASS with `OK (329 tests, 4110 assertions)`.
- Final rule locked: sealed/current/readable live artifacts cannot be mutated before finalize authorizes pointer promotion; candidate replacement artifacts must be built and verified through publication-bound history.


## COVERAGE_GATE_CANDIDATE_SCOPE_HARDENING — COVERAGE_GATE_ENFORCEMENT_CONTRACT

- Status: PARTIAL / STATIC_GUARDED / WAITING_OPERATOR_LOCAL_PHPUNIT.
- Related implementation: Coverage Gate Candidate Scope Hardening.
- Existing owner: `COVERAGE_GATE_ENFORCEMENT_CONTRACT`; this is not coverage gate enforcement ulang and does not replace prior coverage gate enforcement history.
- Enforcement hardening: promote, manual promote, and correction coverage evaluation must use candidate publication / candidate artifact scope as coverage basis.
- The correction candidate must be evaluated separately from baseline/current publication.
- Baseline/current publication may be used for correction lineage, comparison, fallback preservation, and unchanged detection only. It must not be used as coverage basis for candidate publishability.
- Runtime patch: `MarketDataPipelineService` resolves `coverageBasisPublicationId` before coverage evaluation and records candidate/baseline proof in run notes.
- Runtime patch: `EodArtifactRepository` resolves candidate coverage ticker ids from `eod_bars_history` and `eod_bars` using explicit `publication_id`; no current pointer/latest/MAX-date fallback is used.
- Proof surface: command output, evidence export, and replay actual context expose `coverage_basis`, `coverage_basis_publication_id`, `coverage_basis_artifact_scope`, `candidate_publication_id`, and `baseline_publication_id`.
- Static guard: `CoverageGateCandidateScopeHardeningStaticGuardTest.php`.
- Inventory: `COVERAGE_GATE_CANDIDATE_SCOPE_HARDENING_INVENTORY.md`.
- Lock condition: promote/manual promote/correction targeted filters and full `vendor/bin/phpunit tests/Unit/MarketData` must PASS in operator-local environment before this hardening can be marked LOCKED.


---
