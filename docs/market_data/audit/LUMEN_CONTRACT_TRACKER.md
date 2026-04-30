# LUMEN_CONTRACT_TRACKER

## ACTIVE SESSION

ACTIVE SESSION:
- Clean Audit Rebuild + One-by-One Regression Retest

[SESSION_STATUS] ACTIVE

[SESSION_SCOPE]
- Start from a clean operational contract tracker.
- Rebuild contract lifecycle one contract at a time from fresh implementation/test/runtime evidence.
- Do not carry forward old LOCKED claims until the related contract is retested or reviewed.
- Keep implementation and contract entries mapped one-to-one.

[SESSION_GOAL]
- Produce a truthful contract tracker where every DONE/LOCKED contract is backed by current evidence and a related implementation entry.

[SESSION_NOTES]
- This file is intentionally not empty. It keeps contract tracking governance-compliant while avoiding false historical LOCKED claims.
- Historical contracts may be restored as LOCKED only after scoped review confirms the final rule remains valid.
- If a retest finds regression, keep the related contract IN_PROGRESS and add GAP, IMPACT, and NEXT_ACTION.
- Do not create a new contract when the scope belongs to an existing contract.

---

## OPERATIONAL STATUS

[CURRENT_AUDIT_MODE]
- CLEAN_START_RETEST

[HISTORICAL_STATUS_POLICY]
- Previous DONE/LOCKED contract claims are not copied into this clean file as current status.
- Previous contract text may be referenced later only after scoped review.
- Current contract status must be rebuilt from fresh evidence and mapped implementation status.

[DEFAULT_RULE]
- No contract may be marked DONE without current implementation evidence.
- No contract may be marked LOCKED without FINAL_RULE and VALIDATED evidence.
- One contract must be reviewed at a time.

---

## CURRENT WORKING CONTRACT

- AUDIT_REBUILD_BASELINE_CONTRACT → IN_PROGRESS

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] Audit Rebuild Baseline / One-by-One Regression Review

  [REVIEW_STATUS] UNDER_REVIEW

  [HISTORY]
  - 2026-05-01 → Clean contract tracker rebuild started; previous broad LOCKED/DONE list intentionally removed from active tracker until one-by-one retest evidence is supplied.

  [DEFINED]
  - This contract controls the temporary audit rebuild state after historical test errors/regression uncertainty.

  [IMPLEMENTED]
  - Implemented as a clean starter tracker structure with active session, one working contract, and no unverified historical LOCKED claims.

  [ENFORCED]
  - Any restored contract must have a matching implementation entry in `LUMEN_IMPLEMENTATION_STATUS.md`.
  - Any restored LOCKED contract must include current validation evidence and a final rule.

  [VALIDATED]
  - Audit reset only. No PHPUnit/artisan/runtime validation is claimed in this file.

  [FINAL_RULE]
  - Pending. This baseline contract is not LOCKED until the clean retest workflow is confirmed through the first reviewed scope.

  [NEXT_ACTION]
  - Select the first market-data contract to retest.
  - Map it to the related implementation entry.
  - Restore DONE/LOCKED only after current validation evidence is recorded.

---

## VERIFIED CONTRACT ENTRIES

<!--
Add verified contract entries here.

Required format:

- CONTRACT_NAME_CONTRACT → STATUS

  [LAST_UPDATED] YYYY-MM-DD

  [RELATED_IMPLEMENTATION] Implementation Entry Name

  [REVIEW_STATUS] UNDER_REVIEW / REVIEWED_OK / BLOCKED

  [HISTORY]
  - YYYY-MM-DD → Significant lifecycle change or validation result

  [DEFINED]
  - Contract scope and source of truth.

  [IMPLEMENTED]
  - Main implementation location or implementation status.

  [ENFORCED]
  - Runtime/code/test enforcement.

  [VALIDATED]
  - Specific PHPUnit/artisan/static/manual evidence.

  [FINAL_RULE]
  - Rule that must not be violated.

  [GAP]
  - Only if unresolved.

  [IMPACT]
  - Only if unresolved.

  [NEXT_ACTION]
  - Only if not DONE/LOCKED.
