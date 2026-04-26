# DB Schema & Migration Sync Contract LOCKED

Status: LOCKED  
Scope: market-data database schema, migrations, SQLite test mirror, repository query compatibility, and schema audit evidence.

---

## 1. Source of Truth Hierarchy

Schema intent must be resolved in this order:

1. LOCKED domain contract in `docs/market_data/book/**`
2. DB contract in `docs/market_data/db/**`
3. Laravel migration in `database/migrations/**`
4. SQLite test schema in `tests/Support/UsesMarketDataSqlite.php`
5. Repository query in `app/Infrastructure/Persistence/MarketData/**`
6. Tests

No market-data field, index, default, status domain, pointer field, publication field, correction field, replay field, coverage field, source identity field, or metadata field may live only in one layer without an explicit `NEEDS_POLICY_DECISION` entry in the schema diff matrix.

---

## 2. Schema Sync Rule

Every new field must be represented consistently in:

- SQL docs / locked DB schema
- Laravel migration path
- SQLite test schema when repository/tests use the table
- repository insert/update/select payloads when the application uses the field
- tests or manual validation evidence when the field changes runtime behavior

The following attributes must be synchronized or explicitly documented as a MariaDB-vs-SQLite compatibility exception:

- type family
- nullable state
- default value
- primary key
- unique key
- index
- foreign key / reference intent
- enum or status domain
- timestamp behavior
- JSON/text metadata representation

SQLite is a mirror for test behavior. SQLite is not a staging area for experimental columns.

---

## 3. Migration Rule

Create a new migration when an existing deployed runtime schema must change.

Existing migrations may only be edited when the project is still treating the migration as development-only and the edit does not contradict `Migration_Policy_LOCKED.md`.

Only update docs when runtime migration already contains the correct schema and the drift is documentation lag.

Only update SQLite when MariaDB schema/migration are correct and the test mirror is lagging.

Update repository queries when the query uses a column that is not guaranteed by SQL docs + migration + SQLite or when insert/update payloads rely on implicit defaults that the app must own explicitly.

Migration DDL must not contradict `Database_Schema_MariaDB.sql` or the owning LOCKED domain contracts.

---

## 4. Repository Query Rule

Repository queries must only read/write columns that exist in SQL docs, migration runtime path, and SQLite test schema when covered by tests.

Select aliases must be explicit when joining tables with overlapping columns.

JSON metadata fields must have a consistent runtime type strategy:

- MariaDB may use `JSON`
- SQLite test mirror may use `text`
- repository/service code must encode/decode explicitly when persistence requires string form

Nullable handling must match schema. Application code must not silently depend on database defaults when the business contract requires an explicit value.

---

## 5. Test Schema Rule

`tests/Support/UsesMarketDataSqlite.php` must mirror the MariaDB schema as closely as possible for all tested market-data tables.

Allowed differences must be explicit compatibility differences only, for example:

- MariaDB `ENUM` mirrored as SQLite `string`
- MariaDB `JSON` mirrored as SQLite `text`
- MariaDB `BIGINT UNSIGNED AUTO_INCREMENT` mirrored as SQLite incrementing integer where needed
- MariaDB foreign key enforcement disabled in SQLite bootstrap when test speed/isolation requires it

Important indexes that affect repository contracts must be represented in SQLite where Laravel's SQLite schema builder supports them.

Coverage, source identity, correction, replay, publication pointer, run state, and session snapshot fields are mandatory in SQLite when tests exercise those tables.

---

## 6. Final Policy Decision

Selected policy: **OPTION C — LOCKED CONTRACT + RUNTIME RECONCILIATION**.

Reason:

- `Database_Schema_MariaDB.sql` was authoritative for core EOD tables, but it lagged migration-created tables (`tickers`, `market_calendar`, `md_session_snapshots`) and later replay expected-context columns.
- Laravel migrations were closest to runtime behavior, but migrations alone cannot override locked domain contracts.
- SQLite was usable for many integration tests, but it had mirror drift for ticker/calendar/session snapshot structure.

