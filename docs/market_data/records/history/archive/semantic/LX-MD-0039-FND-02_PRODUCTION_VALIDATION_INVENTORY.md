# Legacy Semantic Extract — LX-MD-0039-FND-02

- Source ID: `LS-MD-0039`
- Original path: `audit/PRODUCTION_VALIDATION_INVENTORY.md`
- Original SHA1: `A8D8B95268BF27AB2D2EE5D169FEE87AFCAF4EB7`
- Extract role: `FINDING`
- Source range: `L240-L257`
- Extract body SHA1: `3AB4596DEF3060B574EAA0FB5F1566B9EBBC7DCF`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Failure scenario validation matrix

| Scenario | Runtime/Test Proof Required | Expected State | Reason Code Requirement | Status |
|---|---|---|---|---|
| Coverage below threshold | Coverage test or promote runtime output | HELD / NOT_READABLE or SUCCESS / NOT_READABLE according to policy | `COVERAGE_BELOW_THRESHOLD` or equivalent registered reason | PENDING_RUNTIME_EVIDENCE |
| Provider rate limited | Provider resilience test/runtime output | HELD / NOT_READABLE or FAILED / NOT_READABLE | registered rate-limit reason | PENDING_RUNTIME_EVIDENCE |
| Source unavailable | Source/provider test/runtime output | HELD / NOT_READABLE or FAILED / NOT_READABLE | registered source failure reason | PENDING_RUNTIME_EVIDENCE |
| Manual file invalid | Manual file policy test/runtime output | FAILED / NOT_READABLE or rejected import | registered manual-file invalid reason | PENDING_RUNTIME_EVIDENCE |
| Run lock conflict | Finalize/lock test/runtime output | HELD / NOT_READABLE and pointer preserved | `RUN_LOCK_CONFLICT` or equivalent | PENDING_RUNTIME_EVIDENCE |
| Pointer mismatch | Pointer/finalize test/runtime output | FAILED or HELD, no current switch | pointer mismatch reason | PENDING_RUNTIME_EVIDENCE |
| Publication not sealed | Hash/seal/finalize test/runtime output | NOT_READABLE, no current switch | unsealed publication reason | PENDING_RUNTIME_EVIDENCE |
| Correction baseline invalid | Correction test/runtime output | correction blocked, previous current preserved | correction baseline reason | PENDING_RUNTIME_EVIDENCE |
| Correction already published | Correction lifecycle test/runtime output | blocked/rejected rerun | `CORRECTION_ALREADY_PUBLISHED` or equivalent | PENDING_RUNTIME_EVIDENCE |
| Replay mismatch | Replay verify output | FAIL with mismatch details | replay mismatch reason | PENDING_RUNTIME_EVIDENCE |
| Evidence export incomplete | Evidence test/runtime output | FAILED_VALIDATION or blocked export | evidence incomplete reason | PENDING_RUNTIME_EVIDENCE |
| No readable publication | Read-side/session snapshot output | NOT_READABLE/BLOCKED | no readable publication reason | PENDING_RUNTIME_EVIDENCE |
| Session snapshot blocked | Session snapshot runtime output | BLOCKED, no raw/latest fallback | snapshot blocked reason | PENDING_RUNTIME_EVIDENCE |


<!-- LEGACY_EXTRACT_BODY_END -->
