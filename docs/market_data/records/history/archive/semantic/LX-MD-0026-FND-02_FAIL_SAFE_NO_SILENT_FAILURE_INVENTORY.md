# Legacy Semantic Extract — LX-MD-0026-FND-02

- Source ID: `LS-MD-0026`
- Original path: `audit/FAIL_SAFE_NO_SILENT_FAILURE_INVENTORY.md`
- Original SHA1: `968C8EDCD6CA2212F89EADAB5388BA2F831C0715`
- Extract role: `FINDING`
- Source range: `L51-L58`
- Extract body SHA1: `55EBDEFBB2FC4943F8FD73DF280F7035B8A6D0E2`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Source failure rule final

API timeout/rate-limit/unavailable/malformed/no-valid-data must throw or resolve to a safe non-readable run state. Empty API response now uses `RUN_SOURCE_NO_VALID_DATA` and is handled as recoverable source failure for API runs, preserving any prior readable pointer.

## Manual file failure rule final

Manual file missing/unreadable/malformed/empty/all-invalid must not become accepted success. Empty manual CSV/JSON now throws `RUN_SOURCE_MANUAL_FILE_EMPTY`; parsed rows with zero canonical valid bars now throw `RUN_SOURCE_MANUAL_FILE_NO_VALID_ROWS`.


<!-- LEGACY_EXTRACT_BODY_END -->
