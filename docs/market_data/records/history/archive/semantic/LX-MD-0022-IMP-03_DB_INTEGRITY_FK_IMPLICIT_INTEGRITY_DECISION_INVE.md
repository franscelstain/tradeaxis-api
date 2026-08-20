# Legacy Semantic Extract — LX-MD-0022-IMP-03

- Source ID: `LS-MD-0022`
- Original path: `audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md`
- Original SHA1: `BA5CB0819D76C0ADAEA2600174DA40EF3CFF16A3`
- Extract role: `IMPLEMENTATION`
- Source range: `L130-L138`
- Extract body SHA1: `4B38B8D9D5AF80E96F0482AE695C03C8C346A44F`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Patch Matrix

| Gap | File | Change | Why Safe | Test Coverage | Status |
|---|---|---|---|---|---|
| FK vs implicit policy was not explicit enough for live artifact relations | `DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md` | New decision inventory with matrices | Docs-only policy lock, no runtime behavior change | New static guard | Patched |
| Audit docs did not record this scoped decision as active session | `LUMEN_IMPLEMENTATION_STATUS.md`, `LUMEN_CONTRACT_TRACKER.md` | Added current working session/contract entries | Append-only; previous Replay entry preserved below | Audit docs sync guard expected locally | Patched |
| Schema comments did not name final decision explicitly | `Database_Schema_MariaDB.sql` | Added locked policy comment for hybrid FK/implicit integrity | Comment-only, no DDL change | New static guard | Patched |
| Static guard did not lock the new relation decision matrix | `DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` | Added targeted static guard | No runtime behavior change | `php -l` only in container | Patched |


<!-- LEGACY_EXTRACT_BODY_END -->
