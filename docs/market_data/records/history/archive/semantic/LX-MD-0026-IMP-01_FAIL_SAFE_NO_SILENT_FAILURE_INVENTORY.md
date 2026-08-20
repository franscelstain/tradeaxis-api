# Legacy Semantic Extract — LX-MD-0026-IMP-01

- Source ID: `LS-MD-0026`
- Original path: `audit/FAIL_SAFE_NO_SILENT_FAILURE_INVENTORY.md`
- Original SHA1: `968C8EDCD6CA2212F89EADAB5388BA2F831C0715`
- Extract role: `IMPLEMENTATION`
- Source range: `L15-L46`
- Extract body SHA1: `1B8A516990FE018E5B03BB3A290206515CEA0DF2`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Inventory

| Area | Failure Scenario | Required State | Reason Code | Pointer Preserved | Evidence | Replay | Status | Patch | Test Result |
|---|---|---|---|---:|---:|---:|---|---|---|
| API source | API timeout | `HELD` / `NOT_READABLE` when fallback exists, otherwise source failure state | `RUN_SOURCE_TIMEOUT` | Yes | Yes | Yes | Already enforced | Existing provider retry + recoverable source failure path | Closed by later full-suite PHPUnit PASS |
| API source | API rate limit | `HELD` / `NOT_READABLE` when fallback exists, otherwise source failure state | `RUN_SOURCE_RATE_LIMIT` | Yes | Yes | Yes | Already enforced | Existing provider retry + recoverable source failure path | Closed by later full-suite PHPUnit PASS |
| API source | API unavailable / 5xx / status 0 | `HELD` / `NOT_READABLE` after retry exhaustion | `RUN_SOURCE_TIMEOUT` | Yes | Yes | Yes | Already enforced | Existing transient failure mapping | Closed by later full-suite PHPUnit PASS |
| API source | API empty response | `HELD` / `NOT_READABLE`; no ingest artifact | `RUN_SOURCE_NO_VALID_DATA` / `SOURCE_PROVIDER_EMPTY_RESPONSE` | Yes | Yes | Yes | Patched | Generic API empty rows now throw `SourceAcquisitionException` | Closed by later full-suite PHPUnit PASS |
| API source | API malformed response | `FAILED` / `NOT_READABLE`; no publication | `RUN_SOURCE_MALFORMED_PAYLOAD` / `RUN_SOURCE_RESPONSE_CHANGED` | Yes | Yes | Yes | Already enforced | Existing parser exceptions | Closed by later full-suite PHPUnit PASS |
| Manual file | File missing | `FAILED` / `NOT_READABLE`; no publication | `RUN_SOURCE_MANUAL_FILE_NOT_FOUND` | Yes | Yes | Yes | Already enforced | Existing manual-file exception | Closed by later full-suite PHPUnit PASS |
| Manual file | File unreadable | `FAILED` / `NOT_READABLE`; no publication | `RUN_SOURCE_MANUAL_FILE_NOT_READABLE` | Yes | Yes | Yes | Already enforced | Existing manual-file exception | Closed by later full-suite PHPUnit PASS |
| Manual file | Manual file empty | `FAILED` / `NOT_READABLE`; no import/promote success | `RUN_SOURCE_MANUAL_FILE_EMPTY` | Yes | Yes | Yes | Patched | Empty CSV/JSON now blocked before telemetry success | Closed by later full-suite PHPUnit PASS |
| Manual file | Invalid format/header/row | `FAILED` / `NOT_READABLE`; no publication | `RUN_SOURCE_MANUAL_FILE_MALFORMED` / `MANUAL_FILE_FORMAT_INVALID` | Yes | Yes | Yes | Already enforced | Existing parser/header guards | Closed by later full-suite PHPUnit PASS |
| Manual file | All rows invalid / zero canonical bars | `FAILED` / `NOT_READABLE`; no artifact publication | `RUN_SOURCE_MANUAL_FILE_NO_VALID_ROWS` | Yes | Yes | Yes | Patched | Ingest blocks `count($validRows) === 0` | Closed by later full-suite PHPUnit PASS |
| Bars ingest | Source returns zero valid rows | `HELD` or `FAILED`, `NOT_READABLE` | `RUN_SOURCE_NO_VALID_DATA` | Yes | Yes | Yes | Patched | Ingest throws before candidate artifact can be treated as valid | Closed by later full-suite PHPUnit PASS |
| Indicators | Zero indicator rows | `FAILED` / `NOT_READABLE` if required dependency missing | `RUN_INDICATORS_MISSING` / `RUN_COMPUTE_FAILED` | Yes | Yes | Yes | Existing contract | Existing compute/finalize dependency guards | Closed by later full-suite PHPUnit PASS |
| Eligibility | Zero eligibility rows | Coverage `NOT_EVALUABLE` / `NOT_READABLE` | `RUN_ELIGIBILITY_MISSING` / `RUN_COVERAGE_NOT_EVALUABLE` | Yes | Yes | Yes | Existing contract | Coverage/finalize non-readable guard | Closed by later full-suite PHPUnit PASS |
| Coverage | Coverage cannot evaluate | `HELD` or `FAILED`, `NOT_READABLE` | `RUN_COVERAGE_NOT_EVALUABLE` | Yes | Yes | Yes | Existing contract | Finalize blocks `NOT_EVALUABLE` | Closed by later full-suite PHPUnit PASS |
| Coverage | Coverage below threshold | `HELD` or `FAILED`, `NOT_READABLE` | `RUN_PARTIAL_DATA` / `COVERAGE_BELOW_THRESHOLD` | Yes | Yes | Yes | Existing contract | Finalize blocks FAIL | Closed by later full-suite PHPUnit PASS |
| Hash | Hash input empty | `BLOCKED` / `NOT_READABLE`; no seal/readable | `DATASET_HASH_MISSING` / `ARTIFACT_EMPTY` | Yes | Yes | Yes | Static enforced | Hash/seal contract + fail-safe static guard | Closed by later full-suite PHPUnit PASS |
| Seal | Seal target empty | `BLOCKED` / `NOT_READABLE`; no readable seal | `RUN_SEAL_PRECONDITION_FAILED` / `EMPTY_ARTIFACT_NOT_READABLE` | Yes | Yes | Yes | Static enforced | Seal/finalize contract + fail-safe static guard | Closed by later full-suite PHPUnit PASS |
| Finalize | Finalize no valid data | `HELD` or `FAILED`, `NOT_READABLE` | `RUN_SOURCE_NO_VALID_DATA` | Yes | Yes | Yes | Patched | Finalize blocks explicit valid count 0 | Closed by later full-suite PHPUnit PASS |
| Publication | Publication candidate empty | `BLOCKED` / `NOT_READABLE`; no current switch | `RUN_SOURCE_NO_VALID_DATA` / `ARTIFACT_EMPTY` | Yes | Yes | Yes | Patched | Ingest/finalize zero-data guard | Closed by later full-suite PHPUnit PASS |
| Pointer | Pointer switch blocked | Preserve old current pointer | `POINTER_PRESERVED_FAIL_SAFE` / existing pointer reason codes | Yes | Yes | Yes | Existing + static enforced | Existing pointer recovery + static guard | Closed by later full-suite PHPUnit PASS |
| Correction | Correction candidate empty | Preserve baseline current pointer | `CORRECTION_BASELINE_POINTER_PRESERVED` / `POINTER_PRESERVED_FAIL_SAFE` | Yes | Yes | Yes | Existing + static enforced | Existing correction baseline preservation | Closed by later full-suite PHPUnit PASS |
| Correction | Correction failed | Preserve baseline current pointer | `CORRECTION_BASELINE_POINTER_PRESERVED` | Yes | Yes | Yes | Existing contract | Existing correction lifecycle guard | Closed by later full-suite PHPUnit PASS |
| Evidence | Evidence incomplete | Evidence export must show missing/fail-safe context | `EVIDENCE_FAIL_SAFE_CONTEXT_INCLUDED` / existing evidence mismatch codes | Yes | Yes | Yes | Static enforced | Evidence context guard | Closed by later full-suite PHPUnit PASS |
| Replay | Expected proof incomplete | Replay mismatch, not PASS | `REPLAY_EXPECTED_PROOF_INCOMPLETE` / `REPLAY_FAIL_SAFE_REASON_MISMATCH` | Yes | Yes | Yes | Existing + static enforced | Replay context guard | Closed by later full-suite PHPUnit PASS |
| Replay | Actual proof incomplete | Replay mismatch, not PASS | `REPLAY_ACTUAL_PROOF_INCOMPLETE` / `REPLAY_FAIL_SAFE_REASON_MISMATCH` | Yes | Yes | Yes | Existing + static enforced | Replay context guard | Closed by later full-suite PHPUnit PASS |
| Command surface | Failure output | Output must expose final status, reason code, source/context counts, pointer preservation | `COMMAND_EXECUTION_FAILED` / source reason code | Yes | Yes | Yes | Existing command surface + static enforced | Command output guard | Closed by later full-suite PHPUnit PASS |
| Read-side | No readable current exists | No raw/staging/latest fallback; explicit not readable / not found | Read-side pointer reason codes | N/A | Yes | Yes | Existing contract | Read-side pointer enforcement | Closed by later full-suite PHPUnit PASS |


<!-- LEGACY_EXTRACT_BODY_END -->
