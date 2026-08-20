# Legacy Semantic Extract — LX-MD-0243-GOV-01

- Source ID: `LS-MD-0243`
- Original path: `tests/Db_Integrity_Constraint_Inventory.md`
- Original SHA1: `F8EC0B923A05E4141D9FEF6A1E71E132AA698D5B`
- Extract role: `GOVERNANCE`
- Source range: `L9-L12`
- Extract body SHA1: `D248E3366E4211F090E4186BB3D617667C19D8FE`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Final rule

Market-data runtime code must not depend on a primary key, business-key uniqueness, pointer/publication/run relation, index, enum-like value, nullable/default assumption, or reason code that is not guaranteed by SQL schema/migration/SQLite mirror or protected by explicit implicit integrity guard and tests.


<!-- LEGACY_EXTRACT_BODY_END -->
