# LUMEN_IMPLEMENTATION_STATUS

## ACTIVE SESSION

ACTIVE SESSION:
- Coverage Gate Enforcement / No Coverage Bypass

[SESSION_STATUS] DONE

[SESSION_SCOPE]
- Enforce deterministic coverage calculation and no-bypass behavior across finalize, publishability, pointer, evidence, replay, command, and repository surfaces.
- Status is DONE based on operator-supplied local PHPUnit evidence for targeted and full MarketData suites.

[SESSION_GOAL]
- DONE reached after operator supplied targeted and full MarketData PHPUnit evidence for coverage/finalize/publication/pointer/evidence/replay/command scopes.

[SESSION_NOTES]
- Static trace found coverage status PASS checks that did not require complete expected/available/missing/ratio/threshold/mode/basis/contract context.
- Static enforcement was patched so READABLE/current pointer decisions require complete and internally consistent coverage telemetry.
- Operator local rerun exposed regressions in static guard path resolution, coverage alias conflict handling, test fixtures missing new coverage telemetry, and fallback/correction baseline resolution. Recovery patches were applied and final local rerun passed targeted and full MarketData PHPUnit suites.

---

## OPERATIONAL STATUS

[CURRENT_AUDIT_MODE]
- CLEAN_START_RETEST

[HISTORICAL_STATUS_POLICY]
- Previous DONE/LOCKED claims are not copied as current status without fresh evidence.
- Current audit status is rebuilt from scoped test output, static trace, runtime proof, or explicit operator evidence.
- Revalidated scopes must be represented as canonical entries, not repeated hotfix/session fragments.

[DEFAULT_RULE]
- No implementation entry may be marked DONE without current evidence.
- No implementation entry may be split into duplicate entries when the work belongs to one implementation concern.
- Every implementation entry must map to a contract entry in `LUMEN_CONTRACT_TRACKER.md`.

---

## CURRENT WORKING ENTRY

- Coverage Gate Enforcement / No Coverage Bypass -> DONE

  [LAST_UPDATED] 2026-05-02

  [RELATED_CONTRACT] COVERAGE_GATE_ENFORCEMENT_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 -> Coverage gate enforcement session opened against latest source-of-truth ZIP.
  - 2026-05-01 -> Static trace reviewed coverage evaluator, finalize decision, publication outcome, pointer repository, pipeline finalize, evidence/replay/command coverage surfaces, and related tests.
  - 2026-05-01 -> Gap found: PASS coverage state could be used as the primary readable/current gate without proving expected/available/missing/ratio/threshold/mode/basis/contract completeness.
  - 2026-05-01 -> Static enforcement patch added complete coverage telemetry validation before READABLE, promotion_allowed, pointer target, and fallback target states.
  - 2026-05-01 -> Coverage evaluator now counts unique universe tickers, emits canonical coverage basis/contract/reason fields, and returns NOT_EVALUABLE for empty universe instead of any implicit PASS path.
  - 2026-05-01 -> Pointer/readable repository predicates now require persisted coverage telemetry fields, not only coverage_gate_state = PASS.
  - 2026-05-01 -> Static guard and service tests were added/updated to prevent coverage bypass regression.
  - 2026-05-01 -> Operator local validation showed failures: static guard used `base_path()` in a plain Container test, `coverage_gate_status`/`coverage_gate_state` alias conflict could hide FAIL, mocked finalize decisions lacked full coverage summary, and valid baseline/fallback fixtures lacked required coverage telemetry.
  - 2026-05-01 -> Recovery patch replaced static guard path resolution, made conflicting coverage gate aliases fail-safe, completed coverage summaries in service mocks, aligned readable/correction/fallback fixtures with strict telemetry, extended evidence/eligibility read predicates to require complete coverage telemetry, and exposed `coverage_threshold_mode` in command output payload/summary.

  - 2026-05-02 -> Operator final local validation passed: pipeline integration, pointer, coverage, finalize, publication, readable, evidence, replay, command, core service tests, static guard, and full `tests/Unit/MarketData` all PASS. Entry promoted to DONE.

  [IMPLEMENTATION]
  - `MarketDataInvariantGuard` rejects READABLE/promotion/current/fallback targets unless coverage PASS has complete expected/available/missing/ratio/threshold/mode/basis/contract telemetry and consistent count/ratio math.
  - `FinalizeDecisionService` normalizes coverage aliases and downgrades incomplete PASS coverage to NOT_EVALUABLE with `RUN_COVERAGE_NOT_EVALUABLE`.
  - `PublicationFinalizeOutcomeService` carries coverage summary into final outcome guard validation.
  - `CoverageGateEvaluator` uses unique universe ticker count, deduped available ticker count, deterministic missing count, ratio, threshold, basis, contract version, and reason code aliases.
  - `EodPublicationRepository` requires complete run coverage telemetry on readable pointer resolution and pointer/fallback integrity checks.
  - `EodPublicationRepository` now re-validates pointer/fallback rows with `MarketDataInvariantGuard` after query resolution so non-null telemetry alone cannot bypass count/ratio/threshold consistency.
  - `EligibilitySnapshotScopeRepository` and `EodEvidenceRepository` now require full persisted coverage telemetry, not only `coverage_gate_state = PASS`, before returning readable consumer/evidence data.
  - Pipeline finalize guard states and RUN_FINALIZED payloads now carry coverage mode/basis/contract context.

  [ENFORCEMENT]
  - Static guard added: `CoverageGateNoBypassStaticGuardTest`.
  - Guard tests now assert incomplete PASS coverage fails fast.
  - Finalize/outcome tests now include complete coverage context and explicit downgrade behavior for incomplete PASS.
  - Repository integration fixtures were aligned with strict coverage telemetry requirements.
  - Recovery patch updated pipeline/readable fixtures and mocked finalize decisions so tests prove the stricter contract instead of passing through incomplete PASS context.

  [FINAL_BEHAVIOR]
  - DONE. Coverage FAIL or NOT_EVALUABLE cannot produce READABLE/current publication through patched guard, finalize, outcome, or pointer repository paths.
  - Incomplete PASS coverage is treated as NOT_EVALUABLE and fail-safe, not readable.
  - Pointer resolution requires SUCCESS + READABLE + PASS plus complete coverage telemetry fields.

  [EVIDENCE]
  - Container static scan: no forbidden MAX/trade-date shortcut found in runtime coverage/finalize/evidence/replay paths.
  - Container syntax validation: changed PHP files passed `php -l` with no syntax errors.
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

  [GAP]
  - None for this scope after final local validation.

  [NEXT_ACTION]
  - No immediate action. Reopen only if a future coverage/finalize/publication/pointer/evidence/replay/command path changes the contract.

  [FINAL_CONSTRAINT]
  - DONE for the current source-of-truth ZIP. Future changes must preserve no-coverage-bypass enforcement and rerun targeted/full MarketData tests.

