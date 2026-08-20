# Legacy Semantic Extract — LX-MD-0043-GOV-01

- Source ID: `LS-MD-0043`
- Original path: `audit/REPLAY_HISTORICAL_DETERMINISM_HARDENING_INVENTORY.md`
- Original SHA1: `6831E28FEFD55DC99E3BEA0B303AC2A439016C86`
- Extract role: `GOVERNANCE`
- Source range: `L54-L62`
- Extract body SHA1: `A440BBB367CC3AF7F1E28FC30FEF0BEBB30E33D0`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Artifact Scope Matrix

| Artifact Type | Current Replay Actual Source | Historical Replay Actual Source | Publication Scoped? | Missing Artifact Behavior | Status |
|---|---|---|---:|---|---|
| Reason code counts | `dominantReasonCodes()` | `dominantReasonCodesForEvidencePublication()` | Yes | reason-coded mismatch/failure | PATCHED |
| Eligibility rows | `exportEligibilityRows()` | `exportEligibilityRowsForEvidencePublication()` via historical evidence path | Yes | no current fallback | PATCHED |
| Hash/manifest context | run/publication hash fields | selected publication/run evidence context | Yes | reason-coded mismatch | PATCHED |
| Coverage context | run/publication coverage fields | selected run/publication coverage basis | Yes | reason-coded mismatch | PATCHED |


<!-- LEGACY_EXTRACT_BODY_END -->
