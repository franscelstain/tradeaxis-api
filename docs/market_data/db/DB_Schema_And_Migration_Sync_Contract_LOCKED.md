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