-->

---

- DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT → IN_PROGRESS

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] DB Schema & Migration Sync / Schema Drift Cleanup

  [REVIEW_STATUS] UNDER_REVIEW

  [HISTORY]
  - 2026-05-01 → Contract enforcement started for DB schema synchronization across SQL docs, migrations, SQLite test schema, and repository usage. Runtime-orphan SQLite surrogate keys were removed and replay index naming drift was corrected.

  [DEFINED]
  - Source contract: `docs/market_data/db/DB_Schema_And_Migration_Sync_Contract_LOCKED.md`.
  - Runtime schema reference: `docs/market_data/db/Database_Schema_MariaDB.sql`.
  - Migration/runtime generation reference: market-data migrations under `database/migrations/`.
  - Test mirror reference: `tests/Support/UsesMarketDataSqlite.php`.
  - Query validation scope: market-data repository layer under `app/Infrastructure/Persistence/MarketData/` plus market-data services that persist artifacts.

  [IMPLEMENTED]
  - Implemented partial schema sync cleanup in SQLite mirror, SQL schema index definitions, ticker migration timestamp behavior, and schema sync test guard.

  [ENFORCED]
  - SQLite mirror no longer owns extra artifact/history identity columns absent from MariaDB.
  - Publication-bound artifact/history identities are represented using the same composite keys as the runtime SQL schema.
  - Replay metric index names are no longer split between SQL docs and migration sync logic.
  - A PHPUnit schema guard now blocks reintroduction of orphan surrogate keys in the SQLite mirror.

  [VALIDATED]
  - Static schema inventory completed for SQL schema vs SQLite mirror column names.
  - Static repository scan completed for market-data repository query columns.
  - PHP syntax validation passed for changed PHP files.
  - Local PHPUnit/artisan validation is pending because `vendor/` is absent from the uploaded ZIP.

  [FINAL_RULE]
  - No market-data column, identity key, index, nullable/default behavior, or repository-used field may exist only in one layer. SQL schema, final migration result, SQLite test mirror, repository usage, fixtures, and audit records must remain synchronized.

  [GAP]
  - Contract cannot be marked LOCKED until local `migrate:fresh` and targeted PHPUnit evidence are supplied.

  [IMPACT]
  - Current status is IN_PROGRESS, not LOCKED. Any future failure from stricter SQLite constraints must be treated as a real schema/fixture drift issue, not bypassed by loosening the mirror.

  [NEXT_ACTION]
  - Run local migration and PHPUnit validation.
  - Promote this contract to LOCKED only after all required commands PASS and the implementation entry is updated from PARTIAL to DONE.

---

- DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT / Session Snapshot Migration Idempotency → IN_PROGRESS

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] DB Schema & Migration Sync / Session Snapshot Migration Idempotency Hotfix

  [REVIEW_STATUS] UNDER_REVIEW

  [HISTORY]
  - 2026-05-01 → Local migration evidence exposed a migration-chain idempotency gap for `md_session_snapshots`: the locked table could exist before `2026_03_24_000002_create_md_session_snapshots_table` executed, causing `Schema::create()` to fail.
  - 2026-05-01 → The migration was hardened so table creation is conditional and existing-table column alignment is guarded by `Schema::hasColumn()`.

  [DEFINED]
  - `md_session_snapshots` remains part of the locked market-data runtime schema and must stay synchronized across SQL docs, migrations, SQLite mirror, repository writes, and tests.

  [IMPLEMENTED]
  - Migration idempotency implemented in `database/migrations/2026_03_24_000002_create_md_session_snapshots_table.php`.

  [ENFORCED]
  - The migration no longer assumes it owns first creation of `md_session_snapshots` when a prior schema load or sync path has already created the table.
  - Missing column checks preserve schema repair behavior without creating a duplicate table.

  [VALIDATED]
  - Static PHP syntax validation passed for the patched migration.
  - Local `migrate:fresh` rerun is still required to validate the full migration chain.

  [FINAL_RULE]
  - Market-data migrations must be safe for the project’s documented schema-sync workflow: a locked table must not be recreated blindly if it already exists, and schema repair must be explicit rather than hidden by duplicate-table failure.

  [GAP]
  - Contract remains IN_PROGRESS until local `migrate:fresh` and PHPUnit evidence prove the migration chain and schema mirror are clean.

  [IMPACT]
  - DONE/LOCKED promotion is blocked until the local rerun confirms this was the only migration-chain blocker.

  [NEXT_ACTION]
  - Rerun local migration and PHPUnit validation; append the actual result before promoting the implementation status.

