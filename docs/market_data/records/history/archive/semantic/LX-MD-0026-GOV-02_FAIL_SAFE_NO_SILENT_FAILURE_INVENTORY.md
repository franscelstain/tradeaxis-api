# Legacy Semantic Extract — LX-MD-0026-GOV-02

- Source ID: `LS-MD-0026`
- Original path: `audit/FAIL_SAFE_NO_SILENT_FAILURE_INVENTORY.md`
- Original SHA1: `968C8EDCD6CA2212F89EADAB5388BA2F831C0715`
- Extract role: `GOVERNANCE`
- Source range: `L59-L66`
- Extract body SHA1: `E8C106AD47964DDDA758EE12CB1A3EA884531BD8`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Empty artifact/data rule final

Zero valid bars is not a valid artifact. Ingest now blocks before `replaceBars()` when `validRows` is empty. Finalize also blocks explicit `bars_rows_written=0` / `accepted_row_count=0` as `NOT_READABLE`.

## Pointer preservation rule final

Any unsafe source/candidate/finalize/correction outcome must preserve the previous readable current pointer where available. No candidate from zero valid data may switch pointer.


<!-- LEGACY_EXTRACT_BODY_END -->