Therefore schema changes must be reconciled from locked contract intent plus runtime evidence, then reflected consistently across SQL docs, migration path, SQLite mirror, repositories, tests, and audit docs.

---

## 7. Current Session Diff Matrix

| Table | Field / Index / Constraint | SQL Docs | Migration | SQLite Test | Repository Usage | Status | Required Action |
|---|---|---|---|---|---|---|---|
| `tickers` | table definition | Missing from core SQL | Present | Present but reduced | `TickerMasterRepository` uses id/code/active/listed/delisted | `SQLITE_ONLY` / migration-doc drift | Add full table to `Database_Schema_MariaDB.sql`; align SQLite columns/index. |
| `tickers` | `company_name`, `company_logo`, `board_code`, `exchange_code`, timestamps, `ticker_code` unique | Missing from core SQL | Present | Missing/reduced | not all fields queried, but runtime schema requires them | `MIGRATION_ONLY` / `SQLITE_ONLY` mismatch | Add to SQL docs and SQLite mirror. |
| `market_calendar` | `holiday_name`, `session_open_time`, `session_close_time`, `breaks_json`, `source`, `market_calendar_trading_idx` | Missing from core SQL | Present | Missing/reduced and had `market_code` test-only | `MarketCalendarRepository` uses `cal_date`, `is_trading_day` | `MIGRATION_ONLY` / `SQLITE_ONLY` mismatch | Add table to SQL docs; remove test-only `market_code`; align SQLite fields/index. |
| `md_session_snapshots` | full table + unique/indexes | Missing from core SQL | Present | Missing | `SessionSnapshotRepository` writes/ purges table | `MIGRATION_ONLY` / `REPOSITORY_ONLY` against SQL/SQLite | Add table to SQL docs and SQLite mirror. |
| `md_replay_daily_metrics` | expected replay context fields | Missing from core SQL | Added by migrations | Present | `ReplayResultRepository` writes expected fields | `MIGRATION_ONLY` / SQL doc lag | Add expected fields to SQL docs. |
| `eod_runs` | source identity, coverage, publication/correction fields | Present | Base SQL + alter migrations | Present | used by pipeline/evidence/ops | `MATCH` | No runtime patch. |
| `eod_publications` | lineage + source file identity fields | Present | Base SQL + alter migrations | Present | used by publication repo/evidence | `MATCH` | No runtime patch. |
| `eod_current_publication_pointer` | pointer PK/unique/run index | Present | Base SQL | Present except FK enforcement disabled | publication repository | `MATCH` with SQLite compatibility exception | No runtime patch. |
| `eod_dataset_corrections` | re-execution fields/status domain | Present | Base SQL + alter migrations | Present | correction repository/pipeline | `MATCH` | No runtime patch. |
| `eod_bars`, `eod_indicators`, `eod_eligibility` | publication-bound live rows | Present | Base SQL | Present with surrogate test IDs | artifact/evidence/read repos | `MATCH` with SQLite PK compatibility exception | No runtime patch. |
| `*_history` tables | immutable publication snapshots | Present | Base SQL | Present with surrogate test IDs | artifact/evidence/read repos | `MATCH` with SQLite PK compatibility exception | No runtime patch. |
| `md_replay_reason_code_counts` | replay reason counts | Present | Base SQL | Present | replay repository | `MATCH` | No runtime patch. |

---

## 8. Impact Analysis

- Daily pipeline: protected from false-positive SQLite tests caused by missing ticker/calendar/session snapshot columns.
- Promote/finalize/pointer: no behavior change; publication and pointer schema already matched.
- Coverage gate: no behavior change; `eod_runs` coverage fields already matched.
- Manual file publishability: no behavior change; source identity fields already matched.
- Correction re-execution: no behavior change; correction fields already matched.
- Source identity lineage: no behavior change; run/publication source file fields already matched.
- Replay verification: SQL docs now include expected replay context fields already written by repository/migrations.
- Evidence export: no repository patch required; evidence fields remain schema-backed.
- Session snapshot: SQLite tests can now exercise repository paths without missing table/column errors.

---

## 9. Manual Validation Commands

