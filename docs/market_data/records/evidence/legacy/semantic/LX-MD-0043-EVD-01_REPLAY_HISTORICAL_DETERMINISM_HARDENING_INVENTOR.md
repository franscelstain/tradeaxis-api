# Legacy Semantic Extract — LX-MD-0043-EVD-01

- Source ID: `LS-MD-0043`
- Original path: `audit/REPLAY_HISTORICAL_DETERMINISM_HARDENING_INVENTORY.md`
- Original SHA1: `6831E28FEFD55DC99E3BEA0B303AC2A439016C86`
- Extract role: `EVIDENCE`
- Source range: `L63-L71`
- Extract body SHA1: `5825FEF9D32BF8D2F5A7D6CD6E0BCACD665CED9D`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Evidence Historical Reuse Matrix

| Evidence Historical Component | Reused By Replay? | Replay-Specific Wrapper Needed? | Risk | Action |
|---|---:|---:|---|---|
| `resolvePublicationForEvidenceAudit()` | Yes | Yes | Direct use could blur replay vs evidence wording | Wrapped by `resolvePublicationForReplayActualState()` |
| `dominantReasonCodesForEvidencePublication()` | Yes | No | None if publication_id passed explicitly | Used for historical replay reason codes |
| `exportEligibilityRowsForEvidencePublication()` | Yes | No | None if publication_id passed explicitly | Used for historical replay eligibility count |
| Evidence output labels | Partially | Yes | Evidence field names are not replay field names | Replay context maps to `replay_*` fields |


<!-- LEGACY_EXTRACT_BODY_END -->
