# Legacy Semantic Extract — LX-MD-0019-GOV-01

- Source ID: `LS-MD-0019`
- Original path: `audit/CONFIG_ENV_GOVERNANCE_CLEANUP_INVENTORY.md`
- Original SHA1: `B522643CB68AFF2ECC9A8268A482C11CE2D61598`
- Extract role: `GOVERNANCE`
- Source range: `L11-L27`
- Extract body SHA1: `323DCF85FD8B804564DFFF71F718FD0E95A33B1B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Scope

This inventory records the config/env cleanup for market-data. It is intentionally scoped to schema/config/runtime alignment and pruning of stale config surfaces. It does not rewrite source-mode, coverage, read-side pointer, publication, replay, evidence, or DB integrity policy.

## Existing Contract / Test / Doc Matrix

| Existing Contract / Test / Doc | Role | Current Status | Relevance to Config/ENV Cleanup | Reuse / Extend / Do Not Touch |
|---|---|---|---|---|
| `AUDIT_UPDATE_GOVERNANCE.md` | Audit update rules | LOCKED | Append-only audit-doc update and anti-duplication rule | Reuse |
| `LUMEN_IMPLEMENTATION_STATUS.md` | Implementation evidence | UPDATED | Records active session, patch, runtime status, and validation limits | Extend |
| `LUMEN_CONTRACT_TRACKER.md` | Contract lifecycle | UPDATED | Records `CONFIG_ENV_GOVERNANCE_CLEANUP_CONTRACT` status | Extend |
| `Database_Schema_MariaDB.sql` | Schema truth for market-data | LOCKED | Proves `tickers.is_active` is `TINYINT(1) NOT NULL DEFAULT 1` | Reuse |
| `2026_03_22_000001_create_tickers_table.php` | Migration truth | LOCKED | Proves `tickers.is_active` is Laravel boolean default true | Reuse |
| `TickerMasterRepository.php` | Runtime ticker universe resolver | PATCHED | Uses strict numeric active value, no `Yes` fallback | Extend |
| `ConfigEnvGovernanceCleanupStaticGuardTest.php` | Static policy guard | ADDED | Prevents config/env/schema mismatch regression | Extend |
| `TickerMasterRepositoryTest.php` | Behavioral guard | ADDED | Proves numeric active filtering excludes stale string value | Extend |


<!-- LEGACY_EXTRACT_BODY_END -->
