# Legacy Semantic Extract — LX-MD-0022-GOV-01

- Source ID: `LS-MD-0022`
- Original path: `audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md`
- Original SHA1: `BA5CB0819D76C0ADAEA2600174DA40EF3CFF16A3`
- Extract role: `GOVERNANCE`
- Source range: `L11-L26`
- Extract body SHA1: `CE4E34D4CC77156A27363BD7DEC433DBE9789284`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Scope confirmation

Audit terakhir tidak menyatakan seluruh schema sync gagal. Scope sesi ini hanya menutup risiko bahwa beberapa live artifact relation masih bergantung pada asumsi code/repository, bukan jaminan eksplisit database constraint. Keputusan final untuk source-of-truth ZIP ini adalah mengunci policy `HYBRID_REQUIRED`: FK eksplisit dipakai hanya untuk relation yang stabil dan aman, sedangkan relation lifecycle yang phase-dependent tetap memakai implicit guard yang wajib tertulis, teruji, dan tidak boleh melemahkan current-pointer/read-side contract.

## Existing contract owner

| Existing Contract / Test / Doc | Role | Current Status | Relevance to DB Integrity Decision | Reuse / Extend / Do Not Touch |
|---|---|---|---|---|
| `DB_INTEGRITY_CONSTRAINT_ENFORCEMENT_CONTRACT` | Canonical owner untuk PK/unique/index/implicit guard DB integrity | LOCKED by prior operator-local proof | Basis kebijakan bahwa non-FK lifecycle relation harus punya implicit guard/test | Extend via this decision inventory; do not duplicate old contract wording |
| `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT` | Four-way schema/runtime sync | DONE historical | Batas supaya sesi ini tidak dianggap schema sync ulang seluruh sistem | Do not reopen unless migration/schema actually changes |
| `READ_SIDE_POINTER_ENFORCEMENT_CONTRACT` | Consumer current-pointer-only contract | LOCKED | Live artifact current reads tidak boleh bypass pointer | Preserve |
| `REPLAY_HISTORICAL_DETERMINISM_HARDENING_CONTRACT` | Historical replay publication proof | LOCKED | Replay historical must remain publication-scoped and not current-pointer dependent | Preserve |
| `EVIDENCE_HISTORICAL_LINEAGE_COMPLETENESS_CONTRACT` | Historical evidence audit resolver | LOCKED | Evidence may resolve historical publication by explicit selector | Preserve |
| `DbIntegrityConstraintEnforcementStaticGuardTest.php` | Existing DB integrity static guard | Present | Guards PK/index/implicit policy and no latest/MAX shortcuts | Extend conceptually with new decision guard |
| `DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` | New decision static guard | Added in this session | Locks FK vs implicit decision matrix and audit docs sync | New guard |


<!-- LEGACY_EXTRACT_BODY_END -->
