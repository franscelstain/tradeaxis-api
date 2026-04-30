# LUMEN_CONTRACT_TRACKER

## ACTIVE SESSION

ACTIVE SESSION:
- DB Schema & Migration Sync Execution Session

[SESSION_STATUS] COMPLETED

[SESSION_SCOPE]
- Lock the DB schema/migration synchronization contract using final local evidence.
- Keep implementation and contract entries mapped one-to-one.
- Consolidate duplicate DB schema hotfix contract fragments into one canonical locked contract.

[SESSION_GOAL]
- Mark `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT` as LOCKED only after migration, schema guard, repository, pipeline integration, and full MarketData PHPUnit evidence are recorded.

[SESSION_NOTES]
- Governance recovery applied: prior DB schema contract sub-entries were merged into the canonical `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT` entry.
- No unresolved gap remains for this DB Schema & Migration Sync scope in the current source-of-truth ZIP.
- Future schema-related work must preserve this locked rule or reopen it explicitly as a contract/policy change.

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
