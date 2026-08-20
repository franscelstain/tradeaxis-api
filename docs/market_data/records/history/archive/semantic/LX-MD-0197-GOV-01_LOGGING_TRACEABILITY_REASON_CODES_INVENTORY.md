# Legacy Semantic Extract — LX-MD-0197-GOV-01

- Source ID: `LS-MD-0197`
- Original path: `ops/LOGGING_TRACEABILITY_REASON_CODES_INVENTORY.md`
- Original SHA1: `A1940E4465EB5BE5C45139CB797981907401A453`
- Extract role: `GOVERNANCE`
- Source range: `L12-L15`
- Extract body SHA1: `B457C8A8CC913F52100B96019A73F4D63D82025F`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Final rule

Market-data lifecycle logging is valid only when every important state transition can be reconstructed from persisted run events, run telemetry, publication/pointer state, correction/replay/evidence context, and registered reason codes. Failure, held, blocked, skipped, not-readable, mismatch, and destructive-operation outcomes must be reason-coded. Happy-path success may keep `reason_code = null` only when the surrounding payload proves the successful context.


<!-- LEGACY_EXTRACT_BODY_END -->
