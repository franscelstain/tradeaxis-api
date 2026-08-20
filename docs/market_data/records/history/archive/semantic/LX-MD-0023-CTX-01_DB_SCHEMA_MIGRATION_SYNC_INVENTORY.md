# Legacy Semantic Extract — LX-MD-0023-CTX-01

- Source ID: `LS-MD-0023`
- Original path: `audit/DB_SCHEMA_MIGRATION_SYNC_INVENTORY.md`
- Original SHA1: `2C21334498F0471DF8EE45D555AC98F3F5279BB4`
- Extract role: `CONTEXT`
- Source range: `L37-L44`
- Extract body SHA1: `E74BFF0D36C3F5D7B3C16A871EA224D08C24A3DF`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Decisions

- Coverage decimal precision is locked to `DECIMAL(12,6)` for `coverage_ratio`, `coverage_min_threshold`, `expected_coverage_ratio`, and `expected_coverage_min_threshold`.
- There is no `actual_coverage_ratio` column in the active schema; actual replay coverage uses `md_replay_daily_metrics.coverage_ratio`.
- `eod_current_publication_pointer` is the sole authoritative current publication pointer.
- `eod_publications.is_current` and `eod_runs.is_current_publication` are mirror/cache fields validated against the pointer.
- FK policy remains `HYBRID_REQUIRED`: pointer/history publication relations have explicit FKs; phase-dependent run/publication/correction/replay/evidence links use explicit columns plus repository/service/static guard enforcement.


<!-- LEGACY_EXTRACT_BODY_END -->
