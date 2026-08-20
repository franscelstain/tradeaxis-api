# Legacy Semantic Extract — LX-MD-0044-DEC-01

- Source ID: `LS-MD-0044`
- Original path: `audit/reports/AUDIT_FINAL_STATE.md`
- Original SHA1: `4F88A2D91FB7215D7D64C2F3ACFED4764964CB20`
- Extract role: `DECISION`
- Source range: `L1230-L1247`
- Extract body SHA1: `E7A7F735ABFC1C820F4B146A84B42A497D207C13`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Yahoo Finance decision within this roadmap

Yahoo Finance is **accepted**, not tolerated as an accidental defect.

Current decision:

- primary bootstrap EOD source: `api_free/yahoo_finance`;
- controlled **one-date operational rescue**: `manual_file`; bukan continuity source untuk outage multi-hari;
- purpose: prove the market-data product can deliver useful, governed data for an initial use case before paid-data spending;
- quality posture: full validation, provenance, quarantine, correction, and readability gates remain mandatory;
- disclosure: never label Yahoo data as official IDX data;
- licensing posture: usage and redistribution remain subject to applicable provider terms;
- paid-provider project: deferred and not part of current remediation.

Yahoo dependency changes the implementation of acquisition resilience, but it does not lower the target quality of canonical data. The future-safe investment now is provider-neutral contracts and immutable lineage, not early vendor procurement.

---


<!-- LEGACY_EXTRACT_BODY_END -->