```bash
vendor/bin/phpunit tests/Unit/MarketData/PublicationRepositoryIntegrationTest.php
vendor/bin/phpunit tests/Unit/MarketData/CorrectionRepositoryIntegrationTest.php
vendor/bin/phpunit tests/Unit/MarketData/ReplayResultRepositoryIntegrationTest.php
vendor/bin/phpunit tests/Unit/MarketData/ReadablePublicationReadContractIntegrationTest.php
vendor/bin/phpunit tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php
vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php
vendor/bin/phpunit tests/Unit/MarketData/ReplayVerificationServiceTest.php
vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php
```

```sql
SHOW COLUMNS FROM tickers;
SHOW COLUMNS FROM market_calendar;
SHOW COLUMNS FROM eod_runs;
SHOW COLUMNS FROM eod_publications;
SHOW COLUMNS FROM eod_current_publication_pointer;
SHOW COLUMNS FROM eod_dataset_corrections;
SHOW COLUMNS FROM md_replay_daily_metrics;
SHOW COLUMNS FROM md_session_snapshots;

SHOW INDEX FROM tickers;
SHOW INDEX FROM market_calendar;
SHOW INDEX FROM eod_runs;
SHOW INDEX FROM eod_publications;
SHOW INDEX FROM eod_current_publication_pointer;
SHOW INDEX FROM eod_dataset_corrections;
SHOW INDEX FROM md_replay_daily_metrics;
SHOW INDEX FROM md_session_snapshots;
```

---

## 10. 2026-04-26 Session Evidence Refresh

Status: LOCKED + IMPLEMENTED

### Policy Confirmation

Selected policy remains **OPTION C — LOCKED CONTRACT + RUNTIME RECONCILIATION**.

Authority is locked as:

1. DB contract docs define schema governance and sync rules.
2. `docs/market_data/db/Database_Schema_MariaDB.sql` is the canonical full MariaDB schema snapshot and is executed directly by the core market-data migration.
3. Laravel/Lumen migrations implement runtime schema creation and runtime schema evolution.
4. `tests/Support/UsesMarketDataSqlite.php` mirrors the runtime schema for tested market-data tables only, with explicit compatibility exceptions.
5. Repository/model/query usage must be backed by contract + MariaDB schema + migration + SQLite mirror when covered by tests.

### Current Drift Resolution

| Area | Finding | Resolution |
|---|---|---|
| `eod_reason_codes` | Present in MariaDB SQL schema but missing from SQLite test mirror. | Added to SQLite mirror with the same contract columns and category/active index. |
| `md_replay_daily_metrics.source_file_*` | Present only in SQLite test mirror. Repository replay persistence does not write these fields, and MariaDB SQL/migrations do not own them for replay metrics. | Removed from SQLite mirror. Source-file identity remains owned by `eod_runs` and `eod_publications`, not replay metrics. |
| `eod_bars`, `eod_indicators`, `eod_eligibility`, history tables | SQLite has surrogate increment IDs while MariaDB uses contract primary keys or history IDs. | Allowed SQLite compatibility exception remains locked. These surrogate IDs are not business fields and must not be queried as domain contract fields. |

### No-Orphan Rule Enforcement

After this refresh, no market-data test mirror field is allowed to exist only in SQLite unless documented as a compatibility-only surrogate key. Replay source-file fields are explicitly not part of `md_replay_daily_metrics` until a future locked policy and runtime migration requires them.

### Required Validation

`MarketDataSqliteSchemaSyncTest` now checks:

- `eod_reason_codes` exists in SQLite with contract columns.
- `md_replay_daily_metrics` does not contain SQLite-only `source_file_*` fields.

---

## 11. 2026-04-26 Runtime DB Validation Follow-up

Status: RUNTIME DRIFT FOUND + REMEDIATION MIGRATION ADDED

The operator-supplied `SHOW COLUMNS` / `SHOW INDEX` workbook confirmed that PHPUnit/SQLite mirror validation passed, but the developer MariaDB runtime database still had deployed-state drift from the locked schema snapshot.

### Runtime Drift Evidence

