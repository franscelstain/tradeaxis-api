# Legacy Semantic Extract — LX-MD-0023-IMP-02

- Source ID: `LS-MD-0023`
- Original path: `audit/DB_SCHEMA_MIGRATION_SYNC_INVENTORY.md`
- Original SHA1: `2C21334498F0471DF8EE45D555AC98F3F5279BB4`
- Extract role: `IMPLEMENTATION`
- Source range: `L45-L56`
- Extract body SHA1: `AF9839A297B3336005265D9308346C46F0608840`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
