# Legacy Semantic Extract — LX-MD-0030-DEC-01

- Source ID: `LS-MD-0030`
- Original path: `audit/LUMEN_CONTRACT_TRACKER.md`
- Original SHA1: `88A4CD4C9D12A578A2837DCB275B315C91D1492A`
- Extract role: `DECISION`
- Source range: `L161-L178`
- Extract body SHA1: `FC46BD9299473A2A86A3337C383D9407085B8994`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## OPERATIONAL STATUS


[CURRENT_AUDIT_MODE]
- CLEAN_START_RETEST

[HISTORICAL_STATUS_POLICY]
- Previous DONE/LOCKED contract claims are not copied as current status without fresh scoped evidence.
- Contract status is rebuilt one concern at a time and mapped to implementation evidence.
- Revalidated contracts must be represented as canonical entries, not repeated hotfix/session fragments.

[DEFAULT_RULE]
- No contract may be marked DONE without current implementation evidence.
- No contract may be marked LOCKED without FINAL_RULE and VALIDATED evidence.
- One contract concern must have one canonical tracker entry.

---


<!-- LEGACY_EXTRACT_BODY_END -->