---

- DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT / Correction Reexecution Migration Idempotency → IN_PROGRESS

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] DB Schema & Migration Sync / Correction Reexecution Migration Idempotency Hotfix

  [REVIEW_STATUS] UNDER_REVIEW

  [HISTORY]
  - 2026-05-01 → Local migration evidence exposed a duplicate-column gap for `eod_dataset_corrections.execution_count`: the locked SQL schema already includes correction reexecution policy fields, while `2026_04_23_000004_add_correction_reexecution_policy_fields` attempted to add them unconditionally.
  - 2026-05-01 → The migration was hardened so each correction reexecution policy field is added only when missing.

  [DEFINED]
  - Correction reexecution policy fields remain part of the locked `eod_dataset_corrections` schema: `execution_count`, `last_executed_at`, and `current_consumed_at`.

  [IMPLEMENTED]
  - Migration idempotency implemented in `database/migrations/2026_04_23_000004_add_correction_reexecution_policy_fields.php`.

  [ENFORCED]
  - The migration no longer assumes it owns first creation of correction reexecution policy fields when the locked SQL schema has already created them.
  - The status enum expansion still runs so the runtime enum remains aligned with correction lifecycle policy.

  [VALIDATED]
  - Static PHP syntax validation passed for the patched migration.
  - Local `migrate:fresh` rerun is still required to validate the full migration chain.

  [FINAL_RULE]
  - Additive market-data migrations must be safe against the project’s locked-schema bootstrap path: existing contract fields must be detected explicitly and must not cause duplicate-column failure.

  [GAP]
  - Contract remains IN_PROGRESS until local `migrate:fresh` and PHPUnit evidence prove the migration chain and schema mirror are clean.

  [IMPACT]
  - DONE/LOCKED promotion is blocked until the local rerun confirms the migration chain is clean.

  [NEXT_ACTION]
  - Rerun local migration and PHPUnit validation; append the actual result before promoting the implementation status.

---

- DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT / SQLite Fixture Runtime Contract Alignment → IN_PROGRESS

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] DB Schema & Migration Sync / SQLite Fixture Runtime Contract Hotfix

  [REVIEW_STATUS] UNDER_REVIEW

  [HISTORY]
  - 2026-05-01 → Local migration evidence confirmed the migration chain is clean after the prior idempotency hotfixes.
  - 2026-05-01 → Local PHPUnit evidence exposed runtime-contract fixture drift: test inserts omitted fields that the runtime schema requires or defaulted only in MariaDB.

  [DEFINED]
  - SQLite mirror must preserve runtime behavior for required fields and defaults.
  - Fixture inserts must provide required runtime fields when the runtime schema has no default.
  - Schema defaults may be mirrored only when the MariaDB contract defines equivalent defaults.

  [IMPLEMENTED]
  - `tickers.created_at` and `tickers.updated_at` SQLite defaults now mirror MariaDB timestamp defaults.
  - Direct repository/read-contract `eod_runs` fixtures now include required runtime fields rather than making SQLite looser.

  [ENFORCED]
  - `eod_runs.source` remains required and fixture-owned.
  - Ticker timestamp defaults are schema-owned, matching the MariaDB contract.

  [VALIDATED]
  - Static PHP syntax validation passed for the changed SQLite support/test files.
  - Local `migrate:fresh` is already PASS based on user-provided evidence.
  - Local PHPUnit rerun after this fixture patch is still required.

  [FINAL_RULE]
  - When MariaDB supplies a default, the SQLite mirror may mirror that default. When MariaDB requires an explicit value, tests must seed that explicit value rather than weakening SQLite.

  [GAP]
  - Contract remains IN_PROGRESS until local Repository, PipelineIntegration, and full MarketData PHPUnit reruns pass after the fixture patch.

  [IMPACT]
  - DONE/LOCKED promotion is blocked until local PHPUnit evidence confirms no remaining schema/fixture drift.

  [NEXT_ACTION]
  - Rerun local PHPUnit commands and append the actual evidence before promoting this contract.

