# Legacy Semantic Extract — LX-MD-0026-GOV-01

- Source ID: `LS-MD-0026`
- Original path: `audit/FAIL_SAFE_NO_SILENT_FAILURE_INVENTORY.md`
- Original SHA1: `968C8EDCD6CA2212F89EADAB5388BA2F831C0715`
- Extract role: `GOVERNANCE`
- Source range: `L11-L14`
- Extract body SHA1: `77553927A1A18391BDA5FD24AF6F750D4C8DCF5A`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Final rule

Market-data must never expose empty, failed, incomplete, or unproven data as `SUCCESS + READABLE`. Source failure, empty manual/API input, zero valid canonical bars, empty artifacts, non-evaluable coverage, missing hash/seal/candidate proof, invalid pointer target, failed correction candidate, incomplete evidence, or incomplete replay proof must produce a safe state: `HELD`, `FAILED`, `BLOCKED`, or `NOT_READABLE`, with registered reason code and pointer preservation where a previous readable publication exists.


<!-- LEGACY_EXTRACT_BODY_END -->