| Table | Finding from runtime DB workbook | Locked schema state | Decision |
|---|---|---|---|
| `eod_publications` | `promote_mode` and `publish_target` existed in the live table. | These fields are not in `Database_Schema_MariaDB.sql`, not owned by the publication repository, and are not required for runtime publication behavior. Promote intent is owned by `eod_runs`. | Treat as DB-only orphan fields and remove through idempotent remediation migration. |
| `md_replay_daily_metrics` | Actual coverage context fields such as `coverage_universe_count`, `coverage_available_count`, `coverage_missing_count`, `coverage_min_threshold`, `coverage_gate_state`, `coverage_threshold_mode`, `coverage_universe_basis`, `coverage_contract_version`, and `coverage_missing_sample_json` were missing from live DB. `coverage_ratio` existed but was `DECIMAL(6,4)` instead of locked `DECIMAL(12,6)`. | Locked schema and SQLite mirror contain actual replay coverage context fields used by `ReplayResultRepository` and `ReplayVerificationService`. | Add missing columns and widen `coverage_ratio` through idempotent remediation migration. |
| `md_replay_daily_metrics` | Replay metric indexes were not proven present in runtime workbook. | Locked schema defines replay status/effective/comparison/coverage/artifact indexes. | Recreate indexes idempotently. Existing indexes are ignored by migration try/catch. |
| `md_replay_reason_code_counts` | Secondary reason-code replay index was not proven present. | Locked schema defines `idx_replay_reason_code`. | Recreate index idempotently. |
| `md_session_snapshots` | Snapshot unique/index rows were not proven present in runtime workbook. | Locked schema defines unique `(trade_date, snapshot_slot, ticker_id)` and lookup indexes. | Recreate indexes idempotently. |

### Remediation Rule

Fresh databases remain governed by `Database_Schema_MariaDB.sql` because the core schema migration executes that file. Existing developer/runtime databases that were created before later locked schema updates must be reconciled by forward-only remediation migrations instead of silently relying on edited SQL snapshots.

### Remediation Migration

Added:

- `database/migrations/2026_04_26_000001_sync_runtime_db_to_locked_schema_contract.php`

The migration is intentionally schema-only and does not change market-data business behavior. It:

- adds missing replay actual coverage context fields to `md_replay_daily_metrics`;
- widens `md_replay_daily_metrics.coverage_ratio` to the locked `DECIMAL(12,6)` precision where supported;
- recreates locked replay/session indexes idempotently;
- removes DB-only orphan publication intent fields from `eod_publications` when present.

### Impact Lock

This follow-up does not change coverage-gate decisions, correction lifecycle, manual-file publishability, force replace behavior, read-side enforcement, finalize lock behavior, or publication replacement policy. It only reconciles deployed schema shape to the already locked DB schema contract.

## 12. 2026-04-26 Runtime DB Validation Final

Status: LOCKED + IMPLEMENTED + VALIDATED

The remediation migration was executed successfully in the operator environment:

- `php artisan migrate` migrated `2026_04_26_000001_sync_runtime_db_to_locked_schema_contract` successfully.
- `php -l database/migrations/2026_04_26_000001_sync_runtime_db_to_locked_schema_contract.php` passed.
- Targeted PHPUnit replay/schema validation passed after migration:
  - `MarketDataSqliteSchemaSyncTest` → OK (`2 tests`, `64 assertions`).
  - `ReplayResultRepositoryIntegrationTest` → OK (`1 test`, `5 assertions`).
  - `ReplayVerificationServiceTest` → OK (`5 tests`, `15 assertions`).

The post-migration `Column_Index.xlsx` runtime DB evidence was reviewed for the supplied tables. The checked runtime table columns now align with the locked schema snapshot for `eod_runs`, `eod_publications`, `eod_current_publication_pointer`, `eod_dataset_corrections`, `md_replay_daily_metrics`, `md_replay_reason_code_counts`, and `md_session_snapshots`.

The checked runtime indexes align with the locked index intent for the supplied tables. Foreign key constraints are governed by migration/schema contract review and are not expected to be represented as `SHOW INDEX` rows.

Final result: **DB schema and migration sync scope is DONE for the checked market-data tables.**