---

- DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT / Repository And Pipeline Fixture Integrity Alignment → IN_PROGRESS

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] DB Schema & Migration Sync / Repository And Pipeline Fixture Integrity Hotfix

  [REVIEW_STATUS] UNDER_REVIEW

  [HISTORY]
  - 2026-05-01 → Local PHPUnit evidence after hotfix 3 showed that schema guard and migration chain were no longer the blocker; remaining failures came from fixtures and repository recovery behavior that did not satisfy the stricter runtime contract.
  - 2026-05-01 → Current mirror fixture metadata, prior restore validation, fallback effective-date preservation, and duplicate current artifact seeding were aligned with runtime constraints.

  [DEFINED]
  - A valid current pointer requires aligned pointer, publication, and run mirror metadata.
  - Restore-prior is allowed only for readable, successful, coverage-PASS, sealed prior runs whose run mirror matches the publication.
  - Pipeline tests must not bypass the runtime composite key on current artifact tables.
  - A HELD correction caused by promotion/pointer mismatch may remain NOT_READABLE while preserving the available prior readable fallback effective date.

  [IMPLEMENTED]
  - Repository fixture current run mirror now includes publication identity/version.
  - Restore-prior validation now rejects invalid fallback run states before pointer restoration.
  - Pipeline mismatch hold path now preserves fallback effective date.
  - Pointer mismatch fixture now uses an idempotent current artifact seed path.

  [ENFORCED]
  - Tests must be corrected to satisfy runtime constraints; schema constraints must not be relaxed to make fixtures pass.
  - Current pointer restoration cannot invent or promote unreadable fallback state.

  [VALIDATED]
  - Static PHP syntax validation passed for all changed files.
  - Local PHPUnit rerun remains required because `vendor/` is not included in the uploaded ZIP.

  [FINAL_RULE]
  - Runtime schema constraints are authoritative. Test fixtures and recovery paths must obey pointer/run/publication mirror invariants and current artifact uniqueness.

  [GAP]
  - Contract remains IN_PROGRESS until the next local Repository, PipelineIntegration, and full MarketData PHPUnit reruns pass.

  [IMPACT]
  - DONE/LOCKED promotion remains blocked pending local PHPUnit evidence.

  [NEXT_ACTION]
  - Rerun the targeted and full MarketData PHPUnit commands and append actual evidence before promotion.

---

- DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT / Promotion Failure Fallback Effective Date Alignment → IN_PROGRESS

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] DB Schema & Migration Sync / Promotion Failure Fallback Effective Date Hotfix

  [REVIEW_STATUS] UNDER_REVIEW

  [HISTORY]
  - 2026-05-01 → Repository tests are locally proven PASS after fixture and repository integrity alignment.
  - 2026-05-01 → Remaining local evidence isolated one pipeline fallback-effective-date mismatch in correction promotion failure handling.
  - 2026-05-01 → Promotion-lost-ownership HELD/NOT_READABLE outcomes were aligned with the fallback publication contract by retaining a valid prior readable fallback effective date when available.

  [DEFINED]
  - A failed correction promotion must not publish the candidate publication.
  - If a valid prior readable fallback exists, the held non-readable correction run may record the fallback readable trade date as `trade_date_effective`.
  - If fallback resolution is malformed or unavailable, the run must not invent an effective trade date.

  [IMPLEMENTED]
  - `MarketDataPipelineService` now uses fallback `readable_trade_date` for promotion-lost-ownership outcomes when the run has no existing `trade_date_effective`.

  [ENFORCED]
  - Fallback effective-date preservation is allowed only through resolved fallback publication data.
  - Schema and fixture constraints remain strict; this is a runtime outcome alignment, not a schema relaxation.

  [VALIDATED]
  - Static PHP syntax validation passed for the changed service file.
  - Local PipelineIntegration/full PHPUnit rerun remains required because `vendor/` is not included in the uploaded ZIP.

  [FINAL_RULE]
  - Correction promotion failure is fail-safe: preserve prior current publication, keep candidate non-current, mark run HELD/NOT_READABLE, and retain fallback effective date only when the fallback publication lookup is valid.

  [GAP]
  - Contract remains IN_PROGRESS until local PipelineIntegration and full MarketData PHPUnit reruns pass after this patch.

  [IMPACT]
  - DONE/LOCKED promotion remains blocked pending final local PHPUnit evidence.

  [NEXT_ACTION]
  - Rerun PipelineIntegration and full MarketData PHPUnit locally and append evidence before promotion.

