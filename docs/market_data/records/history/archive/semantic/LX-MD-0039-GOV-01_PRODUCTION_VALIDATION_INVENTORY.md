# Legacy Semantic Extract — LX-MD-0039-GOV-01

- Source ID: `LS-MD-0039`
- Original path: `audit/PRODUCTION_VALIDATION_INVENTORY.md`
- Original SHA1: `A8D8B95268BF27AB2D2EE5D169FEE87AFCAF4EB7`
- Extract role: `GOVERNANCE`
- Source range: `L258-L268`
- Extract body SHA1: `35580F09C734F0E399218B082D052913701D82D7`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Audit docs synchronization rule

- `Production Validation / Manual + Runtime Proof` may be marked READY_FOR_LOCAL_RUNTIME_VALIDATION while only static/container proof exists.
- `PRODUCTION_VALIDATION_CONTRACT` may be ENFORCED while runtime evidence is pending.
- Do not mark this implementation DONE unless targeted ProductionValidation, related targeted suites, full `tests/Unit/MarketData`, artisan list/help, evidence export, and replay runtime proof have actual output.
- Do not mark this contract LOCKED unless targeted and full suite PASS plus artisan/evidence/replay runtime proof are recorded.
- Static guard is not runtime proof.
- PENDING_RUNTIME_EVIDENCE must stay visible until closed by actual output.
- PARTIAL_RUNTIME_PROOF must list exactly which evidence exists and which proof is still missing.
- READY_FOR_LOCAL_RUNTIME_VALIDATION is the correct status when vendor/artisan/PHPUnit cannot be run from the uploaded ZIP.


<!-- LEGACY_EXTRACT_BODY_END -->