---

- Read-Side Enforcement / Anti Bypass Total → DONE

  [LAST_UPDATED] 2026-05-01

  [RELATED_CONTRACT] READ_SIDE_POINTER_ENFORCEMENT_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Read-side anti-bypass session opened against the latest source-of-truth ZIP.
  - 2026-05-01 → Static trace reviewed repository, service, command, evidence, replay, test, DB schema, and locked book-contract surfaces for market-data read paths.
  - 2026-05-01 → `EligibilitySnapshotScopeRepository` was hardened to require `coverage_gate_state = PASS` and run mirror match before returning pointer-scoped eligibility rows.
  - 2026-05-01 → `EodEvidenceRepository` was hardened so publication lookup, eligibility export, and reason-code export require pointer/current/readable/PASS/mirror-valid context.
  - 2026-05-01 → Static guard and integration tests were extended to prevent regression of coverage-gate and run-mirror enforcement.
  - 2026-05-01 → Operator local PHPUnit evidence showed 4 MarketData integration regressions in correction/fallback behavior after run-mirror enforcement was applied too broadly to the internal prior-readable fallback lookup.
  - 2026-05-01 → Regression patch restored `EodPublicationRepository::findLatestReadablePublicationBefore` as an internal pipeline fallback resolver while keeping consumer gateway, evidence, and eligibility scope mirror-enforced.
  - 2026-05-01 → Operator retest confirmed targeted readable/pointer tests, full MarketData suite, readable-publication integration test, and pointer static guard all PASS after the regression patch.

  [IMPLEMENTATION]
  - Consumer eligibility scope reads are pointer-scoped through `eod_current_publication_pointer`, `eod_publications`, and `eod_runs`.
  - Evidence eligibility export returns rows only when the requested publication is the current pointer target and the run is `SUCCESS`, `READABLE`, `coverage_gate_state = PASS`, current, sealed, and mirror-aligned.
  - Evidence dominant reason-code export stops with an empty result when the publication/run context is not current-readable/PASS/mirror-valid, preventing event reason leakage from invalid read contexts.
  - Prior-readable fallback lookup remains a pipeline/internal fallback path, not a public consumer resolver; it preserves fallback/correction behavior without weakening consumer read enforcement.
  - The locked read-side contract document explicitly requires coverage PASS and run mirror validation for consumer read gateways.

  [ENFORCEMENT]
  - Static guards assert official pointer gateway predicates, consumer no-latest/no-MAX rules, pointer-scoped eligibility predicates, coverage PASS, and run publication mirror checks.
  - Integration tests cover no-leak behavior for non-PASS coverage and run/publication mirror mismatch.
  - Raw/current artifact table access remains allowed only for ingestion, build, seal/finalize, admin/repair, evidence invalid-row sampling, and test fixtures.
  - Internal fallback lookup is explicitly classified as `ALLOWED_INTERNAL_PIPELINE_FALLBACK`, not a consumer read gateway.

  [FINAL_BEHAVIOR]
  - DONE. Market-data consumer read paths are pointer-resolved, current-readable, publication-scoped, coverage-PASS, and fail-safe.
  - No patched read-side consumer may return eligibility rows or evidence reason codes unless current pointer, sealed publication, SUCCESS/READABLE/PASS run, current mirror, run mirror, and publication scope all match.
  - If the readable pointer context is absent or invalid, patched read paths return an empty controlled result or controlled failure; they do not fallback to raw/staging/latest/current artifact shortcuts.
  - Correction/fallback pipeline behavior remains valid after the regression patch: internal prior-readable lookup can preserve prior current readable publication without becoming a consumer latest shortcut.

  [EVIDENCE]
  - Static scan: no consumer app path uses `MAX(trade_date)`, `max('trade_date')`, `latest('trade_date')`, or `orderByDesc('trade_date')` as a consumer readable-data resolver.
  - Static scan: direct `eod_bars`, `eod_indicators`, and `eod_eligibility` app access is isolated to artifact build/write/finalize repositories or pointer-scoped evidence/scope reads.
  - Static scan: no market-data HTTP/controller read path exists in the current source tree.
  - Container syntax validation: changed PHP files passed `php -l`.
  - Local command: `php artisan migrate:fresh --env=testing` → PASS; migrations completed successfully through `2026_04_27_000001_expand_coverage_gate_state_not_evaluable`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "readable"` → PASS; `OK (45 tests, 256 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "pointer"` → PASS; `OK (51 tests, 551 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData` → PASS; `OK (250 tests, 2355 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php` → PASS; `OK (8 tests, 15 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/PublicationCurrentPointerReadinessStaticGuardTest.php` → PASS; `OK (3 tests, 23 assertions)`.

  [FINAL_CONSTRAINT]
  - This implementation is DONE for the current source-of-truth ZIP.
  - Future read-side changes must not create duplicate audit entries for this scope; append reconciliation notes under this canonical implementation concern.
  - Any future consumer read path must resolve current readable publication via pointer, enforce SUCCESS/READABLE/PASS and run mirror checks, and fail-safe without raw/staging/latest fallback.


---

- Audit Rebuild Baseline / One-by-One Regression Review → DONE

  [LAST_UPDATED] 2026-05-01

  [RELATED_CONTRACT] AUDIT_REBUILD_BASELINE_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Clean audit rebuild started; previous broad DONE list intentionally removed from active implementation status until one-by-one retest evidence is supplied.
  - 2026-05-01 → First retested scope completed through DB Schema & Migration Sync final validation; clean rebuild workflow is now proven usable.

  [IMPLEMENTATION]
  - Operational audit remains in clean-start retest mode.
  - DB Schema & Migration Sync is the first restored DONE implementation scope under the cleaned governance model.
  - Duplicate DB schema hotfix entries were merged into a single canonical implementation entry.

  [ENFORCEMENT]
  - New DONE entries require current validation evidence.
  - Duplicate entries for the same implementation concern must be merged into a canonical entry with HISTORY, FINAL_BEHAVIOR, and EVIDENCE preserved.
  - Contract mapping remains mandatory through `LUMEN_CONTRACT_TRACKER.md`.

  [FINAL_BEHAVIOR]
  - The clean audit rebuild process is active as the operating audit model, and the first validated scope has been recorded without carrying forward unverified historical DONE claims.

  [EVIDENCE]
  - DB Schema & Migration Sync implementation entry below records the first completed validation scope with local migration and PHPUnit evidence.

  [FINAL_CONSTRAINT]
  - Future audit restoration must continue one scope at a time and must not reintroduce broad DONE/LOCKED claims without fresh evidence.

---

## VERIFIED IMPLEMENTATION ENTRIES

- DB Schema & Migration Sync / Runtime Schema Four-Way Synchronization → DONE

  [LAST_UPDATED] 2026-05-01

  [RELATED_CONTRACT] DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT

  [REVIEW_STATUS] REVIEWED_OK

  [HISTORY]
  - 2026-05-01 → Static schema inventory compared `Database_Schema_MariaDB.sql`, migration output expectations, SQLite market-data mirror, and market-data repository/query usage.
  - 2026-05-01 → SQLite-only orphan surrogate keys were removed from current/history artifact tables and runtime composite keys were enforced in the test mirror.
  - 2026-05-01 → Replay metric index names and ticker timestamp update behavior were synchronized between SQL schema, migration, and test expectations.
  - 2026-05-01 → `md_session_snapshots` migration idempotency was fixed after local `migrate:fresh` exposed duplicate-table failure.
  - 2026-05-01 → Correction reexecution policy migration idempotency was fixed after local `migrate:fresh` exposed duplicate-column failure on `execution_count`.
  - 2026-05-01 → Local migration evidence confirmed `php artisan migrate:fresh --env=testing` completed successfully through `2026_04_27_000001_expand_coverage_gate_state_not_evaluable`.
  - 2026-05-01 → Stricter SQLite schema exposed fixture drift on `tickers.created_at` and `eod_runs.source`; test fixtures/default mirrors were corrected without weakening runtime constraints.
  - 2026-05-01 → Repository/current-pointer fixtures and restore-prior validation were aligned with pointer/publication/run mirror integrity requirements.
  - 2026-05-01 → Pipeline correction promotion failure handling was aligned to preserve a valid prior readable fallback effective date while keeping failed candidate publication non-current and non-readable.
  - 2026-05-01 → Final local validation passed for schema guard, repository-targeted tests, pipeline integration tests, and the full MarketData PHPUnit suite.
  - 2026-05-01 → Audit recovery applied: prior DB schema cleanup/hotfix/final-closure entries were merged into this canonical implementation entry.

  [IMPLEMENTATION]
  - `tests/Support/UsesMarketDataSqlite.php` no longer creates SQLite-only surrogate keys on `eod_bars`, `eod_indicators`, `eod_eligibility`, `eod_bars_history`, `eod_indicators_history`, and `eod_eligibility_history`.
  - SQLite mirror uses runtime composite identities for canonical artifact tables: `(trade_date, ticker_id)`.
  - SQLite mirror uses runtime composite identities for publication-bound history tables: `(publication_id, trade_date, ticker_id)`.
  - SQLite mirror includes runtime-aligned indexes/default behavior required by repository/test usage.
  - `docs/market_data/db/Database_Schema_MariaDB.sql` uses replay index names aligned with runtime migration sync: `idx_replay_daily_comparison`, `idx_replay_daily_coverage_gate`, and `idx_replay_daily_artifact_scope`.
  - `database/migrations/2026_03_22_000001_create_tickers_table.php` aligns `tickers.updated_at` update behavior with the SQL schema timestamp contract.
  - `database/migrations/2026_03_24_000002_create_md_session_snapshots_table.php` is idempotent when the locked schema path already created `md_session_snapshots`.
  - `database/migrations/2026_04_23_000004_add_correction_reexecution_policy_fields.php` adds correction reexecution policy fields only when missing.
  - `tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` guards against reintroducing runtime-orphan surrogate keys.
  - Repository and read-contract tests seed runtime-required `eod_runs` fields instead of relying on looser SQLite behavior.
  - `EodPublicationRepository` validates prior fallback run readability and publication mirror integrity before restoring a prior current publication.
  - `MarketDataPipelineService` keeps fail-safe correction behavior while retaining a valid fallback effective date when promotion fails after a valid prior readable publication is resolved.
  - Pipeline fixtures use idempotent seeding where runtime composite uniqueness would otherwise reject duplicate current artifact rows.

  [ENFORCEMENT]
  - SQLite tests can no longer pass with artifact/history identity columns that do not exist in MariaDB.
  - Runtime schema constraints remain authoritative; tests and fixtures must satisfy them instead of weakening the schema mirror.
  - Migration chain is safe for the project’s canonical SQL-schema bootstrap path and later additive migrations.
  - Repository restore-prior behavior rejects invalid fallback targets before pointer restoration.
  - Current pointer replacement and fallback restoration require aligned pointer/publication/run mirror state.
  - Composite artifact uniqueness remains enforced in SQLite tests.

  [FINAL_BEHAVIOR]
  - DONE. Market-data DB schema, migration chain, SQLite test schema, repository/query usage, fixtures, and correction fallback behavior are synchronized for the current source-of-truth ZIP.
  - A clean `migrate:fresh` path is valid.
  - The schema guard, repository-targeted tests, pipeline integration tests, and full MarketData PHPUnit suite are green.
  - Failed correction promotion remains fail-safe: the candidate is not published, prior current publication is preserved, the run stays HELD/NOT_READABLE, and a valid prior readable fallback date is retained only when resolved from the fallback publication lookup.

  [FINAL_CONSTRAINT]
  - Future market-data schema changes must update and validate all affected layers together: `Database_Schema_MariaDB.sql`, Laravel/Lumen migrations, SQLite test schema, repository/query usage, test fixtures, and audit records.
  - Field drift, nullable/default drift, index/unique drift, orphan test-only columns, and repository usage of non-schema fields must be fixed directly or recorded as an explicit policy gap before any new DONE claim.
  - Test failures caused by stricter runtime-aligned SQLite constraints must be resolved by fixing fixtures or implementation, not by relaxing the SQLite mirror.

  [EVIDENCE]
  - Local command: `php artisan migrate:fresh --env=testing` → PASS; all listed market-data migrations completed successfully through `2026_04_27_000001_expand_coverage_gate_state_not_evaluable`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "schema"` → PASS; `OK (3 tests, 70 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData --filter "Repository"` → PASS; `OK (33 tests, 180 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → PASS; `OK (52 tests, 1182 assertions)`.
  - Local command: `vendor/bin/phpunit tests/Unit/MarketData` → PASS; `OK (244 tests, 2327 assertions)`.
  - Container static validation during the session: changed PHP files passed `php -l` before local PHPUnit reruns.

## Recovery-3 malformed fallback pointer fix — Coverage Gate Enforcement / No Coverage Bypass

- Status: SUPERSEDED_BY_FINAL_LOCK.
- Local evidence received: static guard, Coverage, Publication, readable, Evidence, Replay, and Command suites passed; one integration/pointer failure remained for malformed fallback pointer effective-date handling.
- Recovery-3 fix: when correction pointer mismatch occurs and no contract-valid readable fallback exists, `trade_date_effective` is explicitly cleared to null instead of retaining the requested candidate date.
- Final result: superseded by Recovery-5 final local validation; `MarketDataPipelineIntegrationTest`, pointer filter, and full `tests/Unit/MarketData` all PASS.

## Recovery-4 fallback mirror fixture alignment — Coverage Gate Enforcement / No Coverage Bypass

- Status: SUPERSEDED_BY_FINAL_LOCK.
- Local evidence received after Recovery-3: static guard, Coverage, Publication, readable, Evidence, Replay, and Command suites passed; remaining failures were isolated to correction fallback/effective-date and low-coverage fallback preservation.
- Recovery-4 fix: `seedReadableFallbackPublication()` now mirrors `eod_runs.publication_id` to the seeded fallback publication id instead of hard-coding publication `1`, so strict pointer/publication/run mirror validation can resolve valid fallback baselines while still rejecting malformed fallback pointers.
- Recovery-4 fix: correction baseline pointer mismatch messages are classified as pointer-integrity failures, so failed correction promotion preserves prior current state and uses the contract-valid fallback date when one resolves.
- Final result: superseded by Recovery-5 final local validation; `MarketDataPipelineIntegrationTest`, pointer filter, and full `tests/Unit/MarketData` all PASS.

## Recovery-5 baseline pointer mismatch message preservation — Coverage Gate Enforcement / No Coverage Bypass

- Status: DONE / LOCKED by final local validation.
- Local evidence after Recovery-5: `MarketDataPipelineIntegrationTest`, pointer filter, targeted coverage/finalize/publication/readable/evidence/replay/command suites, core service tests, static guard, and full `tests/Unit/MarketData` all PASS.
- Recovery-5 fix: pointer-integrity handling now preserves the explicit `Correction baseline no longer matches current publication pointer` note instead of collapsing it to the generic post-finalize pointer mismatch message.
- Final lock completed for Coverage Gate Enforcement / No Coverage Bypass.
