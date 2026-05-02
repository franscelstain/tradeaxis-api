# LUMEN_CONTRACT_TRACKER

## ACTIVE SESSION

ACTIVE SESSION:
- Publishability State Integrity / No Invalid State Combination

[SESSION_STATUS] LOCKED

[SESSION_SCOPE]
- Track no-invalid-state-combination enforcement across run, publication, current pointer, fallback, correction, evidence, replay, command, repository, schema, and tests.
- Contract is LOCKED after operator-supplied targeted finalize/correction and full MarketData PHPUnit evidence passed.

[SESSION_GOAL]
- `PUBLISHABILITY_STATE_INTEGRITY_CONTRACT` is locked after local validation proved READABLE/current states cannot bypass terminal SUCCESS, sealed publication, coverage PASS, run-publication mirror, pointer resolver, and fallback/correction safety.

[SESSION_NOTES]
- Static enforcement was added for publication identity matching, post-switch pointer assertion, evidence/replay state context, and replay persistence/schema sync.
- Container cannot validate PHPUnit/artisan because `vendor/` is not present in the ZIP; final runtime validation is based on operator-supplied local PHPUnit evidence.

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

- COVERAGE_GATE_ENFORCEMENT_CONTRACT -> LOCKED

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

- READ_SIDE_POINTER_ENFORCEMENT_CONTRACT → LOCKED

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

## VERIFIED CONTRACT ENTRIES

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
