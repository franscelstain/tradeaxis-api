# Legacy Semantic Extract — LX-MD-0023-FND-01

- Source ID: `LS-MD-0023`
- Original path: `audit/DB_SCHEMA_MIGRATION_SYNC_INVENTORY.md`
- Original SHA1: `2C21334498F0471DF8EE45D555AC98F3F5279BB4`
- Extract role: `FINDING`
- Source range: `L17-L36`
- Extract body SHA1: `F00D196403FB3EC2E365E421D6D0EA695BD1212B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
