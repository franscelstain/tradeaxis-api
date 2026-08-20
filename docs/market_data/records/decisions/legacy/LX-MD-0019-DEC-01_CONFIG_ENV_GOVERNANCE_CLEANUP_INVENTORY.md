# Legacy Semantic Extract — LX-MD-0019-DEC-01

- Source ID: `LS-MD-0019`
- Original path: `audit/CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md`
- Original SHA1: `B522643CB68AFF2ECC9A8268A482C11CE2D61598`
- Extract role: `DECISION`
- Source range: `L112-L123`
- Extract body SHA1: `EADEAF01E699F0EFFC331E65499B4C7333D2C13D`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## `tickers.is_active` Decision Matrix

| Area | Current Value / Behavior Before Patch | Expected | Patch Needed | Status |
|---|---|---|---:|---|
| Migration | `$table->boolean('is_active')->default(true)` | boolean/numeric | no | VERIFIED |
| SQL schema doc | `is_active TINYINT(1) NOT NULL DEFAULT 1` | numeric `1/0` | no | VERIFIED |
| Config before patch | `active_yes_value => env(..., 'Yes')` | numeric active value | yes | PATCHED |
| ENV before patch | `MARKET_DATA_TICKERS_ACTIVE_YES_VALUE=Yes` | `MARKET_DATA_TICKERS_ACTIVE_VALUE=1` | yes | PATCHED |
| Repository before patch | accepted configured `Yes`, `1`, and `true` | strict numeric active value | yes | PATCHED |
| Tests before patch | seeded `is_active => 'Yes'` in two integration fixtures | seed `1` | yes | PATCHED |
| Generic DB ticker doc before patch | `ENUM('Yes','No') atau BOOLEAN canonical` | boolean/TINYINT canonical | yes | PATCHED |


<!-- LEGACY_EXTRACT_BODY_END -->