---

- DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT / Final Local Validation Closure → LOCKED

  [LAST_UPDATED] 2026-05-01

  [RELATED_IMPLEMENTATION] DB Schema & Migration Sync / Final Local Validation Closure

  [REVIEW_STATUS] VALIDATED

  [HISTORY]
  - 2026-05-01 → Final local evidence confirmed PipelineIntegration and full MarketData PHPUnit are green after the promotion failure fallback effective-date patch.
  - 2026-05-01 → The same session already established successful migration fresh, schema guard, and repository-targeted evidence.
  - 2026-05-01 → DB schema/migration/SQLite/repository sync scope is promoted from IN_PROGRESS/PARTIAL to LOCKED/DONE for the current ZIP state.

  [DEFINED]
  - The runtime DB contract is represented by `docs/market_data/db/Database_Schema_MariaDB.sql` and must remain synchronized with migration final state.
  - SQLite test schema must mirror runtime behavior for columns, defaults, NOT NULL constraints, composite keys, unique constraints, and repository-visible fields.
  - Repository/query/test fixture usage must not depend on columns, defaults, or looser SQLite behavior that are absent from runtime schema.
  - Migration history may be idempotent when the project loads a final canonical SQL schema first and later additive migrations would otherwise duplicate existing final-state columns/tables.

  [IMPLEMENTED]
  - Orphan SQLite surrogate keys were removed from current/history artifact tables.
  - Migration idempotency guards were added for final-state duplicate table/column conditions encountered during `migrate:fresh`.
  - `tickers` timestamp default behavior was aligned between runtime and SQLite.
  - Test fixtures were corrected to seed runtime-required run/source/pointer fields.
  - Repository fallback restoration now validates prior run readability and publication mirror integrity.
  - Pipeline correction promotion failure handling now keeps fail-safe non-readable behavior while preserving a valid fallback effective date.

  [ENFORCED]
  - No schema side is allowed to carry a field that is absent from the other authoritative sides unless explicitly marked as a policy gap.
  - Tests must be fixed to satisfy schema constraints; schema must not be weakened to make tests pass.
  - Current pointer replacement and fallback restoration require aligned pointer/publication/run mirror state.
  - Composite artifact uniqueness is part of the runtime contract and remains enforced in SQLite tests.

  [VALIDATED]
  - `php artisan migrate:fresh --env=testing` → PASS.
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "schema"` → PASS; `OK (3 tests, 70 assertions)`.
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "Repository"` → PASS; `OK (33 tests, 180 assertions)`.
  - `vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → PASS; `OK (52 tests, 1182 assertions)`.
  - `vendor/bin/phpunit tests/Unit/MarketData` → PASS; `OK (244 tests, 2327 assertions)`.

  [FINAL_RULE]
  - LOCKED. Market-data DB schema changes must be kept in four-way sync across `Database_Schema_MariaDB.sql`, Laravel/Lumen migrations, SQLite test schema, and repository/test usage. Any drift must be fixed directly or recorded as an explicit policy gap before further implementation claims are marked DONE.

  [GAP]
  - None for the current DB Schema & Migration Sync scope.

  [IMPACT]
  - This contract is validated for the current source-of-truth ZIP.

  [NEXT_ACTION]
  - Keep this contract as the baseline for future schema-related sessions.
  - Reopen as REVIEW_REQUIRED if any future migration, schema doc, SQLite schema, or repository query change introduces a new mismatch.
