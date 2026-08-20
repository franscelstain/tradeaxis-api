# Legacy Semantic Extract — LX-MD-0022-GOV-02

- Source ID: `LS-MD-0022`
- Original path: `audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md`
- Original SHA1: `BA5CB0819D76C0ADAEA2600174DA40EF3CFF16A3`
- Extract role: `GOVERNANCE`
- Source range: `L150-L152`
- Extract body SHA1: `3581274E69E72254571A71BB1163E283629CEF19`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Final rule

`HYBRID_REQUIRED` is the locked policy validated by operator-local runtime proof. Do not add physical FK to current live artifact publication/run/ticker relations without a separate migration/data-cleanup proof and full local PHPUnit proof. Do not remove existing pointer/history publication FKs. Do not treat implicit lifecycle integrity as optional: every non-FK relation must stay protected by repository/service/evidence/replay/static guard proof. Do not claim entire schema sync failure from this risk; this is scoped live artifact relation hardening. Current status is DONE/LOCKED for this decision scope based on supplied operator-local PHPUnit proof.

<!-- LEGACY_EXTRACT_BODY_END -->
