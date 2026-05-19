# DB Schema / Migration Sync Inventory

Status: `LOCKED_LOCAL_PHPUNIT_AND_MIGRATION_PASS`
Last updated: 2026-05-19
Related contract: `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT`
Related implementation: `DB Schema & Migration Sync / Runtime Schema Four-Way Synchronization`

## Scope

This inventory records the 2026-05-19 schema/migration sync refresh for market-data. It is limited to schema docs, Laravel migrations, SQLite mirror/test guards, repository query assumptions, runtime schema contracts, and audit ledger synchronization.

This session does not claim full market-data production readiness.

## Drift Matrix

| Table / Area | Docs SQL | Migration | SQLite Test Schema | Repository Query | Runtime/Audit Value | Drift |
|---|---|---|---|---|---|---|
| `eod_runs` coverage precision | 8,6 precision before patch | `2026_04_03_000002` adds `DECIMAL(12,6)` only when missing | `DECIMAL(12,6)` | `EodRunRepository`, pointer/evidence/replay services persist/read ratios | Runtime threshold model uses `0.98` and full precision proof | SQL docs and fresh core schema lagged migration/runtime precision |
| `md_replay_daily_metrics` coverage precision | actual/expected coverage ratio/threshold used 8,6 precision before patch | `2026_04_03_000001` and `2026_04_26_000001` target `DECIMAL(12,6)` where missing/covered | `DECIMAL(12,6)` | `ReplayResultRepository` writes actual/expected coverage fields | Replay comparison expects actual vs expected coverage proof | SQL docs and fresh core schema lagged migration/runtime precision |
| `eod_publications` | Canonical schema had lineage/source-file/index fields; sidecar doc lagged | Core SQL plus `2026_04_24`/`2026_05_07` add lineage/source/indexes | Present | `EodPublicationRepository::getOrCreateCandidatePublication`, `buildManifestByPublicationId` | Publication identity, lineage, seal/hash, source context | Sidecar `EOD_Publications_Table.sql` was stale |
| `eod_current_publication_pointer` | Canonical schema had PK, unique publication, FK, run and run/version indexes; sidecar doc lagged | Core SQL plus `2026_05_07` enforces run/version index | Present | Pointer-first repository resolvers | Pointer is authoritative current identity | Sidecar pointer DDL/policy was stale |
| `eod_dataset_corrections` | Baseline/replacement publication columns and indexes present | `2026_05_08_000001_add_correction_publication_lineage_fields` idempotent | Present | `EodCorrectionRepository` persists lifecycle linkage | Correction lineage guarded implicitly | No current column drift found |
| `eod_bars`, `eod_indicators`, `eod_eligibility` | PK `(trade_date,ticker_id)`, mandatory `run_id`/`publication_id`, publication-scoped indexes | Core SQL plus DB-integrity index migration | Present | `EodArtifactRepository`, evidence/read-side repos | Live artifact relation policy `HYBRID_REQUIRED` | No current column drift found |
| History artifact tables | PK `(publication_id,trade_date,ticker_id)` plus publication FK | Core SQL | Present without FK enforcement as SQLite compatibility | Evidence/replay historical resolvers | Immutable publication-bound proof | No current column drift found |
| `md_session_snapshots` | Full table/indexes present | `2026_03_24_000002` idempotent | Present | `SessionSnapshotRepository` | Snapshot unique `(trade_date,snapshot_slot,ticker_id)` | No current column drift found |
| Source/provider telemetry fields | Present on `eod_runs`; expected source fields present in replay metrics | Source/provider migrations target replay expected context | Present | Run, evidence, replay repositories/services | Source failure/partial response evidence | No current column drift found |
| Seal/hash fields | Present on runs/publications/history/replay | Core SQL and lineage migrations | Present | Publication/evidence/replay services | Seal/hash proof required for readable publication | No current column drift found |
| Pointer integrity fields | `trade_date`, `publication_id`, `run_id`, `publication_version`, `sealed_at`, `updated_at` | Core SQL and index migration | Present | Pointer-first repositories | Pointer owns current identity | Sidecar doc drift closed |
| Indexes | Main SQL had current indexes; index contract sidecar was incomplete | `2026_05_07` idempotent integrity indexes | Present | Repository read paths depend on pointer/publication/run indexes | DB integrity contract locked | Index contract sidecar drift closed |
| Unique constraints | Publication `(trade_date,publication_version)` and pointer `publication_id` unique | Core SQL | Present | Publication/pointer tests depend on them | One current pointer per date + unique pointed publication | No current unique drift found |
| FK assumptions | Pointer/history publication FKs explicit; lifecycle relations implicit | Core SQL | SQLite FK enforcement disabled | Repositories implement guards | `HYBRID_REQUIRED` | No hidden FK drift; policy remains explicit |
| Audit docs entry | Historical DB schema entry existed for 2026-05-01 | n/a | n/a | n/a | Needed active current entry | Updated current working implementation/contract |

## Decisions

- Coverage decimal precision is locked to `DECIMAL(12,6)` for `coverage_ratio`, `coverage_min_threshold`, `expected_coverage_ratio`, and `expected_coverage_min_threshold`.
- There is no `actual_coverage_ratio` column in the active schema; actual replay coverage uses `md_replay_daily_metrics.coverage_ratio`.
- `eod_current_publication_pointer` is the sole authoritative current publication pointer.
- `eod_publications.is_current` and `eod_runs.is_current_publication` are mirror/cache fields validated against the pointer.
- FK policy remains `HYBRID_REQUIRED`: pointer/history publication relations have explicit FKs; phase-dependent run/publication/correction/replay/evidence links use explicit columns plus repository/service/static guard enforcement.

## Patch Matrix

| Gap | File | Change | Status |
|---|---|---|---|
| Coverage precision docs/core schema lag | `Database_Schema_MariaDB.sql`, `DB_FIELDS_AND_METADATA.md` | Changed active coverage precision to `DECIMAL(12,6)` | Patched |
| Existing deployed DB precision lag | `database/migrations/2026_05_19_000001_widen_market_data_coverage_decimal_precision.php` | Added forward-only MySQL/MariaDB widening migration | Patched |
| Publication sidecar SQL stale | `EOD_Publications_Table.sql` | Mirrored canonical publication fields/indexes and `is_current` mirror policy | Patched |
| Pointer sidecar SQL stale | `EOD_Current_Publication_Pointer_Table.sql` | Mirrored canonical pointer DDL and pointer authority policy | Patched |
| Index contract incomplete | `Indices_and_Constraints_Contract_LOCKED.md` | Added current index/unique/pointer addendum | Patched |
| Schema guard did not lock precision drift | `MarketDataSqliteSchemaSyncTest.php` | Added precision sync assertion | Patched |
| Audit active session stale | `LUMEN_IMPLEMENTATION_STATUS.md`, `LUMEN_CONTRACT_TRACKER.md`, `AuditDocsSynchronizationStaticGuardTest.php` | Updated active/current working schema sync state without duplicating canonical contract | Patched |

## Validation Matrix

| Command | Result | Status |
|---|---|---|
| `php -l database/migrations/2026_05_19_000001_widen_market_data_coverage_decimal_precision.php` | No syntax errors detected | PASS |
| `php -l tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` | No syntax errors detected | PASS |
| `php -l tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` | No syntax errors detected | PASS |
| `php -l tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php` | No syntax errors detected | PASS |
| `php -l tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` | No syntax errors detected | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData/MarketDataSqliteSchemaSyncTest.php` | OK (5 tests, 139 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` | OK (9 tests, 297 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Schema"` | OK (15 tests, 357 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "DbIntegrity"` | OK (11 tests, 892 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` | OK (106 tests, 1269 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` | OK (82 tests, 1164 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` | OK (68 tests, 1336 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` | OK (55 tests, 850 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` | OK (54 tests, 989 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` | OK (9 tests, 297 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK (169 tests, 3842 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK (447 tests, 6488 assertions) | PASS |
| `php artisan migrate:fresh --env=testing` | PASS; migration `2026_05_19_000001_widen_market_data_coverage_decimal_precision` applied | PASS |
| Runtime `information_schema.COLUMNS` precision smoke | Six coverage ratio/threshold columns report precision 12 and scale 6 | PASS |

## Remaining Risk

- None for the DB schema/migration sync scope after the current local PHPUnit, migration, and runtime precision smoke validation.
- This inventory does not claim full market-data production-ready. Read-side consumer completion, evidence/replay runtime matrix coverage, ops runtime matrix closure, and final roadmap-wide audit synchronization remain separate scopes if not already closed.
